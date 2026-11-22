<?php
/**
 * Página Sobre (Fase 5A): campos de texto da página, e a lista de pessoas de
 * "Quem faz" (usuários marcados em "Aparecer na página Sobre" no perfil, na ordem
 * escolhida), cada uma com até 3 itens: o livro e os textos mais recentes.
 *
 * Os 3 destaques (Estações, Literatura, Blog) reaproveitam os textos da Home,
 * então são editados num lugar só.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Textos provisórios do mockup (usados quando o campo da página está vazio). */
function rac_sobre_padroes() {
    return array(
        'sobre_selo'          => 'Votuporanga, SP — Brasil',
        'sobre_titulo'        => 'Uma rádio que nasceu de uma coleção de discos',
        'sobre_texto'         => 'A Agenda Cultural é uma rádio web independente, feita no interior paulista por gente que ama música, livros e boa conversa.',
        'sobre_historia_rotulo' => 'Nossa história',
        'sobre_historia_titulo' => 'De uma estante de vinis para o mundo',
        'sobre_historia_texto'  => '<p>Tudo começou em Votuporanga, com uma estante cheia de vinis e a vontade de compartilhar o que tocava dentro de casa.</p>'
            . '<p>Durante anos, os discos circularam entre amigos, em fitas gravadas e em tardes de domingo com a vitrola ligada. Quando a internet tornou possível transmitir para qualquer lugar, a ideia ficou óbvia: transformar aquela coleção em rádio.</p>'
            . '<p>A primeira estação tocava jazz e bossa nova, montada à mão, faixa por faixa. Os ouvintes foram chegando — primeiro da cidade, depois de outros estados e até de fora do país — e com eles vieram pedidos, sugestões e novas estações.</p>'
            . '<p>Hoje a Agenda Cultural reúne estações temáticas tocando 24 horas por dia, uma biblioteca gratuita de literatura e um blog com crônicas, entrevistas e descobertas musicais. Continuamos independentes, sem pressa e com o mesmo cuidado de quem escolhe o próximo disco para um amigo.</p>',
        'sobre_equipe_titulo' => 'As pessoas por trás da rádio',
        'sobre_cta_titulo'    => 'Tem uma música para sugerir?',
        'sobre_cta_texto'     => 'Pedidos, ideias de estação, parcerias ou só um oi — a gente lê tudo.',
    );
}

/** Texto da página Sobre (campo da página; vazio = texto provisório do mockup). */
function rac_sobre_texto( $nome, $pagina_id = null ) {
    $padroes = rac_sobre_padroes();
    return (string) rac_campo( $nome, $pagina_id, isset( $padroes[ $nome ] ) ? $padroes[ $nome ] : '' );
}

/** Campos da página (aparecem ao escolher o modelo "Sobre" em Atributos da página). */
function rac_campos_sobre() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
    acf_add_local_field_group( array(
        'key'    => 'group_rac_pagina_sobre',
        'title'  => 'Textos da página Sobre',
        'fields' => array(
            array( 'key' => 'field_rac_sobre_aviso', 'label' => 'Como editar', 'name' => '', 'type' => 'message', 'message' => 'Campo vazio mostra o texto provisório. A foto do topo é a "Imagem destacada" da página. As pessoas de "Quem faz" vêm dos perfis dos usuários (marque "Aparecer na página Sobre"). Os 3 destaques usam os textos da página inicial.', 'new_lines' => 'wpautop', 'esc_html' => 0 ),
            array( 'key' => 'field_rac_sobre_selo', 'label' => 'Topo — selo', 'name' => 'sobre_selo', 'type' => 'text', 'placeholder' => 'Votuporanga, SP — Brasil' ),
            array( 'key' => 'field_rac_sobre_titulo', 'label' => 'Topo — título', 'name' => 'sobre_titulo', 'type' => 'text', 'placeholder' => 'Uma rádio que nasceu de uma coleção de discos' ),
            array( 'key' => 'field_rac_sobre_texto', 'label' => 'Topo — texto', 'name' => 'sobre_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '' ),
            array( 'key' => 'field_rac_sobre_historia_rotulo', 'label' => 'História — rótulo', 'name' => 'sobre_historia_rotulo', 'type' => 'text', 'placeholder' => 'Nossa história' ),
            array( 'key' => 'field_rac_sobre_historia_titulo', 'label' => 'História — título', 'name' => 'sobre_historia_titulo', 'type' => 'text', 'placeholder' => 'De uma estante de vinis para o mundo' ),
            array( 'key' => 'field_rac_sobre_historia_texto', 'label' => 'História — texto', 'name' => 'sobre_historia_texto', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0, 'instructions' => 'O primeiro parágrafo aparece maior, como abertura. Parágrafos separados por linha em branco.' ),
            array( 'key' => 'field_rac_sobre_equipe_titulo', 'label' => 'Quem faz — título', 'name' => 'sobre_equipe_titulo', 'type' => 'text', 'placeholder' => 'As pessoas por trás da rádio' ),
            array( 'key' => 'field_rac_sobre_cta_titulo', 'label' => 'Convite final — título', 'name' => 'sobre_cta_titulo', 'type' => 'text', 'placeholder' => 'Tem uma música para sugerir?' ),
            array( 'key' => 'field_rac_sobre_cta_texto', 'label' => 'Convite final — texto', 'name' => 'sobre_cta_texto', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '' ),
        ),
        'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-sobre.php' ) ) ),
    ) );
}
add_action( 'acf/init', 'rac_campos_sobre' );

