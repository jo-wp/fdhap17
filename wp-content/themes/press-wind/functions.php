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
// Facetwp setup
require_once dirname(__FILE__) . '/inc/facetwp.php';
// Menu setup
require_once dirname(__FILE__) . '/inc/menu.php';
// Lead Form
require_once dirname(__FILE__) . '/inc/lead_form.php';

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

  // https://unpkg.com/leaflet@1.9.4/dist/leaflet.css
  wp_enqueue_style(
    'leaflet-css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
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
add_filter('template_include', function ($template) {
  if (is_singular('popup')) {
    $custom = get_stylesheet_directory() . '/templates/popup.php';
    if (file_exists($custom)) {
      return $custom;
    }
  }
  return $template;
}, 1000);

add_action('pre_get_posts', function ($q) {
  if (is_admin() || !$q->is_main_query())
    return;
  if ($q->is_author()) {
    $q->set('posts_per_page', 9);
    $q->set('orderby', 'date');
    $q->set('order', 'DESC');
  }
});

add_filter('wpseo_canonical', function ($canonical) {
  if (is_author() && is_paged()) {
    $author = get_queried_object();
    if ($author && isset($author->ID)) {
      return get_author_posts_url($author->ID);
    }
  }
  return $canonical;
});




add_action('wp_enqueue_scripts', function () {
  // Uniquement sur les single du CPT "camping"
  if (!is_singular('camping')) {
    return;
  }

  // jQuery de WP
  wp_enqueue_script('jquery');

  // Helpers version = filemtime pour bust de cache (si le fichier existe)
  $theme_uri = get_template_directory_uri();
  $theme_path = get_template_directory();

  $ver_core_js = file_exists("$theme_path/assets/js/library/featherlight.js") ? filemtime("$theme_path/assets/js/library/featherlight.js") : null;
  $ver_gallery_js = file_exists("$theme_path/assets/js/library/featherlight.gallery.js") ? filemtime("$theme_path/assets/js/library/featherlight.gallery.js") : null;
  $ver_core_css = file_exists("$theme_path/assets/css/library/featherlight.css") ? filemtime("$theme_path/assets/css/library/featherlight.css") : null;
  $ver_gallery_css = file_exists("$theme_path/assets/css/library/featherlight.gallery.css") ? filemtime("$theme_path/assets/css/library/featherlight.gallery.css") : null;

  // CSS (handles distincts)
  wp_enqueue_style(
    'featherlight-core-css',
    $theme_uri . '/assets/css/library/featherlight.css',
    [],
    $ver_core_css
  );
  wp_enqueue_style(
    'featherlight-gallery-css',
    $theme_uri . '/assets/css/library/featherlight.gallery.css',
    ['featherlight-core-css'],
    $ver_gallery_css
  );

  // JS (handles distincts, ordre correct, en footer)
  wp_enqueue_script(
    'featherlight-core-js',
    $theme_uri . '/assets/js/library/featherlight.js',
    ['jquery'],
    $ver_core_js,
    true
  );
  wp_enqueue_script(
    'featherlight-gallery-js',
    $theme_uri . '/assets/js/library/featherlight.galery.js',
    ['jquery', 'featherlight-core-js'],
    $ver_gallery_js,
    true
  );

  // Ton init, injecté APRÈS featherlight-gallery-js pour garantir $.fn.featherlightGallery
  $inline = <<<JS
jQuery(function($){
  if (typeof $.fn.featherlightGallery !== 'function') {
    console.error('Featherlight Gallery non chargé : vérifie featherlight.gallery.js + l\'ordre des dépendances.');
    return;
  }

  var \$items = $('#gallery img.fl-item');

  if (\$items.length) {
    \$items.featherlightGallery({
      previousIcon: '‹',
      nextIcon: '›',
      galleryFadeIn: 120,
      galleryFadeOut: 120,
      openSpeed: 160,
      closeSpeed: 160
    });
  }

  $('#open-all').on('click', function(e){
    e.preventDefault();
		console.log(\$items.length)
    if (\$items.length) {
      \$items.first().trigger('click');
    }
  });
});
JS;

  wp_add_inline_script('featherlight-gallery-js', $inline, 'after');

  // (Optionnel) Leaflet, si tu en as besoin sur cette page
  wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
  wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
});




add_action('wp_enqueue_scripts', function () {
  // Uniquement sur les single du CPT "camping"
  if (!is_front_page()) {
    return;
  }

  // jQuery de WP
  wp_enqueue_script('jquery');

  // Helpers version = filemtime pour bust de cache (si le fichier existe)
  $theme_uri = get_template_directory_uri();
  $theme_path = get_template_directory();

  $ver_core_js = file_exists("$theme_path/assets/js/library/featherlight.js") ? filemtime("$theme_path/assets/js/library/featherlight.js") : null;
  $ver_core_css = file_exists("$theme_path/assets/css/library/featherlight.css") ? filemtime("$theme_path/assets/css/library/featherlight.css") : null;

  // CSS (handles distincts)
  wp_enqueue_style(
    'featherlight-core-css',
    $theme_uri . '/assets/css/library/featherlight.css',
    [],
    $ver_core_css
  );

  // JS (handles distincts, ordre correct, en footer)
  wp_enqueue_script(
    'featherlight-core-js',
    $theme_uri . '/assets/js/library/featherlight.js',
    ['jquery'],
    $ver_core_js,
    true
  );

  // Ton init, injecté APRÈS featherlight-gallery-js pour garantir $.fn.featherlightGallery
  $inline = <<<JS
jQuery(function($){
  if (typeof $.fn.featherlightGallery !== 'function') {
    console.error('Featherlight Gallery non chargé : vérifie featherlight.gallery.js + l\'ordre des dépendances.');
    return;
  }
   const el = document.getElementById('block-render-campings-map')

   console.log('here')
    if (!el) return

    map = L.map(el, { scrollWheelZoom: false })
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 12,
      attribution: '&copy; OpenStreetMap',
    }).addTo(map)

    markersLayer = L.layerGroup().addTo(map)
    map.setView([46.1603, -1.1511], 9)
 
});
JS;

  wp_add_inline_script('featherlight-gallery-js', $inline, 'after');

  // (Optionnel) Leaflet, si tu en as besoin sur cette page
  wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
  wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
});



