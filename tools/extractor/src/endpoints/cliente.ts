import type { TanbiutiApiClient } from '../client.js';

export interface SalonClient {
  id: number;
  salao_id: number;
  codigo: number;
  nome: string;
  nome_social: string | null;
  apelido: string | null;
  email: string | null;
  telefone: string | null;
  celular: string | null;
  datanasc: string | null;
  sexo: string | null;
  cpf: string | null;
  datacad: string;
  UA: string;
  data_primeira_comanda: string | null;
  data_ultima_comanda: string | null;
  [key: string]: unknown;
}

interface ClienteDetailResponse {
  salonClient: SalonClient;
}

/** Fetches full detail for a single salon client (name, contact info, CRM fields). */
export async function fetchClienteDetail(
  client: TanbiutiApiClient,
  salaoClienteId: number,
): Promise<SalonClient> {
  const response = await client.get<ClienteDetailResponse>(
    `/salao/${client.salonId}/cliente/${salaoClienteId}`,
  );
  return response.salonClient;
}
