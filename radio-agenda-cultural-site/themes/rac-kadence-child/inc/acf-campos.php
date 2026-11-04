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
}
add_action( 'acf/init', 'rac_registrar_campos_acf' );