add_action('wp_enqueue_scripts', function () {
  if (defined('WP_ENV') && WP_ENV != 'development') {
    wp_enqueue_script(
      'cmp-script',
      get_template_directory_uri() . '/assets/js/cmp/cmp.js',
      array('jquery'), // <--- dépendance jQuery
      null,
      true // dans le footer
    );
  }
});


function fdhpa17_first_term_name($post_id, array $tax_candidates)
{
  foreach ($tax_candidates as $tax) {
    $terms = get_the_terms($post_id, $tax);
    if ($terms && !is_wp_error($terms) && !empty($terms)) {
      $term = reset($terms);
      $name = $term->name;

      // Limite à 25 caractères max
      if (mb_strlen($name) > 15) {
        $name = mb_substr($name, 0, 15) . '…';
      }

      return $name;
    }
  }
  return '';
}


add_action('wp_head', function () {
  if (is_admin() || is_feed() || is_robots())
    return;

  // --- Récup dynamiques + fallbacks agence ---
  $name = get_bloginfo('name') ?: 'FDHPA17'; // fallback agence
  $url = home_url('/') ?: 'https://www.fdhpa17.com/'; // fallback agence

  // Logo : d’abord le "logo du site" WordPress, sinon fallback agence
  $logo_url = '';
  $custom_logo_id = (int) get_theme_mod('custom_logo');
  if ($custom_logo_id) {
    // ok si ton env permet cette fonction ; sinon remplace par une URL fixe
    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
  }
  if (!$logo_url) {
    $logo_url = get_field('logo_header_hero', 'option');
  }

  // Réseaux : si tu as des champs ACF Options, remplace ici :
  $sameAs = array_values(array_filter([
    function_exists('get_field') ? get_field('social_facebook', 'option') : null,
    function_exists('get_field') ? get_field('social_instagram', 'option') : null,
    // ajoute d’autres réseaux si tu veux (YouTube, TikTok…)
  ]));

  // Structure de base (issue du fichier agence)
  $data = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $name,
    'url' => $url,
    'logo' => $logo_url,
  ];
  if (!empty($sameAs)) {
    $data['sameAs'] = $sameAs;
  } else {
    // Fallback strict agence si pas de données dynamiques
    $data['sameAs'] = [
      'https://www.facebook.com/campings17',
      'https://www.instagram.com/campings17/',
    ];
  }

  // Hook pour surcharger facilement (utile en staging/prod)
  $data = apply_filters('fdhpa_org_schema', $data);

  echo '<script type="application/ld+json">' .
    wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
    '</script>' . "\n";
}, 6);



