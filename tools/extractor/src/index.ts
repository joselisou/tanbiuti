import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import pLimit from 'p-limit';
import { config } from './config.js';
import { TanbiutiApiClient } from './client.js';
import { eachDate, todayIso } from './dateRange.js';
import { fetchAgendaDay, type Booking } from './endpoints/agenda.js';
import { fetchComandaDetail, type ComandaDetail } from './endpoints/comanda.js';
import { fetchClienteDetail, type SalonClient } from './endpoints/cliente.js';
import { collectClienteIds } from './collectClienteIds.js';

const here = path.dirname(fileURLToPath(import.meta.url));
const dataRoot = path.resolve(here, '../../../data/real');

// Sequential on purpose — this hits the source system's real production API, not a sandbox. Combined with the
// fixed per-request delay in TanbiutiApiClient.get(), this keeps load light and spread out instead
// of bursty, to avoid tripping rate limits or adding noticeable load to their system.
const AGENDA_CONCURRENCY = 1;
const COMANDA_CONCURRENCY = 1;
const CLIENTE_CONCURRENCY = 1;

async function readJsonIfExists<T>(filePath: string): Promise<T | null> {
  try {
    return JSON.parse(await fs.readFile(filePath, 'utf8')) as T;
  } catch (error) {
    if ((error as NodeJS.ErrnoException).code === 'ENOENT') return null;
    throw error;
  }
}

async function writeJson(filePath: string, data: unknown): Promise<void> {
  await fs.mkdir(path.dirname(filePath), { recursive: true });
  await fs.writeFile(filePath, JSON.stringify(data, null, 2));
}

interface ExtractionError {
  stage: 'agenda' | 'comanda' | 'cliente';
  id: string;
  message: string;
}

/**
 * Collects per-item failures instead of letting one bad record (e.g. a 404 on a client that no
 * longer exists) abort the whole multi-hour run. Every failure is written immediately to
 * `errorLogPath`, so a hard crash elsewhere still leaves a usable log on disk.
 */
class ErrorLog {
  readonly errors: ExtractionError[] = [];

  constructor(private readonly filePath: string) {}

  async init(): Promise<void> {
    await fs.mkdir(path.dirname(this.filePath), { recursive: true });
    await fs.writeFile(this.filePath, `Extraction errors — started ${new Date().toISOString()}\n`);
  }

  async record(stage: ExtractionError['stage'], id: string | number, error: unknown): Promise<void> {
    const message = error instanceof Error ? error.message : String(error);
    this.errors.push({ stage, id: String(id), message });
    await fs.appendFile(this.filePath, `[${new Date().toISOString()}] ${stage} ${id}: ${message}\n`);
    console.error(`  ✖ ${stage} ${id}: ${message}`);
  }
}

interface CliOptions {
  from: string;
  to: string;
  force: boolean;
}

function parseArgs(argv: string[]): CliOptions {
  const args = new Map<string, string>();
  for (const arg of argv) {
    const [key, value] = arg.replace(/^--/, '').split('=');
    args.set(key, value ?? 'true');
  }
  return {
    from: args.get('from') ?? config.extractionStartDate,
    to: args.get('to') ?? todayIso(),
    force: args.get('force') === 'true',
  };
}

function formatDuration(ms: number): string {
  const totalSeconds = Math.round(ms / 1000);
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  return `${minutes}m ${seconds}s`;
}

