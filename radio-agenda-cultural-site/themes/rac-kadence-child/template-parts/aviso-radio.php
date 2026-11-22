<?php
/**
 * Aviso "A rádio está tocando": aparece (por assets/js/blog.js) só quando a rádio está
 * tocando, ao lado de vídeos e players de artista, com um botão que a pausa. O
 * player de terceiros (YouTube, Spotify) é independente da rádio; sem isso os dois sons
 * se misturariam.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="rac-aviso-radio" data-rac-aviso-radio hidden role="status">
    <span>A rádio está tocando. Para ouvir com calma, pause a rádio.</span>
    <button type="button" class="rac-botao-claro" data-rac-pausar-radio>Pausar a rádio</button>
</div>
