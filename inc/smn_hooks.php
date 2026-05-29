<?php

// Agrega un filtro para el bloque de consulta de WordPress
// que muestra los posts relacionados en la página de un post y los filtra por categorías
function smn_filter_render_block_data($parsed_block) {

    if (
        is_single() &&
        isset($parsed_block['blockName']) &&
        $parsed_block['blockName'] === 'core/query' &&
        isset($parsed_block['attrs']['className']) &&
        strpos($parsed_block['attrs']['className'], 'is-style-is-related-posts') !== false
    ) {
        $category_ids = wp_get_post_categories(get_the_ID());

        if (!empty($category_ids)) {
            $parsed_block['attrs']['query']['categoryIds'] = $category_ids;
            $parsed_block['attrs']['query']['exclude'] = [get_the_ID()];
            $parsed_block['attrs']['query']['sticky'] = '';
            $parsed_block['attrs']['query']['perPage'] = 6;
        }
    }

    return $parsed_block;
}
add_filter('render_block_data', 'smn_filter_render_block_data');

add_filter('render_block', 'smn_add_breadcrumbs_to_headings', 10, 2);
function smn_add_breadcrumbs_to_headings($block_content, $block) {
    
    if ( is_front_page() ) return $block_content;

    // Only on the frontend, not in admin or REST
    if (is_admin() || defined('REST_REQUEST') && REST_REQUEST) {
        return $block_content;
    }

    // Check if block is core/heading with h1 or core/post-title
    $is_h1 = (
        isset($block['blockName']) &&
        $block['blockName'] === 'core/heading' &&
        isset($block['attrs']['level']) &&
        $block['attrs']['level'] == 1
    );
    $is_post_title = (
        isset($block['blockName']) &&
        $block['blockName'] === 'core/post-title' &&
        isset($block['attrs']['level']) &&
        $block['attrs']['level'] == 1
    );

    if ($is_h1 || $is_post_title) {
        // Render breadcrumbs using Rank Math
        ob_start();
        if (function_exists('rank_math_the_breadcrumbs')) {
            rank_math_the_breadcrumbs();
        }
        $breadcrumbs = ob_get_clean();
        return $block_content . $breadcrumbs;
    }

    return $block_content;
}

add_filter( 'render_block', 'smn_wrap_classic_block_in_columns', 10, 2 );
function smn_wrap_classic_block_in_columns( $block_content, $block ) {
    if ( !is_singular('solucion') || is_admin() || defined('REST_REQUEST') && REST_REQUEST) {
        return $block_content;
    }

    if ( !$block['blockName'] && trim( $block['innerHTML'] ) != '' ) {

        $r = '';

        $r .= '<div class="wp-block-columns is-layout-flex smn-classic-columns">';
            $r .= '<div class="wp-block-column">';
                $r .= $block_content;
            $r .= '</div>';
            $r .= '<div class="wp-block-column">';
                $r .= '<div class="wp-block-image smn-classic-image">';
                    $r .= get_the_post_thumbnail( null, 'medium_large', ['class' => 'aligncenter'] );
                $r .= '</div>';
            $r .= '</div>';
        $r .= '</div>';

        return $r;
    }

    return $block_content;
}

function smn_filter_get_the_archive_title($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title('', false);
    } elseif (is_tax()) {
        $title = single_term_title('', false);
    }
    return $title;
}
add_filter('get_the_archive_title', 'smn_filter_get_the_archive_title');

add_filter('rank_math/frontend/breadcrumb/items', 'smn_modify_rank_math_breadcrumbs', 10, 1);
function smn_modify_rank_math_breadcrumbs($crumbs) {

    if ( is_tax( 'tipo' ) ) {

        $post_type_archive_link = get_post_type_archive_link( 'solucion' );
        
        // Create the new breadcrumb item
        $new_crumb = array(
            __( 'Soluciones', 'sorribes' ), // Label for the archive
            $post_type_archive_link,
            'hide_in_mobile' => false,
        );
        
        // Insert it after the home link (index 0)
        array_splice( $crumbs, 1, 0, array( $new_crumb ) );

    }

    foreach ($crumbs as $i => $crumb) {
        
        if (
            isset($crumb[1]) &&
            get_post_type_archive_link('solucion') === $crumb[1]
        ) {
            // Replace with the CATALOGO_ID page
            $crumbs[$i][0] = get_the_title(CATALOGO_ID);
            $crumbs[$i][1] = get_permalink(CATALOGO_ID);
        }


    }

    return $crumbs;
}

