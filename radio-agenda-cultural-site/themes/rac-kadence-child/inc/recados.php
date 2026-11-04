<?php
/**
 * Recados dos ouvintes (Home): comentários nativos do WordPress feitos na
 * própria página inicial, com moderação.
 *
 * O formulário envia por JavaScript (fetch) para wp-comments-post.php, para
 * a página não recarregar e o áudio não parar. A moderação, o Akismet e as
 * demais regras do WordPress continuam valendo. Em cima disso, só nos
 * comentários da página inicial: um campo-armadilha escondido e um tempo
 * mínimo de 3 s entre abrir o formulário e enviar (medido no navegador).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Comentários aprovados da página (mais recentes primeiro). */
function rac_recados( $pagina_id, $limite = 60 ) {
    return get_comments( array(
        'post_id' => $pagina_id,
        'status'  => 'approve',
        'type'    => 'comment',
        'parent'  => 0,
        'number'  => $limite,
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
    ) );
}

/** "há 2 horas" para recados recentes; data curta para os mais antigos (7 dias ou mais). */
function rac_tempo_relativo( $comentario ) {
    $ts = strtotime( $comentario->comment_date_gmt . ' GMT' );
    if ( ! $ts ) return '';
    return ( time() - $ts ) < 7 * DAY_IN_SECONDS ? 'há ' . human_time_diff( $ts, time() ) : wp_date( 'd M Y', $ts );
}

/** Rótulo "N recados". */
function rac_rotulo_recados( $n ) {
    return $n === 1 ? '1 recado' : $n . ' recados';
}

/** Letra do avatar a partir do nome. */
function rac_inicial( $nome ) {
    $nome = trim( (string) $nome );
    return $nome === '' ? 'A' : mb_strtoupper( mb_substr( $nome, 0, 1 ) );
}

/**
 * Proteção extra para os comentários feitos pelos formulários do tema: os recados
 * da página inicial (sempre) e os comentários dos posts (que enviam o campo
 * "rac_form"). Bloqueia: campo-armadilha preenchido, ou envio em menos de 3 s /
 * sem a medição do navegador. Comentários que não vêm dos formulários do tema
 * seguem só a moderação (e o Akismet, se houver).
 */
function rac_filtrar_recado( $dados ) {
    $inicial = (int) get_option( 'page_on_front' );
    $da_home = $inicial > 0 && (int) $dados['comment_post_ID'] === $inicial;
    if ( ! $da_home && empty( $_POST['rac_form'] ) ) return $dados;

    if ( ! empty( $_POST['rac_site'] ) ) {
        wp_die( 'Não foi possível enviar o recado.', 'Recado não enviado', array( 'response' => 400, 'back_link' => true ) );
    }
    $ms = isset( $_POST['rac_ms'] ) ? (int) $_POST['rac_ms'] : 0;
    if ( $ms < 3000 ) {
        wp_die( 'Calma! Aguarde alguns segundos antes de enviar.', 'Recado não enviado', array( 'response' => 400, 'back_link' => true ) );
    }
    return $dados;
}
add_filter( 'preprocess_comment', 'rac_filtrar_recado' );
