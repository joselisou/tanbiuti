=== Tanbiuti ===
Contributors: joselisou
Tags: import, wxr, custom post type
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clona os dados e a UI funcional de um painel de gestão de salão (agenda, comandas, comissões, clientes) em um site WordPress, via import WXR.

== Description ==

Este plugin registra os Custom Post Types e taxonomias necessários para representar os dados de
um painel de gestão de salão (Agenda, Comandas, Comissões/Recibos, Clientes e Vale Rápido), e
importa um arquivo WXR gerado pelas ferramentas em `tools/` deste repositório.

A importação é feita por `_tanbiuti_source_id`: reimportar o mesmo arquivo atualiza os posts
existentes em vez de duplicá-los. Use a tela **Tanbiuti > Limpar dados** (ou
`wp tanbiuti clean`) para remover tudo antes de importar um dataset diferente.

O front-end (`/minha-conta/`) exige login e mostra as mesmas seções do painel original, em
layout mobile-first inspirado no "Minha Conta" do WooCommerce (sem depender dele).

== Changelog ==

= 0.1.0 =
* Scaffold inicial: CPTs, taxonomias, importer/cleaner, admin, front-end básico.
