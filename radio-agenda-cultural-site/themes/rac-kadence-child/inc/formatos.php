<?php
/**
 * Formatos de post (Fase 4B): Texto/Crônica, Poesia, Vídeo, Música/Artista,
 * Tirinha, Imagem e Citação.
 *
 * O formato vem do campo "Formato do post" (ACF, criado por código). Post sem
 * formato é "Texto". Para criar um formato novo no futuro: acrescente uma linha em
 * rac_formatos_post(), os campos em rac_campos_formatos() e um arquivo em
 * template-parts/formatos/<chave>.php (ou use o filtro "rac_formatos_post").
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Formatos disponíveis: chave => [rotulo, badge]. */
function rac_formatos_post() {
    return apply_filters( 'rac_formatos_post', array(
        'texto'   => array( 'rotulo' => 'Texto / Crônica', 'badge' => '' ),
        'poesia'  => array( 'rotulo' => 'Poesia', 'badge' => 'Poesia' ),
        'video'   => array( 'rotulo' => 'Vídeo', 'badge' => 'Vídeo' ),
        'musica'  => array( 'rotulo' => 'Música / Artista', 'badge' => 'Música' ),
        'tirinha' => array( 'rotulo' => 'Tirinha do dia', 'badge' => 'Tirinha' ),
        'imagem'  => array( 'rotulo' => 'Imagem', 'badge' => 'Imagem' ),
        'citacao' => array( 'rotulo' => 'Citação', 'badge' => 'Citação' ),
    ) );
}

/** Formato de um post (cai em "texto" se vazio ou desconhecido). */
function rac_formato_do_post( $id ) {
    $f = (string) rac_campo( 'formato', $id, 'texto' );
    return isset( rac_formatos_post()[ $f ] ) ? $f : 'texto';
}

/** Países (iso => nome). */
function rac_paises() {
    static $lista = null;
    if ( $lista === null ) $lista = require get_stylesheet_directory() . '/inc/paises.php';
    return $lista;
}

/** Bandeira (imagem do tema; o código ISO em minúsculas). */
function rac_bandeira_html( $iso, $classe = 'rac-bandeira' ) {
    $iso = strtolower( (string) $iso );
    $paises = rac_paises();
    if ( ! isset( $paises[ $iso ] ) ) return '';
    return '<img class="' . esc_attr( $classe ) . '" src="' . esc_url( get_stylesheet_directory_uri() . '/assets/flags/' . $iso . '.svg' ) . '" alt="Bandeira: ' . esc_attr( $paises[ $iso ] ) . '" width="32" height="24" loading="lazy">';
}

/* ============================================================
   CAMPOS (ACF, por código)
   ============================================================ */
