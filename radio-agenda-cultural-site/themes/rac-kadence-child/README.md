# Tema filho do Kadence — Rádio Agenda Cultural

Versão 0.3.0 (Fases 1, 2 e 3A). Requer o tema **Kadence** instalado (é o tema pai).

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
- **CardEstacao** (`template-parts/card-estacao.php`): versão normal e `compact`. Clicar em
  **qualquer ponto do card** toca (ou pausa) a estação pelo player; o card da estação que
  está tocando fica destacado. O título **não é link**, para ninguém sair da página sem
  querer ao tentar dar play. O card completo tem um link pequeno "Ver estação" para a página
  da estação; o compacto não tem link. Cada estação já é um gênero, então o nome da estação
  faz o papel do "gênero".
- **CardPost** (`template-parts/card-post.php`): imagem destacada (ou placeholder listrado),
  categoria, data, título em até 2 linhas e autor com link.
- **Prévia com dados reais:** crie uma página com o slug `componentes` (ou escolha o
  template "Componentes (apenas para teste)") e deixe como Rascunho/Privada. Ela mostra os
  cards com suas estações e posts reais e sai dos buscadores (noindex). Pode ser apagada
  quando as Fases 3 a 5 estiverem prontas.
- O volume fica escondido no iPhone (o Safari não permite mudar o volume por JS). O controle
  tem reset próprio porque o Kadence aplica estilo de campo de formulário a todo `input`.
- A pílula de play + volume ocupa ~1/3 da barra (máx. 640px) e fica centralizada.
- "Tocando agora": o plugin escreve "Artista - Música"; o tema divide no primeiro " - ".

## Páginas (Fase 3A)

- **Estações** (`page-estacoes.php`, página com o slug `estacoes`): topo com foto e selo
  "N estações ao vivo agora" (N é calculado), grade de cards em **ordem alfabética**, sem
  filtro. Desktop: cards completos em 4 colunas; mobile: cards compactos em 2.
  - Imagem do topo: **Imagem destacada** da página. Sem ela, aparece a imagem placeholder do
    tema (`assets/img/placeholder-topo.jpg`).
  - Título e texto do topo: escolha o modelo **"Estações"** em Atributos da página; aparecem os
    campos "Título do topo" e "Texto do topo" (vazios = texto padrão do mockup).
- **Estação individual** (`single-estacao.php`, serve todas as estações):
  - Topo com a **Imagem destacada da estação** (sem ela, o placeholder), selo "No ar agora",
    nome, botão "Ouvir agora" (toca/pausa pelo player) e "Tocando agora" atualizado a cada 20 s.
  - O "Tocando agora" usa o endpoint do plugin (cache de 15 s por estação), então não abre
    conexões novas no Zeno.fm.
  - Texto: campo ACF **"Texto Sobre a Estação"** (`texto_sobre_a_estacao`). O primeiro parágrafo
    sai em destaque. Vazio = texto padrão curto com o nome da estação.
  - "Outras estações": as 4 seguintes em ordem alfabética (volta ao início no fim da lista).
  - "Para ler enquanto ouve": posts com **tag ou categoria de mesmo nome da estação** primeiro
    e, se faltar, os mais recentes (3 no total).
- Cada estação já é um gênero: não há linha de gênero nem filtro por grupo.
- Os **campos novos** são criados por código em `inc/acf-campos.php` (ACF gratuito).
- O player e o rodapé seguem a ordem do plugin (`ordem_de_exibicao` e depois alfabética).

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
page-estacoes.php  single-estacao.php             (Fase 3A)
inc/helpers.php  inc/customizer.php  inc/player.php  inc/acf-campos.php
template-parts/theme-toggle.php  player-fixo.php  card-estacao.php  card-post.php  tile-estacao.php
assets/css/tema.css  base.css  componentes.css  paginas.css
assets/js/rac.js  player.js  estacao.js
assets/img/placeholder-topo.jpg
```
