import type { TanbiutiApiClient } from '../client.js';

export interface TabItem {
  id: number;
  comanda_id: number;
  tipo: string;
  tipo_id: number;
  item: string;
  valor: number;
  desconto: number;
  acrescimo: number;
  quantidade: number;
  custo: number;
  comissao: number;
  profissional_id: number;
  status: number;
  datacad: string;
  [key: string]: unknown;
}

export interface ComandaDetail {
  tab: {
    id: number;
    numero: number;
    data: string;
    caixa_id: number;
    salao_cliente_id: number;
    tab_items: TabItem[];
  };
  salon_client: { id: number; nome: string; apelido: string | null };
  salon_incoming_out_going: Array<{ tipo_pagamento: string; valor: number; [key: string]: unknown }>;
  salon_invoice: unknown[];
}

/** Fetches the full detail (service line items, commission %, payments) of a single comanda. */
export async function fetchComandaDetail(
  client: TanbiutiApiClient,
  comandaId: number,
): Promise<ComandaDetail> {
  return client.get<ComandaDetail>(`/salao/${client.salonId}/comanda/${comandaId}`);
}