add_action('wp_head', function () {
  if (is_admin())
    return;

  // Page : /nous-contacter/
  if (is_page('nous-contacter')) {
    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'ContactPage',
      'name' => 'Contact FDHPA17',
      'description' => 'Prenez contact avec la Fédération Départementale de l’Hôtellerie de Plein Air de Charente-Maritime.',
      'url' => 'https://www.fdhpa17.com/nous-contacter/',
    ];

    echo '<script type="application/ld+json">'
      . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      . '</script>' . "\n";
  }

  // Page : /fdhpa-17/qui-sommes-nous/
  if (is_page('qui-sommes-nous') && strpos($_SERVER['REQUEST_URI'], '/fdhpa-17/') !== false) {
    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'AboutPage',
      'name' => 'À propos de la FDHPA17',
      'description' => 'La FDHPA17 accompagne les campings de Charente-Maritime dans leur développement et leur promotion touristique.',
      'url' => 'https://www.fdhpa17.com/fdhpa-17/qui-sommes-nous/',
    ];

    echo '<script type="application/ld+json">'
      . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      . '</script>' . "\n";
  }

}, 20);


/**
 * JSON-LD LodgingBusiness pour les single de CPT "camping"
 */
add_action('wp_head', function () {
  if (is_admin() || !is_singular('camping'))
    return;

  $post_id = get_queried_object_id();

  // Utilitaires
  $meta = function ($key, $default = '') use ($post_id) {
    $v = get_post_meta($post_id, $key, true);
    return $v !== '' ? $v : $default;
  };
  $float = function ($v) {
    return is_numeric($v) ? (float) $v : null;
  };
  $clean = function ($value) use (&$clean) {
    if (is_array($value)) {
      $value = array_filter(array_map($clean, $value), function ($v) {
        return $v !== null && $v !== '' && $v !== [];
      });
      return $value;
    }
    return ($value === '' || $value === null) ? null : $value;
  };

  // Données de base
  $name = get_the_title($post_id);
  $permalink = get_permalink($post_id);
  $image = get_the_post_thumbnail_url($post_id, 'full'); // tombe à null si pas d'image
  $description = has_excerpt($post_id)
    ? get_the_excerpt($post_id)
    : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $post_id))), 40);

  // Metas saisis via votre metabox
  $telephone = $meta('telephone');
  $adresse = $meta('adresse');
  $commune = $meta('commune');
  $cp = $meta('code_postal');
  $pays = $meta('pays') ?: 'FR';
  $lat = $float($meta('latitude'));
  $lng = $float($meta('longitude'));
  $price_min = $meta('price_mini');
  $price_cur = 'EUR';
  $reserve = $meta('url_reservation_direct') ?: $permalink;

  // (Optionnel) Si vous stockez des avis : créez des metas "rating_value" et "review_count"
  $rating_value = $meta('rating_value');
  $review_count = $meta('review_count');

  // Construction JSON-LD
  $data = [
    '@context' => 'https://schema.org',
    '@type' => 'LodgingBusiness', // vous pouvez passer à "Campground" si tous sont des campings
    'name' => $name,
    'image' => $image,
    'description' => $description,
    'url' => $permalink,
    'telephone' => $telephone,
    'address' => [
      '@type' => 'PostalAddress',
      'streetAddress' => $adresse,
      'addressLocality' => $commune,
      'postalCode' => $cp,
      'addressRegion' => 'Nouvelle-Aquitaine',
      'addressCountry' => $pays,
    ],
    'geo' => ($lat !== null && $lng !== null) ? [
      '@type' => 'GeoCoordinates',
      'latitude' => $lat,
      'longitude' => $lng,
    ] : null,
    'offers' => $price_min ? [
      '@type' => 'Offer',
      'price' => (string) $price_min,
      'priceCurrency' => $price_cur,
      'url' => $reserve,
      'availability' => 'https://schema.org/InStock',
      'description' => 'Séjour à partir de ' . $price_min . ' € la nuit.',
    ] : null,
    'containedInPlace' => $commune ? [
      '@type' => 'Place',
      'name' => $commune,
      'containedInPlace' => [
        '@type' => 'Place',
        'name' => 'Charente-Maritime',
      ],
    ] : null,
    // Ajout "AggregateRating" seulement si complet
    'aggregateRating' => ($rating_value && $review_count) ? [
      '@type' => 'AggregateRating',
      'ratingValue' => (string) $rating_value,
      'reviewCount' => (string) $review_count,
    ] : null,
  ];

  // Nettoyage récursif des champs vides
  $data = $clean($data);

  // Point d’extension si vous voulez surcharger depuis un plugin/thème enfant
  $data = apply_filters('fdhpa17_camping_jsonld', $data, $post_id);

  if (!empty($data)) {
    echo '<script type="application/ld+json">'
      . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      . '</script>' . "\n";
  }
}, 20);



