<?php
/**
 * Contato (Fase 5B): formulário da página Contato, enviado sem recarregar a página
 * (a rádio continua tocando) para o admin-ajax do WordPress.
 *
 * Cada mensagem:
 *   1) é SALVA no painel (menu "Mensagens de contato"), então nada se perde se o e-mail falhar;
 *   2) é ENVIADA por e-mail (wp_mail) para o endereço definido na página Contato
 *      (campo "E-mail de destino"; vazio = e-mail do administrador do WordPress). O
 *      "Responder" do e-mail já vai para quem escreveu (Reply-To).
 *
 * Anti-spam (a página pode estar em cache, então não usa nonce): campo-armadilha escondido,
 * tempo mínimo de 3 s entre abrir o formulário e enviar (medido no navegador), limite de envios por
 * visitante (5 por hora) e limite geral (40 por hora).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Assuntos (slug => rótulo) e quais pedem "Música" e "Artista". */
function rac_contato_assuntos() {
    return array(
        'sugestao-de-musica' => 'Sugestão de música',
        'pedido-musical'     => 'Pedido musical',
        'parcerias'          => 'Parcerias',
        'outro'              => 'Outro',
    );
}
function rac_contato_assunto_musical( $slug ) {
    return in_array( $slug, array( 'sugestao-de-musica', 'pedido-musical' ), true );
}

/** Textos provisórios (usados quando o campo da página está vazio). */
function rac_contato_padroes() {
    return array(
        'contato_titulo'  => 'Fale com a gente',
        'contato_texto'   => 'Quer sugerir uma música, pedir aquela faixa especial, propor uma parceria ou só mandar um oi? Escreva para a gente — todas as mensagens são lidas pela equipe.',
        'contato_local'   => 'Votuporanga, SP — Brasil',
        'contato_enviado' => 'Obrigado por escrever! Respondemos pelo e-mail que você informou.',
    );
}
function rac_contato_texto( $nome, $pagina_id = null ) {
    $padroes = rac_contato_padroes();
    return (string) rac_campo( $nome, $pagina_id, isset( $padroes[ $nome ] ) ? $padroes[ $nome ] : '' );
}

/** Campos da página (aparecem ao escolher o modelo "Contato" em Atributos da página). */
function rac_campos_contato() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
    acf_add_local_field_group( array(
        'key'    => 'group_rac_pagina_contato',
        'title'  => 'Página Contato',
        'fields' => array(
            array( 'key' => 'field_rac_contato_destino', 'label' => 'E-mail de destino das mensagens', 'name' => 'contato_email_destino', 'type' => 'email', 'instructions' => 'Para onde chegam as mensagens do formulário. Vazio: vai para o e-mail do administrador do WordPress (Configurações → Geral). Toda mensagem também fica salva em "Mensagens de contato", no menu lateral.' ),
            array( 'key' => 'field_rac_contato_titulo', 'label' => 'Título', 'name' => 'contato_titulo', 'type' => 'text', 'placeholder' => 'Fale com a gente' ),
            array( 'key' => 'field_rac_contato_texto', 'label' => 'Texto de apresentação', 'name' => 'contato_texto', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '' ),
            array( 'key' => 'field_rac_contato_email_publico', 'label' => 'E-mail público (opcional)', 'name' => 'contato_email_publico', 'type' => 'email', 'instructions' => 'Aparece como link na página. Vazio: a linha "E-mail" não aparece (fica só o formulário).' ),
            array( 'key' => 'field_rac_contato_local', 'label' => 'Onde estamos', 'name' => 'contato_local', 'type' => 'text', 'placeholder' => 'Votuporanga, SP — Brasil' ),
            array( 'key' => 'field_rac_contato_instagram', 'label' => 'Instagram (link)', 'name' => 'contato_instagram', 'type' => 'url' ),
            array( 'key' => 'field_rac_contato_facebook', 'label' => 'Facebook (link)', 'name' => 'contato_facebook', 'type' => 'url' ),
            array( 'key' => 'field_rac_contato_youtube', 'label' => 'YouTube (link)', 'name' => 'contato_youtube', 'type' => 'url', 'instructions' => 'Só as redes com link preenchido aparecem.' ),
            array( 'key' => 'field_rac_contato_enviado', 'label' => 'Texto da tela "Mensagem enviada"', 'name' => 'contato_enviado', 'type' => 'textarea', 'rows' => 2, 'new_lines' => '' ),
        ),
        'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-contato.php' ) ) ),
    ) );
}
add_action( 'acf/init', 'rac_campos_contato' );

