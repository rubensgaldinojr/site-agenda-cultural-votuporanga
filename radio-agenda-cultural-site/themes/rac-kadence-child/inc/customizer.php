<?php
/**
 * Personalização do WordPress (Aparência → Personalizar).
 *
 * Textos GLOBAIS do rodapé e a logo para fundo escuro. Usa só recursos
 * nativos do WordPress (não depende de ACF nem do CPT UI).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Valores padrão (os mesmos textos do mockup SiteFooter). */
function rac_opcoes_padrao() {
    return array(
        'rac_rodape_texto'     => 'Uma rádio feita com carinho em Votuporanga, SP, para quem gosta de boa música, boa leitura e boa conversa.',
        'rac_rodape_email'     => 'contato@agendacultural.fm',
        'rac_rodape_instagram' => '',
        'rac_rodape_facebook'  => '',
        'rac_rodape_youtube'   => '',
        'rac_rodape_copy'      => 'Rádio Agenda Cultural — Votuporanga, SP, Brasil.',
    );
}

/** Lê uma opção do rodapé, caindo no valor padrão quando vazia. */
function rac_opcao( $chave ) {
    $padrao = rac_opcoes_padrao();
    $valor  = get_theme_mod( $chave, isset( $padrao[ $chave ] ) ? $padrao[ $chave ] : '' );
    return $valor;
}

function rac_customizer( $wp_customize ) {

    /* ---------- Logo para fundo escuro (Identidade do site) ---------- */
    $wp_customize->add_setting( 'rac_logo_escuro', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'rac_logo_escuro', array(
        'label'       => 'Logo para fundo escuro',
        'description' => 'Opcional. Usada no modo escuro no lugar da logo principal. Sem ela, a logo principal é clareada por um filtro.',
        'section'     => 'title_tagline',
        'mime_type'   => 'image',
    ) ) );

    /* ---------- Rodapé ---------- */
    $wp_customize->add_section( 'rac_rodape', array(
        'title'    => 'Rodapé do site',
        'priority' => 130,
    ) );

    $campos = array(
        'rac_rodape_texto'     => array( 'Texto curto', 'textarea', 'sanitize_textarea_field' ),
        'rac_rodape_email'     => array( 'E-mail de contato', 'text', 'sanitize_email' ),
        'rac_rodape_instagram' => array( 'Link do Instagram', 'url', 'esc_url_raw' ),
        'rac_rodape_facebook'  => array( 'Link do Facebook', 'url', 'esc_url_raw' ),
        'rac_rodape_youtube'   => array( 'Link do YouTube', 'url', 'esc_url_raw' ),
        'rac_rodape_copy'      => array( 'Linha de direitos (o "© ano" é automático)', 'text', 'sanitize_text_field' ),
    );
    $padrao = rac_opcoes_padrao();

    foreach ( $campos as $id => $cfg ) {
        $wp_customize->add_setting( $id, array(
            'default'           => $padrao[ $id ],
            'sanitize_callback' => $cfg[2],
        ) );
        $wp_customize->add_control( $id, array(
            'label'   => $cfg[0],
            'type'    => $cfg[1],
            'section' => 'rac_rodape',
        ) );
    }
}
add_action( 'customize_register', 'rac_customizer' );
