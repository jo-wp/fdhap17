<?php

namespace PressWindStarter;

// not defined => development
if (!defined('WP_ENV')) {
	define('WP_ENV', 'development');
}


// gutenberg setup, change like you want
require_once dirname(__FILE__) . '/inc/gutenberg.php';
// auto loading acf blocks
require_once dirname(__FILE__) . '/inc/acf_blocks.php';
// auto loading login assets
require_once dirname(__FILE__) . '/inc/login_assets.php';
// Custom Post Type setup
require_once dirname(__FILE__) . '/inc/cpt.php';
// Shortcodes Type setup
require_once dirname(__FILE__) . '/inc/shortcodes.php';
// YoastSEO Vars setup
require_once dirname(__FILE__) . '/inc/vars_yoast.php';

// pwa icons
if (file_exists(dirname(__FILE__) . '/inc/pwa_head.php')) {
	include dirname(__FILE__) . '/inc/pwa_head.php';
}

/**
 * Theme setup.
 */
function setup()
{
	add_theme_support('automatic-feed-links');

	add_theme_support('title-tag');

	add_theme_support('post-thumbnails');

	// load i18n text
	load_theme_textdomain('press-wind-theme', get_template_directory() . '/languages');
}

add_action('after_setup_theme', __NAMESPACE__ . '\setup');

/**
 * init assets front
 * require presswind plugin to work
 */
if (class_exists('PressWind\PWVite')) {

	\PressWind\PWVite::init(port: 3000, path: '');
	/**
	 * init assets admin
	 */
	\PressWind\PWVite::init(
		port: 4444,
		path: '/admin',
		position: 'editor',
		is_ts: false
	);
}


/**
 * Api import pages APIDAE && Ctoutvert
 */
require_once dirname(__FILE__) . '/api/apidae/apidae.php';
require_once dirname(__FILE__) . '/api/ctoutvert/ctoutvert.php';

/**Import featherlight */


add_action('wp_enqueue_scripts', function () {
	// CSS local
	wp_enqueue_style(
		'featherlight-css',
		get_template_directory_uri() . '/assets/css/library/featherlight.css',
	);
	// https://unpkg.com/leaflet@1.9.4/dist/leaflet.css
	wp_enqueue_style(
		'leaflet-css',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
	);


	// JS local
	wp_enqueue_script(
		'featherlight-js',
		get_template_directory_uri() . '/assets/js/library/featherlight.js',
		array('jquery'),
	);
	//https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
	wp_enqueue_script(
		'leaflet-js',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
		// array('jquery'),
	);

});

/**
 * Allow SVG uploads
 */
//ALLOW SVG
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
	$filetype = wp_check_filetype($filename, $mimes);
	return [
		'ext' => $filetype['ext'],
		'type' => $filetype['type'],
		'proper_filename' => $data['proper_filename']
	];
}, 10, 4);

function cc_mime_types($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', __NAMESPACE__ . '\cc_mime_types');

function fix_svg()
{
	echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action('admin_head', __NAMESPACE__ . '\fix_svg');

/**
 * Term to page mapping
 */
require_once dirname(__FILE__) . '/config/term_to_page.php';


/**
 * Menus
 */

// Activer le support des menus
add_theme_support('menus');

// Déclarer tes emplacements de menu
register_nav_menus([
	'minisite-primary' => __('Mini site : menu principal', 'press-wind'),
	'minisite-preheader' => __('Mini site : menu secondaire', 'press-wind'),
]);

// Custom template popup maker
add_filter('template_include', function($template) {
    if (is_singular('popup')) {
        $custom = get_stylesheet_directory() . '/templates/popup.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 1000);

add_action('pre_get_posts', function( $q ){
  if ( is_admin() || ! $q->is_main_query() ) return;
  if ( $q->is_author() ) {
    $q->set('posts_per_page', 9);
    $q->set('orderby', 'date');
    $q->set('order', 'DESC');
  }
});

add_filter('wpseo_canonical', function( $canonical ){
  if ( is_author() && is_paged() ) {
    $author = get_queried_object();
    if ( $author && isset($author->ID) ) {
      return get_author_posts_url( $author->ID );
    }
  }
  return $canonical;
});



add_filter( 'facetwp_query_args', function( $args, $class ) {
    if ( isset( $class->ajax_params['extras']['destination_term_id'] ) ) {
        $term_id = (int) $class->ajax_params['extras']['destination_term_id'];
        if ( $term_id > 0 ) {
            $args['tax_query'][] = [
                'taxonomy' => 'destination',
                'field'    => 'term_id',
                'terms'    => [ $term_id ],
                'include_children' => false,
            ];
        }
    }
    return $args;
}, 10, 2 );

	add_action( 'wp_footer', function() {
  if ( is_tax() ) : ?>
    <script>
      document.addEventListener('facetwp-refresh', function() {
        FWP.extras.destination_term_id = <?php echo (int)get_queried_object_id() ?>;
      });
    </script>
  <?php endif;
}, 100 );