<?php
class CPT_CAMPING
{
  public static function init()
  {
    add_action('init', [__CLASS__, 'register_post_types']);
    add_action('init', [__CLASS__, 'register_taxonomies']);

    // Metabox Apidae
    add_action('add_meta_boxes', function () {
      add_meta_box('apidae_infos', 'Informations Apidae', [__CLASS__, 'render_apidae_metabox'], 'camping', 'normal', 'high');
    });
    add_action('save_post_camping', [__CLASS__, 'save_apidae_metabox']);

    // 🔎 Admin list: colonne + tri + recherche apidae_id
    add_filter('manage_edit-camping_columns', [__CLASS__, 'add_apidae_column']);
    add_action('manage_camping_posts_custom_column', [__CLASS__, 'render_apidae_column'], 10, 2);
    add_filter('manage_edit-camping_sortable_columns', [__CLASS__, 'make_apidae_column_sortable']);
    add_action('pre_get_posts', [__CLASS__, 'extend_admin_search_and_sort']);
    add_action('admin_head-edit.php', [__CLASS__, 'enhance_search_placeholder']);
    add_action('restrict_manage_posts', [__CLASS__, 'add_apidae_id_filter']); // input en admin
    add_action('pre_get_posts', [__CLASS__, 'apply_apidae_id_filter']);       // filtrage requête

  }

  public static function register_post_types()
  {
    register_post_type('camping', [
      'labels' => [
        'name' => __('Campings', 'fdhpa17'),
        'singular_name' => __('Camping', 'fdhpa17'),
      ],
      'public' => true,
      'has_archive' => true,
      'supports' => ['title', 'editor', 'thumbnail'],
      'rewrite' => ['slug' => 'camping'],
      'show_in_rest' => true,
    ]);
  }

  public static function register_taxonomies()
  {
    $taxonomies = [
      'destination',
      'equipement',
      'atout',
      'etoile',
      'aquatique',
      'service',
      'label',
      'hebergement',
      'cible',
      'groupe',
      'confort',
      'paiement'
    ];

    foreach ($taxonomies as $taxonomy) {

      register_taxonomy($taxonomy, 'camping', [
        'labels' => [
          'name' => __(ucfirst($taxonomy) . 's', 'fdhpa17'),
          'singular_name' => __(ucfirst($taxonomy), 'fdhpa17'),
        ],
        'public' => true,
        'hierarchical' => true,
        'rewrite'      => [
          'slug'         => $taxonomy,
          'hierarchical' => false, // <<< clé magique pour l’URL
          'with_front'   => false,
        ],
        'show_in_rest' => true,
      ]);
    }
  }

  public static function render_apidae_metabox($post)
  {
    $fields = [
      'apidae_id' => 'ID Apidae',
      'apidae_identifier' => 'Identifiant Apidae',
      'apidae_update_date_modification' => 'Mise à jour APIDAE',
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
      'presentation_complement' => 'Complément de localisation',
      'nb_real' => 'Nombre réel d\'emplacements',
      'nb_mobilhomes' => 'Nombre de mobil-homes',
      'nb_bungalows' => 'Nombre de bungalows',
      'nb_insolites' => 'Nombre d\'hébergements insolites',
      'empl_campingcars' => 'Nombre d\'emplacements camping cars',
      'empl_caravanes' => 'Nombre d\'emplacements caravanes',
      'empl_tentes' => 'Nombre d\'emplacements tentes',
      'superficie' => 'Superficie',
      'ouverture' => 'Ouverture',
      'fermeture' => 'Fermeture',
      'id_reservation_direct' => 'ID de réservation Direct',
      'url_reservation_direct' => 'URL de réservation Direct',
      'id_reservation_ctoutvert' => 'ID de réservation CTOUTVERT',
      'langues' => 'Langues parlées',
      'periodes_date_debut' => 'Date de début',
      'periodes_date_fin' => 'Date de fin',
      'periodes_type' => 'Type d\'ouverture',
      'price_mini' => 'Prix mini',
      'price_max' => 'Prix max',
      'price_mini_mobilhomes' => 'Prix mini mobilhome',
      'price_max_mobilhomes' => 'Prix max mobilhome'
    ];

    $capacites = get_post_meta($post->ID);

    wp_nonce_field('save_apidae_meta', 'apidae_meta_nonce');
    echo '<table class="form-table">';
    foreach ($fields as $key => $label) {
      $value = esc_attr(get_post_meta($post->ID, $key, true));
      echo "<tr>
              <th><label for='{$key}'>{$label}</label></th>
              <td><input type='text' id='{$key}' name='{$key}' value='{$value}' class='regular-text' /></td>
            </tr>";
    }

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
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!current_user_can('edit_post', $post_id)) return;
    if (!isset($_POST['apidae_meta_nonce']) || !wp_verify_nonce($_POST['apidae_meta_nonce'], 'save_apidae_meta')) return;

