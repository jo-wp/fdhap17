<?php

/**
 * Plugin Name: APIDAE CLI Importer
 * Description: Commandes WP-CLI pour importer/mettre à jour/supprimer des campings depuis APIDAE sans saturer PHP/Apache.
 * Author: BeeCom
 * Version: 1.0.0
 */

if (! defined('ABSPATH')) {
  exit;
}

// ▶️ Dépendances admin pour l’upload en CLI
if (defined('WP_CLI') && WP_CLI) {
  if (! function_exists('media_handle_sideload')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
  }
}

/**
 * Classe utilitaire APIDAE (HTTP + import logique)
 */
class APIDAE_Service
{

  public static function connect_to_apidae($endpoint, $params = [], $method = 'GET', $json = false, $retries = 3, $sleepSeconds = 1)
  {
    $config = [
      'base_url'  => 'https://api.apidae-tourisme.com/api/v002',
      'api_key'   => defined('APIDAE_KEY') ? APIDAE_KEY : '',
      'project_id' => defined('APIDAE_PROJECT_ID') ? APIDAE_PROJECT_ID : '',
      'headers'   => ['Content-Type' => 'application/json'],
      'timeout'   => 60,
    ];

    // Clés API
    $params = array_merge([
      'apiKey'   => $config['api_key'],
      'projetId' => $config['project_id'],
    ], $params);

    $url = rtrim($config['base_url'], '/') . $endpoint;

    // Build request args
    $args = [
      'headers' => $config['headers'],
      'timeout' => $config['timeout'],
    ];

    // Route GET + JSON query param pris en charge
    if ($method === 'GET' && !$json) {
      $url .= '?' . http_build_query($params);
      $request_fn = function () use ($url, $args) {
        return wp_remote_get($url, $args);
      };
    } else {
      if ($json) {
        $url .= '?query=' . urlencode(json_encode($params));
        $request_fn = function () use ($url, $args) {
          return wp_remote_get($url, $args);
        };
      } else {
        $args['body'] = wp_json_encode($params);
        $request_fn = function () use ($url, $args) {
          return wp_remote_post($url, $args);
        };
      }
    }

    // Retries (429/5xx)
    $attempt = 0;
    do {
      $response = $request_fn();

      if (is_wp_error($response)) {
        $err = $response->get_error_message();
        if (defined('WP_CLI') && WP_CLI) {
          WP_CLI::warning("HTTP error: $err");
        }
      } else {
        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 200 && $code < 300) {
          $body = json_decode(wp_remote_retrieve_body($response), true);
          return ['success' => true, 'data' => $body];
        }
        if ($code == 429 || $code >= 500) {
          if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::warning("HTTP $code — retry in {$sleepSeconds}s");
          }
          sleep($sleepSeconds);
          $sleepSeconds = min($sleepSeconds * 2, 20); // backoff
        } else {
          $body = wp_remote_retrieve_body($response);
          return ['success' => false, 'message' => "HTTP $code: $body"];
        }
      }