async function main(): Promise<void> {
  const startedAt = new Date();
  const { from, to, force } = parseArgs(process.argv.slice(2));
  console.log(`Started at ${startedAt.toLocaleString()}`);
  console.log(`Extracting source data from ${from} to ${to} (force=${force})`);

  const errorLogPath = path.join(dataRoot, 'extraction-errors.log');
  const errorLog = new ErrorLog(errorLogPath);
  await errorLog.init();

  const client = await TanbiutiApiClient.create();
  console.log(`Logged in. salon_id=${client.salonId} professional_id=${client.professionalId}`);

  const agendaLimit = pLimit(AGENDA_CONCURRENCY);
  const dates = eachDate(from, to);
  const allBookings: Booking[] = [];
  const comandaIds = new Set<number>();

  await Promise.all(
    dates.map((isoDate) =>
      agendaLimit(async () => {
        try {
          const rawPath = path.join(dataRoot, 'raw', 'agenda', `${isoDate}.json`);
          let day = await (force ? Promise.resolve(null) : readJsonIfExists<{ bookings: Booking[] }>(rawPath));

          if (!day) {
            day = await fetchAgendaDay(client, isoDate);
            await writeJson(rawPath, day);
          }

          for (const booking of day.bookings) {
            allBookings.push(booking);
            if (booking.comanda_id) comandaIds.add(booking.comanda_id);
          }
        } catch (error) {
          await errorLog.record('agenda', isoDate, error);
        }
      }),
    ),
  );

  console.log(`Agenda: ${allBookings.length} bookings across ${dates.length} days, ${comandaIds.size} unique comandas.`);

  const comandaLimit = pLimit(COMANDA_CONCURRENCY);
  const comandas: ComandaDetail[] = [];

  await Promise.all(
    Array.from(comandaIds).map((comandaId) =>
      comandaLimit(async () => {
        try {
          const rawPath = path.join(dataRoot, 'raw', 'comandas', `${comandaId}.json`);
          let detail = await (force ? Promise.resolve(null) : readJsonIfExists<ComandaDetail>(rawPath));

          if (!detail) {
            detail = await fetchComandaDetail(client, comandaId);
            await writeJson(rawPath, detail);
          }

          comandas.push(detail);
        } catch (error) {
          await errorLog.record('comanda', comandaId, error);
        }
      }),
    ),
  );

  console.log(`Comandas: ${comandas.length} fetched.`);

  const clienteIds = collectClienteIds(allBookings, comandas);
  const clienteLimit = pLimit(CLIENTE_CONCURRENCY);
  const clientes: SalonClient[] = [];

  await Promise.all(
    clienteIds.map((clienteId) =>
      clienteLimit(async () => {
        try {
          const rawPath = path.join(dataRoot, 'raw', 'clientes', `${clienteId}.json`);
          let detail = await (force ? Promise.resolve(null) : readJsonIfExists<SalonClient>(rawPath));

          if (!detail) {
            detail = await fetchClienteDetail(client, clienteId);
            await writeJson(rawPath, detail);
          }

          clientes.push(detail);
        } catch (error) {
          await errorLog.record('cliente', clienteId, error);
        }
      }),
    ),
  );

  console.log(`Clientes: ${clientes.length} fetched.`);

  await writeJson(path.join(dataRoot, 'normalized', 'agenda.json'), allBookings);
  await writeJson(path.join(dataRoot, 'normalized', 'comandas.json'), comandas);
  await writeJson(path.join(dataRoot, 'normalized', 'clientes.json'), clientes);

  const finishedAt = new Date();

  await writeJson(path.join(dataRoot, 'extraction-summary.json'), {
    startedAt: startedAt.toISOString(),
    finishedAt: finishedAt.toISOString(),
    range: { from, to },
    counts: {
      days: dates.length,
      bookings: allBookings.length,
      comandas: comandas.length,
      clientes: clientes.length,
    },
    errors: errorLog.errors,
  });

  console.log(
    `Finished at ${finishedAt.toLocaleString()} (took ${formatDuration(finishedAt.getTime() - startedAt.getTime())}).`,
  );
  console.log('See data/real/extraction-summary.json for counts.');

  if (errorLog.errors.length > 0) {
    const byStage = errorLog.errors.reduce<Record<string, number>>((acc, { stage }) => {
      acc[stage] = (acc[stage] ?? 0) + 1;
      return acc;
    }, {});
    const breakdown = Object.entries(byStage)
      .map(([stage, count]) => `${count} ${stage}`)
      .join(', ');
    console.log(`⚠ ${errorLog.errors.length} item(s) failed and were skipped (${breakdown}).`);
    console.log(`  See ${errorLogPath} for details.`);
  } else {
    console.log('No errors.');
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
