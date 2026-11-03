# Contexto do projeto — Player de Rádio Persistente

## Objetivo
Construir um plugin WordPress próprio que toca 16 estações de rádio (streams do Zeno.fm),
com um player que continua tocando enquanto o visitante navega entre páginas do site
(sem reiniciar o áudio a cada troca de página).

## Site
- WordPress + tema Kadence + Kadence Blocks
- Plugins ativos: ACF, Custom Post Type UI, Rank Math SEO, LiteSpeed Cache

## Já existe no site: CPT "Estação"
Custom Post Type `estacao`, criado via CPT UI, com os seguintes campos ACF
(grupo "Detalhes da Estação"):

| Campo ACF | Tipo | Meta key (provável) |
|---|---|---|
| Texto sobre a estação | WYSIWYG | (verificar no ACF, provavelmente `texto_sobre_a_estacao` ou similar) |
| URL do Stream | URL | (verificar no ACF) |
| Cor de destaque | Color Picker | (verificar no ACF) |
| Ordem de exibição | Number | (verificar no ACF) |

Título e Imagem Destacada são nativos do WordPress (não são campos ACF).

**Importante:** o plugin novo deve LER esses campos ACF já existentes — não criar campos
duplicados nem pedir pra recadastrar as estações.

## Material de referência incluído nesta pasta
- `/radio-player-page/` — código-fonte completo do plugin gratuito "Radio Player Page"
  (licença GPLv2, portanto livre pra reaproveitar). O motor de áudio dele
  (`player/dist/*.js`) é o que queremos reaproveitar — suporta HLS, DASH, MP3,
  Media Session API (controles na tela de bloqueio do celular). Os blocos Gutenberg dele
  são só informativos, não incluem o player — não precisam ser copiados.
- `/catalogo-livros-rac.php` — plugin que já construímos pro site (catálogo de livros).
  Serve de referência de estilo de código: PHP comentado em português, nomes de função
  com prefixo (nesse caso `rac_`), CSS/JS inline via `wp_add_inline_style`/`wp_add_inline_script`,
  tudo em um único arquivo de plugin.

## O que o plugin novo precisa fazer

1. **Ler as 16 estações do CPT "Estação"** já existente (título, imagem destacada, URL do
   stream, cor de destaque)
2. **Player com seletor de estação** — trocar de rádio sem sair da página atual
3. **Player persistente entre páginas** — usando navegação via AJAX (interceptar cliques em
   links internos, trocar só o conteúdo principal via fetch(), mantendo o cabeçalho/rodapé
   — onde mora o player — intactos)
4. **Metadado de "tocando agora"** — nome da música e artista, se o stream do Zeno.fm
   fornecer essa informação (ICY metadata)
5. **Fácil de inserir no site** — shortcode ou bloco Gutenberg
6. **Disparar pageview manual no Google Analytics/GTM a cada navegação "virtual"** (já que
   a navegação via AJAX não dispara pageview automático)

## Abordagem sugerida (fazer em fases, testando cada uma antes de avançar)

1. Player simples tocando 1 estação fixa, sem navegação persistente ainda — validar que o
   motor de áudio reaproveitado funciona com o stream do Zeno.fm
2. Seletor entre as 16 estações (trocar fonte do player sem reload)
3. Camada de navegação persistente entre páginas (a parte mais delicada)
4. Metadado de música/artista
5. Polimento: testar em celular (iOS Safari especialmente), disparo de pageview no GA

## Preferências de código
- Comentários em português
- Nomes de função com prefixo único (sugestão: `racr_` de "Rádio Agenda Cultural Radio")
- Um único arquivo de plugin sempre que possível, como fizemos no catálogo de livros
- Sempre explicar o plano antes de escrever código, e avisar quando algo for arriscado
  ou precisar de teste manual no navegador
