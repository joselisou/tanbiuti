import { describe, expect, it } from 'vitest';
import { collectClienteIds } from './collectClienteIds.js';
import type { Booking } from './endpoints/agenda.js';
import type { ComandaDetail } from './endpoints/comanda.js';

function booking(overrides: Partial<Booking>): Booking {
  return {
    id: 1,
    servico_id: 1,
    profissional_id: 1,
    salao_id: 1,
    salao_cliente_id: 1,
    data: '2025-07-19',
    hora_ini: 0,
    hora_fim: 0,
    valor: 0,
    cliente_nome: '',
    cliente_tel: '',
    comanda_id: null,
    status: 1,
    status_agendamento: '',
    obs: '',
    servicos: '',
    ...overrides,
  };
}

function comanda(salaoClienteId: number): ComandaDetail {
  return {
    tab: { id: 1, numero: 1, data: '2025-07-19', caixa_id: 1, salao_cliente_id: salaoClienteId, tab_items: [] },
    salon_client: { id: salaoClienteId, nome: '', apelido: null },
    salon_incoming_out_going: [],
    salon_invoice: [],
  };
}

describe('collectClienteIds', () => {
  it('deduplicates ids shared between bookings and comandas', () => {
    const ids = collectClienteIds(
      [booking({ salao_cliente_id: 10 }), booking({ salao_cliente_id: 20 })],
      [comanda(10)],
    );

    expect(ids.sort()).toEqual([10, 20]);
  });

  it('includes a client that only appears on a comanda tab (walk-in, no booking)', () => {
    const ids = collectClienteIds([booking({ salao_cliente_id: 10 })], [comanda(99)]);

    expect(ids.sort((a, b) => a - b)).toEqual([10, 99]);
  });

  it('ignores falsy client ids', () => {
    const ids = collectClienteIds([booking({ salao_cliente_id: 0 })], []);

    expect(ids).toEqual([]);
  });

  it('returns an empty array for no bookings and no comandas', () => {
    expect(collectClienteIds([], [])).toEqual([]);
  });
});
