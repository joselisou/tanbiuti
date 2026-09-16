import type { Booking } from './endpoints/agenda.js';
import type { ComandaDetail } from './endpoints/comanda.js';

/**
 * Client ids to fetch are the union of ids referenced by bookings and by comanda tabs — a comanda
 * can include a walk-in client that never shows up as its own booking.
 */
export function collectClienteIds(bookings: Booking[], comandas: ComandaDetail[]): number[] {
  const ids = new Set<number>();

  for (const booking of bookings) {
    if (booking.salao_cliente_id) ids.add(booking.salao_cliente_id);
  }
  for (const comanda of comandas) {
    if (comanda.tab.salao_cliente_id) ids.add(comanda.tab.salao_cliente_id);
  }

  return Array.from(ids);
}
