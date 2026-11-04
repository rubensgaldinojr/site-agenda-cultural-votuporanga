<?php
/**
 * Listagem do Blog (Fase 4A): página Blog, categorias (inclusive Podcasts) e tags.
 *
 * Os filtros são LINKS com parâmetros na própria URL (?categoria=&autor=&ordem=),
 * não formulários: assim a busca é compartilhável e, como são links, a navegação
 * persistente do player continua valendo (um formulário recarregaria a página e
 * cortaria o áudio). 6 posts por página, no desktop e no mobile.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'RAC_POSTS_POR_PAGINA', 6 );

/** Opções de ordenação. */
function rac_ordens() {
    return array(
        'recentes' => 'Mais recentes primeiro',
        'antigos'  => 'Mais antigos primeiro',
        'titulo'   => 'Título (A–Z)',
        'autor'    => 'Autor (A–Z)',
    );
}

/** Endereço da página do Blog (página de posts ou, se não houver, /blog/). */
function rac_url_blog() {
    $id = (int) get_option( 'page_for_posts' );
    return $id ? get_permalink( $id ) : rac_url_pagina( 'blog' );
}

/** 'blog' | 'categoria' | 'tag' */
function rac_contexto_listagem() {
    if ( is_category() ) return 'categoria';
    if ( is_tag() ) return 'tag';
    return 'blog';
}

/** Filtros atuais da URL, já saneados. */
function rac_param_listagem() {
    $ordens = rac_ordens();
    $ordem  = isset( $_GET['ordem'] ) ? sanitize_key( wp_unslash( $_GET['ordem'] ) ) : 'recentes';
    if ( ! isset( $ordens[ $ordem ] ) ) $ordem = 'recentes';
    return array(
        'categoria' => isset( $_GET['categoria'] ) ? sanitize_title( wp_unslash( $_GET['categoria'] ) ) : '',
        'autor'     => isset( $_GET['autor'] ) ? sanitize_title( wp_unslash( $_GET['autor'] ) ) : '',
        'ordem'     => $ordem,
    );
}

/** Variáveis de consulta para cada ordenação. */
function rac_vars_ordem( $ordem ) {
    switch ( $ordem ) {
        case 'antigos': return array( 'orderby' => 'date', 'order' => 'ASC' );
        case 'titulo':  return array( 'orderby' => 'title', 'order' => 'ASC' );
        case 'autor':   return array( 'orderby' => 'date', 'order' => 'DESC', 'rac_ordem_autor' => 1 );
        default:        return array( 'orderby' => 'date', 'order' => 'DESC' );
    }
}

/** Ordenação por nome de exibição do autor (junta a tabela de usuários). */
function rac_clausulas_autor( $clausulas, $consulta ) {
    if ( ! $consulta->get( 'rac_ordem_autor' ) ) return $clausulas;
    global $wpdb;
    $clausulas['join']   .= " LEFT JOIN {$wpdb->users} rac_u ON rac_u.ID = {$wpdb->posts}.post_author";
    $clausulas['orderby'] = "rac_u.display_name ASC, {$wpdb->posts}.post_date DESC";
    return $clausulas;
}
add_filter( 'posts_clauses', 'rac_clausulas_autor', 10, 2 );

/** Ajusta a consulta principal das listagens e do autor. */
function rac_ajustar_consulta( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;

    if ( $q->is_home() || $q->is_category() || $q->is_tag() || $q->is_author() ) {
        $q->set( 'posts_per_page', RAC_POSTS_POR_PAGINA );
        $q->set( 'ignore_sticky_posts', true );
    }
    if ( $q->is_home() || $q->is_category() || $q->is_tag() ) {
        $p = rac_param_listagem();
        if ( $p['autor'] ) $q->set( 'author_name', $p['autor'] );
        if ( $p['categoria'] && ! $q->is_category() ) $q->set( 'category_name', $p['categoria'] );
        foreach ( rac_vars_ordem( $p['ordem'] ) as $chave => $valor ) $q->set( $chave, $valor );
    }
}
add_action( 'pre_get_posts', 'rac_ajustar_consulta' );

