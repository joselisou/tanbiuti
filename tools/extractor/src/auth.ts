import { chromium } from 'playwright';
import { config } from './config.js';

export interface Session {
  token: string;
  salonId: string;
  salonSlug: string;
  professionalId: string;
}

/**
 * Logs into the source app's terminal with a headless browser and reads the session
 * data straight out of localStorage — the SPA stores the JWT and the salon/professional
 * ids there under plain keys (`token`, `salon_id`, `salon_slug`, `professional_id`)
 * after a successful login, so there is no need to intercept network responses
 * or decode the JWT payload ourselves.
 */
export async function login(): Promise<Session> {
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage();
    await page.goto(config.loginUrl, { waitUntil: 'networkidle' });

    await page.getByPlaceholder('Digite aqui...').fill(config.email);
    await page.getByPlaceholder('Digite sua senha...').fill(config.password);
    await page.getByRole('button', { name: 'Acessar conta' }).click();

    await page.waitForURL(/\/agenda$/, { timeout: 30_000 });

    const session = await page.evaluate(() => ({
      token: localStorage.getItem('token'),
      salonId: localStorage.getItem('salon_id'),
      salonSlug: localStorage.getItem('salon_slug'),
      professionalId: localStorage.getItem('professional_id'),
    }));

    if (!session.token || !session.salonId || !session.professionalId || !session.salonSlug) {
      throw new Error(
        `Login succeeded but session data is incomplete: ${JSON.stringify({
          hasToken: Boolean(session.token),
          salonId: session.salonId,
          salonSlug: session.salonSlug,
          professionalId: session.professionalId,
        })}`,
      );
    }

    return {
      token: session.token,
      salonId: session.salonId,
      salonSlug: session.salonSlug,
      professionalId: session.professionalId,
    };
  } finally {
    await browser.close();
  }
}
