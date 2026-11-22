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
        // 1º parágrafo = abertura; os demais ficam ao lado da Imagem 1. A "continuação" (ao lado da Imagem 2) só tem texto provisório
        // enquanto o texto principal também é o provisório (veja rac_sobre_historia()).
        'sobre_historia_texto'  => '<p>Tudo começou em Votuporanga, com uma estante cheia de vinis e a vontade de compartilhar o que tocava dentro de casa.</p>'
            . '<p>Durante anos, os discos circularam entre amigos, em fitas gravadas e em tardes de domingo com a vitrola ligada. Quando a internet tornou possível transmitir para qualquer lugar, a ideia ficou óbvia: transformar aquela coleção em rádio.</p>'
            . '<p>A primeira estação tocava jazz e bossa nova, montada à mão, faixa por faixa. Os ouvintes foram chegando — primeiro da cidade, depois de outros estados e até de fora do país — e com eles vieram pedidos, sugestões e novas estações.</p>',
        'sobre_historia_texto_2' => '<p>Hoje a Agenda Cultural reúne estações temáticas tocando 24 horas por dia, uma biblioteca gratuita de literatura e um blog com crônicas, entrevistas e descobertas musicais. Continuamos independentes, sem pressa e com o mesmo cuidado de quem escolhe o próximo disco para um amigo.</p>',
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

/** Campos da página (aparecem ao escolher o modelo "Sobre" em Atributos da página), em 3 abas. */
function rac_campos_sobre() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
    $aba = function ( $chave, $rotulo ) {
        return array( 'key' => 'field_rac_sobre_aba_' . $chave, 'label' => $rotulo, 'name' => '', 'type' => 'tab', 'placement' => 'top' );
    };
    acf_add_local_field_group( array(
        'key'    => 'group_rac_pagina_sobre',
        'title'  => 'Textos da página Sobre',
        'fields' => array(
            $aba( 'topo', 'Topo' ),
            array( 'key' => 'field_rac_sobre_aviso', 'label' => 'Como editar', 'name' => '', 'type' => 'message', 'message' => 'Campo vazio mostra o texto provisório. A foto do topo é a "Imagem destacada" da página. As pessoas de "Quem faz" vêm dos perfis dos usuários (marque "Aparecer na página Sobre"). Os 3 destaques usam os textos da página inicial.', 'new_lines' => 'wpautop', 'esc_html' => 0 ),
            array( 'key' => 'field_rac_sobre_selo', 'label' => 'Topo — selo', 'name' => 'sobre_selo', 'type' => 'text', 'placeholder' => 'Votuporanga, SP — Brasil' ),
            array( 'key' => 'field_rac_sobre_titulo', 'label' => 'Topo — título', 'name' => 'sobre_titulo', 'type' => 'text', 'placeholder' => 'Uma rádio que nasceu de uma coleção de discos' ),
            array( 'key' => 'field_rac_sobre_texto', 'label' => 'Topo — texto', 'name' => 'sobre_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '' ),

            $aba( 'historia', 'Nossa história' ),
            array( 'key' => 'field_rac_sobre_historia_aviso', 'label' => 'Como a história é montada', 'name' => '', 'type' => 'message', 'message' => 'A seção é montada em blocos, de cima para baixo: <strong>abertura</strong> (o 1º parágrafo do texto principal, em letra maior) → <strong>Imagem 1</strong> ao lado do resto do texto principal → <strong>continuação</strong> ao lado da <strong>Imagem 2</strong>. As imagens são opcionais: sem imagem, o texto ocupa a largura toda.', 'new_lines' => 'wpautop', 'esc_html' => 0 ),
            array( 'key' => 'field_rac_sobre_historia_rotulo', 'label' => 'Rótulo', 'name' => 'sobre_historia_rotulo', 'type' => 'text', 'placeholder' => 'Nossa história' ),
            array( 'key' => 'field_rac_sobre_historia_titulo', 'label' => 'Título', 'name' => 'sobre_historia_titulo', 'type' => 'text', 'placeholder' => 'De uma estante de vinis para o mundo' ),
            array( 'key' => 'field_rac_sobre_historia_texto', 'label' => 'Texto principal', 'name' => 'sobre_historia_texto', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0, 'instructions' => 'O 1º parágrafo vira a abertura (letra maior); os outros ficam ao lado da Imagem 1. Parágrafos separados por linha em branco.' ),
            array( 'key' => 'field_rac_sobre_imagem_1', 'label' => 'Imagem 1 (ao lado do texto principal)', 'name' => 'sobre_imagem_1', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'mime_types' => 'jpg,jpeg,png,webp', 'instructions' => 'Foto em pé (retrato, proporção 4:5), ex.: 1000×1250 px. Aparece à esquerda do texto.' ),
            array( 'key' => 'field_rac_sobre_imagem_1_legenda', 'label' => 'Legenda da Imagem 1 (opcional)', 'name' => 'sobre_imagem_1_legenda', 'type' => 'text' ),
            array( 'key' => 'field_rac_sobre_historia_texto_2', 'label' => 'Continuação (ao lado da Imagem 2)', 'name' => 'sobre_historia_texto_2', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0, 'instructions' => 'Opcional. Aparece à esquerda da Imagem 2. Se você preencher o texto principal e deixar este vazio, a Imagem 2 (se houver) aparece sozinha, em faixa larga.' ),
            array( 'key' => 'field_rac_sobre_imagem_2', 'label' => 'Imagem 2 (ao lado da continuação)', 'name' => 'sobre_imagem_2', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'mime_types' => 'jpg,jpeg,png,webp', 'instructions' => 'Foto deitada (paisagem, proporção 3:2), ex.: 1200×800 px. Aparece à direita do texto.' ),
            array( 'key' => 'field_rac_sobre_imagem_2_legenda', 'label' => 'Legenda da Imagem 2 (opcional)', 'name' => 'sobre_imagem_2_legenda', 'type' => 'text' ),

            $aba( 'equipe', 'Quem faz e convite' ),
            array( 'key' => 'field_rac_sobre_equipe_titulo', 'label' => 'Quem faz — título', 'name' => 'sobre_equipe_titulo', 'type' => 'text', 'placeholder' => 'As pessoas por trás da rádio' ),
            array( 'key' => 'field_rac_sobre_cta_titulo', 'label' => 'Convite final — título', 'name' => 'sobre_cta_titulo', 'type' => 'text', 'placeholder' => 'Tem uma música para sugerir?' ),
            array( 'key' => 'field_rac_sobre_cta_texto', 'label' => 'Convite final — texto', 'name' => 'sobre_cta_texto', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '' ),
        ),
        'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-sobre.php' ) ) ),
    ) );
}
add_action( 'acf/init', 'rac_campos_sobre' );

