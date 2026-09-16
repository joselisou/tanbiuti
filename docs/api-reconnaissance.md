# Reconhecimento da API do sistema de origem

> Documento vivo. Contém apenas **shapes/schemas** dos endpoints, com valores de exemplo genéricos ou anonimizados. **Nunca** cole aqui payloads reais com nome/telefone/e-mail/valores de clientes reais — use o dataset fake (`data/fake/`) para exemplos.

## Autenticação

Login em `SOURCE_LOGIN_URL` (SPA cujo path de login termina em `/login/<salao-slug>`). Após login bem-sucedido e redirecionamento para `/agenda`, os seguintes dados ficam disponíveis diretamente em `localStorage` (não é preciso decodificar o JWT nem interceptar rede):

| Chave localStorage | Conteúdo |
|---|---|
| `token` | JWT Bearer, usado no header `authorization` de toda chamada à API (`SOURCE_API_URL`) (~7 dias de validade) |
| `salon_id` | ID numérico do salão |
| `salon_slug` | Slug do salão (igual ao path da URL de login) |
| `professional_id` / `professionalSelectedId` | ID numérico do profissional logado |
| `is_logged` | `"true"` quando a sessão está ativa |

Estratégia de extração recomendada: Playwright preenche e envia o formulário de login, aguarda navegação para `/agenda`, e lê essas chaves via `page.evaluate()`. Não é necessário interceptar respostas de rede do login.

`GET /auth/validate-session` é chamado a cada navegação da SPA para revalidar a sessão — útil para o extractor checar rapidamente se o token ainda é válido antes de um lote de chamadas.

**Observação operacional**: o sistema de origem parece permitir só uma sessão ativa por conta — rodar o extractor (que faz seu próprio login) invalidou o token de uma aba do navegador logada com a mesma conta simultaneamente. Não rodar o extractor e navegar manualmente logado ao mesmo tempo com o mesmo usuário.

## Endpoints confirmados

Todas as chamadas usam header `authorization: <token>` e `accept: application/json`. Base: `https://<SOURCE_API_URL>` (ver `.env`).

### Agenda — fonte primária de histórico (por dia)

`GET /salao/{salao_id}/agenda/profissional/{profissional_id}?data=YYYY-MM-DD`

Confirmado retornar **histórico real** (testado com data de 2025-08-15). Só aceita um dia por chamada — precisa iterar dia a dia no range 2025-07-19 → hoje.

```
data.journey[]   // blocos de expediente do profissional (id, data_inicio, data_fim, hora_inicio/fim em minutos, semana, tipo, status)
data.blocked[]   // horários bloqueados (schema ainda não observado com itens — pode vir vazio)
data.bookings[]  // agendamentos do dia:
  id, servico_id, profissional_id, salao_id, id_profissional_servico,
  salao_cliente_id, data, hora_ini, hora_fim,   // horários em minutos desde 00:00
  valor, cliente_nome, cliente_tel,
  comanda_id,        // <- chave para buscar o detalhe financeiro (ver seção Comanda)
  status, status_agendamento, obs, servicos,     // nome do(s) serviço(s), string
  salon_service: {   // definição do serviço no catálogo do salão
    id, categoria_id, categoria_mkt_id, servico, descricao,
    tempo, valor, comissao,   // "comissao" aqui é o % padrão do serviço, pode ser sobrescrito por item na comanda
    tipo_valor, status, ...
  },
  home_care, recurrence, salon_tags
```

`GET /salao/{salao_id}/horario_estabelecimento?data=YYYY-MM-DD` — horário de funcionamento do salão nesse dia (schema simples, não detalhado ainda — baixo impacto no BI).

### Comanda — detalhe financeiro e comissão granular

`GET /salao/{salao_id}/comanda/{comanda_id}` (singular — **não** `/comandas/{id}`, que dá 404)

Esta é a fonte granular real de comissão, não `/comissoes` nem `/recibo/lista-por-datas` (ver abaixo por quê).

```
data.tab: {
  id, numero, data, caixa_id, salao_cliente_id,
  tab_items: [{
    id, comanda_id, tipo, tipo_id, item,          // "item" = nome do serviço/produto
    valor, desconto, acrescimo, quantidade, custo,
    comissao,             // % de comissão efetivo deste lançamento
    profissional_id,      // profissional que prestou ESTE item (pode diferir de outros itens da mesma comanda)
    assistente1, assistente2, comissao1, comissao2,
    status, datacad
  }],
  salon_client: { id, nome, apelido },
  salon_incoming_out_going: [{ tipo, tipo_id, tipo_pagamento, valor, parcela, bandeira_id, informacao, ... }],  // formas de pagamento usadas
  salon_invoice: []
}
```