/* ============================================================
   MENSAGENS NO PAINEL
   ============================================================ */
function rac_registrar_mensagens_contato() {
    register_post_type( 'mensagem_contato', array(
        'labels' => array(
            'name' => 'Mensagens de contato', 'singular_name' => 'Mensagem de contato', 'menu_name' => 'Mensagens de contato',
            'all_items' => 'Todas as mensagens', 'edit_item' => 'Mensagem', 'view_item' => 'Ver mensagem', 'search_items' => 'Buscar mensagens',
            'not_found' => 'Nenhuma mensagem ainda.', 'not_found_in_trash' => 'Nada na lixeira.',
        ),
        'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true, 'show_in_rest' => false,
        'show_ui' => true, 'show_in_menu' => true, 'menu_position' => 26, 'menu_icon' => 'dashicons-email-alt',
        'supports' => array( 'title', 'editor' ), 'capability_type' => 'post', 'map_meta_cap' => true,
        'capabilities' => array( 'create_posts' => 'do_not_allow' ), // só o formulário cria mensagens
        'has_archive' => false, 'rewrite' => false, 'query_var' => false,
    ) );
}
add_action( 'init', 'rac_registrar_mensagens_contato' );

function rac_colunas_mensagens( $colunas ) {
    return array(
        'cb'            => isset( $colunas['cb'] ) ? $colunas['cb'] : '',
        'title'         => 'Assunto e nome',
        'rac_remetente' => 'De',
        'rac_email'     => 'E-mail enviado?',
        'date'          => 'Data',
    );
}
add_filter( 'manage_mensagem_contato_posts_columns', 'rac_colunas_mensagens' );

function rac_conteudo_coluna_mensagem( $coluna, $post_id ) {
    if ( $coluna === 'rac_remetente' ) {
        $nome  = get_post_meta( $post_id, 'contato_nome', true );
        $email = get_post_meta( $post_id, 'contato_email', true );
        echo esc_html( $nome ) . '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
    }
    if ( $coluna === 'rac_email' ) {
        echo get_post_meta( $post_id, 'contato_email_enviado', true ) === '1'
            ? '✓ enviado'
            : '<strong style="color:#b32d2e">⚠ não saiu</strong> (a mensagem está salva aqui)';
    }
}
add_action( 'manage_mensagem_contato_posts_custom_column', 'rac_conteudo_coluna_mensagem', 10, 2 );