/**
 * Monta os blocos da seção "Nossa história".
 * Devolve: abertura (HTML), texto1 (HTML, ao lado da Imagem 1), texto2 (HTML, ao lado da Imagem 2),
 * img1/img2 (ID ou 0) e leg1/leg2 (legendas).
 *
 * Regras: o 1º parágrafo do texto principal é a abertura. A continuação só usa o texto provisório quando o texto
 * principal TAMBÉM está vazio (assim quem já preencheu a história não ganha um parágrafo provisório de brinde).
 */
function rac_sobre_historia( $pagina_id = null ) {
    $padroes   = rac_sobre_padroes();
    $principal = trim( (string) rac_campo( 'sobre_historia_texto', $pagina_id, '' ) );
    $provisorio = ( $principal === '' );
    if ( $provisorio ) $principal = $padroes['sobre_historia_texto'];

    $abertura = '';
    if ( preg_match( '#^\s*<p[^>]*>(.*?)</p>#is', $principal, $m ) ) {
        $abertura  = $m[1];
        $principal = trim( substr( $principal, strlen( $m[0] ) ) );
    }

    $continuacao = trim( (string) rac_campo( 'sobre_historia_texto_2', $pagina_id, '' ) );
    if ( $continuacao === '' && $provisorio ) $continuacao = $padroes['sobre_historia_texto_2'];

    return array(
        'abertura' => $abertura,
        'texto1'   => $principal,
        'texto2'   => $continuacao,
        'img1'     => (int) rac_campo( 'sobre_imagem_1', $pagina_id, 0 ),
        'leg1'     => trim( (string) rac_campo( 'sobre_imagem_1_legenda', $pagina_id, '' ) ),
        'img2'     => (int) rac_campo( 'sobre_imagem_2', $pagina_id, 0 ),
        'leg2'     => trim( (string) rac_campo( 'sobre_imagem_2_legenda', $pagina_id, '' ) ),
    );
}

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
