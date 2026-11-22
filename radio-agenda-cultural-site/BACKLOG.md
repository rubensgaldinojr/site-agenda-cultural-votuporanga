# Backlog — Site Rádio Agenda Cultural

> Como usar: peça no chat, em linguagem natural. Exemplos:
> - "mostra o backlog" / "mostra só o que é meu"
> - "adiciona no backlog: <texto>"
> - "marca B-03 como feito" / "muda B-05 para em andamento"
> - "remove B-09"
>
> Eu atualizo este arquivo, faço o commit e mostro a lista atualizada no chat.
> Status: ⏳ pendente · 🔨 em andamento · ✅ feito · ⏸️ bloqueado/aguardando · 💡 decidir

## Fases do projeto

| Fase | Escopo | Status |
|---|---|---|
| 0 | Leitura do README e levantamento de lacunas | ✅ |
| 1 | Tema filho, tema claro/escuro, header e footer, navegação persistente | ✅ testada pelo Rubens no site real (menus desktop e mobile, cabeçalho e rodapé) |
| 2 | CardEstacao, CardPost, PlayerFixo (visual), ThemeToggle | ✅ testada no site real e mergeada (PR #2) |
| 3A | Listagem de Estações e Estação individual | ✅ testada no site real e mergeada (PR #3) |
| 3B-1 | Home: topo, destaques, estações em destaque (mais ouvidas e descoberta), todas as estações e Sobre nós | ✅ testada no site real e mergeada (PR #4) |
| 3B-2 | Home: carrosséis de Blog e Literatura, Podcasts e Recados dos ouvintes; "Da biblioteca" na página da estação | ✅ testada no site real e mergeada (PR #5) |
| 4 (4A+4B) | Blog (listagem, categorias, tags), página do Autor e Post com 8 formatos, compartilhar, caixa do autor, comentários e relacionados + ajustes pós-teste (player, cards, toggle, bolinhas) | ✅ testada no site real e mergeada (PR #6, junto com a 5A) |
| 5A | Sobre (com os ajustes pós-teste: links nos destaques, botão do autor embaixo, foto com borda, história com 2 imagens) | ✅ testada no site real (0.7.2) e mergeada (PR #6) |
| 5B | Contato | ⏳ |
| 5C | Revisão geral | ⏳ |

## Itens

### Com você (insumos e decisões)

| ID | Item | Status | Notas |
|---|---|---|---|
| B-01 | Enviar logo para fundo escuro | ⏸️ | Enquanto não vier, usa o filtro do `tema.css` |
| B-02 | Enviar logo original e fotos (heros, avatares, capas) | ⏸️ | O repositório não tem nenhuma imagem; confirmar se vêm junto com o logo |
| B-03 | Criar o mockup da página **Literatura** (hero + complemento ao que o plugin já renderiza) | ⏳ | O plugin de catálogo renderiza a maior parte da página |
| B-04 | Decidir se existe página individual de livro (`/livro/slug`) | 💡 | Hoje o CPT é público e não tem template nem mockup |
| B-05 | Informar se o ACF é Pro (para usar página de opções) ou free | ✅ | ACF free; textos globais do rodapé ficam na Personalização do WordPress |
| B-06 | Definir a página fixa usada como "mural" dos recados da Home | 💡 | Precisa ter comentários abertos |
| B-07 | Escolher o formulário de Contato (wp_mail nativo ou plugin leve) | 💡 | Opções serão apresentadas na Fase 5 |

### Com o Claude (implementação)

| ID | Item | Fase | Status | Notas |
|---|---|---|---|---|
| B-10 | Campos da Estação para a Home: "Ouvintes" (número) e escolha da descoberta | 3B-1 | ✅ | Criados por código em `inc/acf-campos.php` (campo "Ouvintes" na estação; aba "Estações em destaque" na Home) |
| B-11 | API do Zeno.fm para contagem de ouvintes | 3B | ✅ investigado | Não há API pública documentada de ouvintes (só a Metadata API, de música; "ouvintes atuais" só no painel web; a API privada `api.zeno.fm` exige pedir acesso). **Caminho:** campo manual "Ouvintes", que no futuro um script (ou a API, se liberada) pode atualizar |
| B-12 | Lógica "Descoberta do editor" (sorteio diário fora do top 2, ou estação fixada) | 3B-1 | ✅ | Em `inc/home.php`; coberta por `tests/harness/teste-home.php` (rotação ao longo de um ano) |
| B-13 | Formato de post (Texto, Poesia, Vídeo, Música, Podcast, Tirinha, Imagem, Citação) | 4 | ✅ | 8 formatos, campo ACF por código; sem o campo cai em "Texto"; formato novo = 1 linha no registro + campos + 1 arquivo em `template-parts/formatos/` |
| B-14 | Campos de autor: papel, "escreve desde", citação favorita, origem da citação, foto | 4 | ✅ | Feito no perfil do usuário (grupo "Página do autor") + campo "Endereço da página do autor". **Falta** "exibir no Sobre + ordem" (vai com a Fase 5, B-59) |
| B-15 | Podcasts na Home como subcategorias da categoria Podcasts | 3B-2 | 💡 | A seção de podcasts ainda não foi totalmente pensada: por ora Podcasts é só uma categoria do Blog. A seção da Home fica escondida até existirem subcategorias |
| B-16 | Textos da Home editáveis direto na página Home (ACF na própria página) | 3B-1 | ✅ | Aba por seção; campo vazio mostra o texto provisório do mockup |
| B-17 | PlayerFixo: capa, volume, "Tocando agora" separado em faixa/artista, bottom sheet no mobile | 2 | ✅ | Feito no tema, sem alterar o plugin. Falta validar com o Zeno.fm real (B-36) |
| B-18 | Ajuste só visual do catálogo de livros para o modo escuro (cores fixas no CSS) | 2–3 | ⏳ | Sem mudar comportamento |
| B-19 | Verificar se o catálogo de livros perde CSS/JS ao chegar por navegação AJAX | 1 | ⏳ | O plugin só carrega assets em `is_singular() \|\| is_page()` |
| B-20 | Atualizar menu ativo e re-inicializar scripts no evento `racr:conteudo-trocado` | 1 | ✅ | Feito em `assets/js/rac.js`; componentes novos usam `RAC.aoCarregar(fn)` |

### Plugin do player (pendências já conhecidas)

| ID | Item | Status | Notas |
|---|---|---|---|
| B-30 | Pageview no GA/GTM a cada navegação virtual | ⏸️ | Depende de instalar GA4/GTM no site |
| B-31 | Testar em iPhone real | ⏳ | Baixa prioridade (~3% da audiência) |
| B-32 | Revalidar tudo no domínio final | ⏸️ | Quando sair do domínio de desenvolvimento |
| B-33 | Link para URL que dá HTTP 404 recarrega a página inteira (e para o áudio), porque o plugin só troca o conteúdo se a resposta for 2xx | 💡 | Comportamento do plugin; só afeta links quebrados. Corrigir no plugin só se você quiser |

### Limpeza e ideias

| ID | Item | Status | Notas |
|---|---|---|---|
| B-34 | Confirmar os endereços reais das páginas | ✅ | `/estacoes/`, `/blog/`, `/literatura/`, `/sobre/` e `/category/podcasts/` (Podcasts é categoria). `/contato/` ainda a confirmar na Fase 5 |
| B-35 | Testar o tema no site real (Kadence, LiteSpeed, ACF) | ✅ | Menus e mobile funcionando. Falta só confirmar o catálogo de livros via navegação AJAX (B-19) |
| B-36 | Validar no site real: player com os streams do Zeno.fm e os cards | ✅ | Testado pelo Rubens |
| B-37 | Imagem destacada em cada Estação (capa do player e topo da página da estação) | 💡 | Sem imagem, a capa mostra o placeholder listrado e o topo usa a imagem placeholder do tema. Algumas estações ainda estão sem imagem |
| B-38 | Trocar a imagem placeholder única (`assets/img/placeholder-topo.jpg`) pelas imagens reais de cada topo (Estações, estações, Home, Sobre) e confirmar a licença de uso da imagem atual | ⏳ | Para trocar: definir a Imagem destacada da página/estação; o placeholder só aparece quando não há imagem |
| B-41 | Página de Estações com cards grandes (imagem de fundo, nome, "Ao vivo", trecho, "Ver estação" e play) | 3A | ✅ | Feito: 4 colunas no desktop, 2 no mobile; placeholder em toda estação sem imagem destacada |
| B-42 | Seção de livros na página da estação ("Da biblioteca") | 3B-2 | ✅ | Livros com categoria de mesmo nome da estação primeiro, depois os mais recentes; reaproveita o card de livro |
| B-43 | Renomear a categoria padrão "Uncategorized" e apagar o post "Hello world!" | — | 💡 | Aparecem nos cards de post em inglês; ajuste no painel (Posts → Categorias) |
| B-44 | Tags nos cards de post do Blog | 4 | ✅ | Resolvido: cards do Blog seguem sem tags (como no mockup); as tags aparecem como `#tag` no fim do post e levam à página da tag (`/tag/nome/`, mesma listagem do Blog). Na página da estação, o card mostra um selinho com o nome da estação quando o post tem a tag/categoria dela |
| B-45 | Futuro: plugin próprio para cadastrar e gerenciar podcasts (séries e episódios) | — | 💡 | Ideia do Rubens; hoje Podcasts é uma categoria do Blog |
| B-46 | Preencher os dados reais da Home: foto, nome, papel e texto do fundador; textos de topo, destaques e Sobre nós; foto do Sobre; "Ouvintes" de cada estação; imagem destacada da Home | — | ⏳ | Tudo provisório até o Rubens preencher |
| B-47 | Home como página inicial estática (feito), comentários liberados nela (recados) e "O comentário deve ser aprovado manualmente" (feito) | 3B-2 | ⏳ | Falta ligar "Permitir comentários" na página Home |
| B-48 | Livro: página individual e card com botão de baixar; hoje o card de livro da Home leva à página Literatura | 4–5 | 💡 | Depende do mockup da Literatura (B-03) |
| B-49 | Spam nos recados: se aparecer mesmo com a moderação manual, instalar o Akismet ou outro anti-spam; o tema já tem campo-armadilha e tempo mínimo | — | 💡 | Só se for preciso |
| B-50 | Fase 4 — ajustes de painel (confirmar se tudo foi feito): Configurações → Discussão (comentários aninhados em 2 níveis, quebrar em páginas de ~10, aprovar manualmente); criar os usuários (autores e "Equipe Agenda Cultural") e preencher "Página do autor" no perfil; regravar Links permanentes (para `/autor/`); escolher a página "Blog" em Configurações → Leitura → Página de posts | 4 | ⏳ | Rubens já criou 3 usuários e testou os formatos; confirmar bio/perfil preenchidos, Links permanentes e página Blog como "Página de posts" |
| B-51 | Tirinhas: confirmar os direitos de uso de cada tirinha publicada (o campo "Direitos/referência" é opcional) | 4 | 💡 | Tirinhas de terceiros normalmente têm direitos autorais; o tema só exibe a referência |
| B-52 | Spotify no formato Música: o player do Spotify toca só prévias de 30 s para quem não está logado no Spotify, e carrega conteúdo de terceiros (privacidade) | 4 | 💡 | Alternativa futura: link para abrir no Spotify |
| B-53 | "Ordenar por autor(a)" usa SQL próprio (junta a tabela de usuários); testar no site real | 4 | ⏳ | O simulador não executa SQL |
| B-54 | Ajustes pós-teste da Fase 4 (0.6.2): contraste da barra do player, cards de estação (abrem a página; só o play toca; ativo mais visível; play maior no mobile), chave claro/escuro "selecionada", Home mobile com 4 cards em carrossel, campo de endereço do autor | 4 | ✅ | Aguardando teste no site real |
| B-55 | Endereço dos posts com `/blog/` (`/blog/%postname%/`) e e-mail aparecendo no endereço do autor | 4 | ⏳ | Passos na mensagem de entrega: Links permanentes + campo "Endereço da página do autor" no perfil. Testar `/blog/page/2/` depois |
| B-56 | Mobile: slider de largura total com 4 bolinhas no topo da Home e bolinhas em todos os carrosséis (Estações em destaque, Blog, Literatura, "Da biblioteca") | 4 | ✅ | Tema 0.6.3; aguardando teste no site real. Alternativa se preferir: setas no topo de cada seção |
| B-57 | Home mobile — slider do topo (4 cards): passa sozinho (5 s) e mostra a ponta do próximo card | 4 | ✅ | Feito no 0.7.0 (junto com o Sobre): cards de 86%, bolinhas, auto-avanço que para na interação, pausa com mouse/foco e respeita "reduzir movimento". Aguardando teste no site real |
| B-58 | PR da Fase 4 + 5A e merge | 4–5A | ✅ | PR #6 mergeado (main no 0.7.2) |
| B-59 | **Fase 5A — Sobre** (código pronto no 0.7.0, aguardando teste no site real): topo com foto e a história; "Quem faz" (3 autores alternando o lado da foto, com cards pequenos de livros e artigos e o botão "Página do autor"); 3 destaques; convite para o Contato. Inclui os campos de autor "exibir no Sobre" e "ordem" | 5 | ⏳ | Textos provisórios do mockup (Rubens decidiu seguir com eles por enquanto). História em blocos com 2 imagens opcionais (abas no painel). "Quem faz" lista N pessoas: marcar "Aparecer na página Sobre" no perfil + posição. Foto de cada pessoa = foto do perfil |
| B-60 | **Fase 5 — Contato**: assunto em botões (Sugestão de música, Pedido musical, Parcerias, Outro); campos Música e Artista só nos dois primeiros; tela de confirmação; envio por e-mail | 5 | ⏳ | Mockup `Contato.dc.html` pronto. Depende de B-07 (nativo ou plugin) e do e-mail de destino. Anti-spam como nos recados (campo-armadilha + 3 s) |
| B-61 | **Fase 5 — Revisão geral**: acessibilidade (teclado, contraste, leitor de tela), desempenho (imagens, fontes, cache LiteSpeed), 404, SEO (Rank Math), conferência em celular e desktop, revalidação no domínio final (B-32) | 5 | ⏳ | Último passo antes de considerar o site pronto |
| B-62 | Itens que **não estão nos mockups** e valem uma conversa: página de busca, política de privacidade/cookies (LGPD; o Spotify/YouTube carregam conteúdo de terceiros), imagem de compartilhamento (Open Graph) e favicon | — | 💡 | Sugestão do Claude; decidir se entram na Fase 5 |
| B-40 | `catalogo-livros-rac.php` está duplicado na raiz do repositório (idêntico ao de `plugins/`) | 💡 | Remover a cópia da raiz? |

## Decisões tomadas

- Fase 4: filtros do Blog são LINKS (`?categoria=&autor=&ordem=`), não formulários, para a navegação persistente não parar o áudio; 9 posts por página; categorias e tags usam a mesma listagem; autor em `/autor/nome/`; "Carregar mais posts" na página do autor.
- Fase 4: formato do post = campo ACF "Formato" (Texto, Poesia, Vídeo, Música, Podcast, Tirinha, Imagem, Citação); novos formatos = 1 linha em `rac_formatos_post()` + campos + um arquivo em `template-parts/formatos/`. Música = player do Spotify incorporado (campos país, nome, estilo, link) com título automático "Descoberta Musical: nome - país - estilo"; países com bandeira em imagem (funciona no Windows). Tirinha = imagem pelo editor + campos opcionais de direitos/referência. Compartilhar = WhatsApp, Facebook, X e Copiar link (botões grandes, com texto) + menu nativo do celular quando existir. Comentários = nativos do WordPress, enviados sem recarregar.
- Cards de estação com imagem: o card inteiro abre a página da estação e só o botão de play toca (lista compacta "Todas as estações" mantém o clique que toca). Barra do player: linha laranja no topo (em vez de inverter as cores) e mais alta no mobile.
- Merge: o Claude **sempre indica explicitamente a hora de fazer o merge no site do GitHub** (depois de o Rubens aprovar a versão no site real), com o passo a passo; nunca assume. Depois do merge, o Claude reinicia a branch a partir da `main`.
- Segurança de versões: antes de gerar o zip ou de fazer o merge, rodar `scripts/verificar-estado.sh` (só lê; mostra alterações soltas, se o computador está igual ao GitHub, se falta o merge e a versão do tema de cada lugar). O zip sempre sai da pasta atual; o nome do arquivo é a versão lida de `style.css` daquela pasta.
- Sobre: textos provisórios do mockup até o Rubens enviar os reais; "Quem faz" = usuários marcados no perfil (N pessoas, com posição); os 3 destaques reaproveitam os textos da Home.
- Home mobile: slider do topo com bolinhas, ponta do próximo card e auto-avanço de 5 s (para na primeira interação).
- Sem headless: tema filho do Kadence com templates PHP.
- `#primary` é o seletor da navegação persistente; todo template terá exatamente um `<main id="primary">`. O plugin não é alterado para isso.
- Textos do rodapé são globais; textos da Home são editados na própria página Home.
- Formatos de post: nenhum existe hoje; o campo será criado depois, deixando espaço para novos formatos.
- Descoberta do editor: sempre visível, fixa ou sorteada diariamente entre as estações fora do top 2.
- Ouvintes: tentar a API do Zeno.fm; se não der, campo manual atualizado por script no futuro (valor estático até a próxima atualização).
- Endereços reais: `/estacoes/`, `/blog/`, `/literatura/`, `/sobre/`, `/category/podcasts/`.
- Entrega do tema: a cada etapa, o Claude passa um comando único (git fetch/checkout/pull + `scripts/gerar-zip-tema.sh`) que apaga o zip antigo, gera `rac-kadence-child-<versão>.zip` em `radio-agenda-cultural-site/themes/` e destaca a versão; o Claude também destaca o número da versão ao pedir o upload.
- Formulários (3): **Recados** na Home = comentários nativos da própria página Home (moderados); **Comentários nos posts** = nativos do WordPress; **Recomendação de músicas/pedidos** = formulário de Contato (Fase 5, e-mail).
- Home (3B-2): Blog = 6 posts mais recentes; Literatura = 8 livros mais recentes; carrossel infinito só no desktop e só com itens suficientes (mobile = rolagem lateral); Podcasts escondido até existirem subcategorias; Recados = comentários da própria Home, moderados, enviados sem recarregar a página.
- Home: "Todas as estações" mantém o link "Ver estação ›" no canto do card (desktop); no mobile os cards compactos não têm link.
- Home: mais ouvidas pelo campo manual "Ouvintes"; descoberta fora do top 2 (sorteio diário ou fixada); "Ouvir agora" toca a mais ouvida; dados provisórios do mockup até o Rubens preencher.
- Estações: sem filtro por grupo; listagem em ordem alfabética; "Outras estações" = as 4 seguintes em ordem alfabética; "Para ler enquanto ouve" = posts com tag/categoria de mesmo nome da estação e, se faltar, os mais recentes.
- Estações: card grande com imagem, 4 colunas no desktop; placeholder em toda estação sem imagem destacada (cards, tiles e player). Posts sem imagem continuam com o listrado.
- Imagens: uma única imagem placeholder para todos os topos até chegarem as imagens reais (B-38).
- Estações: cada estação é um gênero; o nome da estação substitui o "gênero" do mockup (sem campo novo).
- PlayerFixo: o tema desenha o player com os mesmos IDs do plugin (`remove_action` do render antigo); o plugin não é editado. "Tocando agora" vem como "Artista - Música" e é dividido no primeiro " - ".
- CardEstacao: o título não é link; o card inteiro toca e há um link "Ver estação" para a página (card compacto sem link).
- Cards e player: `CardEstacao`, `CardPost` e `PlayerFixo` em `template-parts/`; prévia em uma página com o slug `componentes` (rascunho, noindex).
- Textos do rodapé: Personalização do WordPress (ACF free); textos da Home: ACF na própria página Home.
- Tema: pasta `radio-agenda-cultural-site/themes/rac-kadence-child/`; ambiente de teste simulado em `tests/harness/`.
- Literatura: o plugin renderiza a página; o mockup do hero e do complemento entra no backlog.