/**
 * JSON-LD @graph pour les pages de la taxonomie "destination"
 * - TouristDestination (name, description, geo si dispo)
 * - Place (Charente-Maritime -> Nouvelle-Aquitaine) [valeurs fixes]
 * - ItemList des campings de la destination
 */
add_action('wp_head', function () {
  if (is_admin() || !is_tax('destination'))
    return;

  $term = get_queried_object();
  if (!$term || is_wp_error($term))
    return;

  $term_id = $term->term_id;
  $name = $term->name;
  $desc_term = term_description($term_id, $term->taxonomy);
  $desc_term = $desc_term ? wp_strip_all_tags($desc_term) : "Découvrez les meilleurs campings de {$name}.";

  // Récup des metas éventuelles sur la taxonomie (ajustez les clés si besoin)
  $lat = get_term_meta($term_id, 'latitude', true);
  $lng = get_term_meta($term_id, 'longitude', true);
  if ($lat === '' || $lat === null)
    $lat = get_term_meta($term_id, 'lat', true);
  if ($lng === '' || $lng === null)
    $lng = get_term_meta($term_id, 'lng', true);
  $lat = is_numeric($lat) ? (float) $lat : null;
  $lng = is_numeric($lng) ? (float) $lng : null;

  // Récup des campings de cette destination (limite raisonnable pour éviter un JSON gigantesque)
  $campings = get_posts([
    'post_type' => 'camping',
    'posts_per_page' => -1, // mettez un cap si nécessaire (ex. 200)
    'no_found_rows' => true,
    'tax_query' => [
      [
        'taxonomy' => 'destination',
        'field' => 'term_id',
        'terms' => $term_id,
      ]
    ],
  ]);

  // Utilitaires
  $meta = function ($post_id, $key, $default = '') {
    $v = get_post_meta($post_id, $key, true);
    return $v !== '' ? $v : $default;
  };
  $float = function ($v) {
    return is_numeric($v) ? (float) $v : null;
  };
  $clean = function ($value) use (&$clean) {
    if (is_array($value)) {
      $value = array_filter(array_map($clean, $value), function ($v) {
        return $v !== null && $v !== '' && $v !== [];
      });
      return $value;
    }
    return ($value === '' || $value === null) ? null : $value;
  };

  // Construire ItemListElement
  $items = [];
  $position = 1;
  foreach ($campings as $post) {
    $pid = $post->ID;
    $title = get_the_title($pid);
    $url = get_permalink($pid);
    $img = get_the_post_thumbnail_url($pid, 'full');
    $desc = has_excerpt($pid)
      ? get_the_excerpt($pid)
      : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $pid))), 30);

    // Metas issues de votre metabox CPT
    $adresse = $meta($pid, 'adresse');
    $commune = $meta($pid, 'commune');
    $cp = $meta($pid, 'code_postal');
    $tel = $meta($pid, 'telephone');
    $lat_c = $float($meta($pid, 'latitude'));
    $lng_c = $float($meta($pid, 'longitude'));
    $pays = $meta($pid, 'pays', 'FR');

    $price_min = $meta($pid, 'price_mini');
    $reserve = $meta($pid, 'url_reservation_direct') ?: $url;

    $rating_value = $meta($pid, 'rating_value');
    $review_count = $meta($pid, 'review_count');

    $lodging = [
      '@type' => 'LodgingBusiness', // vous pouvez passer à "Campground" si 100% campings
      'name' => $title,
      'url' => $url,
      'image' => $img,
      'description' => $desc,
      'telephone' => $tel,
      'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $adresse,
        'addressLocality' => $commune,
        'postalCode' => $cp,
        'addressRegion' => 'Nouvelle-Aquitaine',
        'addressCountry' => $pays,
      ],
      'geo' => ($lat_c !== null && $lng_c !== null) ? [
        '@type' => 'GeoCoordinates',
        'latitude' => $lat_c,
        'longitude' => $lng_c,
      ] : null,
      'aggregateRating' => ($rating_value && $review_count) ? [
        '@type' => 'AggregateRating',
        'ratingValue' => (string) $rating_value,
        'reviewCount' => (string) $review_count,
      ] : null,
      'offers' => $price_min ? [
        '@type' => 'Offer',
        'price' => (string) $price_min,
        'priceCurrency' => 'EUR',
        'url' => $reserve,
        'availability' => 'https://schema.org/InStock',
        'description' => 'Séjour à partir de ' . $price_min . ' € la nuit.',
      ] : null,
    ];

    $items[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'item' => $clean($lodging),
    ];
  }

  // @graph
  $graph = [];

  // TouristDestination
  $graph[] = $clean([
    '@type' => 'TouristDestination',
    'name' => $name,
    'description' => $desc_term,
    'geo' => ($lat !== null && $lng !== null) ? [
      '@type' => 'GeoCoordinates',
      'latitude' => $lat,
      'longitude' => $lng,
    ] : null,
  ]);

  // Place (fixe) Charente-Maritime -> Nouvelle-Aquitaine
  $graph[] = [
    '@type' => 'Place',
    'name' => 'Charente-Maritime',
    'containedInPlace' => [
      '@type' => 'Place',
      'name' => 'Nouvelle-Aquitaine',
    ],
  ];

  // ItemList
  $graph[] = $clean([
    '@type' => 'ItemList',
    'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
    'name' => 'Liste des campings FDHPA17 - ' . $name,
    'description' => 'Découvrez les campings et hébergements de plein air membres de la FDHPA17 situés à ' . $name . '.',
    'numberOfItems' => count($items),
    'itemListElement' => $items,
  ]);

  $data = [
    '@context' => 'https://schema.org',
    '@graph' => $graph,
  ];

  // Point d’extension si besoin
  $data = apply_filters('fdhpa17_destination_jsonld', $data, $term);

  echo '<script type="application/ld+json">'
    . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . '</script>' . "\n";
}, 20);

