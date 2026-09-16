import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { generateFakeDataset } from './fakeDataset.js';
import { toWxrPosts } from './toWxrPosts.js';
// Direct relative import of the sibling tool's TS source — see the note in toWxrPosts.ts on why
// these packages reference each other by path instead of an npm dependency.
import { buildWxr } from '../../wxr-builder/src/wxrBuilder.js';

const here = path.dirname(fileURLToPath(import.meta.url));
const dataFakeRoot = path.resolve(here, '../../../data/fake');

async function main(): Promise<void> {
  const dataset = generateFakeDataset();

  await fs.mkdir(path.join(dataFakeRoot, 'json'), { recursive: true });
  await fs.writeFile(
    path.join(dataFakeRoot, 'json', 'dataset.json'),
    JSON.stringify(dataset, null, 2),
  );

  const posts = toWxrPosts(dataset);
  const xml = buildWxr(posts, {
    siteTitle: 'Tanbiuti (dataset fake)',
    siteUrl: 'http://localhost:8888',
    authorLogin: 'admin',
  });

  await fs.writeFile(path.join(dataFakeRoot, 'tanbiuti-fake-dataset.xml'), xml);

  console.log(
    `Generated fake dataset: ${dataset.clientes.length} clientes, ${dataset.comandas.length} comandas, ${dataset.bookings.length} agendamentos → ${posts.length} posts WXR.`,
  );
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
