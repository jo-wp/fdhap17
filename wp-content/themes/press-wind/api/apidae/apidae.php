<?php
class APIDAE
{
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
      'post_type' => 'camping', // mauvais post type
      'numberposts' => -1,
      'post_status' => 'any',
      'fields' => 'ids'
    ]);

    var_dump($posts);

    foreach ($posts as $post_id) {
      wp_delete_post($post_id, true); 
    }

    wp_die('✅ Tous les posts de type "campings" ont été définitivement supprimés.');
  }

  public static function update_illustrations_apidae_camping($item)
  {
    // Vérifier si le camping existe déjà

    $existing = get_posts([
      'post_type' => 'camping',
      'meta_key' => 'apidae_id',
      'meta_value' => $item['id'],
      'posts_per_page' => 1,
      'fields' => 'ids',
    ]);


    if ($existing) {
      $post_id = $existing[0];

      //update IMAGES
      $images = $item['illustrations'];
      $sources = [];
      foreach ($images as $image) {
        $sources[] = $image['traductionFichiers'][0]['url'];
      }
      if ($sources) {
        APIDAE::add_images_to_acf_gallery($post_id, 'galerie_photo_camping', $sources,'url');
      }
      //END update IMAGES

    }
  }


  public static function add_images_to_acf_gallery(int $post_id, string $field, array $sources, string $mode = 'id')
  {
    if (empty($sources)) {
      return new WP_Error('no_sources', 'Aucune image fournie.');
    }

    // On veut manipuler la valeur brute (array d’IDs), pas la valeur formatée ACF.
    $existing_ids = get_field($field, $post_id, false);
    if (! is_array($existing_ids)) {
      $existing_ids = [];
    }

    $new_ids = [];

    // Assure que les fonctions d’upload sont dispo (utile en front ou CRON).
    if (in_array($mode, ['url', 'path'], true)) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';
      require_once ABSPATH . 'wp-admin/includes/image.php';
    }



    foreach ($sources as $item) {
      $attachment_id = 0;

      if ('id' === $mode) {
        $attachment_id = absint($item);
      } elseif ('url' === $mode) {
        $attachment_id = APIDAE::_acf_gallery_import_from_url($item, $post_id);
      }


      if (is_wp_error($attachment_id)) {
        // À vous de logger si besoin: error_log( $attachment_id->get_error_message() );
        continue;
      }

      if ($attachment_id && 'attachment' === get_post_type($attachment_id)) {
        $new_ids[] = $attachment_id;
      }
    }

    // Fusion + dédoublonnage, en conservant l’ordre existant puis les nouveaux.
    $final_ids = array_values(array_unique(array_merge($existing_ids, $new_ids)));

    // Met à jour la galerie (utiliser la clé de champ "field_xxx" évite les collisions).
    $ok = update_field($field, $final_ids, $post_id);
    if (! $ok) {
      return new WP_Error('update_failed', 'La mise à jour du champ ACF a échoué.');
    }

    return $final_ids;
  }


  public static function _acf_gallery_import_from_url(string $url, int $parent_post_id = 0)
  {
    // Télécharge dans un fichier temporaire
    $tmp = download_url($url);
    if (is_wp_error($tmp)) {
      return $tmp;
    }

    $filename = basename(parse_url($url, PHP_URL_PATH) ?: 'image');
    // Construit le tableau attendu par media_handle_sideload
    $file = [
      'name'     => $filename,
      'tmp_name' => $tmp,
    ];

    $id = media_handle_sideload($file, $parent_post_id);

    if (is_wp_error($id)) {
      @unlink($tmp);
      return $id;
    }

    return $id;
  }
}

// APIDAE::delete_import_apidae_camping();

// $result = APIDAE::connect_to_apidae('/objet-touristique/get-by-id/5752637',[
//   'responseFields' => 'id,illustrations,informationsHotelleriePleinAir.labels,informationsHotelleriePleinAir.chaines,informationsHotelleriePleinAir.hotelleriePleinAirType,informations.moyensCommunication,informationsHotelleriePleinAir.classement,presentation.descriptifCourt,presentation.descriptifDetaille,localisation.geolocalisation.geoJson.coordinates,localisation.geolocalisation.geoJson.coordinates,localisation.environnements,localisation.perimetreGeographique,localisation.territoiresAffectes,prestations.equipements,prestations.services,prestations.conforts,prestations.activites,prestations.languesParlees,prestations.animauxAcceptes,ouverture.periodesOuvertures,descriptionTarif.tarifsEnClair,descriptionTarif.modesPaiement,reservation.organismes,informations.informationsLegales.siret,contacts',
//   'locales' => 'fr'
// ]);

// echo'<pre>';
// print_r($result['data']['reservation']['organismes'][0]['moyensCommunication'][0]['coordonnees']['fr']);
// echo'</pre>';
// die();



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
// foreach ($result['data']['objetsTouristiques'] as $item) {
//   if (!$item) {
//   } else {

//     // APIDAE::update_illustrations_apidae_camping($item);

//     // if ($images) {
//     //   foreach ($images as $image) {
//     //     // APIDAE::add_images_to_acf_gallery($id_camping, 'galerie_photo_camping', [$image['traductionFichiers'][0]['url']]);
//     //   }
//     // }
//   }
// }
// die();

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