function cmplz_show_banner_on_click() {
	?>
	<script>
        function addEvent(event, selector, callback, context) {
            document.addEventListener(event, e => {
                if ( e.target.closest(selector) ) {
                    callback(e);
                }
            });
        }
        addEvent('click', 'a.cmplz-show-banner, li.cmplz-show-banner a', function(e){
            e.preventDefault();
            document.querySelectorAll('.cmplz-manage-consent').forEach(obj => {
                obj.click();
            });
        });
	</script>
	<?php
}
add_action( 'wp_footer', 'cmplz_show_banner_on_click' );

add_filter('get_terms_args', function($args, $taxonomies) {
    if (in_array('tipo', (array) $taxonomies)) {
        $args['hide_empty'] = false;
    }
    return $args;
}, 10, 2);

// Añade hasta 3 niveles de la taxonomía 'tipo' como submenús anidados al menú Superfly, sin mostrar productos en el último nivel
add_filter('wp_nav_menu_objects', function($items, $args) {
    if (isset($args->menu_id) && $args->menu_id === 'sfm-nav') {
        foreach ($items as $index => $item) {
            if (in_array('taxonomy-tipo', $item->classes)) {
                $new_items = [];
                $id_counter = 900000;

                // Función recursiva para añadir términos hasta 3 niveles
                $add_terms_recursive = function($parent_id, $menu_parent_id, $nivel) use (&$add_terms_recursive, &$id_counter) {
                    $children = [];
                    if ($nivel > 3) return $children;
                    $terms = get_terms([
                        'taxonomy' => 'tipo',
                        'parent' => $parent_id,
                        'hide_empty' => false,
                    ]);
                    if (!empty($terms)) {
                        foreach ($terms as $term) {
                            $id_counter++;
                            $term_item = new stdClass();
                            $term_item->ID = $id_counter;
                            $term_item->title = $term->name;
                            $term_item->url = get_term_link($term);
                            $term_item->menu_item_parent = $menu_parent_id;
                            $term_item->classes = ['menu-item', 'menu-item-type-taxonomy', 'menu-item-object-tipo'];
                            $term_item->type = 'custom';
                            $term_item->object = 'tipo';
                            $term_item->object_id = $term->term_id;
                            $term_item->db_id = $term_item->ID;
                            $term_item->menu_order = 0;
                            // Propiedades estándar para evitar warnings
                            $term_item->current = false;
                            $term_item->current_item_ancestor = false;
                            $term_item->current_item_parent = false;
                            $term_item->type_label = '';
                            $term_item->target = '';
                            $term_item->attr_title = '';
                            $term_item->description = '';
                            $term_item->xfn = '';
                            $children[] = $term_item;

                            // Llamada recursiva para hijos (solo si no hemos llegado al nivel 3)
                            if ($nivel < 3) {
                                $subchildren = $add_terms_recursive($term->term_id, $term_item->ID, $nivel + 1);
                                if (!empty($subchildren)) {
                                    $children = array_merge($children, $subchildren);
                                }
                            }
                        }
                    }
                    return $children;
                };

                // Iniciar con términos raíz y nivel 1
                $new_items = $add_terms_recursive(0, $item->ID, 1);
                array_splice($items, $index + 1, 0, $new_items);
                break;
            }
        }
    }
    return $items;
}, 20, 2);

add_action('wp_footer', function() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a[href$=".pdf"]').forEach(function(link) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        });
    });
    </script>
    <?php
});

add_action('pre_get_posts', function($query) {
    if (
        !is_admin() &&
        $query->is_main_query() &&
        is_tax('tipo')
    ) {
        $query->set('posts_per_page', -1);
    }
});

add_filter( 'wp_get_attachment_url', 'sumun_version_pdf_xlsx_attachment_url', 10, 2 );
function sumun_version_pdf_xlsx_attachment_url( $url, $attachment_id ) {

    $file_path = get_attached_file( $attachment_id );
    if ( ! $file_path || ! file_exists( $file_path ) ) {
        return $url;
    }

    $extension = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
    $allowed_extensions = array( 'pdf', 'xlsx' );

    if ( ! in_array( $extension, $allowed_extensions, true ) ) {
        return $url;
    }

    $version = filemtime( $file_path );

    if ( ! $version ) {
        return $url;
    }

    return add_query_arg( 'ver', $version, $url );
}

