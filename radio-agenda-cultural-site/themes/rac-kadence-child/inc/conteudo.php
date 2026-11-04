<?php
/**
 * Conteúdo da Home (3B-2): posts recentes, livros, séries de podcasts.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** IDs dos posts mais recentes (para o carrossel do Blog). */
function rac_posts_recentes( $n = 6 ) {
    return array_map( 'intval', get_posts( array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $n,
        'fields'              => 'ids',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ) ) );
}

/** IDs dos livros mais recentes (CPT "livro" do plugin de catálogo). */
function rac_livros_recentes( $n = 8 ) {
    return array_map( 'intval', get_posts( array(
        'post_type'      => 'livro',
        'post_status'    => 'publish',
        'posts_per_page' => $n,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) ) );
}

/**
 * Livros para a página de uma estação ("Da biblioteca"): primeiro os cuja
 * categoria de livro tem o MESMO NOME da estação; se faltar, completa com
 * os mais recentes.
 *
 * @return int[] IDs de livros.
 */
function rac_livros_para_estacao( $nome, $limite = 6 ) {
    $ids   = array();
    $termo = get_term_by( 'name', $nome, 'categoria_livro' );
    if ( ! $termo ) $termo = get_term_by( 'slug', sanitize_title( $nome ), 'categoria_livro' );

    if ( $termo && ! is_wp_error( $termo ) ) {
        $ids = get_posts( array(
            'post_type'      => 'livro',
            'post_status'    => 'publish',
            'posts_per_page' => $limite,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'tax_query'      => array( array( 'taxonomy' => 'categoria_livro', 'field' => 'term_id', 'terms' => (int) $termo->term_id ) ),
        ) );
    }
    if ( count( $ids ) < $limite ) {
        $mais = get_posts( array(
            'post_type'      => 'livro',
            'post_status'    => 'publish',
            'posts_per_page' => $limite - count( $ids ),
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'post__not_in'   => $ids ? $ids : array( 0 ),
        ) );
        $ids = array_merge( $ids, $mais );
    }
    return array_map( 'intval', $ids );
}

/**
 * Séries de podcast: subcategorias da categoria "podcasts" que já têm
 * ao menos um post. Sem subcategorias, a seção da Home não aparece.
 *
 * @return array Lista de ['nome', 'descricao', 'episodios' (int), 'url'].
 */
function rac_series_podcast() {
    $pai = get_category_by_slug( 'podcasts' );
    if ( ! $pai ) return array();

    $termos = get_terms( array( 'taxonomy' => 'category', 'parent' => (int) $pai->term_id, 'hide_empty' => true ) );
    if ( is_wp_error( $termos ) || ! $termos ) return array();

    $series = array();
    foreach ( $termos as $t ) {
        $series[] = array(
            'nome'      => $t->name,
            'descricao' => trim( wp_strip_all_tags( $t->description ) ),
            'episodios' => (int) $t->count,
            'url'       => get_term_link( $t ),
        );
    }
    return $series;
}
