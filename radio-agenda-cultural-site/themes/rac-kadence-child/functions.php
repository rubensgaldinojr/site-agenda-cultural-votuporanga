<?php
/**
 * Tema filho do Kadence — Rádio Agenda Cultural.
 *
 * Fase 1: base do tema, tema claro/escuro, header/footer e compatibilidade
 * com a navegação persistente do plugin racr-player-radio.
 * Fase 2: PlayerFixo, CardEstacao e CardPost (veja inc/player.php).
 *
 * REGRA CRÍTICA: o plugin troca via AJAX apenas o conteúdo de "#primary".
 * Todo template deste tema precisa ter exatamente UM <main id="primary">,
 * e o player (injetado em wp_footer) precisa ficar fora dele.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'RAC_TEMA_VERSAO', '0.2.0' );

require_once get_stylesheet_directory() . '/inc/helpers.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';
require_once get_stylesheet_directory() . '/inc/player.php';

/* ============================================================
   1. CONFIGURAÇÃO DO TEMA
   ============================================================ */
function rac_tema_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'custom-logo', array(
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    register_nav_menus( array(
        'rac-principal' => 'Menu principal (cabeçalho e rodapé)',
    ) );
}
add_action( 'after_setup_theme', 'rac_tema_setup' );

/* Classe no <body> para escopar o CSS do tema e vencer o CSS global do Kadence. */
function rac_classe_body( $classes ) {
    $classes[] = 'rac-body';
    return $classes;
}
add_filter( 'body_class', 'rac_classe_body' );

/* ============================================================
   2. TEMA CLARO/ESCURO SEM "FLASH"
   Script inline bem no início do <head> (prioridade 0), antes de
   qualquer CSS ser aplicado. Segue docs/TEMA-CLARO-ESCURO.md:
   - lê a escolha salva em localStorage["rac-tema"];
   - na primeira visita, segue prefers-color-scheme.
   ============================================================ */
function rac_script_tema_inicial() {
    ?>
    <script data-no-optimize="1" data-no-defer="1" data-cfasync="false">
    (function () {
        var t = null;
        try { t = localStorage.getItem('rac-tema'); } catch (e) {}
        if (t !== 'dark' && t !== 'light') {
            t = (window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', t);
    })();
    </script>
    <?php
}
add_action( 'wp_head', 'rac_script_tema_inicial', 0 );

/* ============================================================
   3. CSS E JS DO TEMA
   Prioridade 20: carrega depois do CSS do Kadence (prioridade 10).
   O tema.css vem antes de tudo, como pede o guia de tema.
   ============================================================ */
function rac_enqueue_assets() {
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    // Fontes do mockup: Outfit (títulos) e DM Sans (corpo).
    wp_enqueue_style(
        'rac-fontes',
        'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap',
        array(),
        null
    );

    wp_enqueue_style( 'rac-tema', $uri . '/assets/css/tema.css', array( 'rac-fontes' ), filemtime( $dir . '/assets/css/tema.css' ) );
    wp_enqueue_style( 'rac-base', $uri . '/assets/css/base.css', array( 'rac-tema' ), filemtime( $dir . '/assets/css/base.css' ) );

    wp_enqueue_style( 'rac-componentes', $uri . '/assets/css/componentes.css', array( 'rac-base' ), filemtime( $dir . '/assets/css/componentes.css' ) );

    wp_enqueue_script( 'rac-tema-js', $uri . '/assets/js/rac.js', array(), filemtime( $dir . '/assets/js/rac.js' ), true );
    // Player fixo e cards de estação (Fase 2). Só quando há player na página.
    if ( rac_tem_player() ) {
        wp_enqueue_script( 'rac-player-js', $uri . '/assets/js/player.js', array(), filemtime( $dir . '/assets/js/player.js' ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'rac_enqueue_assets', 20 );

/* Acelera o carregamento das fontes do Google. */
function rac_resource_hints( $urls, $tipo ) {
    if ( 'preconnect' === $tipo ) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'rac_resource_hints', 10, 2 );
