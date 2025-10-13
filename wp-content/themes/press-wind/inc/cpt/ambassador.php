<?php
class CPT_AMBASSADOR
{
  public static function init()
  {
    add_action('init', [__CLASS__, 'register_post_types']);
  }
   public static function register_post_types()
  {
    register_post_type('ambassador', [
      'labels' => [
        'name' => __('Ambassadeurs', 'fdhpa17'),
        'singular_name' => __('Ambassadeur', 'fdhpa17'),
      ],
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'editor', 'thumbnail'],
      'rewrite' => ['slug' => 'ambassador'],
      'show_in_rest' => true,
    ]);
  }
}

CPT_AMBASSADOR::init();