/**
 * Consulta da listagem. Na página de posts, nas categorias e nas tags é a
 * consulta principal; se o Blog for uma página comum (page-blog.php), monta uma
 * consulta própria com os mesmos filtros.
 */
function rac_consulta_listagem() {
    global $wp_query;
    if ( is_home() || is_category() || is_tag() ) return $wp_query;

    $p    = rac_param_listagem();
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => RAC_POSTS_POR_PAGINA,
        'paged'               => max( 1, (int) get_query_var( 'paged' ) ),
        'ignore_sticky_posts' => true,
    );
    if ( $p['autor'] ) $args['author_name'] = $p['autor'];
    if ( $p['categoria'] ) $args['category_name'] = $p['categoria'];
    return new WP_Query( array_merge( $args, rac_vars_ordem( $p['ordem'] ) ) );
}

/** Endereço base da listagem atual (sem filtros nem página). */
function rac_base_listagem() {
    if ( is_category() ) return get_category_link( get_queried_object_id() );
    if ( is_tag() ) return get_tag_link( get_queried_object_id() );
    return rac_url_blog();
}

/** URL da listagem atual com algum filtro trocado (valor '' remove o filtro). */
function rac_url_listagem( $mudar = array() ) {
    $p    = array_merge( rac_param_listagem(), $mudar );
    $args = array();
    if ( $p['categoria'] && ! is_category() ) $args['categoria'] = $p['categoria'];
    if ( $p['autor'] ) $args['autor'] = $p['autor'];
    if ( $p['ordem'] !== 'recentes' ) $args['ordem'] = $p['ordem'];
    return add_query_arg( $args, rac_base_listagem() );
}

/** URL de uma categoria no seletor "Categoria", mantendo autor e ordem. */
function rac_url_categoria_filtro( $termo ) {
    $p    = rac_param_listagem();
    $args = array();
    if ( $p['autor'] ) $args['autor'] = $p['autor'];
    if ( $p['ordem'] !== 'recentes' ) $args['ordem'] = $p['ordem'];
    if ( is_category() ) return add_query_arg( $args, get_category_link( $termo ) );
    $args['categoria'] = $termo->slug;
    return add_query_arg( $args, rac_base_listagem() );
}

/** Categoria atualmente escolhida (slug). */
function rac_categoria_atual() {
    if ( is_category() ) {
        $t = get_queried_object();
        return $t ? $t->slug : '';
    }
    $p = rac_param_listagem();
    return $p['categoria'];
}

/** Opções do seletor de categoria: [ ['rotulo','url','ativa'], ... ]. */
function rac_opcoes_categoria() {
    $atual = rac_categoria_atual();
    $p     = rac_param_listagem();
    $args  = array();
    if ( $p['autor'] ) $args['autor'] = $p['autor'];
    if ( $p['ordem'] !== 'recentes' ) $args['ordem'] = $p['ordem'];

    $opcoes = array( array(
        'rotulo' => 'Todas as categorias',
        'url'    => is_category() ? add_query_arg( $args, rac_url_blog() ) : rac_url_listagem( array( 'categoria' => '' ) ),
        'ativa'  => $atual === '',
    ) );
    foreach ( get_categories( array( 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ) ) as $termo ) {
        $opcoes[] = array( 'rotulo' => $termo->name, 'url' => rac_url_categoria_filtro( $termo ), 'ativa' => $atual === $termo->slug );
    }
    return $opcoes;
}

/** Opções do seletor de autor(a). */
function rac_opcoes_autor() {
    $atual  = rac_param_listagem()['autor'];
    $opcoes = array( array( 'rotulo' => 'Todos', 'url' => rac_url_listagem( array( 'autor' => '' ) ), 'ativa' => $atual === '' ) );
    $usuarios = get_users( array( 'has_published_posts' => array( 'post' ), 'orderby' => 'display_name', 'order' => 'ASC' ) );
    foreach ( $usuarios as $u ) {
        $opcoes[] = array( 'rotulo' => $u->display_name, 'url' => rac_url_listagem( array( 'autor' => $u->user_nicename ) ), 'ativa' => $atual === $u->user_nicename );
    }
    return $opcoes;
}