function rac_campos_formatos() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    $quando = function ( $formato ) {
        return array( array( array( 'field' => 'field_rac_formato', 'operator' => '==', 'value' => $formato ) ) );
    };
    $opcoes = array();
    foreach ( rac_formatos_post() as $chave => $f ) $opcoes[ $chave ] = $f['rotulo'];

    acf_add_local_field_group( array(
        'key'    => 'group_rac_formato_post',
        'title'  => 'Formato do post',
        'fields' => array(
            array( 'key' => 'field_rac_formato', 'label' => 'Formato', 'name' => 'formato', 'type' => 'select', 'choices' => $opcoes, 'default_value' => 'texto', 'ui' => 0, 'instructions' => 'Muda o layout do post. Sem escolha, é um texto comum.' ),

            // Vídeo
            array( 'key' => 'field_rac_video_url', 'label' => 'Link do vídeo', 'name' => 'video_url', 'type' => 'url', 'instructions' => 'YouTube ou Vimeo.', 'conditional_logic' => $quando( 'video' ) ),
            array( 'key' => 'field_rac_video_capitulos', 'label' => 'Capítulos (opcional)', 'name' => 'video_capitulos', 'type' => 'textarea', 'rows' => 4, 'new_lines' => '', 'instructions' => 'Um por linha: "mm:ss | Título". Ex.: 02:15 | Entrevista. Funciona com YouTube e Vimeo.', 'conditional_logic' => $quando( 'video' ) ),

            // Música / Artista
            array( 'key' => 'field_rac_artista_nome', 'label' => 'Nome do artista', 'name' => 'artista_nome', 'type' => 'text', 'conditional_logic' => $quando( 'musica' ) ),
            array( 'key' => 'field_rac_artista_pais', 'label' => 'País', 'name' => 'artista_pais', 'type' => 'select', 'choices' => rac_paises(), 'allow_null' => 1, 'ui' => 1, 'instructions' => 'A bandeira aparece em destaque no post.', 'conditional_logic' => $quando( 'musica' ) ),
            array( 'key' => 'field_rac_artista_genero', 'label' => 'Gênero ou estilo musical', 'name' => 'artista_genero', 'type' => 'text', 'conditional_logic' => $quando( 'musica' ) ),
            array( 'key' => 'field_rac_artista_link', 'label' => 'Link do player', 'name' => 'artista_link', 'type' => 'url', 'instructions' => 'Link do artista, álbum, faixa ou playlist no Spotify. Também aceita YouTube e SoundCloud.', 'conditional_logic' => $quando( 'musica' ) ),
            array( 'key' => 'field_rac_artista_instagram', 'label' => 'Instagram do artista (opcional)', 'name' => 'artista_instagram', 'type' => 'url', 'conditional_logic' => $quando( 'musica' ) ),
            array( 'key' => 'field_rac_artista_estacao', 'label' => 'Toca na estação (opcional)', 'name' => 'artista_estacao', 'type' => 'post_object', 'post_type' => array( 'estacao' ), 'return_format' => 'id', 'allow_null' => 1, 'ui' => 1, 'conditional_logic' => $quando( 'musica' ) ),
            array( 'key' => 'field_rac_artista_titulo_auto', 'label' => 'Gerar o título automaticamente', 'name' => 'artista_titulo_auto', 'type' => 'true_false', 'default_value' => 1, 'ui' => 1, 'message' => '', 'instructions' => 'Título: "Descoberta Musical: Nome - País - Gênero". Desmarque para escrever o seu.', 'conditional_logic' => $quando( 'musica' ) ),

            // Tirinha (as imagens e o texto vão no editor, na ordem que você quiser)
            array( 'key' => 'field_rac_tirinha_numero', 'label' => 'Número da tirinha (opcional)', 'name' => 'tirinha_numero', 'type' => 'number', 'min' => 1, 'step' => 1, 'instructions' => 'Vazio: o número é calculado pela ordem de publicação. As imagens e o texto vão no editor, na ordem que quiser.', 'conditional_logic' => $quando( 'tirinha' ) ),
            array( 'key' => 'field_rac_tirinha_autor', 'label' => 'Direitos/referência — Autor (opcional)', 'name' => 'tirinha_autor', 'type' => 'text', 'conditional_logic' => $quando( 'tirinha' ) ),
            array( 'key' => 'field_rac_tirinha_ano', 'label' => 'Direitos/referência — Ano (opcional)', 'name' => 'tirinha_ano', 'type' => 'text', 'conditional_logic' => $quando( 'tirinha' ) ),
            array( 'key' => 'field_rac_tirinha_fonte', 'label' => 'Direitos/referência — Jornal ou fonte (opcional)', 'name' => 'tirinha_fonte', 'type' => 'text', 'conditional_logic' => $quando( 'tirinha' ) ),

            // Imagem
            array( 'key' => 'field_rac_imagem_credito', 'label' => 'Crédito da foto', 'name' => 'imagem_credito', 'type' => 'text', 'instructions' => 'Ex.: Igor Bastos. A foto é a Imagem destacada; a legenda é a da própria imagem.', 'conditional_logic' => $quando( 'imagem' ) ),

            // Citação
            array( 'key' => 'field_rac_citacao_texto', 'label' => 'Citação', 'name' => 'citacao_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '', 'conditional_logic' => $quando( 'citacao' ) ),
            array( 'key' => 'field_rac_citacao_autor', 'label' => 'Autor da citação', 'name' => 'citacao_autor', 'type' => 'text', 'conditional_logic' => $quando( 'citacao' ) ),
            array( 'key' => 'field_rac_citacao_obra', 'label' => 'Obra e ano', 'name' => 'citacao_obra', 'type' => 'text', 'instructions' => 'Ex.: A Hora da Estrela, 1977.', 'conditional_logic' => $quando( 'citacao' ) ),
        ),
        'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
        'position' => 'acf_after_title',
        'style'    => 'default',
    ) );

    // Textos da página do Blog (página definida como "Página de posts")
    acf_add_local_field_group( array(
        'key'    => 'group_rac_pagina_blog',
        'title'  => 'Topo da página Blog',
        'fields' => array(
            array( 'key' => 'field_rac_blog_titulo', 'label' => 'Título do topo', 'name' => 'blog_titulo', 'type' => 'text', 'instructions' => 'Vazio: "Crônicas, descobertas e boas conversas".' ),
            array( 'key' => 'field_rac_blog_texto', 'label' => 'Texto do topo', 'name' => 'blog_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '' ),
        ),
        'location' => array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'posts_page' ) ) ),
    ) );
}
add_action( 'acf/init', 'rac_campos_formatos' );

