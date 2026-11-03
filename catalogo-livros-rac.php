<?php
/**
 * Plugin Name: Catálogo de Livros - Rádio Agenda Cultural
 * Description: Cadastro e exibição de livros para download gratuito, com filtro por categoria, autor e ano.
 * Version: 1.0
 * Author: Rubens
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   1. CUSTOM POST TYPE "livro"
   ============================================================ */
function rac_registrar_cpt_livro() {
    register_post_type( 'livro', array(
        'labels' => array(
            'name'               => 'Livros',
            'singular_name'      => 'Livro',
            'add_new_item'       => 'Adicionar novo livro',
            'edit_item'          => 'Editar livro',
            'all_items'          => 'Todos os livros',
            'menu_name'          => 'Livros',
        ),
        'public'       => true,
        'has_archive'  => false,
        'menu_icon'    => 'dashicons-book-alt',
        'supports'     => array( 'title', 'thumbnail', 'author' ),
        'show_in_rest' => true,
        'rewrite'      => array( 'slug' => 'livro' ),
    ) );

    register_taxonomy( 'categoria_livro', 'livro', array(
        'labels' => array(
            'name'          => 'Categorias de Livro',
            'singular_name' => 'Categoria de Livro',
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'categoria-livro' ),
    ) );
}
add_action( 'init', 'rac_registrar_cpt_livro' );

/* ============================================================
   2. CAMPOS CUSTOMIZADOS (meta box no admin)
   ============================================================ */
function rac_registrar_metabox_livro() {
    add_meta_box(
        'rac_detalhes_livro',
        'Detalhes do livro',
        'rac_render_metabox_livro',
        'livro',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'rac_registrar_metabox_livro' );

function rac_render_metabox_livro( $post ) {
    wp_nonce_field( 'rac_salvar_livro', 'rac_livro_nonce' );

    $ano_inicio    = get_post_meta( $post->ID, '_rac_ano_inicio', true );
    $ano_fim       = get_post_meta( $post->ID, '_rac_ano_fim', true );
    $volume        = get_post_meta( $post->ID, '_rac_volume', true );
    $descricao     = get_post_meta( $post->ID, '_rac_descricao_breve', true );
    $pdf_id        = get_post_meta( $post->ID, '_rac_pdf_id', true );
    $pdf_url       = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
    ?>
    <style>
        .rac-campo { margin-bottom: 14px; }
        .rac-campo label { display:block; font-weight:600; margin-bottom:4px; }
        .rac-campo input[type=text], .rac-campo input[type=number], .rac-campo textarea { width:100%; max-width:420px; }
        .rac-linha { display:flex; gap:16px; }
        .rac-linha .rac-campo { flex:1; }
    </style>

    <div class="rac-linha">
        <div class="rac-campo">
            <label>Ano de início</label>
            <input type="number" name="rac_ano_inicio" value="<?php echo esc_attr( $ano_inicio ); ?>" placeholder="Ex: 1980" />
        </div>
        <div class="rac-campo">
            <label>Ano final (deixe igual ao inicial se for um só ano)</label>
            <input type="number" name="rac_ano_fim" value="<?php echo esc_attr( $ano_fim ); ?>" placeholder="Ex: 1988" />
        </div>
    </div>

    <div class="rac-campo">
        <label>Volume (opcional)</label>
        <input type="text" name="rac_volume" value="<?php echo esc_attr( $volume ); ?>" placeholder="Ex: Volume 1" />
    </div>

    <div class="rac-linha">
        <div class="rac-campo">
            <p style="color:#666; margin:0;">O autor deste livro é definido pelo campo <strong>"Autor"</strong>, no menu à direita da tela (é o mesmo cadastro de usuário usado no Blog). Se a pessoa ainda não tem uma conta, crie em <strong>Usuários → Adicionar novo</strong> antes de continuar.</p>
        </div>
    </div>

    <div class="rac-campo">
        <label>Descrição breve</label>
        <textarea name="rac_descricao_breve" rows="4"><?php echo esc_textarea( $descricao ); ?></textarea>
    </div>

    <div class="rac-campo">
        <label>Arquivo PDF</label><br/>
        <input type="hidden" name="rac_pdf_id" id="rac_pdf_id" value="<?php echo esc_attr( $pdf_id ); ?>" />
        <button type="button" class="button" id="rac_botao_pdf">Selecionar PDF na Biblioteca de Mídia</button>
        <div id="rac_pdf_preview" style="margin-top:8px;">
            <?php if ( $pdf_url ) : ?>
                <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank"><?php echo esc_html( basename( $pdf_url ) ); ?></a>
            <?php endif; ?>
        </div>
    </div>

    <p style="color:#666;">Lembrete: a capa do livro é definida no campo "Imagem destacada", no menu lateral direito.</p>

    <script>
    jQuery(document).ready(function($){
        var frame;
        $('#rac_botao_pdf').on('click', function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'Selecionar arquivo PDF',
                button: { text: 'Usar este arquivo' },
                library: { type: 'application/pdf' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#rac_pdf_id').val(attachment.id);
                $('#rac_pdf_preview').html('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
            });
            frame.open();
        });
    });
    </script>
    <?php
}