/** Opções de ordenação. */
function rac_opcoes_ordem() {
    $atual  = rac_param_listagem()['ordem'];
    $opcoes = array();
    foreach ( rac_ordens() as $chave => $rotulo ) {
        $opcoes[] = array( 'rotulo' => $rotulo, 'url' => rac_url_listagem( array( 'ordem' => $chave ) ), 'ativa' => $atual === $chave );
    }
    return $opcoes;
}

/** Texto do valor escolhido num seletor (a opção ativa, ou o padrão). */
function rac_valor_ativo( $opcoes ) {
    foreach ( $opcoes as $o ) { if ( $o['ativa'] ) return $o['rotulo']; }
    return $opcoes ? $opcoes[0]['rotulo'] : '';
}

/** Filtros removíveis (etiquetas) e o endereço de "Limpar filtros". */
function rac_filtros_ativos() {
    $p        = rac_param_listagem();
    $etiquetas = array();
    if ( $p['categoria'] && ! is_category() ) {
        $t = get_category_by_slug( $p['categoria'] );
        $etiquetas[] = array( 'rotulo' => $t ? $t->name : $p['categoria'], 'url' => rac_url_listagem( array( 'categoria' => '' ) ) );
    }
    if ( $p['autor'] ) {
        $u = get_user_by( 'slug', $p['autor'] );
        $etiquetas[] = array( 'rotulo' => $u ? $u->display_name : $p['autor'], 'url' => rac_url_listagem( array( 'autor' => '' ) ) );
    }
    return array( 'etiquetas' => $etiquetas, 'limpar' => rac_base_listagem(), 'tem' => ( $p['categoria'] && ! is_category() ) || $p['autor'] || $p['ordem'] !== 'recentes' );
}

/** "N posts" */
function rac_rotulo_posts( $n ) {
    return $n === 1 ? '1 post' : $n . ' posts';
}

/** Cabeçalho da listagem: eyebrow, título e texto de apoio. */
function rac_cabecalho_listagem() {
    if ( is_category() ) {
        $t = get_queried_object();
        $podcasts = $t && $t->slug === 'podcasts';
        return array(
            'eyebrow' => $podcasts ? 'Podcasts' : 'Categoria',
            'titulo'  => $t->name,
            'texto'   => trim( wp_strip_all_tags( $t->description ) ) ?: ( $podcasts ? 'Conversas, entrevistas e histórias para ouvir sem pressa.' : 'Posts desta categoria.' ),
        );
    }
    if ( is_tag() ) {
        $t = get_queried_object();
        return array(
            'eyebrow' => 'Tag',
            'titulo'  => '#' . $t->name,
            'texto'   => trim( wp_strip_all_tags( $t->description ) ) ?: 'Posts marcados com "' . $t->name . '".',
        );
    }
    $pagina = (int) get_option( 'page_for_posts' ) ?: (int) get_queried_object_id();
    return array(
        'eyebrow' => 'Blog',
        'titulo'  => rac_campo( 'blog_titulo', $pagina, 'Crônicas, descobertas e boas conversas' ),
        'texto'   => rac_campo( 'blog_texto', $pagina, 'Textos, poesia, vídeos, tirinhas e descobertas musicais da cena cultural de Votuporanga e de outros cantos do mundo.' ),
    );
}

/** Links numerados da paginação: [ ['n','url','ativa'] ] + anterior/próxima. */
function rac_paginacao( $consulta ) {
    $total = max( 1, (int) $consulta->max_num_pages );
    $atual = max( 1, (int) ( $consulta->get( 'paged' ) ?: 1 ) );
    $saida = array( 'total' => $total, 'atual' => $atual, 'paginas' => array(), 'anterior' => '', 'proxima' => '' );
    for ( $n = 1; $n <= $total; $n++ ) {
        $saida['paginas'][] = array( 'n' => $n, 'url' => get_pagenum_link( $n ), 'ativa' => $n === $atual );
    }
    if ( $atual > 1 ) $saida['anterior'] = get_pagenum_link( $atual - 1 );
    if ( $atual < $total ) $saida['proxima'] = get_pagenum_link( $atual + 1 );
    return $saida;
}
