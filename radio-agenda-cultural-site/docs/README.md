# Handoff — Site Rádio Agenda Cultural

Rádio web independente de Votuporanga, SP, com estações temáticas tocando 24h (número dinâmico, hoje 12), Blog, Literatura e Podcasts. Este pacote traz **todas as páginas aprovadas**, nas versões desktop (1280px) e mobile (375px), com **modo claro e escuro**.

## Como usar este pacote
- Os arquivos `.dc.html` são **protótipos de referência em HTML**, não código de produção. A tarefa é **recriá-los no ambiente do site** (tema WordPress, por exemplo) seguindo este guia.
- Para visualizar, abra qualquer arquivo de `paginas/` no navegador. Os arquivos da pasta precisam ficar juntos.
- **Fidelidade alta:** cores, tipografia, espaçamentos e interações são finais.
- Leia também `TEMA-CLARO-ESCURO.md` (tokens de cor e regras do modo escuro).

## Linguagem visual
- **Cores:** apenas pelos tokens de `paginas/tema.css` (`--bg`, `--surface`, `--surface2`, `--line`, `--text`, `--text-body`, `--muted`, `--accent` #ee8133, `--accent-text`, `--accent-soft`, `--live`…). Nenhuma cor fixa no código.
- **Tipografia:** Outfit para títulos (peso 300–400, letter-spacing de -0.01 a -0.025em) e DM Sans para o corpo (400/500). Nunca usar 700/800.
- **Botões:** em pílula (999px). O primário é laranja com texto branco; o secundário tem fundo `--surface` e borda `--line`; o invertido tem fundo `--text` e texto `--bg`.
- **Cards:** raio de 12 a 16px, borda de 1px `--line` e sombras quase imperceptíveis.
- **Rótulos (eyebrows):** 11px, peso 500, caixa alta, tracking .1em, em `--accent-text` ou `--muted`.
- **"Ao vivo":** bolinha `--live` de 5–6px com anel pulsando (`@keyframes livering`, 1,8s) e texto de 8–10px em caixa alta, cor `--live-text`.
- **Topos (heros) com foto:** a imagem ocupa cerca de 62% à direita e se mescla ao `--bg` com um gradiente horizontal. No mobile, a foto fica de fundo com gradiente vertical e o texto embaixo.

## Componentes compartilhados (usados em todas as páginas)
- **`SiteHeader`:** logo, menu (Estações, Blog, Literatura, Podcasts, Sobre) e a chave de tema. No mobile, menu ☰ com Contato e a linha "Aparência do site".
- **`SiteFooter`:**
  - Logo, texto curto, e-mail e redes sociais.
  - Coluna de menu.
  - Grade "Todas as estações" com link para cada uma.
- **`PlayerFixo`:** barra fixa no rodapé, com 72px no desktop e 56px no mobile.
  - Desktop: capa, "Ao vivo" + estação, "Tocando agora" + faixa e artista, pílula central com play/pause e volume, e "Trocar estação" com dropdown.
  - Mobile: play + botão "Estações", que abre uma lista que sobe da base da tela (bottom sheet).
  - **No site, deve ser `position: fixed`.** Nos mockups ele aparece no fim da página só para não cobrir o conteúdo.
- **`CardEstacao`:** nome, gênero opcional, "Ao vivo" e botão play redondo. A estação que está tocando fica com `--accent-soft` e borda laranja. Tem uma versão `compact` em que o card inteiro toca a estação.
- **`CardPost`:** imagem, categoria + data, título com até 2 linhas e autor com link.
- **`ThemeToggle`:** chave sol/lua com os rótulos "Claro" e "Escuro" e `role="switch"`. Salva a escolha em `localStorage`; na primeira visita segue a preferência do sistema.

## Páginas
1. **Home** (`Home v4 Claro.dc.html`):
   - Topo com foto, card do fundador e selo de Votuporanga, seguido dos 3 destaques sem card.
   - Estações em destaque: 2 mais ouvidas + 1 "Descoberta do editor", e abaixo a grade "Todas as estações".
   - Carrossel infinito do Blog com "Ver todos os posts", carrossel de Literatura, Sobre nós e Podcasts.
   - Recados dos ouvintes: comentários paginados do WordPress.
2. **Estações** (`Estacoes.dc.html`):
   - Topo com foto e o selo "N estações ao vivo agora".
   - Filtro por grupo de gênero (Todas, MPB & Samba, Jazz, Blues & Rock, Clássica & Trilhas, Raízes, Palavra & Cultura).
   - Grade com 4 colunas no desktop e 2 no mobile.
3. **Estação** (`Estacao.dc.html?e=N`), modelo único para todas as estações:
   - O topo com foto traz selo "No ar agora", gênero, nome, "Ouvir agora" e "Tocando agora".
   - Texto com 2–3 parágrafos, "Outras estações" e "Para ler enquanto ouve" (3 posts).
4. **Blog** (`Blog.dc.html`, que também serve a Podcasts via `?c=Podcasts`):
   - Barra de filtros com Categoria, Autor(a) e "Ordenar por" (mais recentes, mais antigos, Título A–Z, Autor A–Z), além de "Limpar filtros" e etiquetas removíveis.
   - Grade paginada, com 6 posts por página no desktop e 4 no mobile.
5. **Post** (`Post.dc.html`), com 7 formatos (o seletor no topo do canvas serve só para apresentação):
   - **Texto/Crônica:** imagem de destaque, intertítulos e citação.
   - **Poesia:** estrofes.
   - **Vídeo:** player 16:9 com capítulos.
   - **Música/Artista:** card com faixas tocáveis e link para a estação.
   - **Tirinha do dia:** 3 quadros, com anterior e próxima.
   - **Imagem:** foto grande com crédito.
   - **Citação:** frase grande com autor e obra.
   - Em todos os formatos: tags, botão "Compartilhar" com ícone (abre um menu no desktop e uma lista que sobe da base no mobile) e caixa do autor.
   - Comentários no padrão WordPress: respostas recuadas, selo do autor, paginação, formulário com Comentário, Nome, E-mail, Site e "Salvar meus dados", e o estado "Aguardando moderação".
   - Por fim, Posts relacionados (sem repetir o post atual).
6. **Autor** (`Autor.dc.html?a=chave`):
   - Avatar grande com a bio contornando a foto (o texto continua por baixo dela).
   - Balão "Citação favorita" com o nome e sobrenome do autor citado.
   - Grade de posts do autor com "Carregar mais".
7. **Sobre** (`Sobre.dc.html`):
   - Topo com foto mesclada e a história completa.
   - "Quem faz": 3 autores alternando o lado da foto, cada um com cards pequenos de livros e artigos e o botão "Página do autor".
   - 3 destaques e um convite para o Contato.
8. **Contato** (`Contato.dc.html`):
   - O assunto é escolhido em botões (Sugestão de música, Pedido musical, Parcerias, Outro).
   - Em sugestão e pedido aparecem os campos Música e Artista.
   - Depois do envio, aparece uma tela de confirmação.
9. **Player** (`Player.dc.html`): estados do player fixo (fechado, dropdown aberto e bottom sheet no mobile).

## Integrações sugeridas (WordPress)
- **Estações:** tipo de post próprio, com nome, gênero, grupo, URL do stream e capa.
  - "Mais ouvidas": ordenar pelo número de ouvintes vindo do servidor de streaming.
  - "Descoberta do editor": uma opção configurável no painel.
- **Blog:** posts com um "formato" (Texto, Poesia, Vídeo, Música, Tirinha, Imagem, Citação), categorias e página de autor com bio, citação favorita e citação de origem (campos personalizados).
- **Comentários:** nativos do WP, com moderação. Os recados da Home podem vir de uma página fixa usada como "mural" (REST `/wp/v2/comments`).
- **Estado do player:** estação atual, play/pause e volume ficam globais e persistem ao navegar entre páginas.
- **Tema:** script inline no `<head>` que aplica `data-theme` antes de a página aparecer (evita o "flash" de tema claro).

## Assets
- `paginas/uploads/cropped-Horizontal-v2.png`: logo. **Falta uma versão do logo para fundo escuro.**
- Todas as imagens (topos, avatares, capas, posts, livros, tirinhas, vídeo) aparecem como espaços reservados e devem vir do CMS. As fotos colocadas durante o design não estão no pacote; envie os originais junto.
- Os textos (história, bios, posts, comentários) são exemplos para substituir.
