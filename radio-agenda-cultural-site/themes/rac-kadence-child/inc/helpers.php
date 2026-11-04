<?php
/**
 * Funções auxiliares do tema: menu, logo e lista de estações.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   MENU
   ============================================================ */

/**
 * Itens do menu principal. Se existir um menu atribuído ao local
 * "Menu principal" (Aparência → Menus), usa os itens de primeiro nível
 * dele; senão, usa o menu padrão do mockup.
 *
 * Os caminhos do menu padrão são suposições (/estacoes/, /blog/, ...).
 * Se as páginas reais tiverem outros endereços, basta atribuir um menu
 * no painel; ele sempre tem prioridade.
 *
 * @return array Lista de ['label' => string, 'url' => string].
 */
function rac_itens_menu() {
    $locais = get_nav_menu_locations();

    if ( ! empty( $locais['rac-principal'] ) ) {
        $itens = wp_get_nav_menu_items( $locais['rac-principal'] );
        if ( $itens ) {
            $lista = array();
            foreach ( $itens as $item ) {
                if ( (int) $item->menu_item_parent !== 0 ) continue; // só primeiro nível
                $lista[] = array( 'label' => $item->title, 'url' => $item->url );
            }
            if ( $lista ) return $lista;
        }
    }

    $pagina_blog = (int) get_option( 'page_for_posts' );
    $podcasts    = get_category_by_slug( 'podcasts' );

    return apply_filters( 'rac_menu_padrao', array(
        array( 'label' => 'Estações',   'url' => home_url( '/estacoes/' ) ),
        array( 'label' => 'Blog',       'url' => $pagina_blog ? get_permalink( $pagina_blog ) : home_url( '/blog/' ) ),
        array( 'label' => 'Literatura', 'url' => home_url( '/literatura/' ) ),
        array( 'label' => 'Podcasts',   'url' => $podcasts ? get_category_link( $podcasts ) : home_url( '/podcasts/' ) ),
        array( 'label' => 'Sobre',      'url' => home_url( '/sobre/' ) ),
    ) );
}

/**
 * Menu do mobile e do rodapé: os mesmos itens + "Contato" (que no desktop
 * só aparece no rodapé), a menos que já esteja no menu.
 */
function rac_itens_menu_completo() {
    $itens = rac_itens_menu();
    foreach ( $itens as $item ) {
        if ( mb_strtolower( trim( $item['label'] ) ) === 'contato' ) {
            return $itens;
        }
    }
    $itens[] = array( 'label' => 'Contato', 'url' => home_url( '/contato/' ) );
    return $itens;
}

/**
 * Marca qual item do menu corresponde à página atual. Mesma regra do JS
 * (assets/js/rac.js → marcarMenuAtivo): vence o item cujo caminho casa
 * por igualdade ou prefixo e é mais longo; se o item tem "?query", ela
 * precisa ser igual à da página.
 *
 * @param array $itens Itens de rac_itens_menu*().
 * @return array Os mesmos itens com a chave 'ativo' (bool).
 */
function rac_marcar_item_ativo( $itens ) {
    $requisicao = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $atual      = wp_parse_url( $requisicao );
    $caminho    = isset( $atual['path'] ) ? untrailingslashit( $atual['path'] ) : '';
    $consulta   = isset( $atual['query'] ) ? '?' . $atual['query'] : '';

    $melhor = -1;
    $pontos = array();
    foreach ( $itens as $i => $item ) {
        $url_item = wp_parse_url( $item['url'] );
        $c_item   = isset( $url_item['path'] ) ? untrailingslashit( $url_item['path'] ) : '';
        $q_item   = isset( $url_item['query'] ) ? '?' . $url_item['query'] : '';

        $pontos[ $i ] = -1;
        if ( $c_item === '' ) continue; // link para a home não marca nada
        if ( $q_item !== '' && $q_item !== $consulta ) continue;
        if ( $caminho === $c_item || strpos( $caminho, $c_item . '/' ) === 0 ) {
            $pontos[ $i ] = strlen( $c_item ) * 10 + ( $q_item !== '' ? 5 : 0 );
            if ( $pontos[ $i ] > $melhor ) $melhor = $pontos[ $i ];
        }
    }
    foreach ( $itens as $i => $item ) {
        $itens[ $i ]['ativo'] = ( $melhor >= 0 && $pontos[ $i ] === $melhor );
    }
    return $itens;
}

/* ============================================================
   LOGO
   ============================================================ */

