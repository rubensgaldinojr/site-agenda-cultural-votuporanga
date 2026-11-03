# Tema filho do Kadence — Rádio Agenda Cultural

Versão 0.2.0 (Fases 1 e 2). Requer o tema **Kadence** instalado (é o tema pai).

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

## Componentes (Fase 2)

- **PlayerFixo** (`template-parts/player-fixo.php`, `inc/player.php`, `assets/js/player.js`):
  barra fixa de 72px (desktop) / 56px (mobile), com dropdown de estações (desktop) e
  bottom sheet (mobile). O tema desliga o desenho antigo do plugin e desenha este, com os
  **mesmos IDs e classes** que o JS do plugin usa. O plugin `racr-player-radio` **não é
  alterado**: se o tema for desativado, o player antigo volta sozinho.
- **CardEstacao** (`template-parts/card-estacao.php`): versão normal e `compact`. Clicar no
  botão (ou no card compacto) aciona o player; o card da estação que está tocando fica
  destacado. Cada estação já é um gênero, então o nome da estação faz o papel do "gênero".
- **CardPost** (`template-parts/card-post.php`): imagem destacada (ou placeholder listrado),
  categoria, data, título em até 2 linhas e autor com link.
- **Prévia com dados reais:** crie uma página com o slug `componentes` (ou escolha o
  template "Componentes (apenas para teste)") e deixe como Rascunho/Privada. Ela mostra os
  cards com suas estações e posts reais e sai dos buscadores (noindex). Pode ser apagada
  quando as Fases 3 a 5 estiverem prontas.
- O volume fica escondido no iPhone (o Safari não permite mudar o volume por JS).
- "Tocando agora": o plugin escreve "Artista - Música"; o tema divide no primeiro " - ".

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
- **Contrato com o plugin do player:** os IDs `racr-player`, `racr-audio`, `racr-botao-play`,
  `racr-status`, `racr-nome-estacao`, `racr-lista-estacoes`, `racr-botao-info` e
  `racr-botao-expandir`, as classes `.racr-estacao` (com `data-indice`) e
  `.racr-estacao-ativa`, e as classes de estado do `#racr-player` (`racr-tocando`,
  `racr-carregando`, `racr-erro`, `racr-expandido`). Não renomear. Se uma versão futura do
  plugin mudar isso, o teste `tests/harness/teste-fase2.js` acusa.

## Teste automatizado

Veja `../../tests/harness/README.md`. Ele não substitui o teste no site real.

## Estrutura

```
style.css  functions.php  header.php  footer.php
index.php  page.php  single.php  404.php          (genéricos; os definitivos vêm nas Fases 3–5)
page-componentes.php                              (prévia de teste dos componentes)
inc/helpers.php  inc/customizer.php  inc/player.php
template-parts/theme-toggle.php  player-fixo.php  card-estacao.php  card-post.php
assets/css/tema.css  base.css  componentes.css
assets/js/rac.js  player.js
```
