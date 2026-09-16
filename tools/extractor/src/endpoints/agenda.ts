import type { TanbiutiApiClient } from '../client.js';

export interface Booking {
  id: number;
  servico_id: number;
  profissional_id: number;
  salao_id: number;
  salao_cliente_id: number;
  data: string;
  hora_ini: number;
  hora_fim: number;
  valor: number;
  cliente_nome: string;
  cliente_tel: string;
  comanda_id: number | null;
  status: number;
  status_agendamento: string;
  obs: string;
  servicos: string;
  [key: string]: unknown;
}

interface AgendaDayResponse {
  journey: unknown[];
  blocked: unknown[];
  bookings: Booking[];
}

/** Fetches a single day of the professional's agenda, including historical dates. */
export async function fetchAgendaDay(
  client: TanbiutiApiClient,
  isoDate: string,
): Promise<AgendaDayResponse> {
  return client.get<AgendaDayResponse>(
    `/salao/${client.salonId}/agenda/profissional/${client.professionalId}`,
    { data: isoDate },
  );
}
