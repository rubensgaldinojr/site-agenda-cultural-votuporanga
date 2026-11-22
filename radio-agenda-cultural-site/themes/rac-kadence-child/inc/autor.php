<?php
/**
 * Autoria: campos do perfil do usuário (ACF, por código), dados da página do
 * autor, endereço /autor/nome/ e o selo "Autor"/"Autora".
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** /author/nome/ vira /autor/nome/ (salve Configurações → Links permanentes uma vez). */
function rac_base_autor() {
    global $wp_rewrite;
    if ( $wp_rewrite ) $wp_rewrite->author_base = 'autor';
}
add_action( 'init', 'rac_base_autor' );

function rac_atualizar_enderecos() {
    rac_base_autor();
    flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'rac_atualizar_enderecos' );

/** Campos extras no perfil do usuário. */
function rac_campos_autor() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
    acf_add_local_field_group( array(
        'key'    => 'group_rac_autor',
        'title'  => 'Página do autor',
        'fields' => array(
            array( 'key' => 'field_rac_autor_foto', 'label' => 'Foto', 'name' => 'autor_foto', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'instructions' => 'Sem foto, aparece a do Gravatar ou a inicial do nome.' ),
            array( 'key' => 'field_rac_autor_papel', 'label' => 'Papel', 'name' => 'autor_papel', 'type' => 'text', 'instructions' => 'Ex.: Cronista e apresentadora do Blues & Jazz Clube.' ),
            array( 'key' => 'field_rac_autor_desde', 'label' => 'Texto de "desde"', 'name' => 'autor_desde', 'type' => 'text', 'instructions' => 'Ex.: Escreve desde 2021.' ),
            array( 'key' => 'field_rac_autor_citacao', 'label' => 'Citação favorita', 'name' => 'autor_citacao', 'type' => 'textarea', 'rows' => 3, 'new_lines' => '' ),
            array( 'key' => 'field_rac_autor_citacao_de', 'label' => 'Autor da citação', 'name' => 'autor_citacao_de', 'type' => 'text', 'instructions' => 'Nome e sobrenome da pessoa citada.' ),
            array( 'key' => 'field_rac_autor_no_sobre', 'label' => 'Aparecer na página Sobre ("Quem faz")', 'name' => 'autor_no_sobre', 'type' => 'true_false', 'ui' => 1, 'default_value' => 0, 'message' => '', 'instructions' => 'Marque para esta pessoa aparecer na seção "As pessoas por trás da rádio" da página Sobre. Pode marcar quantas quiser.' ),
            array( 'key' => 'field_rac_autor_ordem_sobre', 'label' => 'Posição na página Sobre', 'name' => 'autor_ordem_sobre', 'type' => 'number', 'min' => 1, 'step' => 1, 'instructions' => 'Número menor aparece primeiro (1, 2, 3…). Vazio ou empate: ordem alfabética pelo nome.' ),
        ),
        'location' => array( array( array( 'param' => 'user_form', 'operator' => '==', 'value' => 'edit' ) ) ),
        'position' => 'normal',
        'style'    => 'default',
    ) );
}
add_action( 'acf/init', 'rac_campos_autor' );

/** "Autor" ou "Autora" (campo do plugin de catálogo no perfil). */
function rac_rotulo_autoria( $user_id ) {
    return get_user_meta( $user_id, 'rac_autor_genero', true ) === 'autora' ? 'Autora' : 'Autor';
}

/** Primeiro nome, para "Posts de Marina". */
function rac_primeiro_nome( $nome ) {
    $partes = preg_split( '/\s+/', trim( (string) $nome ) );
    return $partes ? $partes[0] : '';
}

/** Dados da página de um autor. */
function rac_dados_autor( $user_id ) {
    $nome   = get_the_author_meta( 'display_name', $user_id );
    $fonte  = 'user_' . $user_id;
    $foto   = (int) rac_campo( 'autor_foto', $fonte, 0 );
    $bio    = trim( (string) get_the_author_meta( 'description', $user_id ) );
    return array(
        'id'          => (int) $user_id,
        'nome'        => $nome,
        'primeiro'    => rac_primeiro_nome( $nome ),
        'papel'       => (string) rac_campo( 'autor_papel', $fonte, '' ),
        'desde'       => (string) rac_campo( 'autor_desde', $fonte, '' ),
        'citacao'     => (string) rac_campo( 'autor_citacao', $fonte, '' ),
        'citacao_de'  => (string) rac_campo( 'autor_citacao_de', $fonte, '' ),
        'foto_id'     => $foto,
        'avatar_url'  => $foto ? '' : get_avatar_url( $user_id, array( 'size' => 336, 'default' => '404' ) ),
        'bio'         => $bio,
        'url'         => get_author_posts_url( $user_id ),
        'rotulo'      => rac_rotulo_autoria( $user_id ),
    );
}

