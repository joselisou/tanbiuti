import { config } from './config.js';
import { login, type Session } from './auth.js';

const MAX_RETRIES = 3;

function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export class TanbiutiApiClient {
  private session: Session;

  private constructor(session: Session) {
    this.session = session;
  }

  static async create(): Promise<TanbiutiApiClient> {
    const session = await login();
    return new TanbiutiApiClient(session);
  }

  get salonId(): string {
    return this.session.salonId;
  }

  get professionalId(): string {
    return this.session.professionalId;
  }

  /** GET `path` under the source API, decoded as JSON. Retries on 5xx/429 and re-logs in once on 401. */
  async get<T>(path: string, searchParams?: Record<string, string>): Promise<T> {
    const url = new URL(path, config.apiBaseUrl);
    for (const [key, value] of Object.entries(searchParams ?? {})) {
      url.searchParams.set(key, value);
    }

    let attempt = 0;
    let reloggedIn = false;

    for (;;) {
      attempt += 1;
      const response = await fetch(url, {
        headers: {
          authorization: this.session.token,
          accept: 'application/json',
        },
      });

      if (response.status === 401 && !reloggedIn) {
        reloggedIn = true;
        this.session = await login();
        continue;
      }

      if ((response.status === 429 || response.status >= 500) && attempt <= MAX_RETRIES) {
        const backoffMs = 500 * 2 ** (attempt - 1);
        await new Promise((resolve) => setTimeout(resolve, backoffMs));
        continue;
      }

      if (!response.ok) {
        throw new Error(`GET ${url} failed: ${response.status} ${await response.text()}`);
      }

      const body = (await response.json()) as { code: number; data: T };

      // Deliberately gentle on the source system's production API: a fixed pause after every successful
      // call, on top of low concurrency (see AGENDA/COMANDA/CLIENTE_CONCURRENCY in index.ts),
      // to avoid bursts that could trip their rate limiting or add load to their system.
      await sleep(config.requestDelayMs);

      return body.data;
    }
  }
}