/**
 * Helpers communs pour JSON-LD ItemList de campings
 */
if (!function_exists(__NAMESPACE__ . '\\fdhpa17_jsonld_clean')) {
  function fdhpa17_jsonld_clean($value)
  {
    if (is_array($value)) {
      foreach ($value as $k => $v) {
        $v = fdhpa17_jsonld_clean($v);
        if ($v === null || $v === '' || $v === []) {
          unset($value[$k]);
        } else {
          $value[$k] = $v;
        }
      }
      return $value;
    }
    return ($value === '' || $value === null) ? null : $value;
  }
}

if (!function_exists('fdhpa17_build_lodging_from_post')) {
  function fdhpa17_build_lodging_from_post($pid)
  {
    $meta = function ($key, $default = '') use ($pid) {
      $v = get_post_meta($pid, $key, true);
      return $v !== '' ? $v : $default;
    };
    $to_float = function ($v) {
      return is_numeric($v) ? (float) $v : null;
    };

    $title = get_the_title($pid);
    $url = get_permalink($pid);
    $img = get_the_post_thumbnail_url($pid, 'full');
    $desc = has_excerpt($pid)
      ? get_the_excerpt($pid)
      : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', $pid))), 30);

    $adresse = $meta('adresse');
    $commune = $meta('commune');
    $cp = $meta('code_postal');
    $tel = $meta('telephone');
    $lat = $to_float($meta('latitude'));
    $lng = $to_float($meta('longitude'));
    $pays = $meta('pays', 'FR');

    $price_min = $meta('price_mini');
    $reserve = $meta('url_reservation_direct') ?: $url;

    $rating_value = $meta('rating_value');
    $review_count = $meta('review_count');

    $lodging = [
      '@type' => 'LodgingBusiness', // ou "Campground" si 100% campings
      'name' => $title,
      'url' => $url,
      'image' => $img,
      'description' => $desc,
      'telephone' => $tel,
      'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $adresse,
        'addressLocality' => $commune,
        'postalCode' => $cp,
        'addressRegion' => 'Nouvelle-Aquitaine',
        'addressCountry' => $pays,
      ],
      'geo' => ($lat !== null && $lng !== null) ? [
        '@type' => 'GeoCoordinates',
        'latitude' => $lat,
        'longitude' => $lng,
      ] : null,
      'aggregateRating' => ($rating_value && $review_count) ? [
        '@type' => 'AggregateRating',
        'ratingValue' => (string) $rating_value,
        'reviewCount' => (string) $review_count,
      ] : null,
      'offers' => $price_min ? [
        '@type' => 'Offer',
        'price' => (string) $price_min,
        'priceCurrency' => 'EUR',
        'url' => $reserve,
        'availability' => 'https://schema.org/InStock',
        'description' => 'Séjour à partir de ' . $price_min . ' € la nuit.',
      ] : null,
    ];

    /**
     * Permettre une surcouche si besoin
     */
    $lodging = apply_filters('fdhpa17_jsonld_lodging_from_post', $lodging, $pid);

    return fdhpa17_jsonld_clean($lodging);
  }
}

