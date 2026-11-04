<?php
/**
 * Campos ACF criados por código (aparecem no editor sem montar nada no
 * painel do ACF). Funciona no ACF gratuito.
 *
 * O campo "Texto Sobre a Estação" (texto_sobre_a_estacao) e os demais da
 * Estação já existem no painel; aqui só entram campos NOVOS.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function rac_registrar_campos_acf() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    /* Topo da página "Estações": aparece quando a página usa o modelo "Estações".
       A imagem do topo é a "Imagem destacada" da própria página. */
    acf_add_local_field_group( array(
        'key'      => 'group_rac_pagina_estacoes',
        'title'    => 'Topo da página Estações',
        'fields'   => array(
            array(
                'key'          => 'field_rac_estacoes_hero_titulo',
                'label'        => 'Título do topo',
                'name'         => 'hero_titulo',
                'type'         => 'text',
                'instructions' => 'Se ficar vazio, aparece "Uma trilha para cada momento do dia".',
            ),
            array(
                'key'          => 'field_rac_estacoes_hero_texto',
                'label'        => 'Texto do topo',
                'name'         => 'hero_texto',
                'type'         => 'textarea',
                'rows'         => 3,
                'new_lines'    => '',
                'instructions' => 'Se ficar vazio, aparece o texto padrão. A imagem do topo é a "Imagem destacada" desta página.',
            ),
        ),
        'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-estacoes.php' ) ) ),
        'position' => 'normal',
        'style'    => 'default',
    ) );
    /* Campo "Ouvintes" em cada estação: alimenta as "mais ouvidas" da Home.
       Preenchido à mão (o Zeno.fm não tem API pública de ouvintes); no futuro
       um script pode atualizar este mesmo campo. */
    acf_add_local_field_group( array(
        'key'      => 'group_rac_estacao_audiencia',
        'title'    => 'Audiência',
        'fields'   => array(
            array(
                'key'           => 'field_rac_estacao_ouvintes',
                'label'         => 'Ouvintes',
                'name'          => 'ouvintes',
                'type'          => 'number',
                'min'           => 0,
                'step'          => 1,
                'instructions'  => 'Número de ouvintes da estação. As duas estações com mais ouvintes aparecem como "Mais ouvida" na página inicial. Atualize à mão (ou por script, no futuro). Vazio conta como 0.',
            ),
        ),
        'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'estacao' ) ) ),
        'position' => 'side',
        'style'    => 'default',
    ) );

    /* Textos da página inicial (valem para a página definida como "Página inicial").
       Campos vazios mostram o texto padrão do mockup. A imagem do topo é a "Imagem destacada" da página. */
    $aba = function ( $chave, $rotulo ) {
        return array( 'key' => 'field_rac_home_aba_' . $chave, 'label' => $rotulo, 'name' => '', 'type' => 'tab', 'placement' => 'top' );
    };
    $texto = function ( $nome, $rotulo, $instrucao = '' ) {
        return array( 'key' => 'field_rac_' . $nome, 'label' => $rotulo, 'name' => $nome, 'type' => 'text', 'instructions' => $instrucao );
    };
    $area = function ( $nome, $rotulo, $instrucao = '' ) {
        return array( 'key' => 'field_rac_' . $nome, 'label' => $rotulo, 'name' => $nome, 'type' => 'textarea', 'rows' => 3, 'new_lines' => '', 'instructions' => $instrucao );
    };
    $imagem = function ( $nome, $rotulo, $instrucao = '' ) {
        return array( 'key' => 'field_rac_' . $nome, 'label' => $rotulo, 'name' => $nome, 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'library' => 'all', 'instructions' => $instrucao );
    };

    acf_add_local_field_group( array(
        'key'    => 'group_rac_pagina_inicial',
        'title'  => 'Textos da página inicial',
        'fields' => array(
            $aba( 'topo', 'Topo' ),
            $texto( 'home_hero_selo', 'Selo do topo', 'Padrão: "Direto de Votuporanga, SP — Brasil".' ),
            $texto( 'home_hero_titulo', 'Título do topo', 'Padrão: "Música, cultura e boas conversas, 24 horas por dia".' ),
            $area( 'home_hero_texto', 'Texto do topo', 'A imagem do topo é a "Imagem destacada" desta página.' ),

            $aba( 'fundador', 'Fundador' ),
            $imagem( 'home_fundador_foto', 'Foto do fundador', 'Sem foto, aparece a inicial do nome.' ),
            $texto( 'home_fundador_nome', 'Nome', 'Padrão: "Nome do Fundador".' ),
            $texto( 'home_fundador_papel', 'Papel', 'Padrão: "fundador e curador".' ),
            $area( 'home_fundador_texto', 'Texto curto' ),

            $aba( 'destaques', 'Destaques' ),
            $area( 'home_destaque1_texto', 'Estações — texto', 'O título ("N estações no ar, 24h por dia") é calculado sozinho.' ),
            $texto( 'home_destaque2_titulo', 'Literatura — título' ),
            $area( 'home_destaque2_texto', 'Literatura — texto' ),
            $texto( 'home_destaque3_titulo', 'Blog — título' ),
            $area( 'home_destaque3_texto', 'Blog — texto' ),

            $aba( 'estacoes', 'Estações em destaque' ),
            $texto( 'home_estacoes_subtitulo', 'Subtítulo da seção' ),
            array(
                'key'           => 'field_rac_home_descoberta_modo',
                'label'         => 'Descoberta do editor',
                'name'          => 'home_descoberta_modo',
                'type'          => 'select',
                'choices'       => array( 'sorteio' => 'Sorteio diário (padrão)', 'fixar' => 'Fixar uma estação' ),
                'default_value' => 'sorteio',
                'ui'            => 0,
                'instructions'  => 'No sorteio, uma estação por dia é escolhida entre as que NÃO estão entre as duas mais ouvidas.',
            ),
            array(
                'key'               => 'field_rac_home_descoberta_estacao',
                'label'             => 'Estação fixada',
                'name'              => 'home_descoberta_estacao',
                'type'              => 'post_object',
                'post_type'         => array( 'estacao' ),
                'return_format'     => 'id',
                'allow_null'        => 1,
                'ui'                => 1,
                'instructions'      => 'Se a estação escolhida estiver entre as duas mais ouvidas, o sorteio diário é usado no lugar.',
                'conditional_logic' => array( array( array( 'field' => 'field_rac_home_descoberta_modo', 'operator' => '==', 'value' => 'fixar' ) ) ),
            ),

            $aba( 'sobre', 'Sobre nós' ),
            $imagem( 'home_sobre_foto', 'Foto', 'Estúdio ou fundador. Sem foto, aparece a imagem placeholder.' ),
            $texto( 'home_sobre_titulo', 'Título' ),
            $area( 'home_sobre_texto', 'Texto' ),
        ),
        'location' => array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ) ),
        'position' => 'normal',
        'style'    => 'default',
    ) );
}
add_action( 'acf/init', 'rac_registrar_campos_acf' );