/**
 * Pessoas de "Quem faz": usuários com "Aparecer na página Sobre" marcado, pela posição
 * escolhida (menor primeiro) e, no empate, em ordem alfabética.
 */
function rac_autores_sobre() {
    $usuarios = get_users( array(
        'meta_key'   => 'autor_no_sobre',
        'meta_value' => '1',
        'orderby'    => 'display_name',
        'order'      => 'ASC',
    ) );
    $lista = array();
    foreach ( $usuarios as $u ) {
        $ordem = (int) get_user_meta( $u->ID, 'autor_ordem_sobre', true );
        $lista[] = array( 'id' => (int) $u->ID, 'ordem' => $ordem > 0 ? $ordem : PHP_INT_MAX, 'nome' => $u->display_name );
    }
    usort( $lista, function ( $a, $b ) {
        if ( $a['ordem'] !== $b['ordem'] ) return $a['ordem'] <=> $b['ordem'];
        return strcmp( remove_accents( mb_strtolower( $a['nome'] ) ), remove_accents( mb_strtolower( $b['nome'] ) ) );
    } );
    return array_column( $lista, 'id' );
}

/**
 * Até 3 itens de uma pessoa: o livro mais recente (se houver) e os textos mais recentes.
 * Cada item: ['tipo' => 'Livro'|'Artigo', 'titulo', 'url', 'thumb' (HTML da imagem ou '')].
 */
function rac_itens_autor_sobre( $user_id, $max = 3 ) {
    $itens = array();

    $livros = get_posts( array( 'post_type' => 'livro', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
    foreach ( $livros as $l ) {
        $itens[] = array(
            'tipo'   => 'Livro',
            'titulo' => get_the_title( $l ),
            'url'    => rac_url_pagina( 'literatura' ),
            'thumb'  => get_the_post_thumbnail( $l->ID, 'thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ),
        );
    }

    $textos = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'author' => $user_id, 'posts_per_page' => $max - count( $itens ), 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
    foreach ( $textos as $t ) {
        $itens[] = array(
            'tipo'   => 'Artigo',
            'titulo' => get_the_title( $t ),
            'url'    => get_permalink( $t ),
            'thumb'  => get_the_post_thumbnail( $t->ID, 'thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ),
        );
    }
    return array_slice( $itens, 0, $max );
}

/** Foto quadrada-arredondada da pessoa (foto do perfil, Gravatar ou inicial sobre o listrado). */
function rac_foto_autor_html( $dados ) {
    if ( $dados['foto_id'] ) {
        return wp_get_attachment_image( $dados['foto_id'], 'medium_large', false, array( 'alt' => '', 'loading' => 'lazy' ) );
    }
    $inicial = '<b aria-hidden="true">' . esc_html( rac_inicial( $dados['nome'] ) ) . '</b>';
    if ( $dados['avatar_url'] ) {
        return $inicial . '<img src="' . esc_url( $dados['avatar_url'] ) . '" alt="" loading="lazy" onerror="this.remove()">';
    }
    return $inicial;
}
