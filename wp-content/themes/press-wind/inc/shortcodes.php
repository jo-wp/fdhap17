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
