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

add_filter('rank_math/frontend/breadcrumb/items', function($crumbs) {

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
            (strpos($crumb[1], get_post_type_archive_link('solucion')) !== false)
        ) {
            // Replace with the CATALOGO_ID page
            $crumbs[$i][0] = get_the_title(CATALOGO_ID);
            $crumbs[$i][1] = get_permalink(CATALOGO_ID);
        }


    }

    return $crumbs;
});

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