/* ============================================================
   TÍTULO AUTOMÁTICO DO FORMATO "MÚSICA"
   ============================================================ */
function rac_titulo_musica( $id ) {
    $pais   = rac_paises();
    $iso    = strtolower( (string) rac_campo( 'artista_pais', $id, '' ) );
    $partes = array_filter( array(
        trim( (string) rac_campo( 'artista_nome', $id, '' ) ),
        isset( $pais[ $iso ] ) ? $pais[ $iso ] : '',
        trim( (string) rac_campo( 'artista_genero', $id, '' ) ),
    ), 'strlen' );
    return $partes ? 'Descoberta Musical: ' . implode( ' - ', $partes ) : '';
}

/** Depois que o ACF salva os campos, grava o título (e o endereço) automático. */
function rac_aplicar_titulo_automatico( $post_id ) {
    if ( ! is_numeric( $post_id ) || get_post_type( $post_id ) !== 'post' ) return;
    if ( rac_formato_do_post( $post_id ) !== 'musica' || ! rac_campo( 'artista_titulo_auto', $post_id, 0 ) ) return;
    $titulo = rac_titulo_musica( $post_id );
    if ( $titulo === '' ) return;

    $post  = get_post( $post_id );
    $dados = array( 'ID' => $post_id, 'post_title' => $titulo );
    $ultimo = get_post_meta( $post_id, '_rac_slug_auto', true );
    // só troca o endereço se ele ainda é o automático (vazio, numérico ou o último gerado)
    if ( $post->post_name === '' || ctype_digit( $post->post_name ) || $post->post_name === $ultimo || $post->post_status === 'draft' ) {
        $slug = wp_unique_post_slug( sanitize_title( $titulo ), $post_id, $post->post_status, 'post', 0 );
        $dados['post_name'] = $slug;
        update_post_meta( $post_id, '_rac_slug_auto', $slug );
    }
    remove_action( 'acf/save_post', 'rac_aplicar_titulo_automatico', 20 );
    wp_update_post( $dados );
    add_action( 'acf/save_post', 'rac_aplicar_titulo_automatico', 20 );
}
add_action( 'acf/save_post', 'rac_aplicar_titulo_automatico', 20 );

/* ============================================================
   EMBEDS: VÍDEO E PLAYER DO ARTISTA
   ============================================================ */

/** Identifica YouTube/Vimeo: ['plataforma','id'] ou null. */
function rac_dados_video( $url ) {
    $url = trim( (string) $url );
    if ( preg_match( '#(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m ) ) {
        return array( 'plataforma' => 'youtube', 'id' => $m[1] );
    }
    if ( preg_match( '#vimeo\.com/(?:video/)?(\d+)#', $url, $m ) ) {
        return array( 'plataforma' => 'vimeo', 'id' => $m[1] );
    }
    return null;
}

/** Endereço do iframe (YouTube no modo de privacidade aprimorada). */
function rac_src_video( $dados, $inicio = 0, $autoplay = false ) {
    if ( $dados['plataforma'] === 'youtube' ) {
        $args = array( 'rel' => 0 );
        if ( $inicio > 0 ) $args['start'] = (int) $inicio;
        if ( $autoplay ) $args['autoplay'] = 1;
        return add_query_arg( $args, 'https://www.youtube-nocookie.com/embed/' . $dados['id'] );
    }
    $src = 'https://player.vimeo.com/video/' . $dados['id'] . ( $autoplay ? '?autoplay=1' : '' );
    return $inicio > 0 ? $src . '#t=' . (int) $inicio . 's' : $src;
}

/** "mm:ss", "h:mm:ss" ou "ss" → segundos. */
function rac_segundos( $texto ) {
    $partes = array_reverse( array_map( 'intval', explode( ':', trim( $texto ) ) ) );
    $seg = 0; $mult = 1;
    foreach ( array_slice( $partes, 0, 3 ) as $p ) { $seg += $p * $mult; $mult *= 60; }
    return $seg;
}

/** Capítulos do vídeo: linhas "mm:ss | título" → [ ['seg','tempo','nome'] ]. */
function rac_capitulos( $texto ) {
    $saida = array();
    foreach ( preg_split( '/\R/', (string) $texto ) as $linha ) {
        if ( ! preg_match( '/^\s*((?:\d{1,2}:)?\d{1,2}:\d{2})\s*[|\-–—]\s*(.+?)\s*$/u', $linha, $m ) ) continue;
        $saida[] = array( 'seg' => rac_segundos( $m[1] ), 'tempo' => $m[1], 'nome' => $m[2] );
    }
    return $saida;
}

/** Player do artista: Spotify (artista, álbum, faixa, playlist, episódio) ou outra plataforma aceita pelo WordPress. */
function rac_embed_artista_html( $url ) {
    $url = trim( (string) $url );
    if ( $url === '' ) return '';
    if ( preg_match( '#open\.spotify\.com/(?:intl-[a-z-]+/)?(artist|album|track|playlist|episode|show)/([A-Za-z0-9]+)#', $url, $m ) ) {
        $altura = in_array( $m[1], array( 'track', 'episode' ), true ) ? 152 : 352;
        return '<iframe class="rac-spotify" src="' . esc_url( 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2] . '?utm_source=generator' ) . '" width="100%" height="' . $altura . '" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" title="Player do Spotify"></iframe>';
    }
    $html = wp_oembed_get( $url );
    return $html ? '<div class="rac-embed-outro">' . $html . '</div>' : '';
}