**Estratégia**: iterar Agenda dia a dia → coletar `comanda_id` únicos dos `bookings[]` → buscar detalhe de cada comanda uma vez (dedupe por id, já que uma comanda pode aparecer em mais de um dia/booking) → filtrar `tab_items` onde `profissional_id` == profissional da JOSI para os valores de comissão dela.

### Endpoints que **não** servem para extração histórica (mantidos aqui só para não serem reinventados)

- `GET /salao/{salao_id}/profissional/{profissional_id}/comandas` — **ignora** `begin_date`/`end_date` (testado); sempre retorna a mesma janela pequena e recente (comandas em aberto/dos últimos dias). Não usar para histórico — usar Agenda + Comanda detail (acima).
- `GET /salao/{salao_id}/profissional/{profissional_id}/comissoes?begin_date&end_date&search_type=todos` — retorna só um **agregado** (`commissions.totals[]` com `valor`, `valor_cobrado`, `valor_pago_split`, `valor_nao_pago`, etc.), sem linhas individuais. Útil só como conferência (somar os `tab_items` de comissão da JOSI no período e comparar com este total).
- `GET /salao/{salao_id}/profissional/{profissional_id}/recibo/lista-por-datas?begin_date&end_date` — lista de **recibos de repasse já pagos**; retornou vazio mesmo num range com comissão pendente de pagamento. Não é a fonte granular de comissão (é downstream de pagamento efetivo do repasse, não do lançamento do serviço).

### Clientes

`GET /salao/{salao_id}/clientes?page=N` — **paginado, escopo do SALÃO INTEIRO** (não filtrado por profissional): `per_page=30`, ~433 páginas, total observado ~12.967 clientes.

```
data.salonClients: {
  current_page, data: [{
    id, salao_id, cliente_id, codigo, nome, nome_social, apelido,
    email, telefone, celular, ddi_telefone, ddi_celular,
    datanasc, sexo, cpf, rg, profissao,
    endereco, numero, bairro, cod_cidade, cidade, estado, cep, complemento,
    obs, como_conheceu, responsavel, responsavel_ua,
    datacad, UA,                          // criado em / atualizado em
    data_primeira_comanda, data_ultima_comanda,
    favorito, agendamento_online, email_mkt, sms_mkt, whatsapp_mkt, emissao_nota
  }],
  first_page_url, last_page_url, last_page, next_page_url, prev_page_url,
  path, per_page, from, to, total
}
```

**Decisão de escopo**: como este endpoint não é filtrável por profissional, o extractor **não** importa o cadastro inteiro do salão — extrai apenas os `salao_cliente_id` que aparecem nos `bookings` da JOSI no período (2025-07-19 → hoje).

**Endpoint de detalhe confirmado** (evita paginar as ~433 páginas do `/clientes`):

`GET /salao/{salao_id}/cliente/{salao_cliente_id}` (singular — mesmo padrão de `/comanda/{id}`)

```
data.salonClient: {
  id, salao_id, cliente_id, codigo, nome, nome_social, apelido,
  email, telefone, celular, ddi_telefone, ddi_celular,
  datanasc, sexo, cpf, rg, profissao,
  endereco, numero, bairro, cod_cidade, cidade, estado, cep, complemento,
  obs, como_conheceu, responsavel, responsavel_ua,
  datacad, UA, data_primeira_comanda, data_ultima_comanda,
  favorito, agendamento_online, email_mkt, sms_mkt, whatsapp_mkt, emissao_nota
}
```

Nota: a resposta usa a chave singular `salonClient` (objeto), diferente da lista paginada que usa `salonClients` (com `.data[]`).

### Outros

- `GET /salao/{salao_id}/funcoes-customizadas/ativas` — campos customizados ativos do salão (schema simples, baixo impacto).
- `GET /auth/validate-session` — revalida a sessão; corpo inclui `cognito_access_token` (não usado pelo extractor, ignorar).

### Vale Rápido — funcionalidade DESATIVADA para esta conta

A tela `/vale-rapido` carrega mas exibe: *"Esta funcionalidade ainda não está disponível para você, entre em contato com o estabelecimento para a ativação desta funcionalidade"*. Nenhuma chamada de API de dados é feita (só `validate-session`). **Não há dados para extrair hoje.** O CPT `tanbiuti_vale_rapido` no plugin fica com schema placeholder, sem dados reais até a funcionalidade ser ativada no sistema de origem — comunicar isso ao usuário antes de considerar essa parte "pronta".

## Pontos ainda em aberto

- Schema de `data.blocked[]` na Agenda (não observado com itens nos dias testados).
- Confirmar se `tab_items[].tipo` tem um enum fechado de valores (`salao_servicos` foi o único visto) — relevante para a taxonomia `tanbiuti_recibo_tipo`/categoria do item.