    $fields = [
      'apidae_id',
      'apidae_identifier',
      'apidae_update_date_modification',
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
      'presentation_complement' => 'Complément de localisation',
      'nb_real' => 'Nombre réel d\'emplacements',
      'nb_mobilhomes' => 'Nombre de mobil-homes',
      'nb_bungalows' => 'Nombre de bungalows',
      'nb_insolites' => 'Nombre d\'hébergements insolites',
      'empl_campingcars' => 'Nombre d\'emplacements camping cars',
      'empl_caravanes' => 'Nombre d\'emplacements caravanes',
      'empl_tentes' => 'Nombre d\'emplacements tentes',
      'superficie' => 'Superficie',
      'ouverture' => 'Ouverture',
      'fermeture' => 'Fermeture',
      'id_reservation_direct' => 'ID de réservation Direct',
      'url_reservation_direct' => 'URL de réservation Direct',
      'id_reservation_ctoutvert' => 'ID de réservation CTOUTVERT',
      'langues' => 'Langues parlées',
      'periodes_date_debut' => 'Date de début',
      'periodes_date_fin' => 'Date de fin',
      'periodes_type' => 'Type d\'ouverture',
      'price_mini' => 'Prix mini',
      'price_max' => 'Prix max',
      'price_mini_mobilhomes' => 'Prix mini mobilhome',
      'price_max_mobilhomes' => 'Prix max mobilhome'
    ];

    foreach ($fields as $key => $label_or_key) {
      $name = is_string($key) ? $key : $label_or_key; // ← clé si associative, sinon valeur
      if (isset($_POST[$name])) {
        update_post_meta($post_id, $name, sanitize_text_field(wp_unslash($_POST[$name])));
      }
    }

