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