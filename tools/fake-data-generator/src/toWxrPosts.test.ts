import { describe, expect, it } from 'vitest';
import { toWxrPosts } from './toWxrPosts.js';
import type { FakeDataset } from './fakeDataset.js';

const dataset: FakeDataset = {
  clientes: [
    { id: 1, nome: 'Cliente Um', email: 'a@example.com', telefone: '', celular: '11999999999', datacad: '2025-07-19 09:00:00' },
  ],
  comandas: [
    {
      id: 100,
      numero: 1,
      data: '2025-07-19 00:00:00',
      salao_cliente_id: 1,
      tab_items: [
        { id: 1000, comanda_id: 100, tipo: 'salao_servicos', item: 'Corte', valor: 90, desconto: 10, quantidade: 1, custo: 0, comissao: 40, profissional_id: 900001, status: 1, datacad: '2025-07-19 10:00:00' },
      ],
    },
  ],
  bookings: [
    { id: 500, servico_id: 1, profissional_id: 900001, salao_cliente_id: 1, data: '2025-07-19', hora_ini: 600, hora_fim: 660, valor: 90, cliente_nome: 'Cliente Um', comanda_id: 100, status: 4, status_agendamento: 'concluido', servicos: 'Corte' },
  ],
};

describe('toWxrPosts', () => {
  const posts = toWxrPosts(dataset);

  it('produces one post per cliente, comanda, tab_item and booking', () => {
    // 1 cliente + 1 comanda + 1 tab_item (tanbiuti_recibo) + 1 booking = 4
    expect(posts).toHaveLength(4);
    expect(posts.map((p) => p.postType).sort()).toEqual([
      'tanbiuti_agendamento',
      'tanbiuti_cliente',
      'tanbiuti_comanda',
      'tanbiuti_recibo',
    ]);
  });

  it('computes comanda total net of discounts', () => {
    const comandaPost = posts.find((p) => p.postType === 'tanbiuti_comanda')!;
    expect(comandaPost.meta?._tanbiuti_total).toBe(80); // 90 - 10 desconto
  });

  it('links tanbiuti_recibo back to its comanda via source id', () => {
    const reciboPost = posts.find((p) => p.postType === 'tanbiuti_recibo')!;
    expect(reciboPost.meta?._tanbiuti_comanda_source_id).toBe(100);
    expect(reciboPost.meta?._tanbiuti_comissao).toBe(40);
  });

  it('tags tanbiuti_agendamento with its status as a taxonomy term', () => {
    const agendamentoPost = posts.find((p) => p.postType === 'tanbiuti_agendamento')!;
    expect(agendamentoPost.terms).toEqual([{ taxonomy: 'tanbiuti_agendamento_status', name: 'concluido' }]);
  });
});
