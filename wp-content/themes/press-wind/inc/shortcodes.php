<?php

function shortcode_featured_campings()
{
  echo 'test';
  // if (is_tax() || is_category() || is_tag()) {
  //   $term = get_queried_object();
  //   if (!empty($term) && isset($term->term_id)) {
  //     return $term->term_id;
  //   }
  // }
  // return '';
}
add_shortcode('featured_campings', 'shortcode_featured_campings');

add_filter('ninja_forms_render_default_value', function ($default, $field) {
    // Vérifie si on est sur une page avec un post
    if (!is_singular()) {
        return $default;
    }

    // Clé de champ Ninja Forms
    if ($field['key'] === 'hidden_1') {
        return get_the_title();
    }

    return $default;
}, 10, 2);

add_filter('ninja_forms_merge_tag_data', function ($tags) {
    if (is_singular()) {
        $tags['post:title'] = get_the_title();
    }
    return $tags;
});