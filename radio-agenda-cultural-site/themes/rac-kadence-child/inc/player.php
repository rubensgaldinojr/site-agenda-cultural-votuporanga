<?php
/**
 * PlayerFixo (Fase 2) — integração com o plugin racr-player-radio.
 *
 * O plugin NÃO é alterado. O tema:
 *   1. desliga o desenho antigo do player (caixinha escura no canto) e o
 *      CSS dele;
 *   2. desenha o PlayerFixo do mockup (template-parts/player-fixo.php)
 *      mantendo os MESMOS IDs e classes que o JS do plugin usa;
 *   3. acrescenta, só no tema, o que o mockup pede e o plugin não faz
 *      (volume, "Tocando agora" em duas linhas, capa, cards que tocam).
 *
 * CONTRATO COM O PLUGIN (v0.4) — o JS do plugin procura estes elementos
 * pelo ID e quebra se algum faltar: racr-player, racr-audio,
 * racr-botao-play, racr-status, racr-nome-estacao, racr-lista-estacoes,
 * racr-botao-info, racr-botao-expandir. Também usa as classes
 * .racr-estacao (com data-indice), .racr-estacao-ativa e, no
 * #racr-player, .racr-tocando / .racr-carregando / .racr-erro /
 * .racr-expandido. Se uma versão futura do plugin mudar isso, o teste
 * em tests/harness/teste-fase2.js avisa.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** O plugin está ativo e há ao menos uma estação com stream? */
function rac_tem_player() {
    return function_exists( 'racr_obter_estacoes' ) && ! is_admin() && ! empty( racr_obter_estacoes() );
}

/* Desliga o player antigo do plugin (o hook é o mesmo, prioridade 10). */
if ( function_exists( 'racr_renderizar_player' ) ) {
    remove_action( 'wp_footer', 'racr_renderizar_player' );
    add_action( 'wp_footer', 'rac_renderizar_player_fixo' );
}

function rac_renderizar_player_fixo() {
    if ( ! rac_tem_player() ) return;
    get_template_part( 'template-parts/player-fixo' );
}

/* Tira o CSS antigo do plugin (ele é enfileirado na prioridade 10). */
function rac_remover_css_player_antigo() {
    if ( function_exists( 'racr_renderizar_player' ) && ! has_action( 'wp_footer', 'racr_renderizar_player' ) ) {
        wp_dequeue_style( 'racr-player' );
    }
}
add_action( 'wp_enqueue_scripts', 'rac_remover_css_player_antigo', 100 );

/* Classe no <body> para reservar espaço no fim da página (o player é fixo). */
function rac_classe_body_player( $classes ) {
    if ( rac_tem_player() ) {
        $classes[] = 'rac-tem-player';
    }
    return $classes;
}
add_filter( 'body_class', 'rac_classe_body_player' );
