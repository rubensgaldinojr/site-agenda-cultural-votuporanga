<?php
/**
 * Comentários do post (Post.dc.html, seção #comentarios): lista com respostas em um
 * nível, selo "Autor/Autora", "Aguardando moderação", troca de ordem, paginação e
 * formulário. O envio é feito por assets/js/comentarios.js (fetch), sem recarregar
 * a página: o áudio da rádio continua tocando. Sem JavaScript o formulário funciona
 * do jeito normal do WordPress (recarrega a página).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( post_password_required() ) return;

$n_total    = (int) get_comments_number();
$abertos    = comments_open();
$novos      = rac_comentarios_novos_primeiro();
$base       = remove_query_arg( 'comentarios', get_permalink() );
$pag        = rac_paginacao_comentarios();
$comentarista = wp_get_current_commenter();
$logado     = is_user_logged_in();
$usuario    = $logado ? wp_get_current_user() : null;
?>
<section class="rac-comentarios" id="comentarios" data-rac-comentarios>
    <div class="rac-comentarios__inner">
        <div class="rac-comentarios__cab">
            <h2><?php echo esc_html( rac_titulo_comentarios( $n_total ) ); ?></h2>
            <?php if ( $n_total > 1 ) : ?>
                <div class="rac-comentarios__ordem" role="group" aria-label="Ordem dos comentários">
                    <a href="<?php echo esc_url( $base . '#comentarios' ); ?>" <?php echo ! $novos ? 'aria-current="true" class="is-ativa"' : ''; ?>>Mais antigos</a>
                    <a href="<?php echo esc_url( add_query_arg( 'comentarios', 'novos', $base ) . '#comentarios' ); ?>" <?php echo $novos ? 'aria-current="true" class="is-ativa"' : ''; ?>>Mais recentes</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( have_comments() ) : ?>
            <div class="rac-comentarios__lista">
                <?php
                wp_list_comments( array(
                    'style'       => 'div',
                    'short_ping'  => true,
                    'max_depth'   => 2,
                    'callback'    => 'rac_comentario_callback',
                ) );
                ?>
            </div>

            <?php if ( $pag['total'] > 1 ) : ?>
                <nav class="rac-comentarios__paginas" aria-label="Páginas de comentários">
                    <?php if ( $pag['anterior'] ) : ?><a href="<?php echo esc_url( $pag['anterior'] ); ?>">‹ Comentários anteriores</a><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
                    <span class="rac-comentarios__pagina">Página <?php echo (int) $pag['atual']; ?> de <?php echo (int) $pag['total']; ?></span>
                    <?php if ( $pag['proxima'] ) : ?><a href="<?php echo esc_url( $pag['proxima'] ); ?>">Próximos comentários ›</a><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php elseif ( $abertos ) : ?>
            <p class="rac-comentarios__vazio">Ainda não há comentários. Seja a primeira pessoa a comentar!</p>
        <?php endif; ?>

        <?php if ( $abertos ) : ?>
        <div class="rac-comentarios__form-caixa" data-rac-comentarios-form-caixa>
            <h3 data-rac-form-titulo>Deixe seu comentário</h3>
            <p class="rac-comentarios__aviso">O comentário aparece depois de aprovado. Seu e-mail não será publicado. Os campos marcados com * são obrigatórios.</p>
            <form class="rac-comentarios__form" data-rac-comentarios-form method="post" action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>">
                <input type="hidden" name="comment_post_ID" value="<?php echo (int) get_the_ID(); ?>">
                <input type="hidden" name="comment_parent" value="0" data-rac-pai>
                <input type="hidden" name="rac_form" value="1">
                <input type="hidden" name="rac_ms" value="0">
                <div class="rac-recados__armadilha" aria-hidden="true"><label>Não preencha este campo <input type="text" name="rac_site" tabindex="-1" autocomplete="off"></label></div>

                <p class="rac-comentarios__respondendo" data-rac-respondendo hidden>
                    <span>Respondendo a <b data-rac-respondendo-nome></b></span>
                    <button type="button" class="rac-botao-texto" data-rac-cancelar-resposta>Cancelar resposta</button>
                </p>

                <label class="rac-campo"><span>Comentário *</span>
                    <textarea name="comment" rows="5" required maxlength="3000"></textarea>
                </label>
                <?php if ( $logado ) : ?>
                    <p class="rac-comentarios__logado">Você está comentando como <b><?php echo esc_html( $usuario->display_name ); ?></b>.</p>
                <?php else : ?>
                    <div class="rac-comentarios__dupla">
                        <label class="rac-campo"><span>Nome *</span>
                            <input type="text" name="author" required maxlength="245" autocomplete="name" value="<?php echo esc_attr( $comentarista['comment_author'] ); ?>">
                        </label>
                        <label class="rac-campo"><span>E-mail *</span>
                            <input type="email" name="email" required maxlength="100" autocomplete="email" value="<?php echo esc_attr( $comentarista['comment_author_email'] ); ?>">
                        </label>
                    </div>
                    <label class="rac-campo"><span>Site <em>(opcional)</em></span>
                        <input type="url" name="url" maxlength="200" autocomplete="url" value="<?php echo esc_attr( $comentarista['comment_author_url'] ); ?>">
                    </label>
                    <label class="rac-comentarios__lembrar">
                        <input type="checkbox" name="wp-comment-cookies-consent" value="yes" <?php checked( ! empty( $comentarista['comment_author_email'] ) ); ?>>
                        <span>Salvar meus dados neste navegador para os próximos comentários.</span>
                    </label>
                <?php endif; ?>

                <div class="rac-comentarios__acoes">
                    <button type="submit" class="rac-botao-cheio" data-rac-enviar>Publicar comentário</button>
                </div>
                <p class="rac-comentarios__erro" role="alert" data-rac-erro hidden></p>
            </form>
            <p class="rac-comentarios__ok" role="status" data-rac-ok hidden>Obrigado! Seu comentário aparece depois de aprovado.</p>
        </div>
        <?php elseif ( $n_total > 0 ) : ?>
            <p class="rac-comentarios__fechado">Os comentários deste post estão fechados.</p>
        <?php endif; ?>
    </div>
</section>
