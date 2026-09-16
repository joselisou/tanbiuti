import type { FakeDataset } from './fakeDataset.js';

// Mirrors the WxrPost shape from @tanbiuti/wxr-builder without adding a package dependency
// (both tools live in this monorepo-lite layout and import each other's TS sources directly).
export interface WxrPostLike {
  postType: string;
  title: string;
  postDate: string;
  meta?: Record<string, string | number | null>;
  terms?: Array<{ taxonomy: string; name: string }>;
}

/** Maps a fake (or real, once shapes are confirmed identical) dataset into flat WXR posts. */
export function toWxrPosts(dataset: FakeDataset): WxrPostLike[] {
  const posts: WxrPostLike[] = [];

  for (const cliente of dataset.clientes) {
    posts.push({
      postType: 'tanbiuti_cliente',
      title: cliente.nome,
      postDate: cliente.datacad,
      meta: {
        _tanbiuti_source_id: cliente.id,
        _tanbiuti_email: cliente.email,
        _tanbiuti_telefone: cliente.telefone,
        _tanbiuti_celular: cliente.celular,
      },
    });
  }

  for (const comanda of dataset.comandas) {
    const total = comanda.tab_items.reduce((sum, item) => sum + item.valor - item.desconto, 0);

    posts.push({
      postType: 'tanbiuti_comanda',
      title: `Comanda #${comanda.numero}`,
      postDate: comanda.data,
      meta: {
        _tanbiuti_source_id: comanda.id,
        _tanbiuti_numero: comanda.numero,
        _tanbiuti_data: comanda.data,
        _tanbiuti_total: total,
        _tanbiuti_cliente_source_id: comanda.salao_cliente_id,
      },
    });

    for (const item of comanda.tab_items) {
      posts.push({
        postType: 'tanbiuti_recibo',
        title: item.item,
        postDate: item.datacad,
        meta: {
          _tanbiuti_source_id: item.id,
          _tanbiuti_valor: item.valor,
          _tanbiuti_desconto: item.desconto,
          _tanbiuti_comissao: item.comissao,
          _tanbiuti_profissional_id: item.profissional_id,
          _tanbiuti_status: item.status,
          _tanbiuti_comanda_source_id: item.comanda_id,
        },
        terms: [{ taxonomy: 'tanbiuti_recibo_tipo', name: item.tipo }],
      });
    }
  }

  for (const booking of dataset.bookings) {
    posts.push({
      postType: 'tanbiuti_agendamento',
      title: `${booking.servicos} — ${booking.cliente_nome}`,
      postDate: `${booking.data} 00:00:00`,
      meta: {
        _tanbiuti_source_id: booking.id,
        _tanbiuti_data: booking.data,
        _tanbiuti_hora_inicio: booking.hora_ini,
        _tanbiuti_hora_fim: booking.hora_fim,
        _tanbiuti_valor: booking.valor,
        _tanbiuti_servico: booking.servicos,
        _tanbiuti_cliente_source_id: booking.salao_cliente_id,
        _tanbiuti_comanda_source_id: booking.comanda_id,
      },
      terms: [{ taxonomy: 'tanbiuti_agendamento_status', name: booking.status_agendamento }],
    });
  }

  return posts;
}
