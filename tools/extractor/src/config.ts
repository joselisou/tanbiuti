import { config as loadEnv } from 'dotenv';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
// Repo root is two levels up from tools/extractor/src.
loadEnv({ path: path.resolve(here, '../../../.env') });

function requireEnv(name: string): string {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Missing required env var: ${name}`);
  }
  return value;
}

export const config = {
  loginUrl: requireEnv('SOURCE_LOGIN_URL'),
  email: requireEnv('SOURCE_EMAIL'),
  password: requireEnv('SOURCE_PASSWORD'),
  // SOURCE_API_URL is a bare host (no scheme), matching how it reads in .env; the scheme is
  // added here since `new URL(path, base)` in client.ts needs an absolute URL as its base.
  apiBaseUrl: `https://${requireEnv('SOURCE_API_URL')}`,
  extractionStartDate: '2025-07-19',
  /** Fixed pause after every successful API call, to spread load gently on the source system's production API. */
  requestDelayMs: 400,
};
