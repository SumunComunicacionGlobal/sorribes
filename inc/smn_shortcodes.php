<?php 
// Shortcodes 
function smn_current_year_shortcode() {
    return date('Y');
}
add_shortcode('year', 'smn_current_year_shortcode');

// Shortcode: [casos_entradas]
function smn_casos_entradas_shortcode() {
    $args = array(
        'post_type' => array('post', 'caso-de-exito'),
        'posts_per_page' => 4,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );
    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '<p>No hay entradas recientes.</p>';
    }
    $output = '<div class="casos-entradas-list">';
    while ($query->have_posts()) {
        $query->the_post();
        $post_type = get_post_type();
        $post_type_obj = get_post_type_object($post_type);
        $label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;
        $date = get_the_date();
        $title = get_the_title();
        $permalink = get_permalink();
        $output .= '<div class="casos-entradas-item">'
            . '<p class="casos-entradas-date has-caption-font-size">' . esc_html($date) . '</p>'
            . '<p class="casos-entradas-label has-small-font-size">' . esc_html($label) . '</p>'
            . '<h3 class="has-heading-4-font-size casos-entradas-title"><a class="stretched-link" href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>'
            . '</div>';
    }
    wp_reset_postdata();
    $output .= '</div>';

    // Solución definitiva: devolver el HTML tal cual
    return $output;
}
add_shortcode('casos_entradas', 'smn_casos_entradas_shortcode');

// Shortcode: [soluciones]
function smn_soluciones_shortcode() {
    $terms = get_terms([
        'taxonomy' => 'tipo',
        'parent' => 0,
        'hide_empty' => false,
    ]);
    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    add_action(
        'wp_enqueue_scripts',
        function () {
            wp_enqueue_style( 'wp-block-list' );
        }
    );

    $output = '<div class="smn-soluciones-list">';
    foreach ($terms as $term) {
        // Imagen del término (thumbnail_id)
        // $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        $thumb_id = smn_get_term_thumbnail_id($term);
        $img_html = '';
        if ($thumb_id) {
            $img_url = wp_get_attachment_image_url($thumb_id, 'medium');
            if ($img_url) {
                $term_link = get_term_link($term);
                $img_html .= '<div class="wp-block-image smn-soluciones-img-wrap">';
                if (!is_wp_error($term_link)) {
                    $img_html .= '<a href="' . esc_url($term_link) . '" class="smn-soluciones-img-link">';
                }
                $img_html .= '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($term->name) . '" class="smn-soluciones-img" />';
                if (!is_wp_error($term_link)) {
                    $img_html .= '</a>';
                }
                $img_html .= '</div>';
            }
        }
        // Nombre del término con enlace
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            $name_html = '<h3 class="smn-soluciones-nombre"><a href="' . esc_url($term_link) . '" class="smn-soluciones-nombre-link">' . esc_html($term->name) . '</a></h3>';
        } else {
            $name_html = '<h3 class="smn-soluciones-nombre">' . esc_html($term->name) . '</h3>';
        }

        $description_home = get_field('term_description_home', 'tipo_' . $term->term_id);
        if ( !$description_home ) {
            $description_home = term_description($term);
        }
        if ( $description_home ) {
            $name_html .= '<div class="smn-soluciones-descripcion has-small-font-size">' . wp_kses_post(wpautop($description_home)) . '</div>';
        }  
        // Términos hijos con enlaces
        $children = get_terms([
            'taxonomy' => 'tipo',
            'parent' => $term->term_id,
            'hide_empty' => false,
        ]);
        $children_html = '';
        if (!empty($children) && !is_wp_error($children)) {
            $children_html .= '<ul class="smn-soluciones-hijos wp-block-list is-style-arrow-list">';
            foreach ($children as $child) {
                $child_link = get_term_link($child);
                if (!is_wp_error($child_link)) {
                    $children_html .= '<li><a href="' . esc_url($child_link) . '" class="smn-soluciones-hijo-link">' . esc_html($child->name) . '</a></li>';
                } else {
                    $children_html .= '<li>' . esc_html($child->name) . '</li>';
                }
            }
            $children_html .= '</ul>';
        }
        // Título "Tipos" traducible
        $tipos_title = '<div class="has-caption-font-size smn-soluciones-tipos-title">' . esc_html__('Tipos', 'sorribes') . '</div>';
        // Composición de columnas usando wp-block-columns
        $output .= '<div class="smn-soluciones-row wp-block-columns is-layout-flex">'
            . '<div class="smn-soluciones-col smn-soluciones-col-img wp-block-column">' . $img_html . '</div>'
            . '<div class="smn-soluciones-col smn-soluciones-col-nombre wp-block-column">' . $name_html . '</div>'
            . '<div class="smn-soluciones-col smn-soluciones-col-tipos wp-block-column">' . $tipos_title . $children_html . '</div>'
            . '</div>';
    }
    $output .= '</div>';
    return $output;
}
add_shortcode('soluciones', 'smn_soluciones_shortcode');

