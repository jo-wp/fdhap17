<?php

/**
 * Plugin Name: APIDAE CLI Importer
 * Description: Commandes WP-CLI pour importer/mettre à jour/supprimer des campings depuis APIDAE sans saturer PHP/Apache.
 * Author: BeeCom
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

// ▶️ Dépendances admin pour l’upload en CLI
if (defined('WP_CLI') && WP_CLI) {
  if (!function_exists('media_handle_sideload')) {
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
      'base_url' => 'https://api.apidae-tourisme.com/api/v002',
      'api_key' => defined('APIDAE_KEY') ? APIDAE_KEY : '',
      'project_id' => defined('APIDAE_PROJECT_ID') ? APIDAE_PROJECT_ID : '',
      'headers' => ['Content-Type' => 'application/json'],
      'timeout' => 60,
    ];

    // Clés API
    $params = array_merge([
      'apiKey' => $config['api_key'],
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
    if ($vals === null)
      return [];

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

  protected static function normalize_term_items($vals): array
  {
    if ($vals === null)
      return [];

    // Emballe en liste
    if (!is_array($vals)) {
      $vals = [$vals];
    } else {
      $isAssoc = $vals !== [] && array_keys($vals) !== range(0, count($vals) - 1);
      if ($isAssoc)
        $vals = [$vals];
    }

    $out = [];
    foreach ($vals as $v) {
      // Cas { name, slug } déjà prêt
      if (is_array($v) && isset($v['name'])) {
        $name = trim(wp_strip_all_tags((string) $v['name']));
        if ($name === '')
          continue;
        $slug = isset($v['slug']) && $v['slug'] !== '' ? sanitize_title((string) $v['slug']) : sanitize_title($name);
        $out[] = ['name' => $name, 'slug' => $slug];
        continue;
      }

      // Cas objet/tableau APIDAE
      if (is_array($v)) {
        $name = $v['libelleFr'] ?? $v['nom'] ?? $v['name'] ?? $v['title'] ?? '';
        $name = trim(wp_strip_all_tags((string) $name));
        if ($name === '')
          continue;
        $out[] = ['name' => $name, 'slug' => sanitize_title($name)];
        continue;
      }

      // Cas string
      $name = trim(wp_strip_all_tags((string) $v));
      if ($name === '')
        continue;
      $out[] = ['name' => $name, 'slug' => sanitize_title($name)];
    }

    // Uniques par slug (priorité au premier rencontré)
    $seen = [];
    $uniq = [];
    foreach ($out as $it) {
      if (!isset($seen[$it['slug']])) {
        $seen[$it['slug']] = true;
        $uniq[] = $it;
      }
    }
    return $uniq;
  }



  /**
   * Assigne une liste de valeurs à une taxonomy en résolvant/creant les termes par libellé.
   * $values peut être string|array de strings|objets APIDAE.
   */
  protected static function set_post_terms_safe(int $post_id, string $taxonomy, $vals, bool $replace = true): void
  {
    if (!taxonomy_exists($taxonomy))
      return;

    $items = self::normalize_term_items($vals);
    if (empty($items))
      return;

    $term_ids = [];
    foreach ($items as $it) {
      $name = $it['name'];
      $slug = $it['slug'] ?: sanitize_title($name);

      // Slug "de base" généré depuis le nom
      $base_slug = sanitize_title($name);

      // On prépare une liste de slugs possibles à tester
      $candidate_slugs = [];

      // Cas particulier : taxonomie "destination"
      // → on teste d’abord la version SEO "camping-..."
      if ($taxonomy === 'destination') {
        $candidate_slugs[] = 'camping-' . $base_slug;
      }

      // Slug venant des données APIDAE
      $candidate_slugs[] = $slug;

      // On ajoute aussi le slug de base si différent
      if ($base_slug !== $slug) {
        $candidate_slugs[] = $base_slug;
      }

      // On enlève les doublons éventuels
      $candidate_slugs = array_values(array_unique($candidate_slugs));

      // 1) On tente de trouver un term existant par slug
      $existing = false;
      foreach ($candidate_slugs as $cand_slug) {
        $test = term_exists($cand_slug, $taxonomy);
        if ($test && !is_wp_error($test)) {
          $existing = $test;
          break;
        }
      }

      // 2) Si rien trouvé, on tente par NOM (vrai name)
      if (!$existing || is_wp_error($existing)) {
        $term_obj = get_term_by('name', $name, $taxonomy);
        if ($term_obj && !is_wp_error($term_obj)) {
          $existing = $term_obj->term_id;
        }
      }

      // 3) Soit on réutilise, soit on crée
      if ($existing && !is_wp_error($existing)) {
        $term_ids[] = (int) ($existing['term_id'] ?? $existing);
      } else {
        // On choisit le slug que l’on veut pour la création
        $created = wp_insert_term($name, $taxonomy, ['slug' => $base_slug]);
        if (!is_wp_error($created)) {
          $term_ids[] = (int) $created['term_id'];
        } else {
          error_log("[APIDAE] wp_insert_term failed for {$taxonomy} / {$name}: " . $created->get_error_message());
        }
      }
    }

    if (!empty($term_ids)) {
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
    if (!$apidae_id) {
      return ['ok' => false, 'reason' => 'no_apidae_id'];
    }

    // existe ?
    $existing = get_posts([
      'post_type' => 'camping',
      'meta_key' => 'apidae_id',
      'meta_value' => $apidae_id,
      'posts_per_page' => 1,
      'fields' => 'ids',
      'suppress_filters' => true,
      'no_found_rows' => true,
    ]);




    $title = $item['nom']['libelleFr'] ?? 'Camping sans nom';
    $description = (!empty($item['presentation']['descriptifDetaille']['libelleFr'])) ? $item['presentation']['descriptifDetaille']['libelleFr'] : $item['presentation']['descriptifCourt']['libelleFr'];

    if ($existing) {
      if ($mode === 'create-only') {
        return ['ok' => true, 'post_id' => $existing[0], 'skipped' => 'exists'];
      }
      $post_id = $existing[0];
      if (!$dry_run) {
        wp_update_post([
          'ID' => $post_id,
          'post_title' => $title,
          'post_content' => $description,
        ]);
      }
      $action = 'updated';
    } else {
      if ($dry_run) {
        return ['ok' => true, 'post_id' => 0, 'skipped' => 'dry-run-create'];
      }
      $post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $description,
        'post_type' => 'camping',
        'post_status' => 'publish',
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

    // Date Update 
    if (isset($item['gestion']['dateModification'])) {
      update_post_meta($post_id, 'apidae_update_date_modification', $item['gestion']['dateModification']);
    }

    // 🏠 Localisation
    $adresse = $item['localisation']['adresse']['adresse1'] ?? '';
    $commune = $item['localisation']['adresse']['commune']['nom'] ?? '';
    $code_postal = $item['localisation']['adresse']['codePostal'] ?? '';
    $pays = $item['localisation']['adresse']['commune']['pays']['libelleFr'] ?? '';
    $lat = $item['localisation']['geolocalisation']['geoJson']['coordinates'][1] ?? '';
    $lng = $item['localisation']['geolocalisation']['geoJson']['coordinates'][0] ?? '';
    update_post_meta($post_id, 'adresse', $adresse);
    update_post_meta($post_id, 'commune', $commune);
    update_post_meta($post_id, 'code_postal', $code_postal);
    update_post_meta($post_id, 'pays', $pays);
    update_post_meta($post_id, 'latitude', $lat);
    update_post_meta($post_id, 'longitude', $lng);


    // 📞 Contacts
    if (!empty($item['informations']['moyensCommunication'])) {
      foreach ($item['informations']['moyensCommunication'] as $moyen) {
        $type = $moyen['type']['libelleFr'] ?? '';
        $valeur = $moyen['coordonnees']['fr'] ?? '';
        if ($type === 'Téléphone')
          update_post_meta($post_id, 'telephone', $valeur);
        elseif ($type === 'Mél')
          update_post_meta($post_id, 'email', $valeur);
        elseif ($type === 'Site web (URL)')
          update_post_meta($post_id, 'site_web', $valeur);
      }
    }



    // 🏕️ HPA
    if (!empty($item['informationsHotelleriePleinAir'])) {
      $info = $item['informationsHotelleriePleinAir'];
      update_post_meta($post_id, 'hotellerie_type', $info['hotelleriePleinAirType']['libelleFr'] ?? '');
      update_post_meta($post_id, 'numero_classement', $info['numeroClassement'] ?? '');
      update_post_meta($post_id, 'date_classement', $info['dateClassement'] ?? '');
      update_post_meta($post_id, 'classement', $info['classement']['libelleFr'] ?? '');
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
        update_post_meta($post_id, 'id_reservation_direct', $id_reservation_direct ?? '');
        update_post_meta($post_id, 'url_reservation_direct', $reservation['organismes'][0]['moyensCommunication'][0]['coordonnees']['fr'] ?? '');
      }
    }

    // 🖼️ Images — skip si le post a déjà des images (featured / attachments / ACF gallery)
    // ---- IMPORTANT en WP-CLI : assure les includes media ----
    if (!function_exists('media_handle_sideload')) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';
      require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    // Helper : log en CLI
    $cli_log = function ($msg) {
      if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::log($msg);
      }
    };
    $cli_warn = function ($msg) {
      if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::warning($msg);
      }
    };

    // Helper : récup URL illustration Apidae (robuste)
    $get_apidae_image_url = function ($illustration) {
      if (!is_array($illustration)) return '';

      // Cas le plus courant (celui que tu avais)
      $u = $illustration['traductionFichiers'][0]['url'] ?? '';
      if (is_string($u) && $u) return $u;

      // Fallbacks possibles selon payload
      $u = $illustration['traductionFichiers'][0]['urlFichier'] ?? '';
      if (is_string($u) && $u) return $u;

      $u = $illustration['fichiers'][0]['url'] ?? '';
      if (is_string($u) && $u) return $u;

      $u = $illustration['fichiers'][0]['urlFichier'] ?? '';
      if (is_string($u) && $u) return $u;

      $u = $illustration['url'] ?? '';
      if (is_string($u) && $u) return $u;

      return '';
    };

    // ---- Détection images existantes ----
    $gallery_ids = [];
    $already_imported = (bool) get_post_meta($post_id, 'apidae_images_imported', true);
    $thumb_id = (int) get_post_thumbnail_id($post_id);

    $attached_ids = get_posts([
      'post_type'        => 'attachment',
      'post_parent'      => $post_id,
      'post_status'      => 'inherit',
      'posts_per_page'   => 1,
      'fields'           => 'ids',
      'post_mime_type'   => 'image',
      'no_found_rows'    => true,
      'suppress_filters' => true,
    ]);

    $acf_gallery_ids = [];
    if (function_exists('get_field')) {
      $acf_gallery_ids = get_field('gallery', $post_id, false);
      if (!is_array($acf_gallery_ids)) $acf_gallery_ids = [];
      $acf_gallery_ids = array_values(array_filter(array_map('intval', $acf_gallery_ids)));
    }

    $has_any_image = ($thumb_id > 0) || !empty($attached_ids) || !empty($acf_gallery_ids);

    // Si déjà des images : bootstrap meta et STOP
    if ($has_any_image) {
      if (!$already_imported) {
        update_post_meta($post_id, 'apidae_images_imported', 1);
        $existing_urls = get_post_meta($post_id, 'apidae_image_urls', true);
        if (!is_array($existing_urls)) update_post_meta($post_id, 'apidae_image_urls', []);
      }
      $cli_log("Post {$post_id}: images déjà présentes => skip import images");
      // Si tu es dans une fonction, tu peux faire return;
    } else {

      // ---- Import images ----
      $apidae_id = $item['id'] ?? 'unknown';
      $existing_urls = get_post_meta($post_id, 'apidae_image_urls', true);
      if (!is_array($existing_urls)) $existing_urls = [];

      if (empty($item['illustrations']) || !is_array($item['illustrations'])) {
        $cli_warn("Apidae {$apidae_id} / post {$post_id}: aucune illustration dans payload");
      } else {

        foreach ($item['illustrations'] as $index => $illustration) {
          $image_url = $get_apidae_image_url($illustration);

          if (!$image_url) {
            $cli_warn("Apidae {$apidae_id} / post {$post_id}: illustration #{$index} => URL introuvable (structure différente)");
            continue;
          }

          if (in_array($image_url, $existing_urls, true)) {
            $cli_log("Apidae {$apidae_id} / post {$post_id}: image déjà importée => {$image_url}");
            continue;
          }

          $cli_log("Apidae {$apidae_id} / post {$post_id}: sideload image #{$index} => {$image_url}");

          // Sideload
          $image_id = media_sideload_image($image_url, $post_id, null, 'id');

          if (is_wp_error($image_id)) {
            $cli_warn("Apidae {$apidae_id} / post {$post_id}: sideload FAIL => " . $image_id->get_error_message());
            continue;
          }

          $image_id = (int) $image_id;
          if ($image_id <= 0) {
            $cli_warn("Apidae {$apidae_id} / post {$post_id}: sideload FAIL => returned 0");
            continue;
          }

          // Featured = première image si pas déjà de thumbnail
          if ($index === 0 && !$thumb_id) {
            set_post_thumbnail($post_id, $image_id);
            $thumb_id = $image_id;
            $cli_log("Apidae {$apidae_id} / post {$post_id}: set FEATURED => attachment {$image_id}");
          } else {
            $gallery_ids[] = $image_id;
            $cli_log("Apidae {$apidae_id} / post {$post_id}: add GALLERY => attachment {$image_id}");
          }

          $existing_urls[] = $image_url;
        }
      }

      update_post_meta($post_id, 'apidae_image_urls', array_values(array_unique($existing_urls)));
      update_post_meta($post_id, 'apidae_images_imported', 1);

      // ---- Update ACF gallery (si ACF chargé) ----
      if (!empty($gallery_ids)) {
        if (function_exists('get_field') && function_exists('update_field')) {
          $existing = get_field('gallery', $post_id, false);
          if (!is_array($existing)) $existing = [];
          $existing = array_values(array_filter(array_map('intval', $existing)));

          $merged = array_values(array_unique(array_merge($existing, $gallery_ids)));
          if ($merged !== $existing) {
            // update_field('gallery', $merged, $post_id);
            update_post_meta($post_id, 'gallery', $merged);
            $cli_log("Apidae {$apidae_id} / post {$post_id}: ACF gallery updated (" . count($merged) . " ids)");
          }
        } else {
          $cli_warn("Apidae {$apidae_id} / post {$post_id}: ACF non chargé => impossible d'update la gallery");
        }
      }
    }



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

        foreach ($periodes as $currentItem) {
          if (
            isset($currentItem['type']['elementReferenceType'], $currentItem['type']['id']) &&
            $currentItem['type']['elementReferenceType'] === 'TarifType' &&
            $currentItem['type']['id'] === 1455
          ) {
            $min = $currentItem['minimum'];
            $max = $currentItem['maximum'];
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
      'destination' => $item['localisation']['adresse']['commune']['nom'] ?? null,
      'atout' => $item['localisation']['environnements'] ?? [],
      'service' => $item['prestations']['services'] ?? [],
      'equipement' => $item['prestations']['equipements'] ?? [],
      'etoile' => $item['informationsHotelleriePleinAir']['classement'] ?? [],
      'hebergement' => $item['informationsHotelleriePleinAir']['hotelleriePleinAirType'] ?? null,
      'label' => $item['informationsHotelleriePleinAir']['labels'] ?? [],
      'confort' => $item['prestations']['conforts'] ?? [],
      'paiement' => $item['descriptionTarif']['modesPaiement'] ?? []
    ];

    // ✅ Reroutage multi-sources (service, equipement, atout, confort)
    self::apply_multi_source_redirects($tax_inputs, ['service', 'equipement', 'atout', 'confort']);

    // Écriture finale
    foreach ($tax_inputs as $tax => $vals) {
      self::set_post_terms_safe($post_id, $tax, $vals, true); // replace = true
    }

    return ['ok' => true, 'post_id' => $post_id, 'action' => $action];
  }


  /**
   * Map de redirection des services vers d'autres taxonomies.
   * key = slug APIDAE du service source
   * value = ['taxonomy'=>..., 'name'=>..., 'slug'=>...]
   */
  // 1) Une map par TAXONOMIE SOURCE (tu mets ici tes règles)
  protected static function service_redirect_map(): array
  {
    return [
      // --- existant (pêche, bien-être, cibles, hébergements) ---
      'vente-de-cartes-de-peche'      => ['taxonomy' => 'atout',       'name' => 'Etang de pêche',            'slug' => 'etang-peche'],
      'vente-de-materiel-de-peche'    => ['taxonomy' => 'atout',       'name' => 'Etang de pêche',            'slug' => 'etang-peche'],
      'massages-modelages'            => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'soins-esthetiques'             => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'espace-coworking'              => ['taxonomy' => 'cible',       'name' => 'Entreprise',                'slug' => 'entreprise'],
      'accessible-en-poussette'       => ['taxonomy' => 'cible',       'name' => 'Avec bébé',                 'slug' => 'camping-avec-bebe'],

      'hebergement-locatif-climatise' => ['taxonomy' => 'hebergement', 'name' => 'Mobil-home climatisé',      'slug' => 'mobil-home-clim'],
      'location-de-mobilhome'         => ['taxonomy' => 'hebergement', 'name' => 'Mobil-home',                'slug' => 'mobil-home'],
      'location-bungatoile'           => ['taxonomy' => 'hebergement', 'name' => 'Insolite',                  'slug' => 'logement-insolite'],
      'location-caravanes'            => ['taxonomy' => 'hebergement', 'name' => 'Emplacement',               'slug' => 'emplacement'],
      'location-hll-chalet'           => ['taxonomy' => 'hebergement', 'name' => 'Chalet',                    'slug' => 'chalet'],
      'location-tentes'               => ['taxonomy' => 'hebergement', 'name' => 'Tente prête à camper',      'slug' => 'tente-prete-a-camper'],
      'camping-cars-autorises'        => ['taxonomy' => 'hebergement', 'name' => 'Emplacement',               'slug' => 'emplacement'],

      // --- aquatiques ---
      'solarium'                      => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'bains-a-remous'                => ['taxonomy' => 'aquatique',   'name' => 'Jacuzzi',                   'slug' => 'jacuzzi'],
      'bain-nordique'                 => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'balneotherapie'                => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'douche-sensorielle'            => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'espace-aquatique-ludique'      => ['taxonomy' => 'aquatique',   'name' => 'Pataugeoire',               'slug' => 'pataugeoire'],
      'espace-spa'                    => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'hammam'                        => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'pataugeoire'                   => ['taxonomy' => 'aquatique',   'name' => 'Pataugeoire',               'slug' => 'pataugeoire'],
      'piscine'                       => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'piscine-chauffee'              => ['taxonomy' => 'aquatique',   'name' => 'Piscine chauffée',          'slug' => 'piscine-chauffee'],
      'piscine-collective'            => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'piscine-couverte'              => ['taxonomy' => 'aquatique',   'name' => 'Piscine couverte',          'slug' => 'piscine-couverte'],
      'piscine-enfants'               => ['taxonomy' => 'aquatique',   'name' => 'Pataugeoire',               'slug' => 'pataugeoire'],
      'piscine-plein-air'             => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'sauna'                         => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],

      // --- atouts ---
      'depot-des-dechets-menagers'    => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],
      'equipements-developpement-durable' => ['taxonomy' => 'atout',      'name' => 'Ecologique',                'slug' => 'ecologique'],
      'etang-de-peche'                => ['taxonomy' => 'atout',       'name' => 'Etang de pêche',            'slug' => 'etang-peche'],
      'gestion-des-dechets'           => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],
      'panneau-photovoltaique'        => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],
      'ponton-de-peche'               => ['taxonomy' => 'atout',       'name' => 'Etang de pêche',            'slug' => 'etang-peche'],
      'recuperateurs-deau-de-pluie'   => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],

      // --- cibles ---
      'salle-de-reunion'              => ['taxonomy' => 'cible',       'name' => 'Entreprise',                'slug' => 'entreprise'],
      'nursery'                       => ['taxonomy' => 'cible',       'name' => 'Avec bébé',                 'slug' => 'camping-avec-bebe'],

      // --- services (regroupements/renommages) ---
      'aire-de-stationnement-camping-cars' => ['taxonomy' => 'service', 'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'camping-car'                   => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'borne-de-service-camping-cars' => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'branchements-deau'             => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'branchements-electriques'      => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'vidange-des-eaux-grises'       => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'vidange-des-eaux-noires'       => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],

      'minigolf'                      => ['taxonomy' => 'service',     'name' => 'Mini golf',                 'slug' => 'mini-golf'],
      'boulodrome-terrain-de-petanque-terrain-de-boule-de-fort'
      => ['taxonomy' => 'service',     'name' => 'Terrain pétanque',          'slug' => 'terrain-petanque'],
      'discotheque'                   => ['taxonomy' => 'service',     'name' => 'Discothèque',               'slug' => 'discotheque'],
      'terrain-de-tennis'             => ['taxonomy' => 'service',     'name' => 'Terrain de tennis',         'slug' => 'terrain-tennis'],
      'bar'                           => ['taxonomy' => 'service',     'name' => 'Bar-Restaurant',            'slug' => 'restauration'],
      'borne-de-recharge-pour-2-roues-electriques'
      => ['taxonomy' => 'service',     'name' => 'Borne de recharge',         'slug' => 'borne-recharge-electrique'],
      'bornes-de-recharge-pour-vehicules-electriques'
      => ['taxonomy' => 'service',     'name' => 'Borne de recharge',         'slug' => 'borne-recharge-electrique'],
      'laverie'                       => ['taxonomy' => 'service',     'name' => 'Laverie',                   'slug' => 'laverie'],
      'restaurant'                    => ['taxonomy' => 'service',     'name' => 'Bar-Restaurant',            'slug' => 'restauration'],
      'salle-de-reception'            => ['taxonomy' => 'service',     'name' => 'Location de salles',        'slug' => 'location-de-salles'],
    ];
  }

  protected static function equipement_redirect_map(): array
  {
    return [
      // --- aquatiques ---
      'solarium'                      => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'bains-a-remous'                => ['taxonomy' => 'aquatique',   'name' => 'Jacuzzi',                   'slug' => 'jacuzzi'],
      'bain-nordique'                 => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'balneotherapie'                => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'douche-sensorielle'            => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'espace-aquatique-ludique'      => ['taxonomy' => 'aquatique',   'name' => 'Pataugeoire',               'slug' => 'pataugeoire'],
      'espace-spa'                    => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'hammam'                        => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],
      'pataugeoire'                   => ['taxonomy' => 'aquatique',   'name' => 'Pataugeoire',               'slug' => 'pataugeoire'],
      'piscine'                       => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'piscine-chauffee'              => ['taxonomy' => 'aquatique',   'name' => 'Piscine chauffée',          'slug' => 'piscine-chauffee'],
      'piscine-collective'            => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'piscine-couverte'              => ['taxonomy' => 'aquatique',   'name' => 'Piscine couverte',          'slug' => 'piscine-couverte'],
      'piscine-enfants'               => ['taxonomy' => 'aquatique',   'name' => 'Pataugeoire',               'slug' => 'pataugeoire'],
      'piscine-plein-air'             => ['taxonomy' => 'aquatique',   'name' => 'Piscine',                   'slug' => 'piscine'],
      'sauna'                         => ['taxonomy' => 'aquatique',   'name' => 'Spa',                       'slug' => 'spa'],

      // --- atouts ---
      'depot-des-dechets-menagers'    => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],
      'equipements-developpement-durable' => ['taxonomy' => 'atout',      'name' => 'Ecologique',                'slug' => 'ecologique'],
      'etang-de-peche'                => ['taxonomy' => 'atout',       'name' => 'Etang de pêche',            'slug' => 'etang-peche'],
      'gestion-des-dechets'           => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],
      'panneau-photovoltaique'        => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],
      'ponton-de-peche'               => ['taxonomy' => 'atout',       'name' => 'Etang de pêche',            'slug' => 'etang-peche'],
      'recuperateurs-deau-de-pluie'   => ['taxonomy' => 'atout',       'name' => 'Ecologique',                'slug' => 'ecologique'],

      // --- cibles ---
      'salle-de-reunion'              => ['taxonomy' => 'cible',       'name' => 'Entreprise',                'slug' => 'entreprise'],
      'nursery'                       => ['taxonomy' => 'cible',       'name' => 'Avec bébé',                 'slug' => 'camping-avec-bebe'],

      // --- hebergements ---
      'emplacement-grand-confort'     => ['taxonomy' => 'hebergement', 'name' => 'Emplacement',               'slug' => 'emplacement'],
      'emplacements-nus'              => ['taxonomy' => 'hebergement', 'name' => 'Emplacement',               'slug' => 'emplacement'],

      // --- services (regroupements/renommages) ---
      'aire-de-stationnement-camping-cars' => ['taxonomy' => 'service', 'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'camping-car'                   => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'borne-de-service-camping-cars' => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'branchements-deau'             => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'branchements-electriques'      => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'vidange-des-eaux-grises'       => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],
      'vidange-des-eaux-noires'       => ['taxonomy' => 'service',     'name' => 'Aire vidange camping car',  'slug' => 'aire-vidange-camping-car'],

      'minigolf'                      => ['taxonomy' => 'service',     'name' => 'Mini golf',                 'slug' => 'mini-golf'],
      'boulodrome-terrain-de-petanque-terrain-de-boule-de-fort'
      => ['taxonomy' => 'service',     'name' => 'Terrain pétanque',          'slug' => 'terrain-petanque'],
      'discotheque'                   => ['taxonomy' => 'service',     'name' => 'Discothèque',               'slug' => 'discotheque'],
      'terrain-de-tennis'             => ['taxonomy' => 'service',     'name' => 'Terrain de tennis',         'slug' => 'terrain-tennis'],
      'bar'                           => ['taxonomy' => 'service',     'name' => 'Bar-Restaurant',            'slug' => 'restauration'],
      'borne-de-recharge-pour-2-roues-electriques'
      => ['taxonomy' => 'service',     'name' => 'Borne de recharge',         'slug' => 'borne-recharge-electrique'],
      'bornes-de-recharge-pour-vehicules-electriques'
      => ['taxonomy' => 'service',     'name' => 'Borne de recharge',         'slug' => 'borne-recharge-electrique'],
      'laverie'                       => ['taxonomy' => 'service',     'name' => 'Laverie',                   'slug' => 'laverie'],
      'restaurant'                    => ['taxonomy' => 'service',     'name' => 'Bar-Restaurant',            'slug' => 'restauration'],
      'salle-de-reception'            => ['taxonomy' => 'service',     'name' => 'Location de salles',        'slug' => 'location-de-salles'],
    ];
  }

  protected static function atout_redirect_map(): array
  {
    return [
      // ---- Coeur de ville ----
      'centre-village'                 => ['taxonomy' => 'atout', 'name' => 'Coeur de ville', 'slug' => 'ville'],
      'centre-ville'                   => ['taxonomy' => 'atout', 'name' => 'Coeur de ville', 'slug' => 'ville'],
      'en-ville'                       => ['taxonomy' => 'atout', 'name' => 'Coeur de ville', 'slug' => 'ville'],
      'en-centre-historique'           => ['taxonomy' => 'atout', 'name' => 'Coeur de ville', 'slug' => 'ville'],

      // ---- Nature ----
      'en-foret'                       => ['taxonomy' => 'atout', 'name' => 'Nature',         'slug' => 'nature'],
      'espace-naturel-sensible'        => ['taxonomy' => 'atout', 'name' => 'Nature',         'slug' => 'nature'],
      'isole'                          => ['taxonomy' => 'atout', 'name' => 'Nature',         'slug' => 'nature'],
      'vue-sur-le-vignoble'            => ['taxonomy' => 'atout', 'name' => 'Nature',         'slug' => 'nature'],

      // ---- Bord de lac ----
      'vue-lac'                        => ['taxonomy' => 'atout', 'name' => 'Bord de lac',    'slug' => 'bord-de-lac'],
      'lac-ou-plan-deau-a-5-km'        => ['taxonomy' => 'atout', 'name' => 'Bord de lac',    'slug' => 'bord-de-lac'],
      'lac-ou-plan-deau-a-moins-de-300-m' => ['taxonomy' => 'atout', 'name' => 'Bord de lac', 'slug' => 'bord-de-lac'],

      // ---- Bord de rivière ----
      'halte-fluviale-a-moins-de-500-m' => ['taxonomy' => 'atout', 'name' => 'Bord de rivière', 'slug' => 'bord-de-riviere'],
      'riviere-ou-fleuve-a-moins-de-300-m' => ['taxonomy' => 'atout', 'name' => 'Bord de rivière', 'slug' => 'bord-de-riviere'],
      'vue-sur-fleuve-ou-riviere'      => ['taxonomy' => 'atout', 'name' => 'Bord de rivière', 'slug' => 'bord-de-riviere'],
      'riviere-a-5-km'                 => ['taxonomy' => 'atout', 'name' => 'Bord de rivière', 'slug' => 'bord-de-riviere'],

      // ---- Bord de mer ----
      'les-pieds-dans-leau-mer'        => ['taxonomy' => 'atout', 'name' => 'Bord de mer',    'slug' => 'bord-de-mer'],
      'les-pieds-dans-leau-plage'      => ['taxonomy' => 'atout', 'name' => 'Bord de mer',    'slug' => 'bord-de-mer'],
      'mer-a-moins-de-300-m'           => ['taxonomy' => 'atout', 'name' => 'Bord de mer',    'slug' => 'bord-de-mer'],
      'plage-a-moins-de-300-m'         => ['taxonomy' => 'atout', 'name' => 'Bord de mer',    'slug' => 'bord-de-mer'],

      // ---- Etang de pêche ----
      'etang-a-moins-de-300-m'         => ['taxonomy' => 'atout', 'name' => 'Etang de pêche', 'slug' => 'etang-peche'],
      'les-pieds-dans-leau-etang'      => ['taxonomy' => 'atout', 'name' => 'Etang de pêche', 'slug' => 'etang-peche'],
      'etang-a-moins-de-5-km'          => ['taxonomy' => 'atout', 'name' => 'Etang de pêche', 'slug' => 'etang-peche'],
    ];
  }

  protected static function confort_redirect_map(): array
  {
    return [
      // ---- Accès Internet / Wifi → Services > Accès Internet Wifi (wifi)
      'acces-internet-privatif-wifi'          => ['taxonomy' => 'service',     'name' => 'Accès Internet Wifi', 'slug' => 'wifi'],
      'acces-internet-privatif-wifi-gratuit'  => ['taxonomy' => 'service',     'name' => 'Accès Internet Wifi', 'slug' => 'wifi'],
      'acces-internet-privatif-wifi-payant'   => ['taxonomy' => 'service',     'name' => 'Accès Internet Wifi', 'slug' => 'wifi'],

      // ---- Laverie → Services > Laverie
      'lave-linge-collectif'                  => ['taxonomy' => 'service',     'name' => 'Laverie',             'slug' => 'laverie'],
      'seche-linge-collectif'                 => ['taxonomy' => 'service',     'name' => 'Laverie',             'slug' => 'laverie'],

      // ---- Avec bébé → Cibles > Avec bébé (camping-avec-bebe)
      'baignoire-bebe'                        => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],
      'chaise-bebe'                           => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],
      'chauffe-biberon'                       => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],
      'lit-bebe'                              => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],
      'materiel-bebe'                         => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],
      'poussette'                             => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],
      'table-a-langer'                        => ['taxonomy' => 'cible',       'name' => 'Avec bébé',           'slug' => 'camping-avec-bebe'],

      // ---- Locatif climatisé → Hebergements > Mobil-home climatisé
      'locatif-climatise'                     => ['taxonomy' => 'hebergement', 'name' => 'Mobil-home climatisé', 'slug' => 'mobil-home-clim'],
    ];
  }

  protected static function redirect_map_for(string $source_tax): array
  {
    switch ($source_tax) {
      case 'service':
        return self::service_redirect_map();
      case 'equipement':
        return self::equipement_redirect_map();
      case 'atout':
        return self::atout_redirect_map();
      case 'confort':
        return self::confort_redirect_map();
      default:
        return [];
    }
  }

  protected static function route_terms_to_other_taxonomies($raw_vals, string $source_tax): array
  {
    $map = self::redirect_map_for($source_tax);

    // Si pas de règles pour cette source, on ne touche à rien
    if (!$map) {
      return [
        'restants' => self::normalize_term_items($raw_vals),
        'additions_par_tax' => [],
      ];
    }

    $items = self::normalize_term_items($raw_vals);
    $restants = [];
    $additions_par_tax = [];

    foreach ($items as $it) {
      $src_slug = $it['slug'];
      if (isset($map[$src_slug])) {
        $dst = $map[$src_slug];
        $tax = $dst['taxonomy'];
        $additions_par_tax[$tax] = $additions_par_tax[$tax] ?? [];
        $additions_par_tax[$tax][] = ['name' => $dst['name'], 'slug' => $dst['slug']];
        // on ne garde pas cet item dans la taxonomie source
      } else {
        $restants[] = $it;
      }
    }

    // dédup par taxo
    foreach ($additions_par_tax as $tax => $list) {
      $additions_par_tax[$tax] = self::normalize_term_items($list);
    }

    return [
      'restants' => $restants,
      'additions_par_tax' => $additions_par_tax,
    ];
  }

  protected static function apply_multi_source_redirects(array &$tax_inputs, array $sources = ['service', 'equipement', 'atout', 'confort']): void
  {
    foreach ($sources as $srcTax) {
      $routed = self::route_terms_to_other_taxonomies($tax_inputs[$srcTax] ?? [], $srcTax);
      // on réécrit la source avec les "restants" (ceux NON redirigés)
      $tax_inputs[$srcTax] = $routed['restants'];

      // on fusionne les ajouts dans les taxos cibles
      foreach (($routed['additions_par_tax'] ?? []) as $dst_tax => $items) {
        $tax_inputs[$dst_tax] = array_merge(
          self::normalize_term_items($tax_inputs[$dst_tax] ?? []),
          $items
        );
      }
    }
  }


  /**
   * Prend la liste brute des services APIDAE (strings/objets) et renvoie :
   * - services_restants : ceux qui doivent rester dans la taxo 'service'
   * - additions_par_tax : ['atout'=>[items...], 'aquatique'=>[items...], ...]
   */
  protected static function route_services_to_other_taxonomies($raw_services): array
  {
    $map = self::service_redirect_map();

    // On convertit les services d’entrée en items normalisés (name, slug)
    $service_items = self::normalize_term_items($raw_services);

    $services_restants = [];
    $additions_par_tax = []; // taxonomy => list of items

    foreach ($service_items as $it) {
      $src_slug = $it['slug'];
      var_dump($src_slug);
      if (isset($map[$src_slug])) {
        $dst = $map[$src_slug];
        $tax = $dst['taxonomy'];
        $additions_par_tax[$tax] = $additions_par_tax[$tax] ?? [];

        // On pousse l’item cible (name/slug imposés)
        $additions_par_tax[$tax][] = [
          'name' => $dst['name'],
          'slug' => $dst['slug'],
        ];
        // Ne pas garder ce service dans la taxo 'service'
      } else {
        // Pas de redirection => il reste dans 'service'
        $services_restants[] = $it;
      }
    }

    // Dédupliquer par taxo
    foreach ($additions_par_tax as $tax => $items) {
      $additions_par_tax[$tax] = self::normalize_term_items($items);
    }

    return [
      'services_restants' => $services_restants,
      'additions_par_tax' => $additions_par_tax,
    ];
  }

  public static function update_illustrations_apidae_camping(array $item, $acf_field = 'galerie_photo_camping')
  {
    $existing = get_posts([
      'post_type' => 'camping',
      'meta_key' => 'apidae_id',
      'meta_value' => $item['id'] ?? 0,
      'posts_per_page' => 1,
      'fields' => 'ids',
      'suppress_filters' => true,
      'no_found_rows' => true,
    ]);
    if (!$existing) {
      return ['ok' => false, 'reason' => 'not_found'];
    }
    $post_id = $existing[0];

    $images = $item['illustrations'] ?? [];
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
    if (!is_array($existing_ids)) {
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
      if (!$ok) {
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
      'post_id' => $post_id,
      'added' => count($new_ids),
      'total' => count($final_ids),
      'mode' => $replace ? 'replace' : 'merge',
      'featured' => $featured_id,
      'unchanged' => $unchanged,
    ];
  }

  // Helpers à mettre dans la même classe (en static private si tu préfères)
  protected static function upsert_terms_for_taxonomy($taxonomy, array $terms): array
  {
    if (!taxonomy_exists($taxonomy)) {
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
        'post_type' => 'camping',
        'posts_per_page' => $batchSize,
        'post_status' => 'any',
        'fields' => 'ids',
        'suppress_filters' => true,
        'no_found_rows' => true,
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

  /**
   * Update March 2026 - Mise à jour des images
   */

  protected static function normalize_image_label(string $label): string
  {
    $label = trim(wp_strip_all_tags($label));
    $label = remove_accents($label);
    $label = strtolower($label);

    // retire extension si présente
    $label = preg_replace('/\.(jpg|jpeg|png|webp|gif|avif)$/i', '', $label);

    // remplace séparateurs
    $label = str_replace(['_', '-'], ' ', $label);

    // compacte espaces
    $label = preg_replace('/\s+/', ' ', $label);

    return trim($label);
  }

  protected static function get_apidae_image_title(array $illustration, string $fallback_url = ''): string
  {
    $candidates = [
      $illustration['traductionFichiers'][0]['name'] ?? '',
      $illustration['traductionFichiers'][0]['nom'] ?? '',
      $illustration['traductionFichiers'][0]['libelleFr'] ?? '',
      $illustration['traductionFichiers'][0]['title'] ?? '',
      $illustration['fichiers'][0]['name'] ?? '',
      $illustration['fichiers'][0]['nom'] ?? '',
      $illustration['libelleFr'] ?? '',
      $illustration['nom'] ?? '',
      $illustration['title'] ?? '',
    ];

    foreach ($candidates as $candidate) {
      if (is_string($candidate) && trim($candidate) !== '') {
        return trim($candidate);
      }
    }

    if ($fallback_url) {
      $path = parse_url($fallback_url, PHP_URL_PATH);
      if ($path) {
        return basename($path);
      }
    }

    return '';
  }

  public static function extract_apidae_images_with_titles(array $item): array
  {
    $out = [];

    if (empty($item['illustrations']) || !is_array($item['illustrations'])) {
      return [];
    }

    foreach ($item['illustrations'] as $index => $illustration) {
      if (!is_array($illustration)) {
        continue;
      }

      $url = $illustration['traductionFichiers'][0]['url']
        ?? $illustration['traductionFichiers'][0]['urlFichier']
        ?? $illustration['fichiers'][0]['url']
        ?? $illustration['fichiers'][0]['urlFichier']
        ?? $illustration['url']
        ?? '';

      $url = is_string($url) ? trim($url) : '';
      if ($url === '') {
        continue;
      }

      $title = self::get_apidae_image_title($illustration, $url);
      if ($title === '') {
        continue;
      }

      $normalized = self::normalize_image_label($title);

      if ($normalized === '') {
        continue;
      }

      $out[] = [
        'index' => (int) $index,
        'url' => $url,
        'title' => $title,
        'normalized_title' => $normalized,
      ];
    }

    // dédup par normalized_title, on garde le premier
    $seen = [];
    $uniq = [];

    foreach ($out as $img) {
      if (!isset($seen[$img['normalized_title']])) {
        $seen[$img['normalized_title']] = true;
        $uniq[] = $img;
      }
    }

    return $uniq;
  }

  protected static function get_post_attachments_by_title_map(int $post_id): array
  {
    $attachments = get_posts([
      'post_type'        => 'attachment',
      'post_parent'      => $post_id,
      'post_status'      => 'inherit',
      'posts_per_page'   => -1,
      'fields'           => 'ids',
      'post_mime_type'   => 'image',
      'orderby'          => 'menu_order ID',
      'order'            => 'ASC',
      'no_found_rows'    => true,
      'suppress_filters' => true,
    ]);

    $map = [];

    foreach ($attachments as $attachment_id) {
      $title = get_the_title($attachment_id);
      $normalized = self::normalize_image_label((string) $title);

      if ($normalized === '') {
        continue;
      }

      // si doublon côté WP, on garde le premier
      if (!isset($map[$normalized])) {
        $map[$normalized] = (int) $attachment_id;
      }
    }

    return $map;
  }

  protected static function delete_attachment_and_cleanup_gallery(int $attachment_id, int $post_id, string $gallery_field = 'gallery'): void
  {
    $thumb_id = (int) get_post_thumbnail_id($post_id);
    if ($thumb_id === $attachment_id) {
      delete_post_thumbnail($post_id);
    }

    $gallery_ids = [];

    if (function_exists('get_field')) {
      $gallery_ids = get_field($gallery_field, $post_id, false);
    } else {
      $gallery_ids = get_post_meta($post_id, $gallery_field, true);
    }

    if (!is_array($gallery_ids)) {
      $gallery_ids = [];
    }

    $gallery_ids = array_values(array_filter(array_map('intval', $gallery_ids)));
    $gallery_ids = array_values(array_diff($gallery_ids, [$attachment_id]));

    if (function_exists('update_field')) {
      $fo = function_exists('get_field_object') ? get_field_object($gallery_field, $post_id, false, false) : null;
      $selector = (!empty($fo['key'])) ? $fo['key'] : $gallery_field;
      update_field($selector, $gallery_ids, $post_id);
    } else {
      update_post_meta($post_id, $gallery_field, $gallery_ids);
    }

    wp_delete_attachment($attachment_id, true);
  }

  protected static function import_single_apidae_image_with_title(
    string $url,
    string $title,
    int $post_id,
    int $apidae_id,
    int $index = 0
  ) {
    if (!function_exists('media_handle_sideload')) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';
      require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $image_id = media_sideload_image($url, $post_id, null, 'id');

    if (is_wp_error($image_id)) {
      return $image_id;
    }

    $image_id = (int) $image_id;
    if ($image_id <= 0) {
      return new WP_Error('invalid_attachment_id', 'media_sideload_image a retourné un ID invalide.');
    }

    // force le titre attachment = titre APIDAE
    wp_update_post([
      'ID' => $image_id,
      'post_title' => $title,
      'post_name' => sanitize_title($title),
    ]);

    update_post_meta($image_id, 'apidae_source_url', $url);
    update_post_meta($image_id, 'apidae_object_id', $apidae_id);
    update_post_meta($image_id, 'apidae_image_index', $index);
    update_post_meta($image_id, 'apidae_image_title', $title);
    update_post_meta($image_id, 'apidae_image_title_normalized', self::normalize_image_label($title));

    return $image_id;
  }

  public static function sync_apidae_images_for_post_by_title(
    int $post_id,
    array $item,
    string $gallery_field = 'gallery',
    bool $dry_run = false
  ): array {
    $apidae_id = (int) ($item['id'] ?? 0);
    if (!$apidae_id) {
      return ['ok' => false, 'reason' => 'missing_apidae_id'];
    }

    $apidae_images = self::extract_apidae_images_with_titles($item);
    $wp_map = self::get_post_attachments_by_title_map($post_id); // [normalized_title => attachment_id]

    $apidae_map = [];
    foreach ($apidae_images as $img) {
      $apidae_map[$img['normalized_title']] = $img;
    }

    $apidae_titles = array_keys($apidae_map);
    $wp_titles = array_keys($wp_map);

    $to_delete_titles = array_values(array_diff($wp_titles, $apidae_titles));
    $to_add_titles    = array_values(array_diff($apidae_titles, $wp_titles));
    $same_titles      = array_values(array_intersect($apidae_titles, $wp_titles));

    $deleted_ids = [];
    $added_ids = [];
    $kept_ids = [];

    foreach ($same_titles as $title_key) {
      if (!empty($wp_map[$title_key])) {
        $kept_ids[] = (int) $wp_map[$title_key];
      }
    }

    if ($dry_run) {
      return [
        'ok' => true,
        'post_id' => $post_id,
        'apidae_id' => $apidae_id,
        'same' => count($same_titles),
        'to_add_titles' => $to_add_titles,
        'to_delete_titles' => $to_delete_titles,
        'deleted_ids' => [],
        'added_ids' => [],
        'kept_ids' => $kept_ids,
        'dry_run' => true,
      ];
    }

    // 1) supprimer les images WP dont le titre n'existe plus sur APIDAE
    foreach ($to_delete_titles as $title_key) {
      $attachment_id = (int) ($wp_map[$title_key] ?? 0);
      if ($attachment_id > 0) {
        self::delete_attachment_and_cleanup_gallery($attachment_id, $post_id, $gallery_field);
        $deleted_ids[] = $attachment_id;
      }
    }

    // 2) reconstruire dans l'ordre APIDAE
    $featured_id = 0;
    $gallery_ids = [];

    foreach ($apidae_images as $index => $img) {
      $title_key = $img['normalized_title'];

      if (isset($wp_map[$title_key]) && !in_array($title_key, $to_delete_titles, true)) {
        $attachment_id = (int) $wp_map[$title_key];
      } else {
        $attachment_id = self::import_single_apidae_image_with_title(
          $img['url'],
          $img['title'],
          $post_id,
          $apidae_id,
          $index
        );

        if (is_wp_error($attachment_id)) {
          continue;
        }

        $attachment_id = (int) $attachment_id;
        $added_ids[] = $attachment_id;
      }

      if ($index === 0) {
        $featured_id = $attachment_id;
      } else {
        $gallery_ids[] = $attachment_id;
      }
    }

    if ($featured_id > 0) {
      set_post_thumbnail($post_id, $featured_id);
    } else {
      delete_post_thumbnail($post_id);
    }

    if (function_exists('update_field')) {
      $fo = function_exists('get_field_object') ? get_field_object($gallery_field, $post_id, false, false) : null;
      $selector = (!empty($fo['key'])) ? $fo['key'] : $gallery_field;
      update_field($selector, $gallery_ids, $post_id);
    } else {
      update_post_meta($post_id, $gallery_field, $gallery_ids);
    }

    // stockage de suivi
    update_post_meta($post_id, 'apidae_image_titles', wp_list_pluck($apidae_images, 'title'));
    update_post_meta($post_id, 'apidae_image_urls', wp_list_pluck($apidae_images, 'url'));
    update_post_meta($post_id, 'apidae_images_imported', 1);
    update_post_meta($post_id, 'apidae_images_last_sync', current_time('mysql'));

    return [
      'ok' => true,
      'post_id' => $post_id,
      'apidae_id' => $apidae_id,
      'same' => count($same_titles),
      'to_add_titles' => $to_add_titles,
      'to_delete_titles' => $to_delete_titles,
      'deleted_ids' => $deleted_ids,
      'added_ids' => $added_ids,
      'kept_ids' => $kept_ids,
      'featured_id' => $featured_id,
      'gallery_ids' => $gallery_ids,
      'dry_run' => false,
    ];
  }

  public static function find_post_id_by_apidae_id(int $apidae_id): int
  {
    $posts = get_posts([
      'post_type' => 'camping',
      'meta_key' => 'apidae_id',
      'meta_value' => $apidae_id,
      'posts_per_page' => 1,
      'fields' => 'ids',
      'suppress_filters' => true,
      'no_found_rows' => true,
    ]);

    return !empty($posts[0]) ? (int) $posts[0] : 0;
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
      $id = (int) ($assoc_args['id'] ?? 0);
      $mode = $assoc_args['mode'] ?? 'upsert';
      $dry = isset($assoc_args['dry-run']);
      $sleep = (int) ($assoc_args['sleep'] ?? 0);

      if (!$id) {
        WP_CLI::error('Paramètre --id manquant.');
      }

      $fields = 'id,nom,gestion,localisation.adresse,reservation.organismes,illustrations,prestations.conforts,informationsHotelleriePleinAir.labels,informationsHotelleriePleinAir.chaines,informationsHotelleriePleinAir.hotelleriePleinAirType,informations.moyensCommunication,informationsHotelleriePleinAir.classement,presentation.descriptifCourt,presentation.descriptifDetaille,localisation.geolocalisation.geoJson.coordinates,localisation.environnements,localisation.perimetreGeographique,localisation.territoiresAffectes,prestations.equipements,prestations.services,prestations.conforts,prestations.activites,prestations.languesParlees,prestations.animauxAcceptes,ouverture.periodesOuvertures,descriptionTarif.periodes,descriptionTarif.tarifsEnClair,descriptionTarif.modesPaiement,reservation.organismes,informations.informationsLegales.siret,contacts,identifier,type,localisation.geolocalisation.complement';
      $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . $id, [
        'responseFields' => $fields,
        'locales' => 'fr',
      ]);


      if (!$res['success']) {
        WP_CLI::error('APIDAE error: ' . $res['message']);
      }

      $item = $res['data'] ?? null;
      if (!$item) {
        WP_CLI::error('Objet introuvable.');
      }

      $r = APIDAE_Service::import_apidae_camping($item, $mode, $dry);
      if (!$r['ok']) {
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
        || empty($item['informationsHotelleriePleinAir']['hotelleriePleinAirType'])
        || empty($item['informationsHotelleriePleinAir']['labels'])
        || empty($item['prestations']['languesParlees'])
        || empty($item['informationsHotelleriePleinAir']['capacite']['nombreEmplacementsDeclares'])
        || empty($item['prestations']['conforts'])
        || empty($item['reservation']['organismes'])
        || empty($item['ouverture']['periodesOuvertures'])
        || empty($item['descriptionTarif']['periodes'])
        || empty($item['descriptionTarif']['modesPaiement'])
        || empty($item['gestion'])
        || empty($item['presentation']['descriptifDetaille']);

      if (!$need) {
        return $item;
      }



      $fields = implode(',', [
        'id',
        'gestion',
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
        'descriptionTarif.periodes',
        'descriptionTarif.modesPaiement',
        'presentation.descriptifDetaille'
      ]);

      $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . (int) $item['id'], [
        'responseFields' => $fields,
        'locales' => 'fr',
      ]);

      // var_dump($res['data']);

      if ($res['success'] && !empty($res['data'])) {
        // on fusionne, les sous-clés manquantes sont complétées
        $item = array_replace_recursive($item, $res['data']);
      }
      return $item;
    }

    private static function cli_send_mail($to, $subject, $html, $dry = false)
    {
      if ($dry) {
        WP_CLI::log("[dry-run] Mail non envoyé à {$to} — subject: {$subject}");
        return true;
      }

      $headers = ['Content-Type: text/html; charset=UTF-8'];
      $ok = wp_mail($to, $subject, $html, $headers);

      if (!$ok) WP_CLI::warning("Échec envoi mail à {$to}");
      return $ok;
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

      // ✅ Mail options
      $mailEnabled = isset($assoc_args['mail']);
      $mailTo = $assoc_args['mail-to'] ?? get_option('admin_email');
      $mailSubject = $assoc_args['mail-subject'] ?? 'APIDAE import-selection: terminé';

      // count demandé par l’utilisateur (plafonné à 200 par l’API)
      $requestedCount = (int) ($assoc_args['count'] ?? 200);
      $pageCount = max(0, min($requestedCount, 200));
      if ($pageCount === 0) $pageCount = 200;

      // bornes
      $cliOffset = (int) ($assoc_args['offset'] ?? 0);
      $cliLimit = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : null;
      $mode = $assoc_args['mode'] ?? 'upsert';
      $sleep = (int) ($assoc_args['sleep'] ?? 0);
      $dry = isset($assoc_args['dry-run']);

      // 1er appel pour connaître numFound
      $params = ['selectionIds' => $selection_ids, 'count' => $pageCount, 'first' => $cliOffset];
      $res = APIDAE_Service::connect_to_apidae('/recherche/list-objets-touristiques', $params, 'GET', true);

      if (!$res['success']) {
        // ✅ Mail en cas d'erreur bloquante
        if ($mailEnabled) {
          $html = "<p><strong>Import APIDAE interrompu</strong></p>"
            . "<p>Erreur API: " . esc_html($res['message']) . "</p>"
            . "<p>selectionIds: " . esc_html(implode(',', $selection_ids)) . "<br>"
            . "offset: " . (int)$cliOffset . "<br>"
            . "count: " . (int)$pageCount . "<br>"
            . "mode: " . esc_html($mode) . "<br>"
            . "dry-run: " . ($dry ? 'oui' : 'non') . "</p>";

          self::cli_send_mail($mailTo, 'APIDAE import-selection: ERREUR', $html, $dry);
        }
        WP_CLI::error('APIDAE error: ' . $res['message']);
      }

      $numFound = (int) ($res['data']['numFound'] ?? 0);
      if ($numFound === 0) {
        WP_CLI::log('Aucun résultat.');

        // ✅ Mail "aucun résultat"
        if ($mailEnabled) {
          $html = "<p><strong>Import APIDAE terminé</strong> — aucun résultat.</p>"
            . "<p>selectionIds: " . esc_html(implode(',', $selection_ids)) . "<br>"
            . "offset: " . (int)$cliOffset . "<br>"
            . "limit: " . esc_html($cliLimit !== null ? (string)$cliLimit : '∞') . "<br>"
            . "mode: " . esc_html($mode) . "<br>"
            . "dry-run: " . ($dry ? 'oui' : 'non') . "</p>";

          self::cli_send_mail($mailTo, $mailSubject, $html, $dry);
        }
        return;
      }

      $remainingToProcess = $cliLimit !== null ? min($cliLimit, $numFound - $cliOffset) : ($numFound - $cliOffset);
      if ($remainingToProcess <= 0) {
        WP_CLI::log("Rien à traiter (offset dépasse numFound).");

        if ($mailEnabled) {
          $html = "<p><strong>Import APIDAE terminé</strong> — rien à traiter.</p>"
            . "<p>numFound: {$numFound}<br>"
            . "offset: " . (int)$cliOffset . "<br>"
            . "limit: " . esc_html($cliLimit !== null ? (string)$cliLimit : '∞') . "</p>";

          self::cli_send_mail($mailTo, $mailSubject, $html, $dry);
        }
        return;
      }

      WP_CLI::log("numFound={$numFound} — offset={$cliOffset} — limit=" . ($cliLimit ?? '∞') . " — pageCount={$pageCount}");

      $done = $created = $updated = $skipped = $errors = 0;

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

      $processPage($res['data']);
      $first += $pageCount;

      while ($remainingToProcess > 0 && $first < $numFound) {
        $batchCount = min($pageCount, $remainingToProcess);
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

      // ✅ Mail récap de fin
      if ($mailEnabled) {
        $html = "<p><strong>Import APIDAE terminé</strong></p>"
          . "<ul>"
          . "<li>selectionIds: " . esc_html(implode(',', $selection_ids)) . "</li>"
          . "<li>numFound: {$numFound}</li>"
          . "<li>offset: " . (int)$cliOffset . "</li>"
          . "<li>limit: " . esc_html($cliLimit !== null ? (string)$cliLimit : '∞') . "</li>"
          . "<li>mode: " . esc_html($mode) . "</li>"
          . "<li>dry-run: " . ($dry ? 'oui' : 'non') . "</li>"
          . "</ul>"
          . "<p><strong>Résultat:</strong> done={$done} created={$created} updated={$updated} skipped={$skipped} errors={$errors}</p>";

        self::cli_send_mail($mailTo, $mailSubject, $html, $dry);
      }
    }


    /**
     * Met à jour les illustrations ACF pour un camping par ID APIDAE.
     *
     * ## OPTIONS
     * [--id=<id>]
     * [--ids=<ids>] : liste CSV d'IDs (ex: 5752595,5752596)
     * [--ids-file=<path>] : fichier contenant 1 ID par ligne
     * [--acf-field=<key>] : clé ACF galerie (defaut: galerie_photo_camping)
     * [--continue-on-error] : continue même si un ID échoue
     *
     * ## EXAMPLES
     *   wp apidae update-images --id=5752595
     *   wp apidae update-images --ids=5752595,5752596 --acf-field=galerie_photo_camping
     *   wp apidae update-images --ids-file=/tmp/ids.txt --continue-on-error
     */
    public function update_images($args, $assoc_args)
    {
      $acf = $assoc_args['acf-field'] ?? 'galerie_photo_camping';
      $continue = isset($assoc_args['continue-on-error']);

      // 1) Récupère les IDs depuis --id, --ids, --ids-file
      $ids = [];

      if (!empty($assoc_args['id'])) {
        $ids[] = (int) $assoc_args['id'];
      }

      if (!empty($assoc_args['ids'])) {
        $parts = preg_split('/[,\s;]+/', (string) $assoc_args['ids']);
        foreach ($parts as $p) {
          $p = trim($p);
          if ($p !== '') $ids[] = (int) $p;
        }
      }

      if (!empty($assoc_args['ids-file'])) {
        $path = (string) $assoc_args['ids-file'];
        if (!is_readable($path)) {
          WP_CLI::error('Fichier --ids-file illisible: ' . $path);
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
          $line = trim($line);
          if ($line === '' || str_starts_with($line, '#')) continue; // accepte commentaires
          // Si le fichier contient aussi des CSV, on gère
          $more = preg_split('/[,\s;]+/', $line);
          foreach ($more as $m) {
            $m = trim($m);
            if ($m !== '') $ids[] = (int) $m;
          }
        }
      }

      // Nettoyage
      $ids = array_values(array_unique(array_filter($ids, fn($v) => (int)$v > 0)));

      if (empty($ids)) {
        WP_CLI::error('Paramètre manquant: utilise --id, --ids ou --ids-file.');
      }

      $fields = 'id,illustrations';

      $ok = 0;
      $fail = 0;

      WP_CLI::log('Traitement de ' . count($ids) . ' ID(s)...');

      foreach ($ids as $id) {
        try {
          WP_CLI::log('> ID ' . $id . '...');

          $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . $id, [
            'responseFields' => $fields,
            'locales' => 'fr',
          ]);

          if (!$res['success']) {
            throw new \Exception('APIDAE error: ' . ($res['message'] ?? 'unknown'));
          }

          $item = $res['data'] ?? null;
          if (!$item) {
            throw new \Exception('Objet introuvable.');
          }

          $r = APIDAE_Service::update_illustrations_apidae_camping($item, $acf);
          if (empty($r['ok'])) {
            throw new \Exception('MAJ images échouée: ' . ($r['error'] ?? $r['reason'] ?? 'unknown'));
          }

          $ok++;
          WP_CLI::success('OK ID ' . $id . ' -> post_id ' . ($r['post_id'] ?? 'n/a'));
        } catch (\Throwable $e) {
          $fail++;
          WP_CLI::warning('KO ID ' . $id . ' : ' . $e->getMessage());
          if (!$continue) {
            WP_CLI::error('Arrêt (utilise --continue-on-error pour ignorer les erreurs).');
          }
        }
      }

      if ($fail === 0) {
        WP_CLI::success("Terminé. OK=$ok / KO=$fail");
      } else {
        WP_CLI::warning("Terminé. OK=$ok / KO=$fail");
      }
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
      $batch = (int) ($assoc_args['batch-size'] ?? 200);
      $sleep = (int) ($assoc_args['sleep'] ?? 0);
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

      $acf = $assoc_args['acf-field'] ?? 'galerie_photo_camping';
      $merge = isset($assoc_args['merge']);
      $limit = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : null;
      $offset = (int) ($assoc_args['offset'] ?? 0);
      $sleep = (int) ($assoc_args['sleep'] ?? 0);
      $dry = isset($assoc_args['dry-run']);
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
          'selectionIds' => $selection_ids,
          'count' => 999,
          'responseFields' => $fields,
          'locales' => 'fr',
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
          'meta_key' => 'apidae_id',
          'meta_value' => $apidae_id,
          'fields' => 'ids',
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
      $acf = $assoc_args['acf-field'] ?? 'galerie_photo_camping';
      $merge = isset($assoc_args['merge']);
      $limit = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : null;
      $offset = (int) ($assoc_args['offset'] ?? 0);
      $sleep = (int) ($assoc_args['sleep'] ?? 0);
      $dry = isset($assoc_args['dry-run']);
      $set_featured = isset($assoc_args['set-featured']); // ➕ NEW


      $query = [
        'post_type' => 'camping',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'any',
        'suppress_filters' => true,
        'no_found_rows' => true,
        'meta_key' => 'apidae_id',
        'meta_compare' => 'EXISTS',
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
          'locales' => 'fr',
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


    /**
     * Met en brouillon tous les campings dont l'apidae_id n'est PAS dans une sélection APIDAE.
     *
     * ## OPTIONS
     * --selection-id=<id>      : ID de la sélection APIDAE (ex: 190549)
     * [--meta-key=<meta_key>]  : meta key qui stocke l'id APIDAE (défaut: apidae_id)
     * [--count=<n>]            : taille de page APIDAE (1–200, défaut 200)
     * [--sleep=<sec>]          : pause entre pages (défaut 0)
     * [--dry-run]              : n'écrit rien, log seulement
     *
     * ## EXAMPLE
     *   wp apidae draft-not-in-selection --selection-id=190549
     *
     * @subcommand draft-not-in-selection
     */
    public function draft_not_in_selection($args, $assoc_args)
    {
      $selection_id = (int)($assoc_args['selection-id'] ?? 0);
      if (!$selection_id) {
        \WP_CLI::error('Paramètre --selection-id manquant.');
      }

      $meta_key = isset($assoc_args['meta-key']) ? sanitize_key($assoc_args['meta-key']) : 'apidae_id';
      $count = isset($assoc_args['count']) ? (int)$assoc_args['count'] : 200;
      if ($count <= 0 || $count > 200) $count = 200;

      $sleep = isset($assoc_args['sleep']) ? (int)$assoc_args['sleep'] : 0;
      $dry   = isset($assoc_args['dry-run']);

      // 1) Récupérer tous les IDs APIDAE dans la sélection (pagination)
      $allowed = [];
      $first = 0;
      $numFound = null;
      $page = 0;

      do {
        $page++;
        $params = [
          'selectionIds' => [$selection_id],
          'count' => $count,
          'first' => $first,
          // Pas besoin de responseFields lourds : id suffit
        ];

        $res = \APIDAE_Service::connect_to_apidae('/recherche/list-objets-touristiques', $params, 'GET', true);
        if (!$res['success']) {
          \WP_CLI::error('APIDAE error: ' . ($res['message'] ?? 'unknown'));
        }

        $data = $res['data'] ?? [];
        if ($numFound === null) {
          $numFound = (int)($data['numFound'] ?? 0);
          \WP_CLI::log("Sélection {$selection_id} — numFound={$numFound}");
          if ($numFound === 0) break;
        }

        $items = $data['objetsTouristiques'] ?? [];
        foreach ($items as $it) {
          if (!empty($it['id'])) {
            $allowed[(string)(int)$it['id']] = true;
          }
        }

        $first += $count;

        if ($sleep) sleep($sleep);
      } while ($numFound !== null && $first < $numFound);

      $allowed_count = count($allowed);
      if ($allowed_count === 0) {
        \WP_CLI::warning("Aucun ID trouvé dans la sélection {$selection_id}. Rien ne sera modifié.");
        return;
      }
      \WP_CLI::log("IDs autorisés (dans la sélection) : {$allowed_count}");

      // 2) Charger tous les campings ayant un apidae_id
      $camping_ids = get_posts([
        'post_type' => 'camping',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'fields' => 'ids',
        'suppress_filters' => true,
        'no_found_rows' => true,
        'meta_key' => $meta_key,
        'meta_compare' => 'EXISTS',
      ]);

      \WP_CLI::log("Campings trouvés avec meta {$meta_key}: " . count($camping_ids));

      // 3) Draft ceux qui ne sont pas dans la sélection
      $done = 0;
      $drafted = 0;
      $already_draft = 0;
      $kept = 0;
      $noid = 0;
      $errors = 0;

      foreach ($camping_ids as $post_id) {
        $done++;
        $apidae_id = (int)get_post_meta($post_id, $meta_key, true);
        if (!$apidae_id) {
          $noid++;
          continue;
        }

        $in_selection = isset($allowed[(string)$apidae_id]);

        if ($in_selection) {
          $kept++;
          continue;
        }

        $current_status = get_post_status($post_id);
        if ($current_status === 'draft') {
          $already_draft++;
          \WP_CLI::log("[$done] post {$post_id} (APIDAE {$apidae_id}) déjà en brouillon");
          continue;
        }

        if ($dry) {
          \WP_CLI::log("[$done] [DRY-RUN] Mettre en brouillon post {$post_id} (APIDAE {$apidae_id}) status={$current_status}");
          $drafted++;
          continue;
        }

        $r = wp_update_post([
          'ID' => $post_id,
          'post_status' => 'draft',
        ], true);

        if (is_wp_error($r)) {
          $errors++;
          \WP_CLI::warning("[$done] post {$post_id} (APIDAE {$apidae_id}) => erreur: " . $r->get_error_message());
          continue;
        }

        $drafted++;
        \WP_CLI::log("[$done] post {$post_id} (APIDAE {$apidae_id}) => mis en brouillon");
      }

      \WP_CLI::success("Terminé. kept={$kept} drafted={$drafted} already_draft={$already_draft} noid={$noid} errors={$errors} dry-run=" . ($dry ? 'oui' : 'non'));
    }


    /**
     * Attache les campings existants aux termes de la taxonomie "liste"
     * à partir de l'ACF "apidae_id_list_selection" sur chaque terme.
     *
     * Pour chaque terme "liste" :
     * - lit le champ ACF "apidae_id_list_selection" (ID de sélection APIDAE)
     * - récupère tous les objets touristiques de cette sélection
     * - pour chaque objet, trouve le camping existant via une meta (par défaut "apidae_id")
     * - ajoute le terme "liste" au camping (sans créer de camping)
     *
     * ## OPTIONS
     *
     * [--liste-ids=<ids>]
     * : Liste d'IDs de termes "liste" séparés par virgules. Par défaut : tous les termes.
     *
     * [--meta-key=<meta_key>]
     * : Meta key utilisée pour stocker l'ID APIDAE sur les campings (défaut: apidae_id).
     *
     * [--count=<n>]
     * : Nombre max d'objets par page APIDAE (1–200,  défaut 200).
     *
     * [--sleep=<sec>]
     * : Pause (en secondes) entre les pages APIDAE (défaut 0).
     *
     * [--dry-run]
     * : N'effectue pas les mises à jour, affiche seulement ce qui serait fait.
     * 
     * [--mail]
     * : Envoie un mail récapitulatif en fin d'exécution.
     *
     * [--mail-to=<emails>]
     * : Destinataires (séparés par des virgules).
     * 
     * ## EXAMPLES
     *
     *   wp apidae sync-lists-from-terms
     *   wp apidae sync-lists-from-terms --liste-ids=10,12
     *   wp apidae sync-lists-from-terms --meta-key=apidae_obj_id --dry-run
     * 
     * @subcommand sync-lists-from-terms
     */

    public function sync_lists_from_terms($args, $assoc_args)
    {
      $tax_slug = 'liste';

      // ---- Options mail
      $send_mail = isset($assoc_args['mail']);
      $mail_to   = $assoc_args['mail-to'] ?? '';
      $mail_to_list = [];

      if ($send_mail) {
        if (!empty($mail_to)) {
          $mail_to_list = array_filter(array_map('trim', explode(',', $mail_to)));
        }
        if (empty($mail_to_list)) {
          $admin_email = get_option('admin_email');
          if (!empty($admin_email)) $mail_to_list = [$admin_email];
        }
      }

      $t0 = microtime(true);
      $errors = []; // on stocke quelques erreurs pour le mail

      if (! taxonomy_exists($tax_slug)) {
        $msg = "La taxonomie '{$tax_slug}' n'existe pas.";
        \WP_CLI::error($msg);
        return;
      }

      // Meta key pour retrouver le camping à partir de l'ID APIDAE
      $meta_key = isset($assoc_args['meta-key']) ? sanitize_key($assoc_args['meta-key']) : 'apidae_id';

      // Taille de page APIDAE (1–200)
      $count = isset($assoc_args['count']) ? (int) $assoc_args['count'] : 200;
      if ($count <= 0 || $count > 200) {
        $count = 200;
      }

      $sleep = isset($assoc_args['sleep']) ? (int) $assoc_args['sleep'] : 0;
      $dry   = isset($assoc_args['dry-run']);

      // Sélection des termes "liste" à traiter
      $include_ids = [];
      if (! empty($assoc_args['liste-ids'])) {
        $include_ids = array_filter(array_map('intval', explode(',', $assoc_args['liste-ids'])));
      }

      $term_args = [
        'taxonomy'   => $tax_slug,
        'hide_empty' => false,
      ];

      if (! empty($include_ids)) {
        $term_args['include'] = $include_ids;
      }

      $terms = get_terms($term_args);

      if (is_wp_error($terms) || empty($terms)) {
        \WP_CLI::warning('Aucun terme "liste" trouvé à traiter.');

        if ($send_mail && !empty($mail_to_list)) {
          wp_mail(
            $mail_to_list,
            "[SYNC LISTES] Aucun terme à traiter",
            "Aucun terme \"{$tax_slug}\" trouvé.\nDate: " . date('Y-m-d H:i:s')
          );
        }
        return;
      }

      \WP_CLI::log(sprintf('Nombre de termes "%s" à traiter : %d', $tax_slug, count($terms)));

      $global_attached        = 0;
      $global_skipped_no_term = 0;
      $global_skipped_no_post = 0;
      $global_errors          = 0;
      $global_terms_processed = 0;
      $global_items_seen      = 0;

      foreach ($terms as $term) {

        $global_terms_processed++;

        $term_label = sprintf('%s (%d)', $term->name, $term->term_id);

        $selection_id = get_term_meta($term->term_id, 'apidae_id_list_selection', true);

        if (empty($selection_id)) {
          \WP_CLI::log("Terme {$term_label} : pas de apidae_id_list_selection, on saute.");
          $global_skipped_no_term++;
          continue;
        }

        $selection_id = (int) $selection_id;
        if ($selection_id <= 0) {
          \WP_CLI::log("Terme {$term_label} : apidae_id_list_selection invalide ({$selection_id}), on saute.");
          $global_skipped_no_term++;
          continue;
        }

        \WP_CLI::log("Terme {$term_label} : traitement de la sélection APIDAE {$selection_id}…");

        // Préparer la map des ID de termes par langue pour CE terme (si WPML)
        $term_ids_by_lang = [
          'default' => (int) $term->term_id,
        ];

        if (defined('ICL_SITEPRESS_VERSION')) {
          $term_element_type = apply_filters('wpml_element_type', $tax_slug);
          $term_trid = apply_filters('wpml_element_trid', null, (int) $term->term_id, $term_element_type);

          if ($term_trid) {
            $term_translations = (array) apply_filters(
              'wpml_get_element_translations',
              [],
              $term_trid,
              $term_element_type
            );

            if (! empty($term_translations)) {
              $term_ids_by_lang = [];
              foreach ($term_translations as $lang_code => $translation) {
                if (! empty($translation->element_id)) {
                  $term_ids_by_lang[$lang_code] = (int) $translation->element_id;
                }
              }
            }
          }
        }

        $first      = 0;
        $numFound   = null;
        $attached   = 0;
        $no_post    = 0;
        $page_index = 0;

        do {
          $params = [
            'selectionIds' => [$selection_id],
            'count'        => $count,
            'first'        => $first,
          ];

          $res = \APIDAE_Service::connect_to_apidae(
            '/recherche/list-objets-touristiques',
            $params,
            'GET',
            true
          );

          if (! $res['success']) {
            $msg = "Terme {$term_label} : erreur APIDAE : " . ($res['message'] ?? 'Erreur inconnue');
            \WP_CLI::warning($msg);
            $global_errors++;
            $errors[] = $msg;
            break;
          }

          $data = $res['data'] ?? [];

          if (null === $numFound) {
            $numFound = (int) ($data['numFound'] ?? 0);
            \WP_CLI::log("  → numFound = {$numFound}");
            if (0 === $numFound) {
              break;
            }
          }

          $items = $data['objetsTouristiques'] ?? [];

          if (empty($items)) {
            break;
          }

          $page_index++;
          \WP_CLI::log(sprintf('  Page %d (first=%d, count=%d)', $page_index, $first, $count));

          foreach ($items as $item) {
            if (empty($item) || empty($item['id'])) {
              continue;
            }

            $global_items_seen++;
            $apidae_id = (int) $item['id'];

            $q = new \WP_Query([
              'post_type'      => 'camping',
              'post_status'    => 'any',
              'fields'         => 'ids',
              'posts_per_page' => 1,
              'meta_query'     => [
                [
                  'key'   => $meta_key,
                  'value' => $apidae_id,
                ],
              ],
            ]);

            if (empty($q->posts)) {
              $no_post++;
              $global_skipped_no_post++;
              \WP_CLI::log("    APIDAE {$apidae_id} : aucun camping trouvé (meta {$meta_key}), on saute.");
              continue;
            }

            $post_id = (int) $q->posts[0];

            // Liste des posts concernés : post trouvé + ses traductions
            $post_ids_for_terms = [$post_id];

            if (defined('ICL_SITEPRESS_VERSION')) {
              $post_type         = get_post_type($post_id);
              $post_element_type = apply_filters('wpml_element_type', $post_type);
              $post_trid         = apply_filters('wpml_element_trid', null, $post_id, $post_element_type);

              if ($post_trid) {
                $post_translations = (array) apply_filters(
                  'wpml_get_element_translations',
                  [],
                  $post_trid,
                  $post_element_type
                );

                if (! empty($post_translations)) {
                  $post_ids_for_terms = [];
                  foreach ($post_translations as $lang_code => $translation) {
                    if (! empty($translation->element_id)) {
                      $post_ids_for_terms[] = (int) $translation->element_id;
                    }
                  }
                  $post_ids_for_terms = array_values(array_unique($post_ids_for_terms));
                }
              }
            }

            foreach ($post_ids_for_terms as $translated_post_id) {

              if ($dry) {
                \WP_CLI::log(
                  sprintf(
                    '    [DRY-RUN] Attacher le terme %s (%d) au camping #%d (APIDAE %d).',
                    $term->name,
                    $term->term_id,
                    $translated_post_id,
                    $apidae_id
                  )
                );
                continue;
              }

              $term_id_to_attach = (int) $term->term_id;

              if (defined('ICL_SITEPRESS_VERSION')) {
                $lang_details = apply_filters('wpml_post_language_details', null, $translated_post_id);
                $lang_code    = (is_array($lang_details) && ! empty($lang_details['language_code']))
                  ? $lang_details['language_code']
                  : null;

                if ($lang_code && isset($term_ids_by_lang[$lang_code])) {
                  $term_id_to_attach = $term_ids_by_lang[$lang_code];
                } elseif (isset($term_ids_by_lang['default'])) {
                  $term_id_to_attach = $term_ids_by_lang['default'];
                }
              }

              $r = wp_set_object_terms($translated_post_id, $term_id_to_attach, $tax_slug, true);

              if (is_wp_error($r)) {
                $global_errors++;
                $msg = sprintf(
                  'Erreur wp_set_object_terms pour le camping #%d : %s',
                  $translated_post_id,
                  $r->get_error_message()
                );
                \WP_CLI::warning('    ' . $msg);
                $errors[] = $msg;
                continue;
              }

              $attached++;
              $global_attached++;

              \WP_CLI::log(
                sprintf(
                  '    OK : camping #%d attaché au terme %s (%d) (APIDAE %d).',
                  $translated_post_id,
                  $term->name,
                  $term_id_to_attach,
                  $apidae_id
                )
              );
            }
          }

          $first += $count;

          if ($sleep > 0) {
            sleep($sleep);
          }
        } while ($first < $numFound);

        \WP_CLI::log("Résumé terme {$term_label} : attachés={$attached}, sans camping correspondant={$no_post}");
      }

      $duration = round(microtime(true) - $t0, 2);

      \WP_CLI::success(
        sprintf(
          'Terminé. Total attachés=%d, termes sans sélection valide=%d, objets sans camping=%d, erreurs=%d',
          $global_attached,
          $global_skipped_no_term,
          $global_skipped_no_post,
          $global_errors
        )
      );

      // ---- Mail récap
      if ($send_mail && !empty($mail_to_list)) {

        $subject = "[SYNC LISTES] OK - attachés={$global_attached}";
        if ($global_errors > 0) {
          $subject = "[SYNC LISTES] PARTIEL - attachés={$global_attached}, erreurs={$global_errors}";
        }

        $body  = "Sync listes (depuis termes) terminée.\n";
        $body .= "Taxonomie: {$tax_slug}\n";
        $body .= "Termes traités: {$global_terms_processed}\n";
        $body .= "Objets APIDAE vus: {$global_items_seen}\n";
        $body .= "Total attachés: {$global_attached}\n";
        $body .= "Termes sans sélection valide: {$global_skipped_no_term}\n";
        $body .= "Objets sans camping: {$global_skipped_no_post}\n";
        $body .= "Erreurs: {$global_errors}\n";
        $body .= "Dry-run: " . ($dry ? 'oui' : 'non') . "\n";
        $body .= "Durée: {$duration}s\n";
        $body .= "Date: " . date('Y-m-d H:i:s') . "\n";

        if (!empty($errors)) {
          $body .= "\nDétails erreurs (max 20):\n- " . implode("\n- ", array_slice($errors, 0, 20));
          if (count($errors) > 20) $body .= "\n... (+" . (count($errors) - 20) . " autres)";
        }

        wp_mail($mail_to_list, $subject, $body);
      }
    }

    /**
     * Synchronise les images d'un ou plusieurs campings avec APIDAE
     * en comparant le TITRE/NOM de l'image.
     *
     * Si un titre WP n'existe plus dans APIDAE => suppression
     * Si un titre APIDAE n'existe pas encore dans WP => téléchargement
     *
     * ## OPTIONS
     * [--id=<id>]
     * [--ids=<ids>]
     * [--ids-file=<path>]
     * [--acf-field=<field>]
     * [--continue-on-error]
     * [--dry-run]
     *
     * ## EXAMPLES
     *   wp apidae sync-images-by-title --id=5752595
     *   wp apidae sync-images-by-title --ids=5752595,5752596
     *   wp apidae sync-images-by-title --ids-file=/tmp/ids.txt --dry-run
     *
     * @subcommand sync-images-by-title
     */
    public function sync_images_by_title($args, $assoc_args)
    {
      $acf_field = $assoc_args['acf-field'] ?? 'gallery';
      $continue = isset($assoc_args['continue-on-error']);
      $dry_run = isset($assoc_args['dry-run']);

      $ids = [];

      if (!empty($assoc_args['id'])) {
        $ids[] = (int) $assoc_args['id'];
      }

      if (!empty($assoc_args['ids'])) {
        $parts = preg_split('/[,\s;]+/', (string) $assoc_args['ids']);
        foreach ($parts as $p) {
          $p = trim($p);
          if ($p !== '') {
            $ids[] = (int) $p;
          }
        }
      }

      if (!empty($assoc_args['ids-file'])) {
        $path = (string) $assoc_args['ids-file'];
        if (!is_readable($path)) {
          WP_CLI::error('Fichier --ids-file illisible: ' . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
          $line = trim($line);
          if ($line === '' || strpos($line, '#') === 0) {
            continue;
          }

          $parts = preg_split('/[,\s;]+/', $line);
          foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
              $ids[] = (int) $p;
            }
          }
        }
      }

      $ids = array_values(array_unique(array_filter($ids, fn($v) => (int) $v > 0)));

      if (empty($ids)) {
        WP_CLI::error('Paramètre manquant: utilise --id, --ids ou --ids-file.');
      }

      $fields = 'id,illustrations';

      $ok = 0;
      $fail = 0;

      WP_CLI::log('Traitement de ' . count($ids) . ' ID(s)...');

      foreach ($ids as $apidae_id) {
        try {
          WP_CLI::log('> ID ' . $apidae_id . '...');

          $post_id = APIDAE_Service::find_post_id_by_apidae_id($apidae_id);
          if (!$post_id) {
            throw new \Exception("Aucun post camping trouvé pour l'ID APIDAE {$apidae_id}");
          }

          $res = APIDAE_Service::connect_to_apidae('/objet-touristique/get-by-id/' . $apidae_id, [
            'responseFields' => $fields,
            'locales' => 'fr',
          ]);

          if (!$res['success']) {
            throw new \Exception('APIDAE error: ' . ($res['message'] ?? 'unknown'));
          }

          $item = $res['data'] ?? null;
          if (!$item) {
            throw new \Exception('Objet introuvable.');
          }

          $r = APIDAE_Service::sync_apidae_images_for_post_by_title($post_id, $item, $acf_field, $dry_run);

          if (empty($r['ok'])) {
            throw new \Exception('Sync images échouée: ' . ($r['reason'] ?? 'unknown'));
          }

          $ok++;

          if ($dry_run) {
            WP_CLI::success(sprintf(
              'DRY RUN ID %d -> post_id=%d | same=%d | add=%d | delete=%d',
              $apidae_id,
              $post_id,
              (int) ($r['same'] ?? 0),
              count($r['to_add_titles'] ?? []),
              count($r['to_delete_titles'] ?? [])
            ));
          } else {
            WP_CLI::success(sprintf(
              'OK ID %d -> post_id=%d | same=%d | added=%d | deleted=%d',
              $apidae_id,
              $post_id,
              (int) ($r['same'] ?? 0),
              count($r['added_ids'] ?? []),
              count($r['deleted_ids'] ?? [])
            ));
          }
        } catch (\Throwable $e) {
          $fail++;
          WP_CLI::warning('KO ID ' . $apidae_id . ' : ' . $e->getMessage());

          if (!$continue) {
            WP_CLI::error('Arrêt (utilise --continue-on-error pour ignorer les erreurs).');
          }
        }
      }

      if ($fail === 0) {
        WP_CLI::success("Terminé. OK=$ok / KO=$fail");
      } else {
        WP_CLI::warning("Terminé. OK=$ok / KO=$fail");
      }
    }
  }


  WP_CLI::add_command('apidae', 'APIDAE_CLI_Command');
}
