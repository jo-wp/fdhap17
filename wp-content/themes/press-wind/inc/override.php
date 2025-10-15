<?php
add_filter('post_thumbnail_url', function ($url, $post_id, $size) {
  $post_type = get_post_type($post_id);

  if ($post_type === 'camping') {
    $galerie_photo_camping = get_field('galerie_photo_camping', $post_id);

    // Si aucune image à la une n'est définie mais qu'une galerie existe
    if (empty($url) && !empty($galerie_photo_camping) && is_array($galerie_photo_camping)) {
      // On récupère la première image de la galerie
      $first_image = $galerie_photo_camping[0];
      if (isset($first_image['sizes'][$size])) {
        $url = $first_image['sizes'][$size];
      } elseif (isset($first_image['url'])) {
        $url = $first_image['url'];
      }
    }

    // Si toujours pas d'image, on met une image par défaut
    if (empty($url)) {
      $url = get_stylesheet_directory_uri() . '/assets/media/image-camping-sans-photo.png';
    }
  }

  return $url;
}, 999, 3);