if (!function_exists('fdhpa17_emit_itemlist_jsonld')) {
  function fdhpa17_emit_itemlist_jsonld($posts)
  {
    $items = [];
    $pos = 1;
    foreach ($posts as $p) {
      $items[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'item' => fdhpa17_build_lodging_from_post($p->ID),
      ];
    }

    $data = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
      'numberOfItems' => count($items),
      'itemListElement' => $items,
    ];

    $data = apply_filters('fdhpa17_jsonld_itemlist', $data, $posts);

    echo '<script type="application/ld+json">'
      . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      . '</script>' . "\n";
  }
}

/**
 * Injection sur :
 * - Taxos (equipement, atout, etoile, aquatique, service, label, hebergement, cible, groupe, confort)
 * - Home (is_front_page() ou is_home())
 * - Page /carte-camping/
 */
add_action('wp_head', function () {
  if (is_admin())
    return;

  $taxos = ['equipement', 'atout', 'etoile', 'aquatique', 'service', 'label', 'hebergement', 'cible', 'groupe', 'confort'];

  // 1) Pages de taxonomie ciblées
  if (is_tax($taxos)) {
    $term = get_queried_object();
    if ($term && !is_wp_error($term)) {
      $campings = get_posts([
        'post_type' => 'camping',
        'post_status' => 'publish',
        'posts_per_page' => 20, // mettez une limite si vous avez des centaines d’items (ex. 300)
        'no_found_rows' => true,
        'tax_query' => [
          [
            'taxonomy' => $term->taxonomy,
            'field' => 'term_id',
            'terms' => $term->term_id,
          ]
        ],
      ]);
      if ($campings) {
        fdhpa17_emit_itemlist_jsonld($campings);
      }
    }
  }

  // 2) Home (page d’accueil ou page des articles)
  if (is_front_page() || is_home()) {
    $campings = get_posts([
      'post_type' => 'camping',
      'post_status' => 'publish',
      'posts_per_page' => 20,
      'no_found_rows' => true,
    ]);
    if ($campings) {
      fdhpa17_emit_itemlist_jsonld($campings);
    }
  }

  // 3) Page /carte-camping/
  if (is_page('carte-camping')) {
    $campings = get_posts([
      'post_type' => 'camping',
      'post_status' => 'publish',
      'posts_per_page' => 20,
      'no_found_rows' => true,
    ]);
    if ($campings) {
      fdhpa17_emit_itemlist_jsonld($campings);
    }
  }
}, 20);


