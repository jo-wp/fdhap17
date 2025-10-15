<?php
add_filter('post_thumbnail_url', function ($url, $post_id, $size) {
  $post_type = get_post_type($post_id);
  if ($post_type === 'camping') {
    if (empty($url)) {
      $url = get_stylesheet_directory_uri() . '/assets/media/image-camping-sans-photo.png';
    }
  }
  return $url;
}, 999, 3);