      $attempt++;
    } while ($attempt < $retries);

    return ['success' => false, 'message' => 'Max retries exceeded'];
  }

  /**
   * Extrait une liste de libellés (strings) depuis des valeurs APIDAE hétérogènes.
   * Accepte: string, objet/array avec libelleFr|libelle|nom|name, ou listes de ces formes.
   */
  protected static function extract_term_labels($vals): array
  {
    if ($vals === null) return [];

    // Normalise en liste :
    if (!is_array($vals)) {
      $vals = [$vals];
    } else {
      // Si $vals est un tableau associatif (pas une liste), on le traite comme un seul item
      // Compat < PHP 8.1 (array_is_list)
      $isAssoc = $vals !== [] && array_keys($vals) !== range(0, count($vals) - 1);
      if ($isAssoc) {
        $vals = [$vals];
      }
    }

    $out = [];
    foreach ($vals as $v) {
      if (is_array($v)) {
        // Priorités de clés fréquentes côté Apidae
        $name = $v['libelleFr'] ?? $v['nom'] ?? $v['name'] ?? $v['title'] ?? '';
      } else {
        $name = (string) $v;
      }

      $name = trim(wp_strip_all_tags((string) $name));
      if ($name !== '') {
        $out[] = $name;
      }
    }

    // unique + reindex
    return array_values(array_unique($out));
  }



  /**
   * Assigne une liste de valeurs à une taxonomy en résolvant/creant les termes par libellé.
   * $values peut être string|array de strings|objets APIDAE.
   */
  protected static function set_post_terms_safe(int $post_id, string $taxonomy, $vals, bool $replace = true): void
  {
    if (!taxonomy_exists($taxonomy)) {
      // Si la taxo n'est pas enregistrée, on ne fait rien
      return;
    }

    $labels = self::extract_term_labels($vals);
    // Si tu veux VIDER la taxo quand la source est vide, décommente la ligne ci-dessous :
    // if (empty($labels)) { wp_set_object_terms($post_id, [], $taxonomy); return; }
    if (empty($labels)) {
      return;
    }

    $term_ids = [];
    foreach ($labels as $name) {
      $slug = sanitize_title($name);

      // Vérifie d'abord par slug (évite les doublons d'accents/majuscules)
      $existing = term_exists($slug, $taxonomy);
      if (!$existing) {
        // fallback par nom si le slug n'est pas trouvé
        $existing = term_exists($name, $taxonomy);
      }

      if ($existing && !is_wp_error($existing)) {
        $term_ids[] = (int) ($existing['term_id'] ?? $existing);
      } else {
        $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
        if (!is_wp_error($created)) {
          $term_ids[] = (int) $created['term_id'];
        } else {
          // Debug utile en dev
          error_log("[APIDAE] wp_insert_term failed for {$taxonomy} / {$name}: " . $created->get_error_message());
        }
      }
    }

    if (!empty($term_ids)) {
      // $replace=true => remplace complètement la liste ; false => fusionne/ajoute
      wp_set_object_terms($post_id, $term_ids, $taxonomy, $append = !$replace);
    }
  }

  //**Function extract id from secureholiday */

  private static function extractSecureHolidayId($url)
  {
    // Vérifie le domaine
    if (strpos($url, "secureholiday.net") === false) {
      return null;
    }

    // Regex pour trouver les nombres précédés d'un /
    if (preg_match_all('/\/(\d+)(?=\/|\?|$)/', $url, $matches)) {
      // Retourne le dernier ID trouvé
      return end($matches[1]);
    }

    return null;
  }

  /**
   * Import ou MAJ d’un camping (idempotent par apidae_id).
   * $mode = 'create-only' | 'upsert'
   */
  public static function import_apidae_camping(array $item, $mode = 'upsert', $dry_run = false)
  {
    $apidae_id = $item['id'] ?? null;
    if (! $apidae_id) {
      return ['ok' => false, 'reason' => 'no_apidae_id'];
    }

    // existe ?
    $existing = get_posts([
      'post_type'      => 'camping',
      'meta_key'       => 'apidae_id',
      'meta_value'     => $apidae_id,
      'posts_per_page' => 1,
      'fields'         => 'ids',
      'suppress_filters' => true,
      'no_found_rows'  => true,
    ]);

    $title       = $item['nom']['libelleFr'] ?? 'Camping sans nom';
    $description = (!empty($item['presentation']['descriptifDetaille']['libelleFr']))? $item['presentation']['descriptifDetaille']['libelleFr'] : $item['presentation']['descriptifCourt']['libelleFr'];

    if ($existing) {
      if ($mode === 'create-only') {
        return ['ok' => true, 'post_id' => $existing[0], 'skipped' => 'exists'];
      }
      $post_id = $existing[0];
      if (! $dry_run) {
        wp_update_post([
          'ID'           => $post_id,
          'post_title'   => $title,
          'post_content' => $description,
        ]);
      }
      $action = 'updated';
    } else {
      if ($dry_run) {
        return ['ok' => true, 'post_id' => 0, 'skipped' => 'dry-run-create'];
      }
      $post_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $description,
        'post_type'    => 'camping',
        'post_status'  => 'publish',
      ]);
      if (is_wp_error($post_id)) {
        return ['ok' => false, 'reason' => 'insert_failed', 'error' => $post_id->get_error_message()];
      }
      $action = 'created';
    }

    if ($dry_run) {
      return ['ok' => true, 'post_id' => $post_id, 'skipped' => 'dry-run-meta'];
    }

    // 🔑 IDs
    update_post_meta($post_id, 'apidae_id', $item['id']);
    if (isset($item['identifier'])) {
      update_post_meta($post_id, 'apidae_identifier', $item['identifier']);
    }

    // 🏠 Localisation
    $adresse     = $item['localisation']['adresse']['adresse1'] ?? '';
    $commune     = $item['localisation']['adresse']['commune']['nom'] ?? '';
    $code_postal = $item['localisation']['adresse']['codePostal'] ?? '';
    $pays        = $item['localisation']['adresse']['commune']['pays']['libelleFr'] ?? '';
    $lat         = $item['localisation']['geolocalisation']['geoJson']['coordinates'][1] ?? '';
    $lng         = $item['localisation']['geolocalisation']['geoJson']['coordinates'][0] ?? '';
    update_post_meta($post_id, 'adresse', $adresse);
    update_post_meta($post_id, 'commune', $commune);
    update_post_meta($post_id, 'code_postal', $code_postal);
    update_post_meta($post_id, 'pays', $pays);
    update_post_meta($post_id, 'latitude', $lat);
    update_post_meta($post_id, 'longitude', $lng);

    // 📞 Contacts
    if (!empty($item['informations']['moyensCommunication'])) {
      foreach ($item['informations']['moyensCommunication'] as $moyen) {
        $type   = $moyen['type']['libelleFr'] ?? '';
        $valeur = $moyen['coordonnees']['fr'] ?? '';
        if ($type === 'Téléphone')        update_post_meta($post_id, 'telephone', $valeur);
        elseif ($type === 'Mél')          update_post_meta($post_id, 'email', $valeur);
        elseif ($type === 'Site web (URL)') update_post_meta($post_id, 'site_web', $valeur);
      }
    }


    // 🏕️ HPA
    if (!empty($item['informationsHotelleriePleinAir'])) {
      $info = $item['informationsHotelleriePleinAir'];
      update_post_meta($post_id, 'hotellerie_type',  $info['hotelleriePleinAirType']['libelleFr'] ?? '');
      update_post_meta($post_id, 'numero_classement', $info['numeroClassement'] ?? '');
      update_post_meta($post_id, 'date_classement',   $info['dateClassement'] ?? '');
      update_post_meta($post_id, 'classement',        $info['classement']['libelleFr'] ?? '');
      update_post_meta($post_id, 'nb_real', $info['capacite']['nombreEmplacementsDeclares'] ?? '');
      update_post_meta($post_id, 'nb_mobilhomes', $info['capacite']['nombreLocationMobilhomes'] ?? '');
      update_post_meta($post_id, 'nb_bungalows', $info['capacite']['nombreLocationBungalows'] ?? '');
      update_post_meta($post_id, 'nb_insolites', $info['capacite']['nombreHebergementsInsolites'] ?? '');
      update_post_meta($post_id, 'empl_campingcars', $info['capacite']['nombreEmplacementsCampingCars'] ?? '');
      update_post_meta($post_id, 'empl_caravanes', $info['capacite']['nombreEmplacementsCaravanes'] ?? '');
      update_post_meta($post_id, 'superficie', $info['capacite']['superficie'] ?? '');

      if (!empty($info['chaines'])) {
        $chaines = wp_list_pluck($info['chaines'], 'libelleFr');
        update_post_meta($post_id, 'chaines', implode(', ', $chaines));
      }
      if (!empty($info['capacite'])) {
        foreach ($info['capacite'] as $key => $value) {
          update_post_meta($post_id, 'capacite_' . $key, $value);
        }
      }
    }

    // Tarifs
    if (!empty($item['descriptionTarif'])) {
    }

    // Reservation
    if (!empty($item['reservation'])) {
      $reservation = $item['reservation'];
      if ($reservation['organismes'][0]['moyensCommunication'][0]['coordonnees']['fr']) {
        $id_reservation_direct = self::extractSecureHolidayId($reservation['organismes'][0]['moyensCommunication'][0]['coordonnees']['fr']);
        update_post_meta($post_id, 'id_reservation_direct', $id_reservation_direct  ?? '');
        update_post_meta($post_id, 'url_reservation_direct', $reservation['organismes'][0]['moyensCommunication'][0]['coordonnees']['fr'] ?? '');
      }
    }

    // 🖼️ Images (featured + galerie simple)
    // $gallery_ids = [];
    // if (!empty($item['illustrations'])) {
    //   foreach ($item['illustrations'] as $index => $illustration) {
    //     $image_url = $illustration['traductionFichiers'][0]['url'] ?? '';
    //     if ($image_url) {
    //       $image_id = media_sideload_image($image_url, $post_id, null, 'id');
    //       if (!is_wp_error($image_id)) {
    //         if ($index === 0) {
    //           set_post_thumbnail($post_id, $image_id);
    //         } else {
    //           $gallery_ids[] = $image_id;
    //         }
    //       }
    //     }
    //   }
    // }
    // if (!empty($gallery_ids)) {
    //   update_post_meta($post_id, 'gallery', $gallery_ids);
    // }

    // ✅ Type + compléments
    update_post_meta($post_id, 'type', $item['type'] ?? '');
    update_post_meta(
      $post_id,
      'presentation_complement',
      $item['localisation']['geolocalisation']['complement']['libelleFr'] ?? ''
    );

    // Langages : ['prestations']['languesParlees']
    if (!empty($item['prestations']['languesParlees'])) {
      $langues = wp_list_pluck($item['prestations']['languesParlees'], 'libelleFr');
      update_post_meta($post_id, 'langues', implode(', ', $langues));
    }

    // Ouvertures : ouverture.periodesOuvertures
    if (!empty($item['ouverture']['periodesOuvertures'])) {
      $periodes = $item['ouverture']['periodesOuvertures'];
      foreach ($periodes as $periode) {
        $periodes_dateDebut = $periode['dateDebut'] ?? '';
        $periodes_dateFin = $periode['dateFin'] ?? '';
        $periodes_type = $periode['type'] ?? '';
      }
      update_post_meta($post_id, 'periodes_date_debut', $periodes_dateDebut);
      update_post_meta($post_id, 'periodes_date_fin', $periodes_dateFin);
      update_post_meta($post_id, 'periodes_type', $periodes_type);
    }

    //Periodes price : descriptionTarif.periodes 
    if (!empty($item['descriptionTarif']['periodes'])) {
      $periodes = $item['descriptionTarif']['periodes'][0]['tarifs'];
      if ($periodes) {
        $min = null;
        $max = null;

        foreach ($periodes as $item) {
          if (
            isset($item['type']['elementReferenceType'], $item['type']['id']) &&
            $item['type']['elementReferenceType'] === 'TarifType' &&
            $item['type']['id'] === 1455
          ) {
            $min = $item['minimum'];
            $max = $item['maximum'];
            break; // on arrête dès qu’on a trouvé
          }
        }

        update_post_meta($post_id, 'price_mini', $min);
        update_post_meta($post_id, 'price_max', $max);
        update_post_meta($post_id, 'price_mini_mobilhomes', $min);
        update_post_meta($post_id, 'price_max_mobilhomes', $max);
      }
    }

    // Update Taxonomy 
    /**
     * destination
     * atout,
     * etoile,
     * aquatique,
     * service,
     * label,
     * hebergement,
     * cible,
     * groupe,
     */

    // ---------- Update Taxonomy ----------
    $tax_inputs = [
      'destination' => $item['localisation']['adresse']['commune']['nom'] ?? null, // string → 1 seul terme
      'atout'       => $item['localisation']['environnements'] ?? [],              // array d'objets Apidae
      'service'     => $item['prestations']['services'] ?? [],                     // array d'objets Apidae
      'equipement'  => $item['prestations']['equipements'] ?? [],                  // array d'objets Apidae
      'etoile'      => $item['informationsHotelleriePleinAir']['classement'] ?? [], // objet Apidae (libelleFr "3 étoiles"…)
      'hebergement' => $item['informationsHotelleriePleinAir']['hotelleriePleinAirType'] ?? null, // objet → 1 terme
      'label'       => $item['informationsHotelleriePleinAir']['labels'] ?? [],    // array d'objets Apidae
      'confort'     => $item['prestations']['conforts'] ?? [],                      // array d'objets Apidae
    ];


    // Normalise: si une valeur n’est pas un array, on l’emballe
    foreach ($tax_inputs as $tax => $vals) {
      // true = on synchronise (on remplace). Mets false si tu veux juste ajouter.
      self::set_post_terms_safe($post_id, $tax, $vals, true);
    }


    return ['ok' => true, 'post_id' => $post_id, 'action' => $action];
  }

  public static function update_illustrations_apidae_camping(array $item, $acf_field = 'galerie_photo_camping')
  {
    $existing = get_posts([
      'post_type'      => 'camping',
      'meta_key'       => 'apidae_id',
      'meta_value'     => $item['id'] ?? 0,
      'posts_per_page' => 1,
      'fields'         => 'ids',
      'suppress_filters' => true,
      'no_found_rows'  => true,
    ]);
    if (! $existing) {
      return ['ok' => false, 'reason' => 'not_found'];
    }
    $post_id = $existing[0];

    $images  = $item['illustrations'] ?? [];
    $sources = [];
    foreach ($images as $image) {
      $u = $image['traductionFichiers'][0]['url'] ?? '';
      if ($u) {
        $sources[] = $u;
      }
    }
    if (!$sources) {
      return ['ok' => true, 'post_id' => $post_id, 'skipped' => 'no_images'];
    }

    $res = self::add_images_to_acf_gallery($post_id, $acf_field, $sources, 'url');
    if (is_wp_error($res)) {
      return ['ok' => false, 'reason' => 'acf_update_failed', 'error' => $res->get_error_message()];
    }
    return ['ok' => true, 'post_id' => $post_id, 'added' => count($res)];
  }

  public static function add_images_to_acf_gallery(int $post_id, string $field, array $sources, string $mode = 'id')
  {
    if (empty($sources)) {
      return new WP_Error('no_sources', 'Aucune image fournie.');
    }
    $existing_ids = function_exists('get_field') ? get_field($field, $post_id, false) : get_post_meta($post_id, $field, true);
    if (! is_array($existing_ids)) {
      $existing_ids = [];
    }

    $new_ids = [];
    foreach ($sources as $item) {
      if ($mode === 'id') {
        $attachment_id = absint($item);
      } elseif ($mode === 'url') {
        $attachment_id = self::_acf_gallery_import_from_url($item, $post_id);
      } else {
        $attachment_id = 0;
      }
      if (is_wp_error($attachment_id)) {
        continue;
      }
      if ($attachment_id && get_post_type($attachment_id) === 'attachment') {
        $new_ids[] = $attachment_id;
      }
    }
    $final_ids = array_values(array_unique(array_merge($existing_ids, $new_ids)));
    if (function_exists('update_field')) {
      $ok = update_field($field, $final_ids, $post_id);
      if (! $ok) {
        return new WP_Error('update_failed', 'La mise à jour du champ ACF a échoué.');
      }
    } else {
      update_post_meta($post_id, $field, $final_ids);
    }
    return $final_ids;
  }

  public static function _acf_gallery_import_from_url(string $url, int $parent_post_id = 0)
  {


    $tmp = download_url($url);
    if (is_wp_error($tmp)) {
      return $tmp;
    }

    $filename = basename(parse_url($url, PHP_URL_PATH) ?: 'image');
    $file = ['name' => $filename, 'tmp_name' => $tmp];
    $id = media_handle_sideload($file, $parent_post_id);


    if (is_wp_error($id)) {
      @unlink($tmp);
      return $id;
    }
    return $id;
  }

  // APRES
  public static function update_acf_gallery_from_urls(int $post_id, string $field, array $urls, bool $replace = true, bool $set_featured = false)
  {
    if (empty($urls)) {
      return new WP_Error('no_urls', 'Aucune URL fournie.');
    }

    // Lire valeur brute existante (array d'IDs)
    $existing_ids = function_exists('get_field') ? get_field($field, $post_id, false) : get_post_meta($post_id, $field, true);
    if (!is_array($existing_ids)) {
      $existing_ids = [];
    }


    $existing_ids = array_values(array_unique(array_map('intval', $existing_ids)));

    // Importer les nouvelles images
    $new_ids = [];
    foreach ($urls as $u) {
      if (!$u) {
        continue;
      }
      $att_id = self::_acf_gallery_import_from_url($u, $post_id);
      if (!is_wp_error($att_id) && get_post_type($att_id) === 'attachment') {
        $new_ids[] = (int) $att_id;
      }
    }

    // Construire la valeur finale
    $final_ids = $replace
      ? $new_ids
      : array_values(array_unique(array_merge($existing_ids, $new_ids)));

    // Si rien ne change, ne pas considérer ça comme une erreur
    $unchanged = ($final_ids === $existing_ids);

    // Déterminer le sélecteur ACF le plus sûr (field key si possible)
    $selector_for_update = $field;
    if (function_exists('get_field_object')) {
      $fo = get_field_object($field, $post_id, false, false); // accepte key ou name
      if (is_array($fo) && !empty($fo['key'])) {
        $selector_for_update = $fo['key']; // utiliser la field key → évite les ambiguïtés
      }
    }


    // Sauvegarde
    if (function_exists('update_field')) {
      $ok = true;
      if (!$unchanged) {
        // On n’essaie d’updater que si ça change vraiment
        $ok = update_field($selector_for_update, $final_ids, $post_id);
        // Selon versions d'ACF, $ok peut être true ou la valeur ; on tolère false si checkons que la base reflète bien la valeur
        if ($ok === false) {
          // Lecture post-update (au cas où ACF retourne false mais a écrit)
          $check = get_field($field, $post_id, false);
          $check = is_array($check) ? array_values(array_map('intval', $check)) : [];
          if ($check === $final_ids) {
            $ok = true;
          }
        }
      }

      if ($ok === false) {
        // Ici, c'est un vrai échec (ex : mauvais selector, champ non trouvé)
        return new WP_Error('update_failed', 'La mise à jour du champ ACF a échoué.');
      }
    } else {
      // Sans ACF, fallback meta (le champ doit accepter un array d'IDs)
      update_post_meta($post_id, $field, $final_ids);
    }

    // Featured image si demandé et si on a importé au moins une nouvelle image
    $featured_id = null;
    if ($set_featured && !empty($new_ids)) {
      set_post_thumbnail($post_id, $new_ids[0]);
      $featured_id = $new_ids[0];
    }

    return [
      'post_id'  => $post_id,
      'added'    => count($new_ids),
      'total'    => count($final_ids),
      'mode'     => $replace ? 'replace' : 'merge',
      'featured' => $featured_id,
      'unchanged' => $unchanged,
    ];
  }

  // Helpers à mettre dans la même classe (en static private si tu préfères)
  protected static function upsert_terms_for_taxonomy($taxonomy, array $terms): array
  {
    if (! taxonomy_exists($taxonomy)) {
      return []; // taxonomy non déclarée → on ignore
    }
    $term_ids = [];
    foreach ($terms as $t) {
      // Accepte soit une string, soit un array (ex: ['libelleFr' => 'Piscine'])
      if (is_array($t)) {
        $name = $t['name'] ?? $t['libelleFr'] ?? $t['nom'] ?? $t['title'] ?? '';
        $slug = $t['slug'] ?? '';
      } else {
        $name = (string) $t;
        $slug = '';
      }
      $name = trim(wp_strip_all_tags($name));
      if ($name === '') {
        continue;
      }

      // Vérifie si le term existe (par slug d’abord pour éviter doublons d’accents)
      $slug = $slug ? sanitize_title($slug) : sanitize_title($name);
      $existing = term_exists($slug, $taxonomy);
      if (!$existing) {
        $existing = term_exists($name, $taxonomy); // fallback par nom
      }

      if ($existing && !is_wp_error($existing)) {
        $term_ids[] = (int) ($existing['term_id'] ?? $existing);
        continue;
      }

      // Crée le term si absent
      $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
      if (!is_wp_error($created)) {
        $term_ids[] = (int) $created['term_id'];
      }
    }
    return array_values(array_unique($term_ids));
  }

  /**
   * Suppression “safe” en lots.
   */
  public static function delete_all_campings($batchSize = 200, $sleep = 0)
  {
    $total_deleted = 0;
    do {
      $posts = get_posts([
        'post_type'      => 'camping',
        'posts_per_page' => $batchSize,
        'post_status'    => 'any',
        'fields'         => 'ids',
        'suppress_filters' => true,
        'no_found_rows'  => true,
      ]);
      if (empty($posts)) {
        break;
      }

      foreach ($posts as $post_id) {
        wp_delete_post($post_id, true);
        $total_deleted++;
      }
      if ($sleep) {
        sleep($sleep);
      }
    } while (count($posts) === $batchSize);

    return $total_deleted;
  }
}

