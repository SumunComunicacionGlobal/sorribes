<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$gallery_ids = false;
$wrap = false;

if ( is_singular() ) {
    $wrap = true;
    $gallery_ids = get_field('product_image_gallery');
} elseif ( is_tax( 'tipo' ) ) {
    
    $args = [
        'post_type' => 'solucion',
        'posts_per_page' => 12,
        'orderby' => 'rand',
        'tax_query' => [
            [
                'taxonomy' => 'tipo',
                'field'    => 'term_id',
                'terms'    => get_queried_object_id(),
            ],
        ],
        'meta_query' => [
            [
                'key' => 'product_image_gallery',
                'compare' => 'EXISTS',
            ],
        ],
        'fields' => 'all',
    ];

    $posts = get_posts($args);
    $gallery_ids = [];
    $gallery_images = [];

    foreach ($posts as $post) {
        $ids = get_field('product_image_gallery', $post->ID );

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $gallery_ids[] = $id;
                $gallery_images[] = [
                    'title' => get_the_title($post->ID),
                    'id' => $id
                ];
            }
        }

    }

    $gallery_ids = array_unique($gallery_ids);
    shuffle($gallery_ids);
    $gallery_ids = array_slice($gallery_ids, 0, 12);

    // Optionally, filter $gallery_images to only include those in $gallery_ids
    $gallery_images = array_filter($gallery_images, function($img) use ($gallery_ids) {
        return in_array($img['id'], $gallery_ids);
    });

}

if ( !$gallery_ids ) return false;

$count = count($gallery_ids);

if ( $count == 1 ) {
    $post_thumbnail_id = get_post_thumbnail_id();
    if ( $post_thumbnail_id && ! is_wp_error( $post_thumbnail_id ) ) {
        if ( in_array( $post_thumbnail_id, $gallery_ids ) ) {
            // Remove thumbnail ID from gallery IDs to avoid duplication
            $gallery_ids = array_diff( $gallery_ids, [ $post_thumbnail_id ] );
        }
    }
}

if (is_array($gallery_ids) && !empty($gallery_ids)) {

    $columns = $count;
    if ($count > 6) {
        $columns = 6;
    } elseif ($count < 3) {
        $columns = 3;
    }

    $html = '';
    $r = '';

    // Prepare image data for Gutenberg gallery block
    $images = [];

    if ( $wrap ) {

        $html .= '<!-- wp:group {"align":"full","backgroundColor":"neutral-white","layout":{"type":"constrained"}} -->';
        $html .= '<div class="wp-block-group alignfull has-neutral-white-background-color has-background">';

        $html .= '<!-- wp:group {"layout":{"type":"constrained"}} -->';
        $html .= '<div class="wp-block-group">';
        $html .= '<!-- wp:paragraph {"className":"is-style-pill"} -->';
        $html .= '<p class="is-style-pill">' . __( 'Galería de imágenes', 'sorribes' ) . '</p>';
        $html .= '<!-- /wp:paragraph -->';
        $html .= '</div>';
        $html .= '<!-- /wp:group -->';

        $html .= '<!-- wp:heading -->';
        $html .= '<h2 class="wp-block-heading">' . sprintf( esc_html__( 'Fotos de %s', 'sorribes' ), get_the_title() ) . '</h2>';
        $html .= '<!-- /wp:heading -->';

    }

    $html .= '<!-- wp:gallery {"columns":'. $columns .',"imageCrop":false,"linkTo":"lightbox","sizeSlug":"large"} -->';
    $html .= '<figure class="wp-block-gallery has-nested-images columns-'. $columns .'">';

    foreach ($gallery_images as $image) {
        $url = wp_get_attachment_image_url($image['id'], 'large');
        $thumb = wp_get_attachment_image_url($image['id'], 'thumbnail');
        $alt = get_post_meta($image['id'], '_wp_attachment_image_alt', true);
        $images[] = [
            'id' => $image['id'],
            'url' => $url,
            'thumb' => $thumb,
            'alt' => $alt,
            'title' => $image['title']
        ];
    }

    foreach ($images as $img) {
        $html .= '<!-- wp:image {"lightbox":{"enabled":true},"id":' . $img['id'] . ',"sizeSlug":"large","linkDestination":"none"} -->';
        $html .= '<figure class="wp-block-image size-large">';
        $html .= wp_get_attachment_image($img['id'], 'large', false, ['alt' => $img['alt']]);
        if ( is_tax( 'tipo' ) ) {
            $html .= '<figcaption class="wp-element-caption">' . $img['title'] . '</figcaption>';
        }
        $html .= '</figure>';
        $html .= '<!-- /wp:image -->';
    }

    $html .= '</figure>';
    $html .= '<!-- /wp:gallery -->';

    if ( $wrap ) {
        $html .= '</div>';
        $html .= '<!-- /wp:group -->';
    }

    $blocks = parse_blocks($html);
    foreach ($blocks as $block) {
        $r .= render_block($block);
    }

    echo $r;

}