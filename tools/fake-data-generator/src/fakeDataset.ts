import { Faker, en, pt_BR } from '@faker-js/faker';

// Fixed seed → deterministic output, so the versioned data/fake/*.json and the generated WXR
// don't churn on every regeneration unless the generator itself changes.
const SEED = 20250719;

export interface FakeTabItem {
  id: number;
  comanda_id: number;
  tipo: string;
  item: string;
  valor: number;
  desconto: number;
  quantidade: number;
  custo: number;
  comissao: number;
  profissional_id: number;
  status: number;
  datacad: string;
}

export interface FakeComanda {
  id: number;
  numero: number;
  data: string;
  salao_cliente_id: number;
  tab_items: FakeTabItem[];
}

export interface FakeCliente {
  id: number;
  nome: string;
  email: string;
  telefone: string;
  celular: string;
  datacad: string;
}

export interface FakeBooking {
  id: number;
  servico_id: number;
  profissional_id: number;
  salao_cliente_id: number;
  data: string;
  hora_ini: number;
  hora_fim: number;
  valor: number;
  cliente_nome: string;
  comanda_id: number;
  status: number;
  status_agendamento: string;
  servicos: string;
}

export interface FakeDataset {
  clientes: FakeCliente[];
  comandas: FakeComanda[];
  bookings: FakeBooking[];
}

const SERVICES = [
  { name: 'Corte Feminino', valor: 90, comissao: 40 },
  { name: 'Escova', valor: 79, comissao: 50 },
  { name: 'Coloração', valor: 250, comissao: 35 },
  { name: 'Sobrancelha Design', valor: 60, comissao: 50 },
  { name: 'Maquiagem', valor: 198, comissao: 70 },
  { name: 'Hidratação', valor: 120, comissao: 45 },
];

const PROFESSIONAL_ID = 900001;
const CLIENT_COUNT = 25;
const DAYS = 21;
const START_DATE = '2025-07-19';

function toIsoDate(baseIso: string, offsetDays: number): string {
  const date = new Date(`${baseIso}T00:00:00Z`);
  date.setUTCDate(date.getUTCDate() + offsetDays);
  return date.toISOString().slice(0, 10);
}

/** Generates a small, deterministic fake dataset with the same shape as the real extracted data. */
export function generateFakeDataset(): FakeDataset {
  const faker = new Faker({ locale: [pt_BR, en] });
  faker.seed(SEED);

  const clientes: FakeCliente[] = Array.from({ length: CLIENT_COUNT }, (_, i) => {
    const firstName = faker.person.firstName();
    const lastName = faker.person.lastName();
    return {
      id: 900100 + i,
      nome: `${firstName} ${lastName}`.toUpperCase(),
      email: faker.internet.email({ firstName, lastName }).toLowerCase(),
      telefone: faker.phone.number({ style: 'national' }),
      celular: faker.phone.number({ style: 'national' }),
      datacad: `${toIsoDate(START_DATE, -faker.number.int({ min: 0, max: 365 }))} 09:00:00`,
    };
  });

  const comandas: FakeComanda[] = [];
  const bookings: FakeBooking[] = [];
  let nextComandaId = 900200;
  let nextTabItemId = 900300;
  let nextBookingId = 900400;
  let dailyCounter = 1;

  for (let day = 0; day < DAYS; day += 1) {
    const isoDate = toIsoDate(START_DATE, day);
    // Skip most Sundays, like a real salon would.
    if (new Date(`${isoDate}T00:00:00Z`).getUTCDay() === 0 && faker.number.int({ min: 0, max: 4 }) !== 0) {
      continue;
    }

    const bookingsToday = faker.number.int({ min: 2, max: 6 });

    for (let i = 0; i < bookingsToday; i += 1) {
      const cliente = faker.helpers.arrayElement(clientes);
      const itemCount = faker.number.int({ min: 1, max: 2 });
      const items = faker.helpers.arrayElements(SERVICES, itemCount);

      const comandaId = nextComandaId++;
      const tabItems: FakeTabItem[] = items.map((service) => ({
        id: nextTabItemId++,
        comanda_id: comandaId,
        tipo: 'salao_servicos',
        item: service.name,
        valor: service.valor,
        desconto: 0,
        quantidade: 1,
        custo: 0,
        comissao: service.comissao,
        profissional_id: PROFESSIONAL_ID,
        // Real repasses lag behind the service date, so a slice of recent items are
        // still unpaid (status 0) — mirrors the "recibo" payment-status field seen in
        // the real API's tab_items.
        status: faker.number.int({ min: 0, max: 9 }) === 0 ? 0 : 1,
        datacad: `${isoDate} 10:00:00`,
      }));

      comandas.push({
        id: comandaId,
        numero: dailyCounter++,
        data: `${isoDate} 00:00:00`,
        salao_cliente_id: cliente.id,
        tab_items: tabItems,
      });

      const startMinute = faker.number.int({ min: 480, max: 1080 });
      const duration = faker.number.int({ min: 30, max: 90 });

      bookings.push({
        id: nextBookingId++,
        servico_id: nextTabItemId,
        profissional_id: PROFESSIONAL_ID,
        salao_cliente_id: cliente.id,
        data: isoDate,
        hora_ini: startMinute,
        hora_fim: startMinute + duration,
        valor: tabItems.reduce((sum, item) => sum + item.valor, 0),
        cliente_nome: cliente.nome,
        comanda_id: comandaId,
        status: 4,
        status_agendamento: 'concluido',
        servicos: items.map((s) => s.name).join(', '),
      });
    }

    dailyCounter = 1;
  }

  return { clientes, comandas, bookings };
}