    foreach ($_POST as $key => $value) {
      if (strpos($key, 'capacite_') === 0) {
        update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($value)));
      }
    }
  }

  /* ========= Admin list: colonne + tri + recherche apidae_id ========= */

  // 1) Ajouter la colonne
  public static function add_apidae_column($cols)
  {
    $new = [];
    foreach ($cols as $k => $v) {
      $new[$k] = $v;
      if ($k === 'title') {
        $new['apidae_id'] = __('Apidae ID', 'fdhpa17');
      }
    }
    if (!isset($new['apidae_id'])) {
      $new['apidae_id'] = __('Apidae ID', 'fdhpa17');
    }
    return $new;
  }

  // 2) Rendre le contenu de la colonne
  public static function render_apidae_column($col, $post_id)
  {
    if ($col !== 'apidae_id') return;
    $id = get_post_meta($post_id, 'apidae_id', true);
    if (!$id) {
      echo '<span style="opacity:.6">—</span>';
      return;
    }
    $esc = esc_attr($id);
    $url = admin_url('edit.php?post_type=camping&s=' . urlencode('apidae:' . $id));
    echo '<code style="font-size:12px;">' . esc_html($id) . '</code> ';
    echo '<a href="' . esc_url($url) . '" class="button button-small" style="margin-left:6px">' . esc_html__('Rechercher', 'fdhpa17') . '</a>';
    echo '<button type="button" class="button button-small" style="margin-left:6px" onclick="navigator.clipboard && navigator.clipboard.writeText(\'' . $esc . '\')">'
      . esc_html__('Copier', 'fdhpa17') . '</button>';
  }

  // 3) Rendre la colonne triable
  public static function make_apidae_column_sortable($cols)
  {
    $cols['apidae_id'] = 'apidae_id';
    return $cols;
  }

  // 4) Étendre tri + recherche
  public static function extend_admin_search_and_sort($q)
  {
    if (!is_admin() || !$q->is_main_query()) return;
    if (($q->get('post_type') ?? '') !== 'camping') return;

    // Tri par apidae_id
    if ($q->get('orderby') === 'apidae_id') {
      $q->set('meta_key', 'apidae_id');
      $q->set('orderby', 'meta_value_num'); // tente numérique
    }

    // Recherche
    $s = $q->get('s');
    if (!is_string($s) || $s === '') return;

    // Mode opérateur dédié: apidae:TERM
    if (preg_match('/^apidae:(.+)$/i', $s, $m)) {
      $term = trim($m[1]);
      $q->set('s', ''); // neutralise la recherche plein texte
      if (ctype_digit($term)) {
        $q->set('meta_query', [[
          'key'     => 'apidae_id',
          'value'   => $term,
          'compare' => '=',
        ]]);
      } else {
        $q->set('meta_query', [[
          'key'     => 'apidae_id',
          'value'   => $term,
          'compare' => 'LIKE',
        ]]);
      }
      return;
    }

    // Sinon : on ajoute un LIKE sur apidae_id en plus de la recherche standard
    $meta_query = (array) $q->get('meta_query');
    $meta_query[] = [
      'key'     => 'apidae_id',
      'value'   => $s,
      'compare' => 'LIKE',
    ];
    $q->set('meta_query', $meta_query);

    // éviter doublons potentiels
    add_filter('posts_distinct', function ($distinct) {
      return 'DISTINCT';
    });
  }

  // 5) Placeholder du champ recherche
  public static function enhance_search_placeholder()
  {
    if (($_GET['post_type'] ?? '') !== 'camping') return;
?>
    <script>
      (function() {
        var s = document.querySelector('#posts-filter input[name="s"]');
        if (s) s.placeholder = 'Rechercher… (ex: apidae:5752595 ou 5752)';
      })();
    </script>
  <?php
  }

  // Affiche l'input "Apidae ID" dans la liste des campings (barre de filtres)
  public static function add_apidae_id_filter()
  {
    global $typenow;
    if ($typenow !== 'camping') return;

    $current = isset($_GET['apidae_id']) ? sanitize_text_field(wp_unslash($_GET['apidae_id'])) : '';
    $mode    = isset($_GET['apidae_match']) ? sanitize_text_field(wp_unslash($_GET['apidae_match'])) : 'exact';

  ?>
    <style>
      .apidae-inline {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        margin-left: 8px;
      }

      .apidae-inline input[type="text"] {
        width: 150px;
      }
    </style>
    <div class="apidae-inline">
      <label for="filter-apidae-id"><strong>Apidae ID</strong></label>
      <input type="text" id="filter-apidae-id" name="apidae_id" value="<?php echo esc_attr($current); ?>" placeholder="ex: 5752595" />
      <select name="apidae_match">
        <option value="exact" <?php selected($mode, 'exact'); ?>>Exact</option>
        <option value="contain" <?php selected($mode, 'contain'); ?>>Contient</option>
      </select>
      <?php
      // WP ajoute déjà un bouton "Filtrer", donc pas besoin d’un submit en plus.
      // Si tu veux, décommente ceci :
      // echo '<button class="button">Filtrer</button>';
      ?>
    </div>
<?php
  }

  // Applique le filtre "Apidae ID" à la requête admin
  public static function apply_apidae_id_filter($q)
  {
    if (!is_admin() || !$q->is_main_query()) return;
    if (($q->get('post_type') ?? '') !== 'camping') return;

    if (!isset($_GET['apidae_id']) || $_GET['apidae_id'] === '') return;

    $value = sanitize_text_field(wp_unslash($_GET['apidae_id']));
    $mode  = isset($_GET['apidae_match']) ? sanitize_text_field(wp_unslash($_GET['apidae_match'])) : 'exact';

    // Construire le meta_query en fonction du mode
    $compare = 'exact' === $mode && ctype_digit($value) ? '=' : 'LIKE';
    $needle  = ('LIKE' === $compare) ? $value : $value;

    $meta_query = (array) $q->get('meta_query');
    $meta_query[] = [
      'key'     => 'apidae_id',
      'value'   => $needle,
      'compare' => $compare,
    ];
    $q->set('meta_query', $meta_query);

    // Évite des doublons si d'autres meta_query existent
    add_filter('posts_distinct', function ($distinct) {
      return 'DISTINCT';
    });
  }
}

CPT_CAMPING::init();