function rac_salvar_metabox_livro( $post_id ) {
    if ( ! isset( $_POST['rac_livro_nonce'] ) || ! wp_verify_nonce( $_POST['rac_livro_nonce'], 'rac_salvar_livro' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $campos = array( 'rac_ano_inicio', 'rac_ano_fim', 'rac_volume', 'rac_pdf_id' );
    foreach ( $campos as $campo ) {
        if ( isset( $_POST[ $campo ] ) ) {
            update_post_meta( $post_id, '_' . $campo, sanitize_text_field( $_POST[ $campo ] ) );
        }
    }
    if ( isset( $_POST['rac_descricao_breve'] ) ) {
        update_post_meta( $post_id, '_rac_descricao_breve', sanitize_textarea_field( $_POST['rac_descricao_breve'] ) );
    }
}
add_action( 'save_post_livro', 'rac_salvar_metabox_livro' );

function rac_admin_scripts( $hook ) {
    global $post_type;
    if ( 'livro' === $post_type && in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'rac_admin_scripts' );

/* ============================================================
   2.1 CAMPO "AUTOR OU AUTORA?" NO PERFIL DO USUÁRIO
   Definido uma vez por pessoa, usado em todos os livros dela.
   ============================================================ */
function rac_campo_genero_perfil( $user ) {
    $genero = get_user_meta( $user->ID, 'rac_autor_genero', true ) ?: 'autor';
    ?>
    <h2>Catálogo de Livros</h2>
    <table class="form-table">
        <tr>
            <th><label for="rac_autor_genero">Autor ou autora?</label></th>
            <td>
                <select name="rac_autor_genero" id="rac_autor_genero">
                    <option value="autor" <?php selected( $genero, 'autor' ); ?>>Autor</option>
                    <option value="autora" <?php selected( $genero, 'autora' ); ?>>Autora</option>
                </select>
                <p class="description">Usado para mostrar "Autor:" ou "Autora:" nos livros desta pessoa.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'rac_campo_genero_perfil' );
add_action( 'edit_user_profile', 'rac_campo_genero_perfil' );

function rac_salvar_genero_perfil( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) return;
    if ( isset( $_POST['rac_autor_genero'] ) ) {
        update_user_meta( $user_id, 'rac_autor_genero', sanitize_text_field( $_POST['rac_autor_genero'] ) );
    }
}
add_action( 'personal_options_update', 'rac_salvar_genero_perfil' );
add_action( 'edit_user_profile_update', 'rac_salvar_genero_perfil' );

/* ============================================================
   3. RENDERIZAÇÃO DO GRID (usada no shortcode e no AJAX)
   ============================================================ */
function rac_montar_query_livros( $filtros = array() ) {
    $ordenar = isset( $filtros['ordenar'] ) ? $filtros['ordenar'] : 'ano_desc';

    $args = array(
        'post_type'      => 'livro',
        'post_status'    => 'publish',
        'posts_per_page' => 24,
    );

    switch ( $ordenar ) {
        case 'titulo_asc':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'autor_asc':
            $args['orderby'] = 'author';
            $args['order']   = 'ASC';
            break;
        case 'ano_asc':
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_rac_ano_inicio';
            $args['order']    = 'ASC';
            break;
        case 'ano_desc':
        default:
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_rac_ano_fim';
            $args['order']    = 'DESC';
            break;
    }

    $tax_query  = array();
    $meta_query = array();

    if ( ! empty( $filtros['categoria'] ) ) {
        $tax_query[] = array(
            'taxonomy' => 'categoria_livro',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $filtros['categoria'] ),
        );
    }
    if ( ! empty( $filtros['autor'] ) ) {
        $args['author'] = intval( $filtros['autor'] );
    }
    if ( ! empty( $filtros['ano'] ) ) {
        $ano = intval( $filtros['ano'] );
        $meta_query[] = array(
            'key'     => '_rac_ano_inicio',
            'value'   => $ano,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
        $meta_query[] = array(
            'key'     => '_rac_ano_fim',
            'value'   => $ano,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    if ( $tax_query ) $args['tax_query'] = $tax_query;
    if ( $meta_query ) {
        $meta_query['relation'] = 'AND';
        $args['meta_query'] = $meta_query;
    }

    return new WP_Query( $args );
}

function rac_renderizar_grid( $filtros = array() ) {
    $query = rac_montar_query_livros( $filtros );

    if ( ! $query->have_posts() ) {
        return '<p class="rac-vazio">Nenhum livro encontrado com esses filtros.</p>';
    }

    ob_start();
    echo '<div class="rac-grid">';
    while ( $query->have_posts() ) {
        $query->the_post();
        $id           = get_the_ID();
        $ano_inicio   = get_post_meta( $id, '_rac_ano_inicio', true );
        $ano_fim      = get_post_meta( $id, '_rac_ano_fim', true );
        $ano_exibicao = ( $ano_inicio && $ano_fim && $ano_inicio != $ano_fim ) ? "$ano_inicio–$ano_fim" : $ano_inicio;
        $volume       = get_post_meta( $id, '_rac_volume', true );
        $autor_id     = get_the_author_meta( 'ID' );
        $autor_nome   = get_the_author_meta( 'display_name', $autor_id );
        $autor_url    = get_author_posts_url( $autor_id );
        $autor_genero = get_user_meta( $autor_id, 'rac_autor_genero', true ) === 'autora' ? 'Autora' : 'Autor';
        $descricao    = get_post_meta( $id, '_rac_descricao_breve', true );
        $pdf_id       = get_post_meta( $id, '_rac_pdf_id', true );
        $pdf_url      = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
        ?>
        <div class="rac-card">
            <div class="rac-capa">
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'medium' ); ?>
                <?php endif; ?>
            </div>
            <div class="rac-info">
                <?php
                $categoria_termos    = get_the_terms( $id, 'categoria_livro' );
                $categoria_primaria  = ( $categoria_termos && ! is_wp_error( $categoria_termos ) ) ? $categoria_termos[0]->name : '';
                ?>
                <?php if ( $categoria_primaria ) : ?>
                    <span class="rac-badge"><?php echo esc_html( $categoria_primaria ); ?></span>
                <?php endif; ?>
                <h3><?php the_title(); ?></h3>
                <p class="rac-meta">
                    <?php echo esc_html( $autor_genero . ': ' ); ?><a class="rac-autor-link" href="<?php echo esc_url( $autor_url ); ?>"><?php echo esc_html( $autor_nome ); ?></a><br/>
                    <?php echo esc_html( $ano_exibicao ); ?>
                    <?php if ( $volume ) echo ' · ' . esc_html( $volume ); ?>
                </p>
                <?php if ( $descricao ) : ?>
                    <div class="rac-descricao-wrap">
                        <p class="rac-descricao"><?php echo esc_html( $descricao ); ?></p>
                        <div class="rac-descricao-full">
                            <p><?php echo esc_html( $descricao ); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( $pdf_url ) : ?>
                    <a class="rac-botao-download" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener">Baixar livro grátis</a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}

/* ============================================================
   4. SHORTCODE + FILTROS + AJAX
   ============================================================ */
function rac_shortcode_catalogo() {
    $categorias = get_terms( array( 'taxonomy' => 'categoria_livro', 'hide_empty' => true ) );

    global $wpdb;
    $autor_ids = $wpdb->get_col( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_type = 'livro' AND post_status = 'publish'" );
    $autores   = array();
    foreach ( $autor_ids as $autor_id ) {
        $autores[] = array( 'id' => $autor_id, 'nome' => get_the_author_meta( 'display_name', $autor_id ) );
    }
    usort( $autores, function( $a, $b ) { return strcasecmp( $a['nome'], $b['nome'] ); } );
    $anos = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_rac_ano_inicio' AND meta_value != '' ORDER BY meta_value DESC" );

    ob_start();
    ?>
    <div class="rac-catalogo">
        <div class="rac-filtros">
            <select id="rac-filtro-categoria">
                <option value="">Categoria</option>
                <?php foreach ( $categorias as $cat ) : ?>
                    <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                <?php endforeach; ?>
            </select>

            <select id="rac-filtro-autor">
                <option value="">Autor(a)</option>
                <?php foreach ( $autores as $autor ) : ?>
                    <option value="<?php echo esc_attr( $autor['id'] ); ?>"><?php echo esc_html( $autor['nome'] ); ?></option>
                <?php endforeach; ?>
            </select>

            <select id="rac-filtro-ano">
                <option value="">Ano</option>
                <?php foreach ( $anos as $ano ) : ?>
                    <option value="<?php echo esc_attr( $ano ); ?>"><?php echo esc_html( $ano ); ?></option>
                <?php endforeach; ?>
            </select>

            <button id="rac-limpar-filtros" type="button">Limpar filtros</button>

            <select id="rac-ordenar" class="rac-ordenar">
                <option value="ano_desc">Mais recentes primeiro</option>
                <option value="ano_asc">Mais antigos primeiro</option>
                <option value="titulo_asc">Título (A-Z)</option>
                <option value="autor_asc">Autor (A-Z)</option>
            </select>
        </div>

        <div id="rac-resultado">
            <?php echo rac_renderizar_grid(); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'catalogo_livros', 'rac_shortcode_catalogo' );

function rac_ajax_filtrar_livros() {
    $filtros = array(
        'categoria' => isset( $_POST['categoria'] ) ? sanitize_text_field( $_POST['categoria'] ) : '',
        'autor'     => isset( $_POST['autor'] ) ? intval( $_POST['autor'] ) : '',
        'ano'       => isset( $_POST['ano'] ) ? sanitize_text_field( $_POST['ano'] ) : '',
        'ordenar'   => isset( $_POST['ordenar'] ) ? sanitize_text_field( $_POST['ordenar'] ) : 'ano_desc',
    );
    echo rac_renderizar_grid( $filtros );
    wp_die();
}
add_action( 'wp_ajax_rac_filtrar_livros', 'rac_ajax_filtrar_livros' );
add_action( 'wp_ajax_nopriv_rac_filtrar_livros', 'rac_ajax_filtrar_livros' );

function rac_enqueue_frontend() {
    if ( ! is_singular() && ! is_page() ) return;

    wp_register_style( 'rac-catalogo', false );
    wp_enqueue_style( 'rac-catalogo' );
    wp_add_inline_style( 'rac-catalogo', '
        .rac-filtros { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:28px; padding:14px 16px; background:#f7f7f5; border-radius:8px; }
        .rac-filtros select, .rac-filtros button { padding:8px 12px; border:1px solid #ddd; border-radius:6px; background:#fff; font-size:13px; color:#333; }
        .rac-filtros .rac-ordenar { margin-left:auto; }
        #rac-limpar-filtros { background:#ee8133; color:#fff; border:none; font-weight:600; cursor:pointer; }
        #rac-limpar-filtros:hover { background:#d6702a; }
        .rac-grid { display:grid; grid-template-columns:repeat(1,1fr); gap:20px; }
        @media (min-width:600px) { .rac-grid { grid-template-columns:repeat(2,1fr); } }
        @media (min-width:900px) { .rac-grid { grid-template-columns:repeat(3,1fr); } }
        @media (min-width:1200px) { .rac-grid { grid-template-columns:repeat(4,1fr); } }
        .rac-card { border:1px solid #eaeaea; border-radius:10px; overflow:hidden; display:flex; flex-direction:column; transition:box-shadow .15s ease; }
        .rac-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.06); }
        .rac-capa { display:flex; justify-content:center; padding:14px 0 0; }
        .rac-capa img { width:65%; aspect-ratio:2/3; object-fit:cover; display:block; border-radius:4px; }
        .rac-info { padding:12px 14px 14px; flex:1; display:flex; flex-direction:column; }
        .rac-badge { align-self:flex-start; font-size:11px; font-weight:600; color:#a3591c; background:#fbe6d3; padding:0px 9px; border-radius:7px; margin-bottom:8px; }
        .rac-info h3 { font-size:14.5px; font-weight:600; line-height:1.3; margin:0 0 5px; }
        .rac-meta { font-size:12px; color:#777; margin:0 0 8px; line-height:1.5; }
        .rac-autor-link { color:#777; text-decoration:underline; }
        .rac-autor-link:hover { color:#a3591c; }
        .rac-descricao-wrap { position:relative; flex:1; }
        .rac-descricao { font-size:12.5px; color:#444; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .rac-descricao-full { display:none; position:absolute; bottom:100%; top:auto; left:-1px; right:-1px; margin-bottom:6px; background:#fff; border:1px solid #eaeaea; border-radius:8px; padding:10px 12px; box-shadow:0 8px 20px rgba(0,0,0,.14); z-index:20; font-size:12.5px; color:#444; line-height:1.5; }
        .rac-descricao-full p { margin:0; }
        .rac-descricao-wrap.rac-truncado:hover .rac-descricao-full { display:block; }
        .rac-botao-download { margin-top:10px; display:inline-block; padding:5px 12px; background:#ee8133; color:#fff; text-decoration:none; border-radius:5px; text-align:center; font-size:15px; font-weight:600; transition:background .15s ease; }
        .rac-botao-download:hover { background:#a3591c; text-decoration:underline; }
        .rac-vazio { color:#777; }
    ' );

    wp_register_script( 'rac-catalogo', '', array(), '1.0', true );
    wp_enqueue_script( 'rac-catalogo' );
    wp_add_inline_script( 'rac-catalogo', '
        (function(){
            var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";
            function marcarTruncados(){
                document.querySelectorAll(".rac-descricao-wrap").forEach(function(wrap){
                    var texto = wrap.querySelector(".rac-descricao");
                    if (!texto) return;
                    if (texto.scrollHeight > texto.clientHeight + 1) {
                        wrap.classList.add("rac-truncado");
                    } else {
                        wrap.classList.remove("rac-truncado");
                    }
                });
            }
            function aplicarFiltros(){
                var categoria = document.getElementById("rac-filtro-categoria");
                var autor = document.getElementById("rac-filtro-autor");
                var ano = document.getElementById("rac-filtro-ano");
                var ordenar = document.getElementById("rac-ordenar");
                if (!categoria) return;
                var dados = new URLSearchParams();
                dados.append("action", "rac_filtrar_livros");
                dados.append("categoria", categoria.value);
                dados.append("autor", autor.value);
                dados.append("ano", ano.value);
                dados.append("ordenar", ordenar ? ordenar.value : "ano_desc");
                fetch(ajaxurl, { method: "POST", body: dados })
                    .then(function(r){ return r.text(); })
                    .then(function(html){
                        document.getElementById("rac-resultado").innerHTML = html;
                        marcarTruncados();
                    });
            }
            document.addEventListener("change", function(e){
                if (["rac-filtro-categoria","rac-filtro-autor","rac-filtro-ano","rac-ordenar"].indexOf(e.target.id) !== -1) {
                    aplicarFiltros();
                }
            });
            document.addEventListener("click", function(e){
                if (e.target && e.target.id === "rac-limpar-filtros") {
                    document.getElementById("rac-filtro-categoria").value = "";
                    document.getElementById("rac-filtro-autor").value = "";
                    document.getElementById("rac-filtro-ano").value = "";
                    document.getElementById("rac-ordenar").value = "ano_desc";
                    aplicarFiltros();
                }
            });
            marcarTruncados();
        })();
    ' );
}
add_action( 'wp_enqueue_scripts', 'rac_enqueue_frontend' );

/* ============================================================
   5. COLUNAS EXTRAS NA LISTAGEM DO ADMIN
   ============================================================ */
function rac_colunas_livro( $columns ) {
    $novas = array();
    foreach ( $columns as $chave => $valor ) {
        $novas[ $chave ] = $valor;
        if ( 'title' === $chave ) {
            $novas['rac_categoria'] = 'Categoria';
            $novas['rac_autor']     = 'Autor(a)';
            $novas['rac_ano']       = 'Ano';
        }
    }
    return $novas;
}
add_filter( 'manage_livro_posts_columns', 'rac_colunas_livro' );

function rac_preencher_colunas_livro( $coluna, $post_id ) {
    switch ( $coluna ) {
        case 'rac_categoria':
            $termos = get_the_terms( $post_id, 'categoria_livro' );
            echo ( $termos && ! is_wp_error( $termos ) )
                ? esc_html( implode( ', ', wp_list_pluck( $termos, 'name' ) ) )
                : '—';
            break;

        case 'rac_autor':
            $nome = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
            echo $nome ? esc_html( $nome ) : '—';
            break;

        case 'rac_ano':
            $inicio = get_post_meta( $post_id, '_rac_ano_inicio', true );
            $fim    = get_post_meta( $post_id, '_rac_ano_fim', true );
            if ( $inicio && $fim && $inicio != $fim ) {
                echo esc_html( $inicio . '–' . $fim );
            } elseif ( $inicio ) {
                echo esc_html( $inicio );
            } else {
                echo '—';
            }
            break;
    }
}
add_action( 'manage_livro_posts_custom_column', 'rac_preencher_colunas_livro', 10, 2 );