// Shortcode: [terms taxonomy="TAXONOMY"]
function smn_terms_shortcode($atts) {
    $atts = shortcode_atts([
        'taxonomy' => '',
        'parent' => 0,
        'title' => 'auto',
    ], $atts, 'terms');

    $taxonomy = $atts['taxonomy'];
    $parent = intval($atts['parent']);
    $title = $atts['title'];

    if ( is_tax() ) {
        $queried_object = get_queried_object();
        $taxonomy = $queried_object->taxonomy;
        if ( $queried_object && isset( $queried_object->term_id ) ) {
            $parent = $queried_object->term_id;
        }
    }

    if (empty($taxonomy) || !taxonomy_exists($taxonomy)) {
        return '';
    }


    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'parent' => $parent,
    ]);
    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    $output = '';

    if ( is_tax() && $title == 'auto' ) {
        $title = sprintf( esc_html__( 'Tipos de %s', 'sorribes' ), '<mark class="has-inline-color has-primary-30-color">' . strtolower( $queried_object->name ) ) . '</mark>';
    } elseif ( $title == 'auto' ) {
        $title = '';
    }

    if ( $title ) {
        $output .= '<div class="wp-block-group">';
            $output .= '<h2 class="terms-shortcode-header">' . $title . '</h2>';
        $output .= '</div>';
    }


    $output .= '<div class="is-layout-grid smn-default-grid smn-terms-grid">';
    foreach ($terms as $term) {
        $term_link = get_term_link($term);
        // $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        $thumb_id = smn_get_term_thumbnail_id($term);
        $img_html = '';
        if ($thumb_id) {
            $img_url = wp_get_attachment_image_url($thumb_id, 'medium');
            if ($img_url) {
                $img_html = '<div class="smn-terms-img-wrap"><img src="' . esc_url($img_url) . '" alt="' . esc_attr($term->name) . '" class="smn-terms-img" /></div>';
            }
        }
        $output .= '<div class="wp-block-cover smn-terms-card">';
        if ($thumb_id) {
            // Extract image URL for background
            $output .= wp_get_attachment_image($thumb_id, 'medium_large', false, ['class' => 'wp-block-cover__image-background']);
        }
        $output .= '<span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span>';
        $output .= '<div class="wp-block-cover__inner-container">';
        $output .= '<h3 class="smn-terms-title"><a href="' . esc_url($term_link) . '" class="stretched-link">' . esc_html($term->name) . '</a></h3>';

        $description = get_field('term_description_pt_archive', $taxonomy . '_' . $term->term_id);
        if ( $description ) {
            $output .= '<div class="smn-terms-description has-small-font-size">' . wp_kses_post(wpautop($description)) . '</div>';
        }

        $output .= '</div>';
        $output .= '</div>';
    }
    $output .= '</div>';

    if ( is_tax() ) {
        ob_start();
        block_template_part( 'area-pedir-presupuesto' );
        $output .= ob_get_clean();
    }

    return $output;
}
add_shortcode('terms', 'smn_terms_shortcode');

/**
 * Shortcode: [tipo_menu]
 * Generates a hierarchical menu based on the "tipo" taxonomy.
 */
function smn_tipo_menu_shortcode() {
    $terms = get_terms([
        'taxonomy' => 'tipo',
        'parent' => 0,
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]);
    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    // Recursive function to build menu
    function smn_tipo_menu_list($parent_id = 0) {
        $children = get_terms([
            'taxonomy' => 'tipo',
            'parent' => $parent_id,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
        if (empty($children) || is_wp_error($children)) {
            return '';
        }
        $level = 1;
        $html = '<ul class="sfm-menu sfm-menu-level-'. $level .' smn-tipo-menu">';
        foreach ($children as $term) {
            $term_link = get_term_link($term);
            $html .= '<li class="sfm-menu-item-'. $term->term_id .' smn-tipo-menu-item">';
            if (!is_wp_error($term_link)) {
                $html .= '<a href="' . esc_url($term_link) . '" class="smn-tipo-menu-link">' . esc_html($term->name) . '</a>';
            } else {
                $html .= esc_html($term->name);
            }
            // Recursively add children
            $html .= smn_tipo_menu_list($term->term_id);
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    // Top-level menu
    $output = '<nav class="smn-tipo-menu-nav" aria-label="' . esc_attr__('Menú de Tipos', 'sorribes') . '">';
    $output .= smn_tipo_menu_list(0);
    $output .= '</nav>';

    return $output;
}
add_shortcode('tipo_menu', 'smn_tipo_menu_shortcode');

add_shortcode( 'galeria', 'smn_galeria_shortcode' );
function smn_galeria_shortcode() {
    ob_start();
    get_template_part( 'parts/galeria' );
    return ob_get_clean();
}

/**
 * Shortcode: [menu_featured]
 * Displays the menu assigned to the 'featured-menu' location.
 */
function smn_menu_featured_shortcode() {
    $menu = wp_nav_menu([
        'theme_location' => 'featured-menu',
        'container' => 'nav',
        'container_class' => 'smn-featured-menu-nav',
        'menu_class' => 'smn-featured-menu',
        'echo' => false,
    ]);
    if (empty($menu)) {
        return '';
    }
    return $menu;
}
add_shortcode('menu_featured', 'smn_menu_featured_shortcode');