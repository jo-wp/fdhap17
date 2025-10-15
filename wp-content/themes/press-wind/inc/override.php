<?php 

add_filter('post_thumbnail_url', function($url, $post_id, $size) {

    // Vérifie si on est sur un CPT ou une catégorie "camping"
    $post_type = get_post_type($post_id);

    // Ici, adapte selon ta logique : si ton CPT s'appelle "camping"
    if ($post_type === 'camping') {

        // Si pas d'image à la une, on renvoie celle du thème
        if (empty($url)) {
            $url = get_stylesheet_directory_uri() . '/assets/media/image-camping-sans-photo.png';
        }
    }

    return $url;
}, 10, 3);
