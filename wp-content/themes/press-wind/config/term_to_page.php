<?php
/** =======================
 *  RÉGLAGES
 *  ======================= */
const TP_TAXONOMIES = [
  'category',
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

// Crée automatiquement une term_page à la création d’un terme si aucune n’est fournie
const TP_AUTO_CREATE_ON_TERM_CREATE = true;

// Redirection 301 de l’archive du terme vers la page liée (par taxonomie)
// Mets true pour certaines taxos seulement si tu veux rediriger plutôt qu’injecter le contenu.
const TP_REDIRECT_MAP = [
  'category'    => false,
  'destination' => false,
  'atout'       => false,
  'etoile'      => false,
  'aquatique'   => false,
  'service'     => false,
  'label'       => false,
  'hebergement' => false,
  'cible'       => false,
  'groupe'      => false,
];

// Meta key commune pour stocker l’ID de la term_page liée
const TP_META_KEY = '_linked_term_page_id';


/** =======================
 *  1) CPT "term_page"
 *  ======================= */
add_action('init', function () {
  register_post_type('term_page', [
    'label'               => 'Pages de taxonomie',
    'public'              => true,                 // public => Yoast SEO OK
    'publicly_queryable'  => false,                // pas d’URL publique dédiée (on utilise les archives de terme, ou redirection)
    'exclude_from_search' => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_rest'        => true,                 // Gutenberg
    'hierarchical'        => true,
    'supports'            => ['title','editor','thumbnail','revisions','page-attributes'],
    'menu_position'       => 21,
  ]);
});


/** =======================
 *  Helpers
 *  ======================= */
function tp_get_linked_post_id($term_id) {
  return (int) get_term_meta($term_id, TP_META_KEY, true);
}
function tp_set_linked_post_id($term_id, $post_id) {
  update_term_meta($term_id, TP_META_KEY, (int) $post_id);
}
function tp_tax_should_redirect($taxonomy) {
  return !empty(TP_REDIRECT_MAP[$taxonomy]);
}
function tp_get_term_by_term_page( $post_id ) {
  $post_id = (int) $post_id;
  if ( $post_id <= 0 ) return null;

  foreach ( TP_TAXONOMIES as $tax ) {
    $terms = get_terms([
      'taxonomy'   => $tax,
      'hide_empty' => false,
      'number'     => 1,
      'fields'     => 'all',
      'meta_query' => [[
        'key'     => TP_META_KEY,
        'value'   => $post_id,
        'compare' => '=',
      ]],
    ]);

    if ( ! is_wp_error($terms) && ! empty($terms) ) {
      return $terms[0]; // Premier match (il ne devrait y en avoir qu’un)
    }
  }

  return null;
}

/**
 * Retourne l'URL de l'archive du terme lié à une term_page donnée.
 * @param int $post_id ID du post term_page
 * @return string|null URL ou null si aucun terme lié
 */
function tp_get_term_url_by_term_page( $post_id ) {
  $post_id = (int) $post_id;
  if ( $post_id <= 0 ) return null;

  foreach ( TP_TAXONOMIES as $tax ) {
    $terms = get_terms([
      'taxonomy'   => $tax,
      'hide_empty' => false,
      'number'     => 1,
      'fields'     => 'all',
      'meta_query' => [[
        'key'     => TP_META_KEY,
        'value'   => $post_id,
        'compare' => '=',
      ]],
    ]);

    if ( ! is_wp_error($terms) && ! empty($terms) ) {
      $term = $terms[0];
      $link = get_term_link($term);
      if ( ! is_wp_error($link) ) return $link;
    }
  }

  return null;
}


function tp_get_taxonomy_by_term_page( $post_id ) {
  $term = tp_get_term_by_term_page( $post_id );
  return $term ? $term->taxonomy : null;
}


/** =======================
 *  2) Champs admin + sauvegarde (toutes taxos)
 *  ======================= */
foreach (TP_TAXONOMIES as $tax) {

  // Ajout du champ sur "ajouter un terme"
  add_action($tax . '_add_form_fields', function () {
    ?>
    <div class="form-field">
      <label for="linked_term_page_id">Page liée (CPT term_page)</label>
      <?php
      wp_dropdown_pages([
        'post_type'         => 'term_page',
        'name'              => 'linked_term_page_id',
        'show_option_none'  => '— Aucune —',
        'option_none_value' => '0',
        'selected'          => 0,
      ]);
      ?>
      <p class="description">Sélectionne la page (CPT term_page) dont le contenu sera utilisé pour ce terme.</p>
    </div>
    <?php
  });

  // Champ sur "éditer un terme"
  add_action($tax . '_edit_form_fields', function ($term) {
    $linked = tp_get_linked_post_id($term->term_id);
    ?>
    <tr class="form-field">
      <th scope="row"><label for="linked_term_page_id">Page liée (CPT term_page)</label></th>
      <td>
        <?php
        wp_dropdown_pages([
          'post_type'         => 'term_page',
          'name'              => 'linked_term_page_id',
          'show_option_none'  => '— Aucune —',
          'option_none_value' => '0',
          'selected'          => $linked,
        ]);
        ?>
        <p class="description">Laisse vide pour ne pas lier.</p>
        <?php if ($linked) { $edit = get_edit_post_link($linked, ''); if ($edit) echo '<p><a class="button" href="'.$edit.'">Éditer le contenu (Gutenberg)</a></p>'; } ?>
      </td>
    </tr>
    <?php
  });

  // Sauvegarde (création)
  add_action('created_' . $tax, function ($term_id) use ($tax) {

    // Ne rien faire si on est en CLI
     if (defined('WP_CLI') && WP_CLI) {
        return;
    }
    
    if (isset($_POST['linked_term_page_id'])) {
      tp_set_linked_post_id($term_id, (int) $_POST['linked_term_page_id']);
      return;
    }
    if (!TP_AUTO_CREATE_ON_TERM_CREATE) return;

    $term = get_term($term_id, $tax);
    if (is_wp_error($term)) return;

    // Hériter du parent si déjà lié
    $parent_post = 0;
    if ($term->parent) $parent_post = tp_get_linked_post_id($term->parent);

    $post_id = wp_insert_post([
      'post_type'   => 'term_page',
      'post_title'  => $term->name,
      'post_name'   => $term->slug,
      'post_parent' => $parent_post,
      'post_status' => 'draft',
    ]);
    if ($post_id && !is_wp_error($post_id)) {
      tp_set_linked_post_id($term_id, $post_id);
    }
  });

  // Sauvegarde (édition) + sync titre/parent
  add_action('edited_' . $tax, function ($term_id) use ($tax) {
    if (isset($_POST['linked_term_page_id'])) {
      tp_set_linked_post_id($term_id, (int) $_POST['linked_term_page_id']);
    }
    $term = get_term($term_id, $tax);
    $post_id = tp_get_linked_post_id($term_id);
    if (!$post_id) return;

    $parent_post = 0;
    if ($term->parent) $parent_post = tp_get_linked_post_id($term->parent);

    wp_update_post([
      'ID'          => $post_id,
      'post_title'  => $term->name,
      'post_parent' => $parent_post,
    ]);
  });

  // Colonne "Page liée" dans la liste des termes
  add_filter('manage_edit-' . $tax . '_columns', function ($cols) {
    $cols['linked_term_page'] = 'Page liée';
    return $cols;
  });
  add_filter('manage_' . $tax . '_custom_column', function ($content, $column, $term_id) {
    if ($column !== 'linked_term_page') return $content;
    $pid = tp_get_linked_post_id($term_id);
    if (!$pid) return '—';
    $title = get_the_title($pid) ?: ('#'.$pid);
    $edit  = get_edit_post_link($pid, '');
    return $edit ? '<a href="'.$edit.'">'.$title.'</a>' : $title;
  }, 10, 3);

  // Lien d’action rapide “Éditer le contenu (Gutenberg)”
  add_filter('term_row_actions', function ($actions, $term) use ($tax) {
    if ($term->taxonomy !== $tax) return $actions;
    $pid = tp_get_linked_post_id($term->term_id);
    if ($pid && ($url = get_edit_post_link($pid, ''))) {
      $actions['edit_term_page'] = '<a href="'.$url.'">Éditer le contenu (Gutenberg)</a>';
    }
    return $actions;
  }, 10, 2);
}


/** =======================
 *  3) Rendu front
 *     - Injection du contenu sur les archives (par défaut)
 *     - OU Redirection 301 selon TP_REDIRECT_MAP
 *  ======================= */

// Shortcode utilisable dans un template FSE : [term_page_content]
add_shortcode('term_page_content', function () {
  $qo = get_queried_object();

  if (!$qo ) return '';

  $term = get_queried_object();
  if (!$term || empty($term->term_id)) return '';

  $post_id = tp_get_linked_post_id($term->term_id);
  if (!$post_id) return '';

  $post = get_post($post_id);
  if (!$post || $post->post_status !== 'publish') return '';

  // var_dump($post);
  echo '<section class="term-page-content">';
  $content = get_post_field('post_content', $post_id);
  echo apply_filters('the_content', $content);
  echo '</section>';

});

// Redirection 301 si activée pour la taxo
add_action('template_redirect', function () {
  if (!is_tax()) return;
  $term = get_queried_object();
  if (!$term || empty($term->taxonomy)) return;

  if (!tp_tax_should_redirect($term->taxonomy)) return;

  $post_id = tp_get_linked_post_id($term->term_id);
  if (!$post_id || get_post_status($post_id) !== 'publish') return;

  $url = get_permalink($post_id);
  if ($url) { wp_redirect($url, 301); exit; }
});

// Injection automatique (au-dessus de la boucle) si PAS de redirection et si thème classique (non-FSE).
// Si tu es 100% FSE, préfère le shortcode [term_page_content] dans ton template d’archive.
add_action('loop_start', function ($q) {
  if (! $q->is_main_query() || !is_tax()) return;

  $term = get_queried_object();
  if (!$term || tp_tax_should_redirect($term->taxonomy)) return;

  $post_id = tp_get_linked_post_id($term->term_id);
  if (!$post_id || get_post_status($post_id) !== 'publish') return;

  echo '<section class="term-page-content">';
  if (has_post_thumbnail($post_id)) {
    echo get_the_post_thumbnail($post_id, 'large', ['style'=>'margin-bottom:1rem;display:block;']);
  }
  $content = get_post_field('post_content', $post_id);
  echo apply_filters('the_content', $content);
  echo '</section>';
});



/** =======================
 *  WPML — Sync uniquement lors de l'update d'un terme
 *  Conditions :
 *   - si la traduction n'a pas de page liée → on met la page traduite
 *   - si elle en a une différente → on remplace par la page traduite
 *   - sinon → no-op
 *  ======================= */

if ( defined('ICL_SITEPRESS_VERSION') ) {

  /**
   * Retourne le tableau des traductions d'un élément WPML, indexé par code langue.
   * @param int    $element_id
   * @param string $element_type 'post_term_page' pour le CPT, 'tax_{taxonomy}' pour un terme
   * @return array<string, object>
   */
  function tp_wpml_get_translations_map( $element_id, $element_type ) {
    $trid = apply_filters( 'wpml_element_trid', null, $element_id, $element_type );
    if ( empty( $trid ) ) return [];
    $translations = apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );
    return is_array($translations) ? $translations : [];
  }

  /**
   * Synchronise les liens pour TOUTES les traductions d'un terme, en se basant
   * sur la page liée au terme courant (dans sa langue) et sa table de traductions.
   * @param int    $term_id
   * @param string $taxonomy
   */
  function tp_wpml_sync_term_links_conditional( $term_id, $taxonomy ) {
    // Page liée au terme courant (langue actuelle du terme)
    $src_post_id = tp_get_linked_post_id( $term_id );
    if ( ! $src_post_id ) {
      // Sans page source, on ne peut rien propager.
      return;
    }

    // Maps de traductions
    $term_translations = tp_wpml_get_translations_map( $term_id, 'tax_' . $taxonomy );
    if ( empty( $term_translations ) ) return;

    $page_translations = tp_wpml_get_translations_map( $src_post_id, 'post_term_page' );
    if ( empty( $page_translations ) ) return;

    foreach ( $term_translations as $lang => $t_trans ) {
      if ( empty( $t_trans->element_id ) ) continue;

      // S'il n'existe pas de page traduite dans cette langue → on ignore
      if ( empty( $page_translations[$lang] ) || empty( $page_translations[$lang]->element_id ) ) continue;

      $term_id_tr   = (int) $t_trans->element_id;
      $page_id_tr   = (int) $page_translations[$lang]->element_id;
      $current_link = (int) tp_get_linked_post_id( $term_id_tr );

      // CONDITIONS :
      // 1) pas de page liée → setter
      if ( ! $current_link ) {
        tp_set_linked_post_id( $term_id_tr, $page_id_tr );
        continue;
      }

      // 2) page liée différente → remplacer par la nouvelle
      if ( $current_link !== $page_id_tr ) {
        tp_set_linked_post_id( $term_id_tr, $page_id_tr );
        continue;
      }

      // 3) sinon, même page → ne rien faire
    }
  }

  // On déclenche UNIQUEMENT lors de la création/édition d'un terme (pas côté post)
  foreach ( TP_TAXONOMIES as $tax ) {
    add_action( 'created_' . $tax, function( $term_id ) use ( $tax ) {
      tp_wpml_sync_term_links_conditional( $term_id, $tax );
    }, 20 );

    add_action( 'edited_' . $tax, function( $term_id ) use ( $tax ) {
      tp_wpml_sync_term_links_conditional( $term_id, $tax );
    }, 20 );
  }
}