/**
 * HTML da logo. Ordem de preferência:
 *  1. logo do site (Aparência → Personalizar → Identidade do site);
 *  2. se também houver "Logo para fundo escuro", troca entre as duas
 *     conforme o tema (CSS: .rac-logo--claro / .rac-logo--escuro);
 *  3. sem logo escura, a logo clara ganha um filtro no modo escuro
 *     (.rac-logo--auto), como no tema.css do mockup;
 *  4. sem logo nenhuma, mostra o nome do site em texto.
 *
 * @param bool $prioritaria true no cabeçalho (acima da dobra, sem lazy-load).
 */
function rac_logo_html( $prioritaria = false ) {
    $nome   = get_bloginfo( 'name' );
    $claro  = (int) get_theme_mod( 'custom_logo' );
    $escuro = (int) get_theme_mod( 'rac_logo_escuro' );
    $carga  = $prioritaria ? 'eager' : 'lazy';

    if ( ! $claro ) {
        return '<span class="rac-logo-texto">' . esc_html( $nome ) . '</span>';
    }

    if ( $escuro ) {
        return wp_get_attachment_image( $claro, 'full', false, array( 'class' => 'rac-logo rac-logo--claro', 'alt' => $nome, 'loading' => $carga ) )
             . wp_get_attachment_image( $escuro, 'full', false, array( 'class' => 'rac-logo rac-logo--escuro', 'alt' => '', 'loading' => $carga ) );
    }

    return wp_get_attachment_image( $claro, 'full', false, array( 'class' => 'rac-logo rac-logo--auto', 'alt' => $nome, 'loading' => $carga ) );
}

/* ============================================================
   ESTAÇÕES (para a grade "Todas as estações" do rodapé)
   ============================================================ */

/**
 * Lista de estações para links, cards e player. Reaproveita
 * racr_obter_estacoes() do plugin do player (já ordenada por
 * "ordem_de_exibicao" e depois alfabética, e só com estações que têm
 * stream). Se o plugin estiver desativado, cai numa consulta simples ao
 * CPT "estacao".
 *
 * Cada estação é um gênero: o nome da estação faz o papel do "gênero"
 * do mockup, então não existe campo de gênero separado.
 *
 * @return array Lista de ['id', 'nome', 'url', 'capa', 'indice'].
 *               'indice' é a posição no player (null sem o plugin) e
 *               'capa' é a imagem destacada em miniatura; sem ela, a URL da
 *               imagem placeholder do tema (nunca vem vazia).
 */
function rac_listar_estacoes() {
    static $lista = null;
    if ( $lista !== null ) return $lista;

    $lista = array();

    if ( function_exists( 'racr_obter_estacoes' ) ) {
        foreach ( racr_obter_estacoes() as $indice => $e ) {
            $lista[] = array(
                'id'     => $e['id'],
                'nome'   => $e['nome'],
                'url'    => get_permalink( $e['id'] ),
                'capa'   => rac_url_imagem_estacao( $e['id'], 'thumbnail' ),
                'indice' => $indice,
            );
        }
        return $lista;
    }

    $posts = get_posts( array(
        'post_type'      => 'estacao',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ) );
    foreach ( $posts as $p ) {
        $lista[] = array(
            'id'     => $p->ID,
            'nome'   => get_the_title( $p ),
            'url'    => get_permalink( $p ),
            'capa'   => rac_url_imagem_estacao( $p->ID, 'thumbnail' ),
            'indice' => null,
        );
    }
    return $lista;
}

/**
 * Estações em ordem alfabética (ignora maiúsculas e acentos). É a ordem
 * da página de Estações e das "Outras estações". O player e o rodapé
 * seguem a ordem do plugin ("ordem_de_exibicao" e depois alfabética).
 */
function rac_listar_estacoes_alfabetica() {
    $lista = rac_listar_estacoes();
    usort( $lista, function ( $a, $b ) {
        return strcmp( remove_accents( mb_strtolower( $a['nome'] ) ), remove_accents( mb_strtolower( $b['nome'] ) ) );
    } );
    return $lista;
}

/** Dados de uma estação tocável (com stream) pelo ID, ou null. */
function rac_estacao_por_id( $id ) {
    foreach ( rac_listar_estacoes() as $e ) {
        if ( (int) $e['id'] === (int) $id ) return $e;
    }
    return null;
}

/**
 * "Outras estações": as N seguintes à atual na ordem alfabética, voltando
 * ao início quando chega no fim. Assim cada página mostra um conjunto
 * diferente, sem repetir a estação atual.
 */
function rac_outras_estacoes( $id_atual, $n = 4 ) {
    $lista  = rac_listar_estacoes_alfabetica();
    $indice = -1;
    foreach ( $lista as $i => $e ) {
        if ( (int) $e['id'] === (int) $id_atual ) { $indice = $i; break; }
    }
    $total = count( $lista );
    $saida = array();
    for ( $passo = 1; $passo <= $total && count( $saida ) < $n; $passo++ ) {
        $e = $lista[ ( $indice + $passo + $total ) % $total ];
        if ( (int) $e['id'] !== (int) $id_atual ) $saida[] = $e;
    }
    return $saida;
}