/** Parágrafos da bio (linha em branco separa). */
function rac_paragrafos( $texto ) {
    $partes = preg_split( '/\R{2,}|\R/', trim( (string) $texto ) );
    return array_values( array_filter( array_map( 'trim', $partes ), 'strlen' ) );
}

/** Avatar redondo (foto do perfil, Gravatar ou a inicial) com o tamanho dado em CSS. */
function rac_avatar_html( $dados, $classe = '' ) {
    $cls = 'rac-avatar' . ( $classe ? ' ' . $classe : '' );
    if ( $dados['foto_id'] ) {
        return '<span class="' . esc_attr( $cls ) . '">' . wp_get_attachment_image( $dados['foto_id'], 'medium', false, array( 'alt' => '', 'loading' => 'lazy' ) ) . '</span>';
    }
    if ( $dados['avatar_url'] ) {
        // a inicial fica atrás: se o Gravatar não existir, a imagem é removida e a inicial aparece
        return '<span class="' . esc_attr( $cls ) . '"><img src="' . esc_url( $dados['avatar_url'] ) . '" alt="" loading="lazy" onerror="this.remove()"><b aria-hidden="true">' . esc_html( rac_inicial( $dados['nome'] ) ) . '</b></span>';
    }
    return '<span class="' . esc_attr( $cls . ' rac-avatar--inicial' ) . '" aria-hidden="true"><b>' . esc_html( rac_inicial( $dados['nome'] ) ) . '</b></span>';
}

/* ============================================================
   ENDEREÇO DA PÁGINA DO AUTOR (user_nicename)
   O WordPress monta o endereço /autor/<nicename>/ a partir do LOGIN na hora em
   que o usuário é criado. Se o login é um e-mail, o e-mail (sem símbolos)
   vira parte do endereço público (ex.: /autor/maria-silvagmail-com/). O painel
   não deixa editar esse campo; este bloco acrescenta, no perfil do usuário,
   o campo "Endereço da página do autor" (é só trocar e salvar).
   ============================================================ */
function rac_campo_nicename( $usuario ) {
    $nicename = $usuario->user_nicename;
    $de_email = strpos( $usuario->user_login, '@' ) !== false || (bool) preg_match( '/-com(-br)?$/', $usuario->user_nicename );
    wp_nonce_field( 'rac_nicename_' . $usuario->ID, 'rac_nicename_nonce' );
    ?>
    <h2>Endereço da página do autor</h2>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="rac_nicename">Endereço (slug)</label></th>
            <td>
                <code><?php echo esc_html( home_url( '/autor/' ) ); ?></code><input type="text" name="rac_nicename" id="rac_nicename" value="<?php echo esc_attr( $nicename ); ?>" class="regular-text" style="width:16em" autocomplete="off">
                <p class="description">
                    Só letras minúsculas, números e hífens (ex.: <code>maria-silva</code>). É a parte final do endereço público da sua página de autor(a).
                    <?php if ( $de_email ) : ?><strong>Atenção: o endereço atual parece vir de um e-mail. Troque para o seu nome, para o e-mail não aparecer no site.</strong><?php endif; ?>
                </p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'rac_campo_nicename' );
add_action( 'edit_user_profile', 'rac_campo_nicename' );

function rac_salvar_nicename( $user_id ) {
    if ( ! isset( $_POST['rac_nicename'], $_POST['rac_nicename_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['rac_nicename_nonce'], 'rac_nicename_' . $user_id ) ) return;
    if ( ! current_user_can( 'edit_user', $user_id ) ) return;

    $novo = sanitize_title( wp_unslash( $_POST['rac_nicename'] ) );
    $usuario = get_userdata( $user_id );
    if ( $novo === '' || ! $usuario || $novo === $usuario->user_nicename ) return;

    // já em uso por outra pessoa? o WordPress acrescenta "-2"; aqui só evitamos trocar para o endereço de outro usuário
    $dono = get_user_by( 'slug', $novo );
    if ( $dono && (int) $dono->ID !== (int) $user_id ) return;

    wp_update_user( array( 'ID' => $user_id, 'user_nicename' => $novo ) );
}
add_action( 'personal_options_update', 'rac_salvar_nicename' );
add_action( 'edit_user_profile_update', 'rac_salvar_nicename' );
