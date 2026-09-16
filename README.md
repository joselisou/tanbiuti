# Tanbiuti

Clona os dados e a UI funcional de um painel de gestão de salão (Agenda, Comandas, Comissões,
Clientes) em um site WordPress, para permitir montar um BI em cima desses dados — algo que o
sistema de origem não oferece nativamente.

O projeto tem duas partes:

1. **`tools/`** (Node.js/TypeScript) — extrai os dados reais do sistema de origem, e gera os
   arquivos WXR (formato de import/export do WordPress) usados pelo plugin.
2. **`plugin/tanbiuti/`** (PHP + WordPress) — registra os Custom Post Types, importa o WXR, e
   expõe os dados numa área logada (`/minha-conta/`) mobile-first, além do admin nativo do WP.

Veja `docs/api-reconnaissance.md` para o mapeamento da API do sistema de origem, e o histórico da
conversa que gerou este projeto para o contexto completo das decisões de escopo/privacidade.

## Pré-requisitos

- Node.js 20+ e npm
- PHP 7.4+ e [Composer](https://getcomposer.org)
- [Docker](https://www.docker.com) rodando (necessário para os testes de integração do plugin via `wp-env`)
- `.env` na raiz do repo com as credenciais do sistema de origem (copie de `.env.example`):
  ```
  SOURCE_LOGIN_URL=https://<url-do-sistema>/login/<slug-do-salao>
  SOURCE_EMAIL=...
  SOURCE_PASSWORD=...
  SOURCE_API_URL=<host-da-api-do-sistema>
  ```
  Todas as quatro são obrigatórias — o script falha na inicialização se alguma faltar.
  `SOURCE_API_URL` é só o host (sem `https://`); o `https://` é adicionado pelo próprio script.

## ⚠️ Privacidade e segurança

- **`data/real/`** (saída do extractor) é **inteiramente ignorado pelo git** — nunca commite nem
  dê push nesse conteúdo. Só `data/fake/` (dataset sintético) é versionado.
- O repositório GitHub (`joselisou/tanbiuti`) deve ficar **privado** sempre que houver qualquer
  chance de dados reais estarem no histórico. Alternar visibilidade:
  `gh repo edit joselisou/tanbiuti --visibility public|private`.
- O extractor faz login de verdade no sistema de origem. **Ele permite só uma sessão ativa por
  conta** — não rode o extractor enquanto estiver navegando manualmente logado com o mesmo usuário
  (a sessão do navegador cai).
- O extractor é deliberadamente lento (concorrência 1, pausa de 400ms entre chamadas) para não
  sobrecarregar a API de produção do sistema de origem nem correr risco de bloqueio por
  rate-limit. Não aumente esses valores sem necessidade real.

## Rodando a extração de dados reais

```bash
cd tools/extractor
npm install
npx playwright install chromium   # só na primeira vez
npx tsx src/index.ts
```

Por padrão, extrai de `2025-07-19` até hoje. Opções:

| Flag | Efeito |
|---|---|
| `--from=YYYY-MM-DD --to=YYYY-MM-DD` | Limita o período (útil para testes rápidos) |
| `--force` | Ignora o cache local em `data/real/raw/` e refaz tudo |

**Tempo esperado**: para o período completo (~14 meses), espere algo entre **15 e 30 minutos** —
o script é sequencial e pausado de propósito (veja a seção de privacidade acima). Ele é resumível:
se for interrompido, rodar de novo sem `--force` pula o que já foi extraído.

Um item que falha (ex.: um cliente que não existe mais, 404) não derruba a extração inteira — o
erro é registrado e o script segue para o próximo item. No final, o terminal mostra quantos itens
falharam (e de qual tipo); os detalhes completos ficam em `data/real/extraction-errors.log`.

Saída:
- `data/real/raw/<entidade>/*.json` — resposta bruta de cada chamada (para depuração)
- `data/real/normalized/{agenda,comandas,clientes}.json` — consolidado
- `data/real/extraction-summary.json` — contagens (dias, agendamentos, comandas, clientes, erros)
- `data/real/extraction-errors.log` — um item por linha, só quando algo falhou

## Gerando o WXR

### Dataset fake (versionado, usado no Playground)

```bash
cd tools/fake-data-generator
npm install
npx tsx src/index.ts
```

Gera `data/fake/json/dataset.json` e `data/fake/tanbiuti-fake-dataset.xml` (determinístico, seed
fixa — só muda se o gerador mudar).

### Dataset real (local, nunca commitado)

Ainda não há um script único "JSON real → WXR real" (o `wxr-builder` hoje só é chamado pelo
gerador fake). Para importar dados reais, use a tela de admin do plugin (**Tanbiuti > Importar
WXR**) ou `wp tanbiuti import-wxr <arquivo.xml>` depois de montar o XML a partir dos mappers em
`tools/wxr-builder/src/`.

## Plugin WordPress (`plugin/tanbiuti`)

```bash
cd plugin/tanbiuti
composer install       # PHPCS/WPCS + PHPUnit
npm install             # wp-env + wp-scripts (build do front-end)
npm run build            # compila assets/src/{scss,ts} → assets/build/
```

### Rodar/testar localmente (wp-env)

```bash
npm run env:start        # sobe WordPress em http://localhost:8888 (admin/password) e :8889 (testes)
npm run test:php          # roda a suíte PHPUnit de integração contra o :8889
npm run env:destroy       # derruba os containers quando terminar
```

### Qualidade de código

```bash
composer lint             # PHPCS/WPCS
composer lint:fix          # phpcbf (autofix)
npm run lint:js            # ESLint (assets/src/ts)
npm run lint:css           # stylelint (assets/src/scss)
```

### Import/limpeza via WP-CLI

```bash
wp tanbiuti import-wxr caminho/para/dataset.xml
wp tanbiuti clean       # remove só os posts importados (identificados por _tanbiuti_source_id)
```

## Abrir no WordPress Playground

Com o repositório **público**, o link abaixo instala o plugin (direto do GitHub, sem precisar de
build/zip) e importa o dataset fake automaticamente:

```
https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/joselisou/tanbiuti/main/blueprint.json
```

Enquanto o repo estiver privado esse link não funciona (raw.githubusercontent.com não serve
conteúdo de repos privados) — teste localmente primeiro com a CLI do Playground:

```bash
npx @wp-playground/cli run-blueprint --blueprint=./blueprint.json
```

(nesse teste local, os passos que referenciam URLs do GitHub também vão falhar até o push real —
sirva como checagem de sintaxe do blueprint, não de conteúdo).

## CI

`.github/workflows/ci.yml` roda em todo push/PR: lint + testes dos três pacotes Node, PHPCS/WPCS
do plugin, lint+build dos assets do front-end (falha se `assets/build/` estiver desatualizado),
PHPUnit via wp-env, e uma checagem de que os WXR gerados (fixture de teste e dataset fake) batem
com o que os geradores produzem.

## Estrutura

```
tanbiuti/
├── docs/api-reconnaissance.md   # schema dos endpoints reais do sistema de origem (sem dados sensíveis)
├── tools/
│   ├── extractor/               # login + extração via API real do sistema de origem
│   ├── wxr-builder/              # JSON → WXR (WordPress eXtended RSS)
│   └── fake-data-generator/      # dataset sintético para demo pública
├── data/
│   ├── real/                     # GITIGNORED — saída real da extração
│   └── fake/                     # versionado — dataset sintético
├── plugin/tanbiuti/               # o plugin WordPress
└── blueprint.json                # link "Open in Playground"
```
