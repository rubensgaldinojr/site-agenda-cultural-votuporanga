<?php
/**
 * Funções auxiliares do tema: menu, logo e lista de estações.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
   MENU
   ============================================================ */

/**
 * Itens do menu principal. Se existir um menu atribuído ao local
 * "Menu principal" (Aparência → Menus), usa os itens de primeiro nível
 * dele; senão, usa o menu padrão do mockup.
 *
 * Os caminhos do menu padrão são suposições (/estacoes/, /blog/, ...).
 * Se as páginas reais tiverem outros endereços, basta atribuir um menu
 * no painel; ele sempre tem prioridade.
 *
 * @return array Lista de ['label' => string, 'url' => string].
 */
function rac_itens_menu() {
    $locais = get_nav_menu_locations();

    if ( ! empty( $locais['rac-principal'] ) ) {
        $itens = wp_get_nav_menu_items( $locais['rac-principal'] );
        if ( $itens ) {
            $lista = array();
            foreach ( $itens as $item ) {
                if ( (int) $item->menu_item_parent !== 0 ) continue; // só primeiro nível
                $lista[] = array( 'label' => $item->title, 'url' => $item->url );
            }
            if ( $lista ) return $lista;
        }
    }

    $pagina_blog = (int) get_option( 'page_for_posts' );
    $podcasts    = get_category_by_slug( 'podcasts' );

    return apply_filters( 'rac_menu_padrao', array(
        array( 'label' => 'Estações',   'url' => home_url( '/estacoes/' ) ),
        array( 'label' => 'Blog',       'url' => $pagina_blog ? get_permalink( $pagina_blog ) : home_url( '/blog/' ) ),
        array( 'label' => 'Literatura', 'url' => home_url( '/literatura/' ) ),
        array( 'label' => 'Podcasts',   'url' => $podcasts ? get_category_link( $podcasts ) : home_url( '/podcasts/' ) ),
        array( 'label' => 'Sobre',      'url' => home_url( '/sobre/' ) ),
    ) );
}

/**
 * Menu do mobile e do rodapé: os mesmos itens + "Contato" (que no desktop
 * só aparece no rodapé), a menos que já esteja no menu.
 */
function rac_itens_menu_completo() {
    $itens = rac_itens_menu();
    foreach ( $itens as $item ) {
        if ( mb_strtolower( trim( $item['label'] ) ) === 'contato' ) {
            return $itens;
        }
    }
    $itens[] = array( 'label' => 'Contato', 'url' => home_url( '/contato/' ) );
    return $itens;
}

/**
 * Marca qual item do menu corresponde à página atual. Mesma regra do JS
 * (assets/js/rac.js → marcarMenuAtivo): vence o item cujo caminho casa
 * por igualdade ou prefixo e é mais longo; se o item tem "?query", ela
 * precisa ser igual à da página.
 *
 * @param array $itens Itens de rac_itens_menu*().
 * @return array Os mesmos itens com a chave 'ativo' (bool).
 */
function rac_marcar_item_ativo( $itens ) {
    $requisicao = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $atual      = wp_parse_url( $requisicao );
    $caminho    = isset( $atual['path'] ) ? untrailingslashit( $atual['path'] ) : '';
    $consulta   = isset( $atual['query'] ) ? '?' . $atual['query'] : '';

    $melhor = -1;
    $pontos = array();
    foreach ( $itens as $i => $item ) {
        $url_item = wp_parse_url( $item['url'] );
        $c_item   = isset( $url_item['path'] ) ? untrailingslashit( $url_item['path'] ) : '';
        $q_item   = isset( $url_item['query'] ) ? '?' . $url_item['query'] : '';

        $pontos[ $i ] = -1;
        if ( $c_item === '' ) continue; // link para a home não marca nada
        if ( $q_item !== '' && $q_item !== $consulta ) continue;
        if ( $caminho === $c_item || strpos( $caminho, $c_item . '/' ) === 0 ) {
            $pontos[ $i ] = strlen( $c_item ) * 10 + ( $q_item !== '' ? 5 : 0 );
            if ( $pontos[ $i ] > $melhor ) $melhor = $pontos[ $i ];
        }
    }
    foreach ( $itens as $i => $item ) {
        $itens[ $i ]['ativo'] = ( $melhor >= 0 && $pontos[ $i ] === $melhor );
    }
    return $itens;
}

/* ============================================================
   LOGO
   ============================================================ */

/**
 * HTML da logo. Ordem de preferência:
 *  1. logo do site (Aparência → Personalizar → Identidade do site);
 *  2. se também houver "Logo para fundo escuro", troca entre as duas
 *     conforme o tema (CSS: .rac-logo--claro / .rac-logo--escuro);
 *  3. sem logo escura, a logo clara ganha um filtro no modo escuro
 *     (.rac-logo--auto), como no tema.css do mockup;
 *  4. sem logo nenhuma, mostra o nome do site em texto.
 *
 * @param bool $prioritaria true no cabeçalho (acima da dobra, sem lazy-load).
 */
function rac_logo_html( $prioritaria = false ) {
    $nome   = get_bloginfo( 'name' );
    $claro  = (int) get_theme_mod( 'custom_logo' );
    $escuro = (int) get_theme_mod( 'rac_logo_escuro' );
    $carga  = $prioritaria ? 'eager' : 'lazy';

    if ( ! $claro ) {
        return '<span class="rac-logo-texto">' . esc_html( $nome ) . '</span>';
    }

    if ( $escuro ) {
        return wp_get_attachment_image( $claro, 'full', false, array( 'class' => 'rac-logo rac-logo--claro', 'alt' => $nome, 'loading' => $carga ) )
             . wp_get_attachment_image( $escuro, 'full', false, array( 'class' => 'rac-logo rac-logo--escuro', 'alt' => '', 'loading' => $carga ) );
    }

    return wp_get_attachment_image( $claro, 'full', false, array( 'class' => 'rac-logo rac-logo--auto', 'alt' => $nome, 'loading' => $carga ) );
}

/* ============================================================
   ESTAÇÕES (para a grade "Todas as estações" do rodapé)
   ============================================================ */

/**
 * Lista de estações para links. Reaproveita racr_obter_estacoes() do
 * plugin do player (já ordenada por "ordem_de_exibicao" e depois
 * alfabética, e só com estações que têm stream). Se o plugin estiver
 * desativado, cai numa consulta simples ao CPT "estacao".
 *
 * @return array Lista de ['id' => int, 'nome' => string, 'url' => string].
 */
function rac_listar_estacoes() {
    static $lista = null;
    if ( $lista !== null ) return $lista;

    $lista = array();

    if ( function_exists( 'racr_obter_estacoes' ) ) {
        foreach ( racr_obter_estacoes() as $e ) {
            $lista[] = array( 'id' => $e['id'], 'nome' => $e['nome'], 'url' => get_permalink( $e['id'] ) );
        }
        return $lista;
    }

    $posts = get_posts( array(
        'post_type'      => 'estacao',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ) );
    foreach ( $posts as $p ) {
        $lista[] = array( 'id' => $p->ID, 'nome' => get_the_title( $p ), 'url' => get_permalink( $p ) );
    }
    return $lista;
}
