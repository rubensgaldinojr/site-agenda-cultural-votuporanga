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
| 3B-2 | Home: carrosséis de Blog e Literatura, Podcasts e Recados dos ouvintes; "Da biblioteca" na página da estação | 🔨 código pronto; aguardando teste manual no site |
| 4 | Blog, Post, Autor | ⏳ |
| 5 | Sobre, Contato, revisão geral | ⏳ |

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
| B-13 | Formato de post (Texto, Poesia, Vídeo, Música, Tirinha, Imagem, Citação) | 4 | ⏳ | Hoje nenhum existe. Os templates devem funcionar sem o campo (cai em "Texto") e aceitar formatos novos depois. ACF a criar em conjunto |
| B-14 | Campos de autor: papel, "escreve desde", citação favorita, origem da citação, foto, exibir no Sobre + ordem | 4–5 | ⏳ | Criar em conjunto |
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
| B-44 | Tags nos cards de post do Blog: hoje o card mostra só categoria, data, título e autor; as tags não aparecem em lugar nenhum | 4 | 💡 | A tratar na Fase 4. Opções: (a) manter o card sem tags, como no mockup; (b) mostrar até 2 tags no card; (c) tags só na página do post (o mockup tem) com página de arquivo por tag. Pode-se ligar a tag com o nome de uma estação à página dessa estação |
| B-45 | Futuro: plugin próprio para cadastrar e gerenciar podcasts (séries e episódios) | — | 💡 | Ideia do Rubens; hoje Podcasts é uma categoria do Blog |
| B-46 | Preencher os dados reais da Home: foto, nome, papel e texto do fundador; textos de topo, destaques e Sobre nós; foto do Sobre; "Ouvintes" de cada estação; imagem destacada da Home | — | ⏳ | Tudo provisório até o Rubens preencher |
| B-47 | Home como página inicial estática (feito), comentários liberados nela (recados) e "O comentário deve ser aprovado manualmente" (feito) | 3B-2 | ⏳ | Falta ligar "Permitir comentários" na página Home |
| B-48 | Livro: página individual e card com botão de baixar; hoje o card de livro da Home leva à página Literatura | 4–5 | 💡 | Depende do mockup da Literatura (B-03) |
| B-49 | Spam nos recados: se aparecer mesmo com a moderação manual, instalar o Akismet ou outro anti-spam; o tema já tem campo-armadilha e tempo mínimo | — | 💡 | Só se for preciso |
| B-40 | `catalogo-livros-rac.php` está duplicado na raiz do repositório (idêntico ao de `plugins/`) | 💡 | Remover a cópia da raiz? |

## Decisões tomadas

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