add_action('wp_enqueue_scripts', function () {
  if (is_singular('camping')) {
    // html2pdf (cdn)
    wp_enqueue_script(
      'html2pdf',
      'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js',
      [],
      null,
      true
    );

    // ton script de page
    $path = get_stylesheet_directory() . '/assets/js/camping-coupon.js';
    wp_enqueue_script(
      'camping-coupon',
      get_stylesheet_directory_uri() . '/assets/js/camping-coupon.js',
      ['html2pdf'],
      file_exists($path) ? filemtime($path) : null,
      true
    );
  }
});

add_filter('facetwp_query_args', function ($args) {
  $http = FWP()->facet->http_params;
  if (isset($http['lang'])) {
    do_action('wpml_switch_language', $http['lang']);
  }
  return $args;
}, 11);


add_filter('facetwp_i18n', function ($text, $args = []) {
    // Langue active (WPML) + fallback locale
    $lang = apply_filters('wpml_current_language', null);
    if (empty($lang)) {
        $locale = get_locale();
        $lang = substr($locale, 0, 2);
    }

    $map = [
        'fr' => [
            'classement' => [ 'label' => 'Classement' ],
            'label' => [ 'label' => 'Label' ],
            'hebergement' => [ 'label' => 'Hébergement' ],
            'services' => [ 'label' => 'Services' ],
            'equipements_aquatiques' => [ 'label' => 'Équipements aquatiques' ],
        ],
        'en' => [
            'classement' => [ 'label' => 'Ranking' ],
            'label' => [ 'label' => 'Label' ],
            'hebergement' => [ 'label' => 'Accommodation' ],
            'services' => [ 'label' => 'Services' ],
            'equipements_aquatiques' => [ 'label' => 'Aquatic equipment' ],
        ],
        'nl' => [
            'classement' => [ 'label' => 'Classificatie' ],
            'label' => [ 'label' => 'Label' ],
            'hebergement' => [ 'label' => 'Accommodatie' ],
            'services' => [ 'label' => 'Diensten' ],
            'equipements_aquatiques' => [ 'label' => 'Waterfaciliteiten' ],
        ],
        'de' => [
            'classement' => [ 'label' => 'Bewertung' ],
            'label' => [ 'label' => 'Label' ],
            'hebergement' => [ 'label' => 'Unterkunft' ],
            'services' => [ 'label' => 'Dienstleistungen' ],
            'equipements_aquatiques' => [ 'label' => 'Wasserausstattung' ],
        ],
    ];

    $facet = (is_array($args) && isset($args['facet'])) ? $args['facet'] : null;
    $key   = (is_array($args) && isset($args['key']))   ? $args['key']   : 'label';

    if ($facet && isset($map[$lang][$facet][$key])) {
        return $map[$lang][$facet][$key];
    }
    return $text;
}, 10, 2);


add_filter('wpseo_breadcrumb_links', function ($links) {
  if (!is_tax()) {
    return $links;
  }

  $q = get_queried_object();
  if (!$q || empty($q->taxonomy)) {
    return $links;
  }

  $flat_tax = ['destination','equipement','atout','etoile','aquatique','service','label','hebergement','cible','groupe','confort','paiement'];
  if (!in_array($q->taxonomy, $flat_tax, true)) {
    return $links;
  }

  $new = [];
  $last_index = count($links) - 1;

  foreach ($links as $i => $link) {

    // 1) Conserve "Accueil"
    if ($i === 0) {
      $new[] = $link;
      continue;
    }

    // 2) Conserve l’archive de la taxonomie si Yoast l’a mise
    if (!empty($link['ptarchive'])) {
      $new[] = $link;
      continue;
    }

    // 3) Conserve TOUJOURS le dernier crumb (terme courant)
    if ($i === $last_index) {
      // Sécurise le libellé si Yoast ne l’a pas fourni
      if (empty($link['text']) && !empty($q->name)) {
        $link['text'] = $q->name;
      }
      // Le dernier n’a souvent pas d’URL (page courante) → ok
      $new[] = $link;
      continue;
    }

    // Tous les autres (parents) sont supprimés pour un breadcrumb "plat"
  }

  return $new;
});