function rac_caixa_dados_mensagem() {
    add_meta_box( 'rac_dados_mensagem', 'Dados do formulário', 'rac_exibir_dados_mensagem', 'mensagem_contato', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'rac_caixa_dados_mensagem' );

function rac_exibir_dados_mensagem( $post ) {
    $linhas = array(
        'Assunto' => get_post_meta( $post->ID, 'contato_assunto', true ),
        'Nome'    => get_post_meta( $post->ID, 'contato_nome', true ),
        'E-mail'  => get_post_meta( $post->ID, 'contato_email', true ),
        'Música'  => get_post_meta( $post->ID, 'contato_musica', true ),
        'Artista' => get_post_meta( $post->ID, 'contato_artista', true ),
    );
    foreach ( $linhas as $rotulo => $valor ) {
        if ( $valor === '' ) continue;
        echo '<p><strong>' . esc_html( $rotulo ) . ':</strong><br>' . esc_html( $valor ) . '</p>';
    }
    $email = get_post_meta( $post->ID, 'contato_email', true );
    if ( $email ) echo '<p><a class="button button-primary" href="mailto:' . esc_attr( $email ) . '">Responder por e-mail</a></p>';
    echo '<p>' . ( get_post_meta( $post->ID, 'contato_email_enviado', true ) === '1' ? '✓ Chegou por e-mail também.' : '⚠ O e-mail não saiu; esta mensagem só existe aqui.' ) . '</p>';
}

/* ============================================================
   RECEBER E ENVIAR
   ============================================================ */

/** E-mail de destino: campo da página Contato; vazio = administrador do WordPress. */
function rac_contato_destino( $pagina_id = 0 ) {
    $email = $pagina_id ? sanitize_email( (string) rac_campo( 'contato_email_destino', $pagina_id, '' ) ) : '';
    if ( ! $email || ! is_email( $email ) ) $email = get_option( 'admin_email' );
    return apply_filters( 'rac_contato_destino', $email, $pagina_id );
}

/** Conta uma tentativa (por visitante e geral) na última hora; devolve true se passou do limite. */
function rac_contato_limite_estourado( $ip ) {
    $chave_ip = 'rac_contato_ip_' . md5( (string) $ip . rac_sal() );
    $chave_geral = 'rac_contato_geral';
    $n_ip = (int) get_transient( $chave_ip );
    $n_geral = (int) get_transient( $chave_geral );
    if ( $n_ip >= 5 || $n_geral >= 40 ) return true;
    set_transient( $chave_ip, $n_ip + 1, HOUR_IN_SECONDS );
    set_transient( $chave_geral, $n_geral + 1, HOUR_IN_SECONDS );
    return false;
}
function rac_sal() { return function_exists( 'wp_salt' ) ? wp_salt( 'auth' ) : 'rac'; }

/**
 * Valida, salva e envia. $dados = o que veio do formulário ($_POST). Devolve
 * array( 'ok' => bool, 'erro' => texto, 'id' => ID da mensagem salva ).
 */
function rac_contato_processar( $dados, $ip = '' ) {
    $erro = function ( $texto ) { return array( 'ok' => false, 'erro' => $texto ); };

    // armadilha para robôs e tempo mínimo
    if ( ! empty( $dados['rac_site'] ) ) return $erro( 'Não foi possível enviar a mensagem.' );
    $ms = isset( $dados['rac_ms'] ) ? (int) $dados['rac_ms'] : 0;
    if ( $ms < 3000 ) return $erro( 'Calma! Aguarde alguns segundos antes de enviar.' );

    // campos
    $assuntos = rac_contato_assuntos();
    $slug     = isset( $dados['assunto'] ) ? sanitize_key( wp_unslash( $dados['assunto'] ) ) : '';
    if ( ! isset( $assuntos[ $slug ] ) ) return $erro( 'Escolha um assunto.' );
    $nome     = isset( $dados['nome'] ) ? sanitize_text_field( wp_unslash( $dados['nome'] ) ) : '';
    $email    = isset( $dados['email'] ) ? sanitize_email( wp_unslash( $dados['email'] ) ) : '';
    $mensagem = isset( $dados['mensagem'] ) ? trim( sanitize_textarea_field( wp_unslash( $dados['mensagem'] ) ) ) : '';
    $musical  = rac_contato_assunto_musical( $slug );
    $musica   = ( $musical && isset( $dados['musica'] ) ) ? sanitize_text_field( wp_unslash( $dados['musica'] ) ) : '';
    $artista  = ( $musical && isset( $dados['artista'] ) ) ? sanitize_text_field( wp_unslash( $dados['artista'] ) ) : '';

    if ( $nome === '' || mb_strlen( $nome ) > 100 ) return $erro( 'Informe seu nome (até 100 letras).' );
    if ( ! $email || ! is_email( $email ) ) return $erro( 'Informe um e-mail válido, para podermos responder.' );
    if ( $musical && $musica === '' ) return $erro( 'Conte qual música você quer sugerir ou pedir.' );
    if ( mb_strlen( $musica ) > 150 || mb_strlen( $artista ) > 150 ) return $erro( 'Os campos Música e Artista aceitam até 150 letras.' );
    if ( mb_strlen( $mensagem ) < 5 ) return $erro( 'Escreva sua mensagem.' );
    if ( mb_strlen( $mensagem ) > 3000 ) return $erro( 'A mensagem passou de 3000 letras. Pode resumir um pouco?' );

    // limite por hora (só conta mensagens que passaram na validação)
    if ( rac_contato_limite_estourado( $ip ) ) return $erro( 'Recebemos várias mensagens seguidas. Tente de novo daqui a pouco.' );

    // 1) salva no painel
    $rotulo = $assuntos[ $slug ];
    $id = wp_insert_post( array(
        'post_type'    => 'mensagem_contato',
        'post_status'  => 'publish',
        'post_title'   => $rotulo . ' — ' . $nome,
        'post_content' => $mensagem,
    ) );
    if ( ! $id || is_wp_error( $id ) ) return $erro( 'Não conseguimos registrar a mensagem agora. Tente de novo em instantes.' );
    update_post_meta( $id, 'contato_assunto', $rotulo );
    update_post_meta( $id, 'contato_nome', $nome );
    update_post_meta( $id, 'contato_email', $email );
    if ( $musica !== '' ) update_post_meta( $id, 'contato_musica', $musica );
    if ( $artista !== '' ) update_post_meta( $id, 'contato_artista', $artista );

    // 2) envia por e-mail
    $pagina_id = isset( $dados['pagina'] ) ? absint( $dados['pagina'] ) : 0;
    $destino   = rac_contato_destino( $pagina_id );
    $corpo     = "Nova mensagem pelo formulário de contato do site.\n\n"
               . 'Assunto: ' . $rotulo . "\n"
               . 'Nome: ' . $nome . "\n"
               . 'E-mail: ' . $email . "\n"
               . ( $musica !== '' ? 'Música: ' . $musica . "\n" : '' )
               . ( $artista !== '' ? 'Artista: ' . $artista . "\n" : '' )
               . "\nMensagem:\n" . $mensagem . "\n\n--\n"
               . "Para responder, é só responder este e-mail.\n"
               . 'A mensagem também ficou salva no painel (Mensagens de contato, nº ' . $id . ").\n";
    $host      = preg_replace( '#^www\.#', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    $cabecalhos = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo( 'name' ) . ' <wordpress@' . ( $host ? $host : 'localhost' ) . '>',
        'Reply-To: ' . str_replace( array( '"', ',', '<', '>' ), '', $nome ) . ' <' . $email . '>',
    );
    $enviado = wp_mail( $destino, '[Contato do site] ' . $rotulo . ' — ' . $nome, $corpo, $cabecalhos );
    update_post_meta( $id, 'contato_email_enviado', $enviado ? '1' : '0' );

    // a pessoa vê "enviado" porque a mensagem ficou guardada, mesmo se o e-mail falhar
    return array( 'ok' => true, 'id' => $id, 'email_enviado' => (bool) $enviado );
}

/** Ponto de entrada do formulário (admin-ajax). */
function rac_contato_ajax() {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
    $r  = rac_contato_processar( $_POST, $ip ); // phpcs:ignore WordPress.Security.NonceVerification
    if ( $r['ok'] ) wp_send_json_success( array( 'mensagem' => 'ok' ) );
    wp_send_json_error( array( 'mensagem' => $r['erro'] ) );
}
add_action( 'wp_ajax_nopriv_rac_contato', 'rac_contato_ajax' );
add_action( 'wp_ajax_rac_contato', 'rac_contato_ajax' );

/** Redes preenchidas na página: [ ['sigla','rotulo','url'] ]. */
function rac_contato_redes( $pagina_id ) {
    $redes = array();
    foreach ( array( 'contato_instagram' => array( 'IG', 'Instagram' ), 'contato_facebook' => array( 'FB', 'Facebook' ), 'contato_youtube' => array( 'YT', 'YouTube' ) ) as $campo => $info ) {
        $url = esc_url_raw( (string) rac_campo( $campo, $pagina_id, '' ) );
        if ( $url ) $redes[] = array( 'sigla' => $info[0], 'rotulo' => $info[1], 'url' => $url );
    }
    return $redes;
}
