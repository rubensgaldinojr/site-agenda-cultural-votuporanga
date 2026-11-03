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
| 2 | CardEstacao, CardPost, PlayerFixo (visual), ThemeToggle | ⏳ |
| 3 | Home, listagem de Estações, Estação individual | ⏳ |
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
| B-10 | Campos da Estação: gênero (texto), grupo (taxonomia), ouvintes, "fixar como destaque" | 3 | ⏳ | Criar por ACF + CPT UI, em conjunto |
| B-11 | Investigar API do Zeno.fm para contagem de ouvintes | 3 | ⏳ | Se não houver API: campo manual, que no futuro pode ser alimentado por script que lê o dashboard do Zeno |
| B-12 | Lógica "Descoberta do editor": o editor fixa uma estação ou deixa sortear uma por dia entre as que não estão no top 2 | 3 | ⏳ | Sempre exibida na Home. Top 2 = mais ouvintes |
| B-13 | Formato de post (Texto, Poesia, Vídeo, Música, Tirinha, Imagem, Citação) | 4 | ⏳ | Hoje nenhum existe. Os templates devem funcionar sem o campo (cai em "Texto") e aceitar formatos novos depois. ACF a criar em conjunto |
| B-14 | Campos de autor: papel, "escreve desde", citação favorita, origem da citação, foto, exibir no Sobre + ordem | 4–5 | ⏳ | Criar em conjunto |
| B-15 | Podcasts como subcategorias da categoria Podcasts | 3–4 | 💡 | Descrição do termo e contagem de posts alimentam os cards da Home |
| B-16 | Textos da Home editáveis direto na página Home (ACF na própria página) | 3 | ⏳ | Rodapé: textos globais (ver B-05) |
| B-17 | PlayerFixo: capa, volume, "Tocando agora" separado em faixa/artista, bottom sheet no mobile | 2 | ⏳ | Exige mexer na marcação e no CSS do plugin, sem alterar a lógica de áudio. Explicar o plano antes |
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
| B-40 | `catalogo-livros-rac.php` está duplicado na raiz do repositório (idêntico ao de `plugins/`) | 💡 | Remover a cópia da raiz? |

## Decisões tomadas

- Sem headless: tema filho do Kadence com templates PHP.
- `#primary` é o seletor da navegação persistente; todo template terá exatamente um `<main id="primary">`. O plugin não é alterado para isso.
- Textos do rodapé são globais; textos da Home são editados na própria página Home.
- Formatos de post: nenhum existe hoje; o campo será criado depois, deixando espaço para novos formatos.
- Descoberta do editor: sempre visível, fixa ou sorteada diariamente entre as estações fora do top 2.
- Ouvintes: tentar a API do Zeno.fm; se não der, campo manual atualizado por script no futuro (valor estático até a próxima atualização).
- Endereços reais: `/estacoes/`, `/blog/`, `/literatura/`, `/sobre/`, `/category/podcasts/`.
- Textos do rodapé: Personalização do WordPress (ACF free); textos da Home: ACF na própria página Home.
- Tema: pasta `radio-agenda-cultural-site/themes/rac-kadence-child/`; ambiente de teste simulado em `tests/harness/`.
- Literatura: o plugin renderiza a página; o mockup do hero e do complemento entra no backlog.