/**
 * Reemplazar PDFs y XLSX manteniendo el mismo attachment en WordPress
 */

// add_filter( 'wp_handle_upload_prefilter', 'sumun_prepare_file_replacement' );
// add_action( 'add_attachment', 'sumun_finish_file_replacement' );

/**
 * Almacén temporal para saber qué archivo se está reemplazando.
 */
$GLOBALS['sumun_file_replacements'] = array();

/**
 * Paso 1:
 * Antes de subir el archivo, comprobamos si ya existe un attachment
 * con el mismo nombre exacto (solo PDF y XLSX).
 * Si existe, borramos el archivo físico antiguo para que WordPress no añada -1, -2, etc.
 * Pero NO borramos el attachment.
 */
function sumun_prepare_file_replacement( $file ) {

    $allowed_extensions = array( 'pdf', 'xlsx' );

    $pathinfo  = pathinfo( $file['name'] );
    $extension = isset( $pathinfo['extension'] ) ? strtolower( $pathinfo['extension'] ) : '';
    $basename  = isset( $pathinfo['basename'] ) ? $pathinfo['basename'] : '';

    if ( ! in_array( $extension, $allowed_extensions, true ) ) {
        return $file;
    }

    $attachments = get_posts( array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_wp_attached_file',
                'value'   => $basename,
                'compare' => 'LIKE',
            ),
        ),
    ) );

    foreach ( $attachments as $attachment_id ) {
        $attached_file = get_post_meta( $attachment_id, '_wp_attached_file', true );

        if ( $attached_file && basename( $attached_file ) === $basename ) {

            $absolute_path = get_attached_file( $attachment_id );

            // Guardamos la información para usarla al crear el nuevo attachment temporal
            $GLOBALS['sumun_file_replacements'][ $basename ] = array(
                'old_attachment_id' => $attachment_id,
                'old_relative_path' => $attached_file,
                'old_absolute_path' => $absolute_path,
            );

            // Borramos solo el archivo físico, NO el attachment
            if ( $absolute_path && file_exists( $absolute_path ) ) {
                @unlink( $absolute_path );
            }

            break;
        }
    }

    return $file;
}

/**
 * Paso 2:
 * WordPress crea un attachment nuevo al subir el archivo.
 * Si en realidad era una sustitución:
 * - actualizamos el attachment antiguo
 * - regeneramos metadatos si procede
 * - eliminamos SOLO el post del attachment nuevo
 * - mantenemos el archivo recién subido
 */
function sumun_finish_file_replacement( $new_attachment_id ) {

    $new_file = get_attached_file( $new_attachment_id );

    if ( ! $new_file ) {
        return;
    }

    $basename = basename( $new_file );

    if ( empty( $GLOBALS['sumun_file_replacements'][ $basename ] ) ) {
        return;
    }

    $replacement = $GLOBALS['sumun_file_replacements'][ $basename ];
    $old_attachment_id = (int) $replacement['old_attachment_id'];

    if ( ! $old_attachment_id || $old_attachment_id === $new_attachment_id ) {
        unset( $GLOBALS['sumun_file_replacements'][ $basename ] );
        return;
    }

    $upload_dir = wp_get_upload_dir();

    // Ruta relativa del archivo nuevo dentro de uploads
    $relative_path = str_replace(
        trailingslashit( $upload_dir['basedir'] ),
        '',
        $new_file
    );

    // Mime type real del archivo nuevo
    $filetype = wp_check_filetype( $new_file );

    // Actualizamos el attachment antiguo para que apunte al archivo nuevo
    update_attached_file( $old_attachment_id, $new_file );
    update_post_meta( $old_attachment_id, '_wp_attached_file', $relative_path );

    wp_update_post( array(
        'ID'             => $old_attachment_id,
        'post_mime_type' => $filetype['type'],
        'post_modified'  => current_time( 'mysql' ),
        'post_modified_gmt' => current_time( 'mysql', 1 ),
    ) );

    // Regenerar metadatos (útil especialmente para PDFs)
    $metadata = wp_generate_attachment_metadata( $old_attachment_id, $new_file );
    if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
        wp_update_attachment_metadata( $old_attachment_id, $metadata );
    }

    // Eliminar SOLO el post del nuevo attachment, sin borrar el archivo
    wp_delete_post( $new_attachment_id, true );

    // Limpiar la memoria temporal
    unset( $GLOBALS['sumun_file_replacements'][ $basename ] );
}