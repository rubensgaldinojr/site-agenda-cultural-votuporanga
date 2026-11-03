# Tema filho do Kadence — Rádio Agenda Cultural

Versão 0.1.0 (Fase 1). Requer o tema **Kadence** instalado (é o tema pai).

## Instalação (site de desenvolvimento)

1. Compacte a pasta `rac-kadence-child` em um `.zip`.
2. Painel → Aparência → Temas → Adicionar novo → Enviar tema → ativar.
3. Limpe o cache do LiteSpeed (LiteSpeed Cache → Gerenciar → Limpar tudo).
4. Rollback, se algo der errado: reative o tema **Kadence**.

## O que configurar no painel

- **Aparência → Menus**: crie um menu e marque o local "Menu principal". Sem isso o
  tema usa o menu padrão do mockup (`/estacoes/`, `/blog/`, `/literatura/`,
  `/podcasts/`, `/sobre/`), cujos endereços são uma suposição. O menu do painel sempre
  tem prioridade. O "Contato" é acrescentado sozinho no mobile e no rodapé.
- **Aparência → Personalizar → Identidade do site**: logo e, opcionalmente, a
  "Logo para fundo escuro". Sem logo, aparece o nome do site em texto.
- **Aparência → Personalizar → Rodapé do site**: texto curto, e-mail, links de
  Instagram/Facebook/YouTube (só aparecem os preenchidos) e linha de direitos.

## Regras que não podem ser quebradas

- Todo template tem **exatamente um** `<main id="primary">`: é a área que o plugin
  `racr-player-radio` troca na navegação persistente. Se faltar, a página recarrega e o
  áudio para.
- O player é injetado em `wp_footer()`, fora do `#primary`. Não mover.
- O cabeçalho e o rodapé **não** são recarregados na navegação. O que depender deles
  (menu ativo, menu mobile) é refeito no evento `racr:conteudo-trocado` (`assets/js/rac.js`).
- Scripts dentro do conteúdo trocado não executam. Componentes novos devem usar
  delegação de eventos ou `RAC.aoCarregar(fn)`.
- Cores só por `var(--token)` (definidos em `assets/css/tema.css`).

## Teste automatizado

Veja `../../tests/harness/README.md`. Ele não substitui o teste no site real.

## Estrutura

```
style.css  functions.php  header.php  footer.php
index.php  page.php  single.php  404.php          (genéricos; os definitivos vêm nas Fases 3–5)
inc/helpers.php  inc/customizer.php
template-parts/theme-toggle.php
assets/css/tema.css  assets/css/base.css  assets/js/rac.js
```
