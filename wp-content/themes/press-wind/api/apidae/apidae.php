<?php
class APIDAE
{
  public static function init()
  {
    add_action('init', [__CLASS__, 'register_post_types']);
    add_action('init', [__CLASS__, 'register_taxonomies']);
    add_action('add_meta_boxes', function () {
      add_meta_box('apidae_infos', 'Informations Apidae', [__CLASS__, 'render_apidae_metabox'], 'camping', 'normal', 'high');
    });
    add_action('save_post_camping', [__CLASS__, 'save_apidae_metabox']);
  }

  public static function register_post_types()
  {
    // register custom post camping 
    register_post_type('camping', [
      'labels' => [
        'name' => __('Campings', 'fdhpa17'),
        'singular_name' => __('Camping', 'fdhpa17'),
      ],
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'editor', 'thumbnail'],
      'rewrite' => ['slug' => 'camping'],
    ]);
  }

  public static function register_taxonomies()
  {
    // Register custom taxonomies here : /atout/, /etoile/, /aquatique/, /service/,  /label/, /hebergement/, /cible/, /groupe/
    $taxonomies = [
      'destination',
      'atout',
      'etoile',
      'aquatique',
      'service',
      'label',
      'hebergement',
      'cible',
      'groupe',
    ];

    foreach ($taxonomies as $taxonomy) {
      register_taxonomy($taxonomy, 'camping', [
        'labels' => [
          'name' => __(ucfirst($taxonomy) . 's', 'fdhpa17'),
          'singular_name' => __(ucfirst($taxonomy), 'fdhpa17'),
        ],
        'public' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => $taxonomy],
      ]);
    }
  }

  public static function connect_to_apidae($endpoint, $params = [], $method = 'GET', $json = false)
  {
    $config = [
      'base_url' => 'https://api.apidae-tourisme.com/api/v002',
      'api_key' => APIDAE_KEY,
      'project_id' => APIDAE_PROJECT_ID,
      'headers' => [
        'Content-Type' => 'application/json'
      ]
    ];

    // Ajout automatique des clés API
    $params = array_merge([
      'apiKey' => $config['api_key'],
      'projetId' => $config['project_id']
    ], $params);

    // Construire l'URL
    $url = $config['base_url'] . $endpoint;

    // GET classique
    if ($method === 'GET' && !$json) {
      $url .= '?' . http_build_query($params);
      $response = wp_remote_get($url, [
        'headers' => $config['headers']
      ]);

      // GET ou POST avec JSON
    } else {
      // Encodage JSON pour query
      if ($json) {
        $url .= '?query=' . urlencode(json_encode($params));
        $response = wp_remote_get($url, ['headers' => $config['headers']]);
      } else {
        $response = wp_remote_post($url, [
          'headers' => $config['headers'],
          'body' => json_encode($params)
        ]);
      }
    }

    if (is_wp_error($response)) {
      return [
        'success' => false,
        'message' => $response->get_error_message()
      ];
    }

    return [
      'success' => true,
      'data' => json_decode(wp_remote_retrieve_body($response), true)
    ];
  }
  public static function import_apidae_camping($item)
  {
    // Vérifier si le camping existe déjà
    $existing = get_posts([
      'post_type' => 'camping',
      'meta_key' => 'apidae_id',
      'meta_value' => $item['id'],
      'posts_per_page' => 1,
      'fields' => 'ids',
    ]);

    if (!empty($existing)) {
      return; // ou mettre à jour si besoin
    }

    // Champs de base
    $title = $item['nom']['libelleFr'] ?? 'Camping sans nom';
    $description = $item['presentation']['descriptifCourt']['libelleFr'] ?? '';

    // Créer le post
    $post_id = wp_insert_post([
      'post_title' => $title,
      'post_content' => $description,
      'post_type' => 'camping',
      'post_status' => 'publish',
    ]);

    if (is_wp_error($post_id)) {
      error_log('Erreur création camping Apidae : ' . $post_id->get_error_message());
      return;
    }

    // 🔑 ID Apidae
    update_post_meta($post_id, 'apidae_id', $item['id']);
    update_post_meta($post_id, 'apidae_identifier', $item['identifier']);

    // 🏠 Localisation
    $adresse = $item['localisation']['adresse']['adresse1'] ?? '';
    $commune = $item['localisation']['adresse']['commune']['nom'] ?? '';
    $code_postal = $item['localisation']['adresse']['codePostal'] ?? '';
    $pays = $item['localisation']['adresse']['commune']['pays']['libelleFr'] ?? '';
    $geoloc_lat = $item['localisation']['geolocalisation']['geoJson']['coordinates'][1] ?? '';
    $geoloc_lng = $item['localisation']['geolocalisation']['geoJson']['coordinates'][0] ?? '';

    update_post_meta($post_id, 'adresse', $adresse);
    update_post_meta($post_id, 'commune', $commune);
    update_post_meta($post_id, 'code_postal', $code_postal);
    update_post_meta($post_id, 'pays', $pays);
    update_post_meta($post_id, 'latitude', $geoloc_lat);
    update_post_meta($post_id, 'longitude', $geoloc_lng);

    // 📞 Moyens de communication
    if (!empty($item['informations']['moyensCommunication'])) {
      foreach ($item['informations']['moyensCommunication'] as $moyen) {
        $type = $moyen['type']['libelleFr'] ?? '';
        $valeur = $moyen['coordonnees']['fr'] ?? '';

        if ($type === 'Téléphone') {
          update_post_meta($post_id, 'telephone', $valeur);
        } elseif ($type === 'Mél') {
          update_post_meta($post_id, 'email', $valeur);
        } elseif ($type === 'Site web (URL)') {
          update_post_meta($post_id, 'site_web', $valeur);
        }
      }
    }

    // 🏕️ Informations Hôtellerie plein air
    if (!empty($item['informationsHotelleriePleinAir'])) {
      $info = $item['informationsHotelleriePleinAir'];

      update_post_meta($post_id, 'hotellerie_type', $info['hotelleriePleinAirType']['libelleFr'] ?? '');
      update_post_meta($post_id, 'numero_classement', $info['numeroClassement'] ?? '');
      update_post_meta($post_id, 'date_classement', $info['dateClassement'] ?? '');
      update_post_meta($post_id, 'classement', $info['classement']['libelleFr'] ?? '');

      // Chaines
      if (!empty($info['chaines'])) {
        $chaines = wp_list_pluck($info['chaines'], 'libelleFr');
        update_post_meta($post_id, 'chaines', implode(', ', $chaines));
      }

      // Capacité
      if (!empty($info['capacite'])) {
        foreach ($info['capacite'] as $key => $value) {
          update_post_meta($post_id, 'capacite_' . $key, $value);
        }
      }
    }

    // 🖼️ Images (on importe la première en image à la une et on stocke les autres)
    if (!function_exists('media_sideload_image')) {
      require_once ABSPATH . 'wp-admin/includes/image.php';
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';
    }
    $gallery_ids = [];
    if (!empty($item['illustrations'])) {
      foreach ($item['illustrations'] as $index => $illustration) {
        $image_url = $illustration['traductionFichiers'][0]['url'] ?? '';
        if ($image_url) {
          $image_id = media_sideload_image($image_url, $post_id, null, 'id');

          if (!is_wp_error($image_id)) {
            if ($index === 0) {
              set_post_thumbnail($post_id, $image_id); // première image
            } else {
              $gallery_ids[] = $image_id;
            }
          }
        }
      }
    }

    if (!empty($gallery_ids)) {
      update_post_meta($post_id, 'gallery', $gallery_ids);
    }

    // ✅ Type (HOTELLERIE_PLEIN_AIR)
    update_post_meta($post_id, 'type', $item['type'] ?? '');

    // ✅ Autres infos utiles
    update_post_meta($post_id, 'presentation_complement', $item['localisation']['geolocalisation']['complement']['libelleFr'] ?? '');
  }
  public static function delete_import_apidae_camping()
  {
    $posts = get_posts([
      'post_type' => 'campings', // mauvais post type
      'numberposts' => -1,
      'post_status' => 'any',
      'fields' => 'ids'
    ]);

    foreach ($posts as $post_id) {
      wp_delete_post($post_id, true); // true = suppression définitive
    }

    wp_die('✅ Tous les posts de type "campings" ont été définitivement supprimés.');
  }

  public static function render_apidae_metabox($post)
  {
    $fields = [
      'apidae_id' => 'ID Apidae',
      'apidae_identifier' => 'Identifiant Apidae',
      'adresse' => 'Adresse',
      'commune' => 'Commune',
      'code_postal' => 'Code Postal',
      'pays' => 'Pays',
      'latitude' => 'Latitude',
      'longitude' => 'Longitude',
      'telephone' => 'Téléphone',
      'email' => 'Email',
      'site_web' => 'Site Web',
      'hotellerie_type' => 'Type d’hôtellerie',
      'numero_classement' => 'Numéro de classement',
      'date_classement' => 'Date de classement',
      'classement' => 'Classement',
      'chaines' => 'Chaînes',
      'type' => 'Type',
      'presentation_complement' => 'Complément de localisation'
    ];

    // Capacités dynamiques
    $capacites = get_post_meta($post->ID);

    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
      $value = esc_attr(get_post_meta($post->ID, $key, true));
      echo "<tr>
                <th><label for='{$key}'>{$label}</label></th>
                <td><input type='text' id='{$key}' name='{$key}' value='{$value}' class='regular-text' /></td>
              </tr>";
    }

    // Champs de capacité
    foreach ($capacites as $key => $value) {
      if (strpos($key, 'capacite_') === 0) {
        $label = ucfirst(str_replace('_', ' ', $key));
        echo "<tr>
                    <th><label for='{$key}'>{$label}</label></th>
                    <td><input type='text' id='{$key}' name='{$key}' value='" . esc_attr($value[0]) . "' class='regular-text' /></td>
                  </tr>";
      }
    }

    echo '</table>';
  }
  public static function save_apidae_metabox($post_id)
  {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return;

    $fields = [
      'apidae_id',
      'apidae_identifier',
      'adresse',
      'commune',
      'code_postal',
      'pays',
      'latitude',
      'longitude',
      'telephone',
      'email',
      'site_web',
      'hotellerie_type',
      'numero_classement',
      'date_classement',
      'classement',
      'chaines',
      'type',
      'presentation_complement'
    ];

    foreach ($fields as $field) {
      if (isset($_POST[$field])) {
        update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
      }
    }

    // Sauvegarde des capacités dynamiques
    foreach ($_POST as $key => $value) {
      if (strpos($key, 'capacite_') === 0) {
        update_post_meta($post_id, $key, sanitize_text_field($value));
      }
    }
  }
}



