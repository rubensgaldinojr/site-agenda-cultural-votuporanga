# Tema filho do Kadence — Rádio Agenda Cultural

Versão 0.5.0 (Fases 1, 2, 3A, 3B-1 e 3B-2). Requer o tema **Kadence** instalado (é o tema pai).

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
- **Volume e mudo:** o ícone de alto-falante é um botão de mudo (o ícone mostra mudo / baixo / alto)
  e o slider é curto. O próprio `<audio>` é a fonte da verdade (`volume` e `muted`); a tela só
  reflete o evento `volumechange`. O estado fica salvo e sobrevive à troca de estação. No iPhone o
  Safari ignora o volume por JS: o slider some, mas o botão de mudo funciona. O controle tem reset
  próprio porque o Kadence aplica estilo de campo de formulário a todo `input`.
- "Tocando agora": o plugin escreve "Artista - Música"; o tema divide no primeiro " - ".

## Páginas (Fase 3A)

- **Estações** (`page-estacoes.php`, página com o slug `estacoes`): topo com foto e selo
  "N estações ao vivo agora" (N é calculado) e grade de **cards com imagem** em **ordem
  alfabética**, sem filtro: 4 colunas no desktop e 2 no mobile.
  - Card (`template-parts/card-estacao-imagem.php`): imagem destacada da estação ao fundo
    (sem ela, o **placeholder** do tema), selo "Ao vivo" (vira "Tocando"), nome, trecho de
    1–2 linhas do "Texto Sobre a Estação" (só no desktop e só se houver texto), link "Ver
    estação" e botão de play. Texto branco sobre escurecimento, igual nos dois temas. Clicar em
    qualquer ponto do card toca/pausa pelo player.
  - O placeholder também aparece nos tiles de "Outras estações" e na capa/lista do player
    quando a estação não tem imagem destacada.
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

## Página inicial (Fase 3B-1)

`front-page.php` vale para a página definida como **Página inicial** em Configurações → Leitura.
Seções: topo, destaques, estações em destaque, todas as estações, Blog, Literatura, Sobre nós,
Podcasts (só se houver séries) e Recados dos ouvintes.

- **Textos e fotos:** em "Textos da página inicial" (abas Topo, Fundador, Destaques, Estações em
  destaque e Sobre nós) da própria página. Campos vazios mostram o texto provisório do mockup (os do
  fundador são placeholder até você preencher). A imagem do topo é a **Imagem destacada** da página
  (sem ela, o placeholder).
- **Mais ouvidas:** campo **"Ouvintes"** (número) em cada estação. As 2 com mais ouvintes aparecem como
  "Mais ouvida #1 e #2" e o botão "Ouvir agora" do topo toca a #1. Sem números preenchidos, vale a
  ordem de exibição e depois a alfabética. (O Zeno.fm não tem API pública de ouvintes; no futuro um
  script pode atualizar este mesmo campo.)
- **Descoberta do editor:** na aba "Estações em destaque", escolha **Sorteio diário** (padrão) ou
  **Fixar uma estação**. O sorteio escolhe entre as estações que NÃO estão no top 2, troca à
  meia-noite (fuso do site) e é igual para todos os visitantes no dia; percorre todas as estações num
  ciclo sem repetir. Se a estação fixada estiver no top 2, usa o sorteio.
- **Cache (LiteSpeed):** um evento diário às 00:05 limpa só a página inicial; salvar uma estação
  também limpa. (Depende do WP-Cron, que roda quando há visitas.)
- **Todas as estações:** cards clicáveis em ordem alfabética; no desktop cada card tem o link "Ver estação ›" no canto (os compactos do mobile não têm link).

### Blog, Literatura, Podcasts e Recados (3B-2)

- **Blog:** os 6 posts mais recentes (card de post). **Literatura:** os 8 livros mais recentes (CPT
  "livro" do plugin de catálogo; card com capa, título e autor; o clique leva à página Literatura).
- **Carrossel (só desktop):** infinito, com setas e avanço a cada 4,5 s; pausa com o mouse/foco, com a
  aba escondida e em "reduzir movimento". Com poucos itens (que cabem na tela) vira uma grade simples,
  sem setas. No **mobile** é rolagem lateral com encaixe (sem JS).
- **Podcasts:** subcategorias da categoria "Podcasts" que já têm posts (título, descrição do termo e
  "N episódios"). Sem subcategorias, a seção não aparece. (A seção ainda não foi totalmente pensada.)
- **Recados:** comentários nativos **da própria página Home** (ligue "Permitir comentários" nela e deixe
  "O comentário deve ser aprovado manualmente" em Configurações → Discussão). 3 por página no desktop
  (com números) e 4 no mobile ("1 de N"). O formulário envia sem recarregar a página (o áudio não
  para) para o `wp-comments-post.php`, então a moderação e o Akismet do WordPress continuam valendo.
  Proteção extra só nos comentários da Home: campo-armadilha escondido e tempo mínimo de 3 s entre
  abrir o formulário e enviar. Sem recados e com comentários fechados, a seção não aparece.
- **Página da estação — "Da biblioteca":** 6 livros; primeiro os cuja **categoria de livro** tem o
  mesmo nome da estação, completando com os mais recentes. Some se não houver livros.

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
front-page.php                                    (Fases 3B-1 e 3B-2)
inc/helpers.php  inc/customizer.php  inc/player.php  inc/acf-campos.php  inc/home.php  inc/conteudo.php  inc/recados.php
template-parts/theme-toggle.php  player-fixo.php  card-estacao.php  card-estacao-imagem.php  card-post.php  card-livro.php  tile-estacao.php
assets/css/tema.css  base.css  componentes.css  paginas.css  home.css
assets/js/rac.js  player.js  estacao.js  carrossel.js  recados.js
assets/img/placeholder-topo.jpg
```
