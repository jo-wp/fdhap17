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

function camping_title_shortcode( $atts ) {
    // Récupère le post courant
    global $post;

    if ( ! $post ) {
        return '';
    }

    // Optionnel : on vérifie qu'on est bien sur un CPT "camping"
    if ( get_post_type( $post ) !== 'camping' ) {
        return '';
    }

    // Renvoie le title du camping
    return get_the_title( $post );
}
add_shortcode( 'camping_title', 'camping_title_shortcode' );