APIDAE::init();

// // APIDAE::delete_import_apidae_camping();

// $result = APIDAE::connect_to_apidae(
//   '/recherche/list-objets-touristiques',
//   [
//     "selectionIds" => [
//       190542,
//       190543,
//       190544,
//       190545,
//       190546,
//       190547,
//       190548,
//       190549,
//       190550,
//       190551,
//       190552,
//       190553,
//       190554,
//       190555,
//       190556,
//       190557,
//       190558,
//       190559,
//       190560,
//       191705
//     ],
//     "count" => 999
//   ],
//   'GET',
//   true
// );

// $seenIds = [];
// $duplicates = [];

// foreach ($result['data']['objetsTouristiques'] as $item) {
//   $id = $item['id'];

//   if (in_array($id, $seenIds)) {
//     $duplicates[] = $id;
//   } else {
//     $seenIds[] = $id;
//   }

//   // Importer le camping
//   // APIDAE::import_apidae_camping($item);
//   // Afficher un message de succès
//   // echo "<h2>✅ Camping importé : {$item['nom']['libelleFr']} (ID: {$id})</h2>";
  
// }

// $totalCampings = count($seenIds);
// echo "<pre>";
// var_dump($result); // Debugging line to check unique IDs
// echo "</pre>";
// die();

// echo "<h2>📊 Nombre total de campings uniques : {$totalCampings}</h2>";

// if (!empty($duplicates)) {
//   echo '<h2>🔁 Doublons détectés :</h2>';
//   echo '<pre>';
//   print_r(array_unique($duplicates));
//   echo '</pre>';
// } else {
//   echo '<h2>✅ Aucun doublon trouvé.</h2>';
// }

// die();
