<?php
/**
 * Comentários dos posts (Fase 4B): comentários nativos do WordPress, com
 * respostas em um nível, selo "Autor/Autora", "Aguardando moderação", troca da
 * ordem e paginação. O formulário envia por fetch (assets/js/comentarios.js), sem
 * recarregar a página, então o áudio da rádio não para.
 *
 * Ajustes no painel (Configurações → Discussão): comentários aninhados em 2
 * níveis, quebra em páginas (~10 por página) e moderação manual.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** "Mais recentes primeiro" por ?comentarios=novos (o padrão é a ordem do WordPress, mais antigos primeiro). */
function rac_args_comentarios( $args ) {
    if ( isset( $_GET['comentarios'] ) && $_GET['comentarios'] === 'novos' ) $args['order'] = 'DESC';
    return $args;
}
add_filter( 'comments_template_query_args', 'rac_args_comentarios' );

/** Está em "mais recentes primeiro"? */
function rac_comentarios_novos_primeiro() {
    return isset( $_GET['comentarios'] ) && $_GET['comentarios'] === 'novos';
}

/** "N comentários" */
function rac_titulo_comentarios( $n ) {
    if ( $n === 0 ) return 'Comentários';
    return $n === 1 ? '1 comentário' : $n . ' comentários';
}

/** Cor do avatar (varia por pessoa, sempre por token do tema). */
function rac_cor_avatar( $comentario ) {
    $cores = array( 'var(--accent-soft)', 'var(--av3)', 'var(--av4)', 'var(--av5)' );
    return $cores[ crc32( strtolower( $comentario->comment_author_email . $comentario->comment_author ) ) % count( $cores ) ];
}

/** Desenha um comentário. O WordPress fecha o <div> e aninha as respostas dentro dele. */
function rac_comentario_callback( $comentario, $args, $profundidade ) {
    $post_autor = (int) get_post_field( 'post_author', $comentario->comment_post_ID );
    $e_autor    = (int) $comentario->user_id > 0 && (int) $comentario->user_id === $post_autor;
    $pendente   = (string) $comentario->comment_approved === '0';
    $pode_responder = comments_open( $comentario->comment_post_ID ) && $profundidade < (int) $args['max_depth'];
    $nome       = $comentario->comment_author ? $comentario->comment_author : 'Ouvinte';
    ?>
    <div id="comment-<?php echo esc_attr( $comentario->comment_ID ); ?>" class="rac-coment rac-coment--n<?php echo (int) $profundidade; ?>">
        <div class="rac-coment__linha">
            <span class="rac-coment__avatar" style="background:<?php echo esc_attr( rac_cor_avatar( $comentario ) ); ?>" aria-hidden="true"><?php echo esc_html( rac_inicial( $nome ) ); ?></span>
            <div class="rac-coment__corpo">
                <div class="rac-coment__cab">
                    <span class="rac-coment__nome"><?php echo esc_html( $nome ); ?></span>
                    <?php if ( $e_autor ) : ?><span class="rac-coment__selo"><?php echo esc_html( rac_rotulo_autoria( $comentario->user_id ) ); ?></span><?php endif; ?>
                    <span class="rac-coment__data"><?php echo esc_html( rac_tempo_relativo( $comentario ) ); ?></span>
                    <?php if ( $pendente ) : ?><span class="rac-coment__pendente">Aguardando moderação</span><?php endif; ?>
                </div>
                <div class="rac-coment__texto"><?php echo wp_kses_post( wpautop( get_comment_text( $comentario ) ) ); ?></div>
                <?php if ( $pode_responder && ! $pendente ) : ?>
                    <button type="button" class="rac-coment__responder" data-rac-responder="<?php echo esc_attr( $comentario->comment_ID ); ?>" data-rac-nome="<?php echo esc_attr( $nome ); ?>">Responder</button>
                <?php endif; ?>
            </div>
        </div>
    <?php
    // sem </div> final: quem fecha é o WordPress, depois de imprimir as respostas
}

/** Dados da paginação dos comentários. */
function rac_paginacao_comentarios() {
    $total = (int) get_comment_pages_count();
    $atual = max( 1, (int) get_query_var( 'cpage' ) );
    if ( $total < 1 ) $total = 1;
    return array(
        'total'    => $total,
        'atual'    => min( $atual, $total ),
        'anterior' => $atual > 1 ? get_comments_pagenum_link( $atual - 1 ) . '#comentarios' : '',
        'proxima'  => $atual < $total ? get_comments_pagenum_link( $atual + 1 ) . '#comentarios' : '',
    );
}
