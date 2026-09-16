import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { buildWxr, type WxrPost } from '../src/wxrBuilder.js';

const here = path.dirname(fileURLToPath(import.meta.url));
const outPath = path.resolve(
  here,
  '../../../plugin/tanbiuti/tests/fixtures/sample.xml',
);

const posts: WxrPost[] = [
  {
    postType: 'tanbiuti_cliente',
    title: 'Cliente Fixture Um',
    postDate: '2025-07-19 09:00:00',
    meta: { _tanbiuti_source_id: 1, _tanbiuti_email: 'fixture.um@example.com', _tanbiuti_celular: '11999990001' },
  },
  {
    postType: 'tanbiuti_cliente',
    title: 'Cliente Fixture Dois',
    postDate: '2025-07-19 09:10:00',
    meta: { _tanbiuti_source_id: 2, _tanbiuti_email: 'fixture.dois@example.com', _tanbiuti_celular: '11999990002' },
  },
  {
    postType: 'tanbiuti_comanda',
    title: 'Comanda #1',
    postDate: '2025-07-19 10:00:00',
    meta: {
      _tanbiuti_source_id: 100,
      _tanbiuti_numero: 1,
      _tanbiuti_data: '2025-07-19 00:00:00',
      _tanbiuti_total: 90,
      _tanbiuti_cliente_source_id: 1,
    },
  },
  {
    postType: 'tanbiuti_recibo',
    title: 'Corte Feminino',
    postDate: '2025-07-19 10:00:00',
    meta: {
      _tanbiuti_source_id: 1000,
      _tanbiuti_valor: 90,
      _tanbiuti_comissao: 40,
      _tanbiuti_comanda_source_id: 100,
    },
    terms: [{ taxonomy: 'tanbiuti_recibo_tipo', name: 'salao_servicos' }],
  },
  {
    postType: 'tanbiuti_agendamento',
    title: 'Corte Feminino — Cliente Fixture Um',
    postDate: '2025-07-19 00:00:00',
    meta: {
      _tanbiuti_source_id: 10000,
      _tanbiuti_data: '2025-07-19',
      _tanbiuti_hora_inicio: 570, // 09:30
      _tanbiuti_hora_fim: 630, // 10:30
      _tanbiuti_valor: 90,
      _tanbiuti_servico: 'Corte Feminino',
      _tanbiuti_cliente_source_id: 1,
      _tanbiuti_comanda_source_id: 100,
    },
    terms: [{ taxonomy: 'tanbiuti_agendamento_status', name: 'concluido' }],
  },
];

const xml = buildWxr(posts, {
  siteTitle: 'Tanbiuti (test fixture)',
  siteUrl: 'http://localhost:8888',
  authorLogin: 'admin',
});

await fs.mkdir(path.dirname(outPath), { recursive: true });
await fs.writeFile(outPath, xml);
console.log(`Wrote fixture with ${posts.length} posts to ${outPath}`);
