<?php
/**
 * Helper: retourne l'URL (et éventuellement width/height) d'une image fallback pour un camping.
 * - Cherche d'abord dans la galerie ACF 'galerie_photo_camping' (première image).
 * - Sinon retourne l'image par défaut du thème.
 *
 * @return array{url:string,width:int|null,height:int|null}
 */
function rp_get_camping_fallback_image( $post_id, $size = 'thumbnail' ) {
  $default_url = get_stylesheet_directory_uri() . '/assets/media/image-camping-sans-photo.png';

  // Récupère la galerie (ACF) — peut retourner des arrays d'images ou des IDs selon le "Return Format".
  $galerie = get_field('galerie_photo_camping', $post_id);

  if (is_array($galerie) && !empty($galerie)) {
    $first = $galerie[0];

    // Cas 1 : la galerie renvoie un tableau d'images (array)
    if (is_array($first)) {
      // Priorité à la taille demandée si dispo
      if (isset($first['sizes'][$size])) {
        // Pas de width/height faciles ici, on retourne juste l'URL
        return ['url' => $first['sizes'][$size], 'width' => null, 'height' => null];
      }
      if (!empty($first['url'])) {
        return ['url' => $first['url'], 'width' => null, 'height' => null];
      }
    }

    // Cas 2 : la galerie renvoie des IDs
    if (is_numeric($first)) {
      $img = wp_get_attachment_image_src((int)$first, $size);
      if ($img && is_array($img)) {
        return ['url' => $img[0], 'width' => (int)$img[1], 'height' => (int)$img[2]];
      }
    }
  }

  // Fallback final : image par défaut du thème
  return ['url' => $default_url, 'width' => null, 'height' => null];
}

/**
 * 1) Couvrir get_the_post_thumbnail_url()
 */
add_filter('get_the_post_thumbnail_url', function ($url, $post_id, $size) {

  if (get_post_type($post_id) !== 'camping') {
    return $url;
  }

  $debug = [
      'url' => $url,
      'post_id' => $post_id,
      'size' => $size,
    ];
    echo '<script>console.log(' . json_encode($debug) . ');</script>';

  if (empty($url)) {
    $fallback = rp_get_camping_fallback_image($post_id, $size);
    if (!empty($fallback['url'])) {
      $url = $fallback['url'];
    }
  }

  // Dernier recours : image par défaut (au cas où)
  if (empty($url)) {
    $url = get_stylesheet_directory_uri() . '/assets/media/image-camping-sans-photo.png';
  }

  return $url;
}, 999, 3);

/**
 * 2) Couvrir the_post_thumbnail()/get_the_post_thumbnail() via post_thumbnail_html
 *    -> Injecte un <img> si aucune miniature n'est définie.
 */
add_filter('post_thumbnail_html', function ($html, $post_id, $thumbnail_id, $size, $attr) {
  if (get_post_type($post_id) !== 'camping') {
    return $html;
  }

  // Si une miniature existe déjà, on ne touche pas.
  if (!empty($html)) {
    return $html;
  }

  // Sinon on fabrique un <img> à partir de la galerie ACF ou de l'image par défaut.
  $fallback = rp_get_camping_fallback_image($post_id, $size);
  if (empty($fallback['url'])) {
    return $html; // rien à faire
  }

  // Classes/alt éventuels passés par $attr
  $classes = isset($attr['class']) ? ' class="' . esc_attr($attr['class']) . '"' : '';
  $alt     = isset($attr['alt'])   ? esc_attr($attr['alt']) : esc_attr(get_the_title($post_id));

  // width/height si on les a (utile pour CLS)
  $wh = '';
  if (!empty($fallback['width']) && !empty($fallback['height'])) {
    $wh = ' width="' . (int)$fallback['width'] . '" height="' . (int)$fallback['height'] . '"';
  }

  $html = '<img src="' . esc_url($fallback['url']) . '" alt="' . $alt . '"' . $classes . $wh . ' />';

  return $html;
}, 999, 5);