/* ============================================================
   PÁGINAS E CAMPOS
   ============================================================ */

/** Endereço de uma página pelo slug (ex.: "estacoes"), com plano B. */
function rac_url_pagina( $slug ) {
    $pagina = get_page_by_path( $slug );
    return $pagina ? get_permalink( $pagina ) : home_url( '/' . $slug . '/' );
}

/**
 * Lê um campo ACF; se o ACF estiver desativado ou o campo vazio, devolve
 * o valor padrão.
 */
function rac_campo( $nome, $post_id = null, $padrao = '' ) {
    if ( function_exists( 'get_field' ) ) {
        $valor = get_field( $nome, $post_id );
        if ( $valor !== null && $valor !== false && $valor !== '' ) return $valor;
    }
    return $padrao;
}

/** URL da imagem placeholder do tema. */
function rac_url_placeholder() {
    return get_stylesheet_directory_uri() . '/assets/img/placeholder-topo.jpg';
}

/** URL da imagem de uma estação (no tamanho pedido) ou, se não houver, a do placeholder. */
function rac_url_imagem_estacao( $id, $tamanho = 'thumbnail' ) {
    $url = get_the_post_thumbnail_url( $id, $tamanho );
    return $url ? $url : rac_url_placeholder();
}

/** <img> de uma estação (imagem destacada ou placeholder), para cards e tiles. */
function rac_html_imagem_estacao( $id, $tamanho = 'medium_large' ) {
    if ( has_post_thumbnail( $id ) ) {
        return get_the_post_thumbnail( $id, $tamanho, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
    }
    return '<img src="' . esc_url( rac_url_placeholder() ) . '" alt="" width="1200" height="675" loading="lazy" decoding="async">';
}

/**
 * Trecho curto do "Texto Sobre a Estação" (para o card grande). Junta os
 * parágrafos com espaço antes de tirar as tags e corta em N palavras.
 * Devolve '' quando a estação não tem texto.
 */
function rac_trecho_estacao( $id, $palavras = 18 ) {
    $html = rac_campo( 'texto_sobre_a_estacao', $id, '' );
    if ( ! $html ) return '';
    $html  = preg_replace( '#</(p|li|div|h[1-6])>|<br\s*/?>#i', ' ', (string) $html );
    $texto = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $html ) ) );
    return $texto === '' ? '' : wp_trim_words( $texto, $palavras, '…' );
}

/**
 * <img> do topo (hero): imagem destacada do post/página ou, se não
 * houver, a imagem placeholder do tema (assets/img/placeholder-topo.jpg).
 * Para trocar o placeholder, basta definir a imagem destacada.
 */
function rac_html_imagem_topo( $post_id, $classe = 'rac-hero__img' ) {
    if ( $post_id && has_post_thumbnail( $post_id ) ) {
        return get_the_post_thumbnail( $post_id, 'full', array(
            'class'         => $classe,
            'alt'           => '',
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'decoding'      => 'async',
        ) );
    }
    return '<img class="' . esc_attr( $classe ) . '" src="' . esc_url( rac_url_placeholder() ) . '" alt="" width="1200" height="675" loading="eager" decoding="async">';
}

/**
 * Posts para "Para ler enquanto ouve" numa estação: primeiro os que têm
 * tag ou categoria com o MESMO NOME da estação; se faltar, completa com
 * os mais recentes.
 *
 * @return int[] IDs de posts.
 */
function rac_posts_para_estacao( $nome, $limite = 3 ) {
    $ids       = array();
    $tax_query = array( 'relation' => 'OR' );

    foreach ( array( 'post_tag', 'category' ) as $taxonomia ) {
        $termo = get_term_by( 'name', $nome, $taxonomia );
        if ( ! $termo ) $termo = get_term_by( 'slug', sanitize_title( $nome ), $taxonomia );
        if ( $termo && ! is_wp_error( $termo ) ) {
            $tax_query[] = array( 'taxonomy' => $taxonomia, 'field' => 'term_id', 'terms' => (int) $termo->term_id );
        }
    }

    if ( count( $tax_query ) > 1 ) {
        $ids = get_posts( array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $limite,
            'fields'              => 'ids',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'tax_query'           => $tax_query,
        ) );
    }

    if ( count( $ids ) < $limite ) {
        $mais = get_posts( array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $limite - count( $ids ),
            'fields'              => 'ids',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'post__not_in'        => $ids ? $ids : array( 0 ),
        ) );
        $ids = array_merge( $ids, $mais );
    }
    return array_map( 'intval', $ids );
}