/**
 * WP-CLI: tp sync-links
 * - Parcourt toutes les taxonomies de TP_TAXONOMIES
 * - Pour chaque terme, propage le lien vers la bonne term_page traduite
 *   uniquement si le terme source a déjà une page liée.
 *
 * Usage:
 *   wp tp sync-links
 *   wp tp sync-links --dry-run
 *   wp tp sync-links --taxonomy=destination --taxonomy=service
 */

if ( defined('WP_CLI') && WP_CLI ) {

  // Sécurité : exigences minimales
  if ( ! function_exists('tp_get_linked_post_id') || ! function_exists('tp_set_linked_post_id') ) {
    WP_CLI::warning( 'Les helpers tp_get_linked_post_id/tp_set_linked_post_id sont introuvables. Charge term_to_page.php avant cette commande.' );
  }

  if ( ! defined('ICL_SITEPRESS_VERSION') ) {
    WP_CLI::error( 'WPML n’est pas actif (ICL_SITEPRESS_VERSION non défini). Abandon.' );
    return;
  }

  if ( ! defined('TP_TAXONOMIES') || ! is_array(TP_TAXONOMIES) ) {
    WP_CLI::error( 'TP_TAXONOMIES est introuvable ou invalide.' );
    return;
  }

  // Helpers WPML (définis si absents)
  if ( ! function_exists('tp_wpml_get_translations_map') ) {
    /**
     * Retourne les traductions WPML d’un élément (post ou term) indexées par code langue.
     * @param int    $element_id
     * @param string $element_type 'post_term_page' pour le CPT, 'tax_{taxonomy}' pour un terme
     * @return array<string, object>
     */
    function tp_wpml_get_translations_map( $element_id, $element_type ) {
      $trid = apply_filters( 'wpml_element_trid', null, $element_id, $element_type );
      if ( empty( $trid ) ) return [];
      $translations = apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );
      return is_array($translations) ? $translations : [];
    }
  }

  class TP_WPML_Sync_CLI_Command {

    /**
     * (Re)lie les traductions de termes à la bonne term_page traduite.
     *
     * ## OPTIONS
     *
     * [--taxonomy=<slug>]
     * : Limite à une ou plusieurs taxonomies (option répétable).
     *
     * [--dry-run]
     * : Affiche ce qui serait modifié sans rien écrire.
     *
     * ## EXAMPLES
     *
     *     wp tp sync-links
     *     wp tp sync-links --dry-run
     *     wp tp sync-links --taxonomy=destination --taxonomy=service
     */
    public function sync_links( $args, $assoc_args ) {

      $dry_run = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
      $only_tax = isset( $assoc_args['taxonomy'] )
        ? (array) $assoc_args['taxonomy']
        : TP_TAXONOMIES;

      // Stats globales
      $stats = [
        'taxonomies' => [],
        'terms_seen' => 0,
        'set_links'  => 0,
        'updated'    => 0,
        'unchanged'  => 0,
        'skipped_no_src_page' => 0,
        'skipped_no_tr_page'  => 0,
      ];

      foreach ( $only_tax as $tax ) {
        if ( ! taxonomy_exists( $tax ) ) {
          WP_CLI::warning( "Taxonomie inconnue: {$tax}" );
          continue;
        }

        WP_CLI::log( "→ Taxonomie: {$tax}" );

        $term_ids = get_terms( [
          'taxonomy'   => $tax,
          'hide_empty' => false,
          'fields'     => 'ids',
        ] );

        if ( is_wp_error( $term_ids ) ) {
          WP_CLI::warning( "  Impossible de lister les termes: " . $term_ids->get_error_message() );
          continue;
        }

        $progress = \WP_CLI\Utils\make_progress_bar( "  Traitement des termes ({$tax})", count( $term_ids ) );

        foreach ( $term_ids as $term_id ) {
          $progress->tick();
          $res = $this->sync_one_term( (int) $term_id, $tax, $dry_run );

          $stats['terms_seen']++;

          if ( $res === 'no_src' ) {
            $stats['skipped_no_src_page']++;
          } elseif ( $res === 'no_translation_page' ) {
            $stats['skipped_no_tr_page']++;
          } elseif ( $res === 'set' ) {
            $stats['set_links']++;
          } elseif ( $res === 'updated' ) {
            $stats['updated']++;
          } elseif ( $res === 'same' ) {
            $stats['unchanged']++;
          }
        }

        $progress->finish();
        $stats['taxonomies'][] = $tax;
      }

      // Récap
      WP_CLI::log('');
      WP_CLI::log('=== Résumé ===');
      WP_CLI::log('Taxonomies: ' . implode(', ', $stats['taxonomies']));
      WP_CLI::log('Termes parcourus: ' . $stats['terms_seen']);
      WP_CLI::log('Liens ajoutés:    ' . $stats['set_links']);
      WP_CLI::log('Liens mis à jour: ' . $stats['updated']);
      WP_CLI::log('Inchangés:        ' . $stats['unchanged']);
      WP_CLI::log('Ignorés (pas de page source liée): ' . $stats['skipped_no_src_page']);
      WP_CLI::log('Ignorés (pas de page traduite dispo): ' . $stats['skipped_no_tr_page']);

      if ( $dry_run ) {
        WP_CLI::success( 'Dry-run terminé. Aucune modification écrite.' );
      } else {
        WP_CLI::success( 'Synchronisation terminée.' );
      }
    }

    /**
     * Applique la logique sur un terme donné:
     * - Si le terme n’a PAS de page source liée → no-op ('no_src').
     * - Pour chaque langue:
     *    - si terme traduit n’a pas de lien → set ('set')
     *    - si lien != page traduite → update ('updated')
     *    - sinon → 'same'
     * - S’il n’existe pas de traduction de la page dans la langue → 'no_translation_page'
     *
     * @param int    $term_id
     * @param string $taxonomy
     * @param bool   $dry_run
     * @return string One of: set|updated|same|no_src|no_translation_page (dernière action rencontrée)
     */
    protected function sync_one_term( $term_id, $taxonomy, $dry_run ) {
      // 1) Page liée au terme courant (langue source du terme)
      $src_post_id = tp_get_linked_post_id( $term_id );
      if ( ! $src_post_id ) {
        return 'no_src';
      }

      // 2) Récupérer les maps de traductions
      $term_translations = tp_wpml_get_translations_map( $term_id, 'tax_' . $taxonomy );
      if ( empty( $term_translations ) ) return 'same';

      $page_translations = tp_wpml_get_translations_map( $src_post_id, 'post_term_page' );
      if ( empty( $page_translations ) ) return 'no_translation_page';

      $last_action = 'same';

      foreach ( $term_translations as $lang => $t_trans ) {
        if ( empty( $t_trans->element_id ) ) continue;

        // Pas de page traduite pour cette langue → on ignore
        if ( empty( $page_translations[ $lang ] ) || empty( $page_translations[ $lang ]->element_id ) ) {
          $last_action = 'no_translation_page';
          continue;
        }

        $term_id_tr = (int) $t_trans->element_id;
        $page_id_tr = (int) $page_translations[ $lang ]->element_id;
        $current    = (int) tp_get_linked_post_id( $term_id_tr );

        // 1) Pas de page liée → set
        if ( ! $current ) {
          $this->maybe_set_link( $term_id_tr, $page_id_tr, $dry_run, $taxonomy, $lang, 'SET' );
          $last_action = 'set';
          continue;
        }

        // 2) Lien différent → update
        if ( $current !== $page_id_tr ) {
          $this->maybe_set_link( $term_id_tr, $page_id_tr, $dry_run, $taxonomy, $lang, 'UPDATE' );
          $last_action = 'updated';
          continue;
        }

        // 3) Sinon identique → no-op
        $last_action = ( $last_action === 'set' || $last_action === 'updated' ) ? $last_action : 'same';
      }

      return $last_action;
    }

    protected function maybe_set_link( $term_id, $page_id, $dry_run, $taxonomy, $lang, $action ) {
      $term = get_term( $term_id, $taxonomy );
      $t_name = $term && ! is_wp_error($term) ? $term->name : "#{$term_id}";
      $p_title = get_the_title( $page_id ) ?: "#{$page_id}";
      WP_CLI::log( sprintf( '  [%s][%s] %s → %s', $taxonomy, $lang, $t_name, $p_title ) );

      if ( ! $dry_run ) {
        tp_set_linked_post_id( $term_id, $page_id );
      }
    }
  }

  WP_CLI::add_command( 'tp sync-links', [ new TP_WPML_Sync_CLI_Command(), 'sync_links' ] );
}
