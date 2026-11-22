<?php
/**
 * Template Name: Contato
 *
 * Página Contato (Contato.dc.html). Vale para a página com o slug "contato" mesmo sem escolher o
 * modelo; escolhendo o modelo "Contato" em Atributos da página, aparecem os campos (e-mail de
 * destino, textos, redes). O formulário envia sem recarregar (assets/js/contato.js) e cada mensagem
 * fica salva em "Mensagens de contato" no painel (inc/contato.php).
 *
 * ?assunto=pedido-musical (ou outro) deixa aquele assunto já marcado.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$pagina_id = get_queried_object_id();
$t         = function ( $nome ) use ( $pagina_id ) { return rac_contato_texto( $nome, $pagina_id ); };
$assuntos  = rac_contato_assuntos();
$escolhido = isset( $_GET['assunto'] ) ? sanitize_key( wp_unslash( $_GET['assunto'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! isset( $assuntos[ $escolhido ] ) ) $escolhido = 'sugestao-de-musica';
$email_pub = sanitize_email( (string) rac_campo( 'contato_email_publico', $pagina_id, '' ) );
$redes     = rac_contato_redes( $pagina_id );

get_header();
?>
<main id="primary" class="rac-main">
    <section class="rac-contato" data-rac-contato>
        <div class="rac-contato__info">
            <span class="rac-eyebrow">Contato</span>
            <h1><?php echo esc_html( $t( 'contato_titulo' ) ); ?></h1>
            <p class="rac-contato__texto"><?php echo esc_html( $t( 'contato_texto' ) ); ?></p>
            <div class="rac-contato__linhas">
                <?php if ( $email_pub ) : ?>
                    <div class="rac-contato__linha"><span>E-mail</span><a href="mailto:<?php echo esc_attr( antispambot( $email_pub ) ); ?>"><?php echo esc_html( antispambot( $email_pub ) ); ?></a></div>
                <?php endif; ?>
                <?php if ( $t( 'contato_local' ) !== '' ) : ?>
                    <div class="rac-contato__linha"><span>Onde estamos</span><strong><?php echo esc_html( $t( 'contato_local' ) ); ?></strong></div>
                <?php endif; ?>
                <?php if ( $redes ) : ?>
                    <div class="rac-contato__linha rac-contato__linha--redes"><span>Redes</span>
                        <div class="rac-contato__redes">
                            <?php foreach ( $redes as $r ) : ?>
                                <a href="<?php echo esc_url( $r['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $r['rotulo'] ); ?>"><?php echo esc_html( $r['sigla'] ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="rac-contato__cartao">
            <form class="rac-contato__form" data-rac-contato-form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                <input type="hidden" name="action" value="rac_contato">
                <input type="hidden" name="pagina" value="<?php echo (int) $pagina_id; ?>">
                <input type="hidden" name="rac_ms" value="0">
                <div class="rac-recados__armadilha" aria-hidden="true"><label>Não preencha este campo <input type="text" name="rac_site" tabindex="-1" autocomplete="off"></label></div>

                <fieldset class="rac-contato__assuntos">
                    <legend>Assunto</legend>
                    <div class="rac-contato__chips">
                        <?php foreach ( $assuntos as $slug => $rotulo ) : ?>
                            <label class="rac-contato__chip">
                                <input type="radio" name="assunto" value="<?php echo esc_attr( $slug ); ?>" data-musical="<?php echo rac_contato_assunto_musical( $slug ) ? '1' : '0'; ?>"<?php checked( $slug, $escolhido ); ?>>
                                <span><?php echo esc_html( $rotulo ); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="rac-contato__dupla">
                    <label class="rac-campo"><span>Nome</span><input type="text" name="nome" placeholder="Seu nome" required maxlength="100" autocomplete="name"></label>
                    <label class="rac-campo"><span>E-mail</span><input type="email" name="email" placeholder="voce@email.com" required maxlength="100" autocomplete="email"></label>
                </div>

                <div class="rac-contato__musica" data-rac-contato-musica<?php echo rac_contato_assunto_musical( $escolhido ) ? '' : ' hidden'; ?>>
                    <label class="rac-campo"><span>Música</span><input type="text" name="musica" placeholder="Nome da faixa" maxlength="150"></label>
                    <label class="rac-campo"><span>Artista</span><input type="text" name="artista" placeholder="Quem canta ou toca" maxlength="150"></label>
                </div>

                <label class="rac-campo"><span>Mensagem</span>
                    <textarea name="mensagem" rows="5" required maxlength="3000" data-rac-contato-mensagem
                        data-ph-musical="Conte por que essa música merece tocar (e em qual estação, se quiser)…" data-ph-outro="Escreva sua mensagem…"
                        placeholder="<?php echo esc_attr( rac_contato_assunto_musical( $escolhido ) ? 'Conte por que essa música merece tocar (e em qual estação, se quiser)…' : 'Escreva sua mensagem…' ); ?>"></textarea>
                </label>

                <p class="rac-contato__erro" role="alert" data-rac-contato-erro hidden></p>
                <div class="rac-contato__rodape">
                    <span>Seu e-mail não será publicado e é usado só para responder.</span>
                    <button type="submit" class="rac-botao-cheio" data-rac-contato-enviar>Enviar mensagem</button>
                </div>
            </form>

            <div class="rac-contato__enviado" role="status" data-rac-contato-enviado hidden>
                <span class="rac-contato__ok" aria-hidden="true">✓</span>
                <h2>Mensagem enviada</h2>
                <p><?php echo esc_html( $t( 'contato_enviado' ) ); ?></p>
                <button type="button" class="rac-botao-contorno" data-rac-contato-outra>Enviar outra mensagem</button>
            </div>
            <noscript><p class="rac-contato__erro">Para enviar a mensagem, ative o JavaScript do navegador.</p></noscript>
        </div>
    </section>
</main>
<?php
get_footer();