/**
 * WP-CLI: wp apidae ...
 */
if (defined('WP_CLI') && WP_CLI) {

  class APIDAE_CLI_Command extends WP_CLI_Command
  {

    /**
     * Importe un objet touristique APIDAE par ID (create ou upsert).
     *
     * ## OPTIONS
     * --id=<id>
     * [--mode=<mode>]        : create-only | upsert (defaut: upsert)
     * [--dry-run]            : ne fait qu’afficher sans écrire
     * [--sleep=<sec>]        : pause entre appels (defaut: 0)
     *
     * ## EXAMPLES
     *   wp apidae import-object --id=5752595
     *   wp apidae import-object --id=5752595 --mode=create-only --dry-run
     */
    public function import_object($args, $assoc_args)
    {
      $id     = (int) ($assoc_args['id'] ?? 0);
      $mode   = $assoc_args['mode'] ?? 'upsert';
      $dry    = isset($assoc_args['dry-run']);
      $sleep  = (int) ($assoc_args['sleep'] ?? 0);

      if (! $id) {
        WP_CLI::error('Paramètre --id manquant.');
      }

      $fields = 'id,nom,reservation.organismes,illustrations,prestations.conforts,informationsHotelleriePleinAir.labels,informationsHotelleriePleinAir.chaines,informationsHotelleriePleinAir.hotelleriePleinAirType,informations.moyensCommunication,informationsHotelleriePleinAir.classement,presentation.descriptifCourt,presentation.descriptifDetaille,localisation.geolocalisation.geoJson.coordinates,localisation.environnements,localisation.perimetreGeographique,localisation.territoiresAffectes,prestations.equipements,prestations.services,prestations.conforts,prestations.activites,prestations.languesParlees,prestations.animauxAcceptes,ouverture.periodesOuvertures,descriptionTarif.periodes,descriptionTarif.tarifsEnClair,descriptionTarif.modesPaiement,reservation.organismes,informations.informationsLegales.siret,contacts,identifier,type,localisation.geolocalisation.complement';
      $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . $id, [
        'responseFields' => $fields,
        'locales'        => 'fr',
      ]);
      if (! $res['success']) {
        WP_CLI::error('APIDAE error: ' . $res['message']);
      }

      $item = $res['data'] ?? null;
      if (! $item) {
        WP_CLI::error('Objet introuvable.');
      }

      $r = APIDAE_Service::import_apidae_camping($item, $mode, $dry);
      if (! $r['ok']) {
        WP_CLI::error('Import failed: ' . ($r['error'] ?? $r['reason'] ?? 'unknown'));
      }

      WP_CLI::success(sprintf("OK (%s) — post_id=%s", $r['action'] ?? ($r['skipped'] ?? 'done'), $r['post_id'] ?? '-'));
      if ($sleep) {
        sleep($sleep);
      }
    }

    private static function ensure_full_item(array $item): array
    {
      // si environnements et services/équipements manquent, on hydrate
      $need = empty($item['localisation']['environnements'])
        || empty($item['prestations']['services'])
        || empty($item['prestations']['equipements'])
        || empty($item['informationsHotelleriePleinAir']['classement'])
        || empty($item['informationsHotelleriePleinAir']['hotelleriePleinAirType']);

      if (!$need) {
        return $item;
      }

      $fields = implode(',', [
        'id',
        'nom',
        'type',
        'identifier',
        'localisation.environnements',
        'prestations.services',
        'prestations.equipements',
        'informationsHotelleriePleinAir.classement',
        'informationsHotelleriePleinAir.hotelleriePleinAirType',
        'informationsHotelleriePleinAir.labels',
        'prestations.languesParlees',
        'informationsHotelleriePleinAir.capacite.nombreEmplacementsDeclares',
        'prestations.conforts',
        'reservation.organismes',
        'ouverture.periodesOuvertures',
        'descriptionTarif.periodes'
      ]);

      $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . (int)$item['id'], [
        'responseFields' => $fields,
        'locales'        => 'fr',
      ]);
      if ($res['success'] && !empty($res['data'])) {
        // on fusionne, les sous-clés manquantes sont complétées
        $item = array_replace_recursive($item, $res['data']);
      }
      return $item;
    }


    /**
     * Importe une sélection APIDAE (list-objets-touristiques) en lot.
     *
     * ## OPTIONS
     * --selection-ids=<ids>  : Liste d’IDs de sélection séparés par virgules
     * [--count=<n>]          : Nb max items (defaut 999)
     * [--limit=<n>]          : Limite locale de traitement (utile pour tester)
     * [--offset=<n>]         : Décalage local de départ
     * [--mode=<mode>]        : create-only | upsert (defaut: upsert)
     * [--sleep=<sec>]        : pause entre imports (defaut: 0)
     * [--dry-run]
     *
     * ## EXAMPLES
     *   wp apidae import-selection --selection-ids=190542,190543 --count=999 --mode=upsert
     */
    public function import_selection($args, $assoc_args)
    {
      $selection_ids = array_filter(array_map('intval', explode(',', $assoc_args['selection-ids'] ?? '')));
      if (empty($selection_ids)) {
        WP_CLI::error('Paramètre --selection-ids manquant ou vide.');
      }

      // count demandé par l’utilisateur (plafonné à 200 par l’API)
      $requestedCount = (int)($assoc_args['count'] ?? 200);
      $pageCount      = max(0, min($requestedCount, 200)); // <= 200 sinon l’API le plafonne
      if ($pageCount === 0) $pageCount = 200; // doc: défaut 20, mais on choisit 200 pour efficacité

      // bornes
      $cliOffset = (int)($assoc_args['offset'] ?? 0); // décalage global demandé
      $cliLimit  = isset($assoc_args['limit']) ? (int)$assoc_args['limit'] : null; // max d’items à traiter
      $mode      = $assoc_args['mode'] ?? 'upsert';
      $sleep     = (int)($assoc_args['sleep'] ?? 0);
      $dry       = isset($assoc_args['dry-run']);

      // 1er appel pour connaître numFound
      $params = ['selectionIds' => $selection_ids, 'count' => $pageCount, 'first' => $cliOffset];
      $res = APIDAE_Service::connect_to_apidae('/recherche/list-objets-touristiques', $params, 'GET', true);
      if (!$res['success']) WP_CLI::error('APIDAE error: ' . $res['message']);

      $numFound = (int)($res['data']['numFound'] ?? 0);
      if ($numFound === 0) {
        WP_CLI::log('Aucun résultat.');
        return;
      }

      // combien on va traiter au total (respecte limit si fournie)
      $remainingToProcess = $cliLimit !== null ? min($cliLimit, $numFound - $cliOffset) : ($numFound - $cliOffset);
      if ($remainingToProcess <= 0) {
        WP_CLI::log("Rien à traiter (offset dépasse numFound).");
        return;
      }

      WP_CLI::log("numFound={$numFound} — offset={$cliOffset} — limit=" . ($cliLimit ?? '∞') . " — pageCount={$pageCount}");

      $done = $created = $updated = $skipped = $errors = 0;

      // On réutilise la 1ère page si elle existe déjà
      $first = $cliOffset;
      $processPage = function ($data) use (&$done, &$created, &$updated, &$skipped, &$errors, $mode, $dry, $sleep, &$remainingToProcess) {
        $items = $data['objetsTouristiques'] ?? [];
        foreach ($items as $item) {
          if ($remainingToProcess <= 0) break;
          if (!$item) continue;

          $item = self::ensure_full_item($item);
          $r = APIDAE_Service::import_apidae_camping($item, $mode, $dry);

          $done++;
          $remainingToProcess--;

          if (!$r['ok']) {
            $errors++;
            WP_CLI::warning("ID {$item['id']}: " . ($r['error'] ?? $r['reason'] ?? 'failed'));
          } else {
            if (isset($r['skipped'])) {
              $skipped++;
            } elseif (($r['action'] ?? '') === 'created') {
              $created++;
            } else {
              $updated++;
            }
            WP_CLI::log(sprintf("[%d] %s — %s", $done, $item['id'], $r['action'] ?? ($r['skipped'] ?? 'ok')));
          }

          if ($sleep) sleep($sleep);
        }
      };

      // Traite la première réponse
      $processPage($res['data']);
      $first += $pageCount;

      // Boucle de pagination tant qu’il reste à traiter
      while ($remainingToProcess > 0 && $first < $numFound) {
        $batchCount = min($pageCount, $remainingToProcess); // on peut réduire la dernière page
        $params = ['selectionIds' => $selection_ids, 'count' => $batchCount, 'first' => $first];
        $res = APIDAE_Service::connect_to_apidae('/recherche/list-objets-touristiques', $params, 'GET', true);
        if (!$res['success']) {
          WP_CLI::warning('APIDAE error en pagination: ' . $res['message']);
          break;
        }
        $processPage($res['data']);
        $first += $batchCount;
      }

      WP_CLI::success("Terminé. created=$created updated=$updated skipped=$skipped errors=$errors");
    }

    /**
     * Met à jour les illustrations ACF pour un camping par ID APIDAE.
     *
     * ## OPTIONS
     * --id=<id>
     * [--acf-field=<key>] : clé ACF galerie (defaut: galerie_photo_camping)
     *
     * ## EXAMPLE
     *   wp apidae update-images --id=5752595 --acf-field=galerie_photo_camping
     */
    public function update_images($args, $assoc_args)
    {
      $id  = (int)($assoc_args['id'] ?? 0);
      $acf = $assoc_args['acf-field'] ?? 'galerie_photo_camping';
      if (!$id) {
        WP_CLI::error('Paramètre --id manquant.');
      }

      $fields = 'id,illustrations';
      $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . $id, [
        'responseFields' => $fields,
        'locales'        => 'fr',
      ]);
      if (! $res['success']) {
        WP_CLI::error('APIDAE error: ' . $res['message']);
      }
      $item = $res['data'] ?? null;
      if (!$item) {
        WP_CLI::error('Objet introuvable.');
      }

      $r = APIDAE_Service::update_illustrations_apidae_camping($item, $acf);
      if (! $r['ok']) {
        WP_CLI::error('MAJ images échouée: ' . ($r['error'] ?? $r['reason'] ?? 'unknown'));
      }
      WP_CLI::success('Images mises à jour pour post_id ' . $r['post_id']);
    }

    /**
     * Supprime tous les posts “camping” en lots.
     *
     * ## OPTIONS
     * [--batch-size=<n>] : defaut 200
     * [--sleep=<sec>]    : pause entre lots
     *
     * ## EXAMPLE
     *   wp apidae delete-all --batch-size=300 --sleep=1
     */
    public function delete_all($args, $assoc_args)
    {
      $batch = (int)($assoc_args['batch-size'] ?? 200);
      $sleep = (int)($assoc_args['sleep'] ?? 0);
      $count = APIDAE_Service::delete_all_campings($batch, $sleep);
      WP_CLI::success("Supprimé $count posts de type 'camping'.");
    }

    // ➕ NEW in APIDAE_CLI_Command
    /**
     * Met à jour les galeries ACF pour tous les objets d'une sélection APIDAE.
     *
     * ## OPTIONS
     * --selection-ids=<ids>   : Liste d’IDs de sélection séparés par virgules
     * [--acf-field=<key>]     : Clé ACF (defaut: galerie_photo_camping)
     * [--merge]               : Fusionner au lieu de remplacer (defaut: replace)
     * [--limit=<n>]           : Limiter le nombre traité localement
     * [--offset=<n>]          : Décalage local
     * [--sleep=<sec>]         : Pause entre items
     * [--dry-run]             : Ne rien écrire
     * [--set-featured]        : Définir la première image importée comme image à la une
     *
     * ## EXAMPLE
     *   wp apidae update-all-images-from-selection --selection-ids=190542,190543 --acf-field=galerie_photo_camping
     *
     * @subcommand update-all-images-from-selection
     */
    public function update_all_images_from_selection($args, $assoc_args)
    {
      $selection_ids = array_filter(array_map('intval', explode(',', $assoc_args['selection-ids'] ?? '')));
      if (empty($selection_ids)) {
        WP_CLI::error('Paramètre --selection-ids manquant.');
      }

      $acf    = $assoc_args['acf-field'] ?? 'galerie_photo_camping';
      $merge  = isset($assoc_args['merge']);
      $limit  = isset($assoc_args['limit']) ? (int)$assoc_args['limit'] : null;
      $offset = (int)($assoc_args['offset'] ?? 0);
      $sleep  = (int)($assoc_args['sleep'] ?? 0);
      $dry    = isset($assoc_args['dry-run']);
      $set_featured = isset($assoc_args['set-featured']); // ➕ NEW


      $fields = implode(',', [
        // identifiants / base
        'id',
        'nom',
        'type',
        'identifier',

        // images (si tu veux poser la featured)
        'illustrations.traductionFichiers.url',

        // localisation (taxos + coordonnées + commune)
        'localisation.environnements',
        'localisation.geolocalisation.geoJson.coordinates',
        'localisation.adresse.commune.nom',
        'localisation.adresse.codePostal',
        'localisation.adresse.adresse1',
        'localisation.adresse.commune.pays.libelleFr',
        'localisation.geolocalisation.complement.libelleFr',

        // prestations (taxos)
        'prestations.services',
        'prestations.equipements',

        // HPA (taxos + métas)
        'informationsHotelleriePleinAir.classement',
        'informationsHotelleriePleinAir.hotelleriePleinAirType',
        'informationsHotelleriePleinAir.labels',
        'informationsHotelleriePleinAir.capacite',
        'informationsHotelleriePleinAir.numeroClassement',
        'informationsHotelleriePleinAir.dateClassement',

        // contacts (si utiles)
        'informations.moyensCommunication',

        // présentation
        'presentation.descriptifCourt.libelleFr',
        'presentation.descriptifDetaille.libelleFr',

      ]);




      $res = APIDAE_Service::connect_to_apidae(
        '/recherche/list-objets-touristiques',
        [
          'selectionIds'   => $selection_ids,
          'count'          => 999,
          'responseFields' => $fields,
          'locales'        => 'fr',
        ],
        'GET',
        true
      );


      if (!$res['success']) {
        WP_CLI::error('APIDAE error: ' . $res['message']);
      }

      $items = $res['data']['objetsTouristiques'] ?? [];
      $total = count($items);
      if ($offset) {
        $items = array_slice($items, $offset);
      }
      if ($limit !== null) {
        $items = array_slice($items, 0, $limit);
      }

      WP_CLI::log("Trouvé: {$total} objets — traitement " . count($items) . " (offset=$offset, limit=" . ($limit ?? '∞') . ")");

      $done = 0;
      $ok = 0;
      $notfound = 0;
      $noimg = 0;
      $err = 0;

      foreach ($items as $item) {
        $done++;
        $apidae_id = $item['id'] ?? 0;
        if (!$apidae_id) {
          $err++;
          WP_CLI::warning("Item sans id APIDAE");
          continue;
        }

        // retrouver le post
        $posts = get_posts([
          'post_type' => 'camping',
          'meta_key'  => 'apidae_id',
          'meta_value' => $apidae_id,
          'fields'    => 'ids',
          'posts_per_page' => 1,
          'suppress_filters' => true,
          'no_found_rows' => true,
        ]);
        if (!$posts) {
          $notfound++;
          WP_CLI::log("[$done] {$apidae_id} — post introuvable");
          continue;
        }
        $post_id = $posts[0];

        // collecter les URLs
        $urls = [];
        foreach (($item['illustrations'] ?? []) as $illu) {
          $u = $illu['traductionFichiers'][0]['url'] ?? '';
          if ($u) {
            $urls[] = $u;
          }
        }

        // WP_CLI::log(print_r($urls,true));
        // die();

        if (!$urls) {
          $noimg++;
          WP_CLI::log("[$done] {$apidae_id} — aucune image");
          continue;
        }

        if ($dry) {
          WP_CLI::log("[$done] {$apidae_id} — DRY RUN " . count($urls) . " images");
        } else {
          $r = APIDAE_Service::update_acf_gallery_from_urls($post_id, $acf, $urls, $replace = !$merge, $set_featured);

          if (is_wp_error($r)) {
            $err++;
            WP_CLI::warning("{$apidae_id} — erreur: " . $r->get_error_message());
          } else {
            $ok++;
            $extra = [];
            if (!empty($r['featured'])) {
              $extra[] = "featured={$r['featured']}";
            }
            if (!empty($r['unchanged'])) {
              $extra[] = "unchanged";
            }
            WP_CLI::log("[$done] {$apidae_id} — {$r['mode']} added={$r['added']} total={$r['total']}" . ($extra ? ' ' . implode(' ', $extra) : ''));
          }
        }

        if ($sleep) {
          sleep($sleep);
        }
      }

      WP_CLI::success("Fini. ok=$ok notfound=$notfound noimg=$noimg errors=$err");
    }

    // ➕ NEW in APIDAE_CLI_Command
    /**
     * Met à jour les galeries ACF pour tous les posts `camping` existants (via get-by-id).
     *
     * ## OPTIONS
     * [--acf-field=<key>]   : Clé ACF (defaut: galerie_photo_camping)
     * [--merge]             : Fusionner au lieu de remplacer
     * [--limit=<n>]         : Limiter le nombre traité
     * [--offset=<n>]        : Décalage local
     * [--sleep=<sec>]       : Pause entre items
     * [--dry-run]
     * [--set-featured]     : Définir la première image importée comme image à la une
     *
     * ## EXAMPLE
     *   wp apidae update-all-images-from-posts --limit=200
     *
     * @subcommand update-all-images-from-posts
     */
    public function update_all_images_from_posts($args, $assoc_args)
    {
      $acf    = $assoc_args['acf-field'] ?? 'galerie_photo_camping';
      $merge  = isset($assoc_args['merge']);
      $limit  = isset($assoc_args['limit']) ? (int)$assoc_args['limit'] : null;
      $offset = (int)($assoc_args['offset'] ?? 0);
      $sleep  = (int)($assoc_args['sleep'] ?? 0);
      $dry    = isset($assoc_args['dry-run']);
      $set_featured = isset($assoc_args['set-featured']); // ➕ NEW


      $query = [
        'post_type'      => 'camping',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'any',
        'suppress_filters' => true,
        'no_found_rows'  => true,
        'meta_key'       => 'apidae_id',
        'meta_compare'   => 'EXISTS',
      ];
      $all = get_posts($query);
      $total = count($all);

      if ($offset) {
        $all = array_slice($all, $offset);
      }
      if ($limit !== null) {
        $all = array_slice($all, 0, $limit);
      }

      WP_CLI::log("Posts trouvés: {$total} — traitement " . count($all) . " (offset=$offset, limit=" . ($limit ?? '∞') . ")");

      $done = 0;
      $ok = 0;
      $noid = 0;
      $noimg = 0;
      $err = 0;

      foreach ($all as $post_id) {
        $done++;
        $apidae_id = get_post_meta($post_id, 'apidae_id', true);
        if (!$apidae_id) {
          $noid++;
          WP_CLI::log("[$done] post {$post_id} — apidae_id manquant");
          continue;
        }

        $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . intval($apidae_id), [
          'responseFields' => 'id,illustrations',
          'locales'        => 'fr',
        ]);
        if (!$res['success'] || empty($res['data'])) {
          $err++;
          WP_CLI::warning("{$apidae_id} — APIDAE error: " . ($res['message'] ?? 'no data'));
          continue;
        }

        $urls = [];
        foreach (($res['data']['illustrations'] ?? []) as $illu) {
          $u = $illu['traductionFichiers'][0]['url'] ?? '';
          if ($u) {
            $urls[] = $u;
          }
        }
        if (!$urls) {
          $noimg++;
          WP_CLI::log("[$done] {$apidae_id} — aucune image");
          continue;
        }

        if ($dry) {
          WP_CLI::log("[$done] {$apidae_id} — DRY RUN " . count($urls) . " images");
        } else {
          $r = APIDAE_Service::update_acf_gallery_from_urls($post_id, $acf, $urls, $replace = !$merge, $set_featured);

          if (is_wp_error($r)) {
            $err++;
            WP_CLI::warning("{$apidae_id} — erreur: " . $r->get_error_message());
          } else {
            $ok++;
            $extra = [];
            if (!empty($r['featured'])) {
              $extra[] = "featured={$r['featured']}";
            }
            if (!empty($r['unchanged'])) {
              $extra[] = "unchanged";
            }
            WP_CLI::log("[$done] {$apidae_id} — {$r['mode']} added={$r['added']} total={$r['total']}" . ($extra ? ' ' . implode(' ', $extra) : ''));
          }
        }

        if ($sleep) {
          sleep($sleep);
        }
      }

      WP_CLI::success("Fini. ok=$ok noid=$noid noimg=$noimg errors=$err");
    }
  }

  WP_CLI::add_command('apidae', 'APIDAE_CLI_Command');
}
