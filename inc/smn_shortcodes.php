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
        $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
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