/* ============================================================
   DADOS DO POST
   ============================================================ */

/** "6 min de leitura". */
function rac_tempo_leitura( $id ) {
    $texto    = wp_strip_all_tags( get_post_field( 'post_content', $id ) );
    $palavras = preg_match_all( '/[\p{L}\p{N}]+/u', $texto );
    return max( 1, (int) ceil( $palavras / 200 ) ) . ' min de leitura';
}

/** Número da tirinha: o campo, ou a posição entre as tirinhas publicadas. */
function rac_tirinha_numero( $id ) {
    $n = (int) rac_campo( 'tirinha_numero', $id, 0 );
    if ( $n > 0 ) return $n;
    $q = new WP_Query( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_key' => 'formato', 'meta_value' => 'tirinha',
        'date_query' => array( array( 'before' => get_post_field( 'post_date', $id ), 'inclusive' => true ) ),
    ) );
    return (int) $q->found_posts;
}

/** Tirinha vizinha (anterior/próxima), ou 0. */
function rac_tirinha_vizinha( $id, $direcao ) {
    $anterior = $direcao === 'anterior';
    $ids = get_posts( array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_key' => 'formato', 'meta_value' => 'tirinha', 'post__not_in' => array( $id ),
        'orderby' => 'date', 'order' => $anterior ? 'DESC' : 'ASC',
        'date_query' => array( array( $anterior ? 'before' : 'after' => get_post_field( 'post_date', $id ), 'inclusive' => false ) ),
    ) );
    return $ids ? (int) $ids[0] : 0;
}

/** Relacionados: tags em comum, depois a mesma categoria, depois os mais recentes. */
function rac_posts_relacionados( $id, $limite = 3 ) {
    $ids = array();
    $base = array( 'post_type' => 'post', 'post_status' => 'publish', 'fields' => 'ids', 'no_found_rows' => true, 'ignore_sticky_posts' => true );
    $tags = wp_get_post_tags( $id, array( 'fields' => 'ids' ) );
    if ( $tags ) {
        $ids = get_posts( $base + array( 'posts_per_page' => $limite, 'post__not_in' => array( $id ), 'tag__in' => $tags ) );
    }
    if ( count( $ids ) < $limite ) {
        $cats = wp_get_post_categories( $id );
        if ( $cats ) {
            $mais = get_posts( $base + array( 'posts_per_page' => $limite - count( $ids ), 'post__not_in' => array_merge( array( $id ), $ids ), 'category__in' => $cats ) );
            $ids = array_merge( $ids, $mais );
        }
    }
    if ( count( $ids ) < $limite ) {
        $mais = get_posts( $base + array( 'posts_per_page' => $limite - count( $ids ), 'post__not_in' => array_merge( array( $id ), $ids ) ) );
        $ids = array_merge( $ids, $mais );
    }
    return array_map( 'intval', $ids );
}

/** Links de compartilhamento (sem chave de API e sem script de terceiros). */
function rac_links_compartilhar( $id ) {
    $url    = get_permalink( $id );
    $titulo = wp_strip_all_tags( get_the_title( $id ) );
    return array(
        'whatsapp' => 'https://wa.me/?text=' . rawurlencode( $titulo . ' ' . $url ),
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ),
        'x'        => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $titulo ) . '&url=' . rawurlencode( $url ),
        'url'      => $url,
        'titulo'   => $titulo,
    );
}
