<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (have_rows('files')) {
    echo '<section class="descargables-section wrapper" id="descargas">';
        echo '<h2>Documentación y descargas</h2>';
        echo '<div class="is-layout-grid smn-default-grid smn-descargables-grid">';
        while (have_rows('files')) {
            the_row();
            $file_id = get_sub_field('file');
            if ($file_id) {
                $url = wp_get_attachment_url($file_id);
                $title = get_the_title($file_id) ? get_the_title($file_id) : basename($url);

                // Get file size
                $file_path = get_attached_file($file_id);
                $file_size = '';
                if ($file_path && file_exists($file_path)) {
                    $size_bytes = filesize($file_path);
                    if ($size_bytes >= 1048576) {
                        $file_size = round($size_bytes / 1048576, 2) . ' MB';
                    } elseif ($size_bytes >= 1024) {
                        $file_size = round($size_bytes / 1024, 0) . ' KB';
                    } else {
                        $file_size = $size_bytes . ' bytes';
                    }
                }

                echo '<div class="card descargable-card">';
                    echo '<img src="' . esc_url(get_stylesheet_directory_uri() . '/assets/icons/icono-descarga.svg') . '" alt="' . __( 'Icono descarga', 'sorribes' ) . '" width="48" height="48" class="descargable-icon" /><br>';
                    echo '<a class="stretched-link" href="' . esc_url($url) . '" target="_blank" rel="noopener">';
                        echo '<span class="descargable-title">' . esc_html($title) . '</span>';
                        if ($file_size) {
                            echo '<br><span class="file-size">' . esc_html($file_size) . '</span>';
                        }
                    echo '</a>';
                echo '</div>';
            }
        }
        echo '</div>';
    echo '</section>';
}