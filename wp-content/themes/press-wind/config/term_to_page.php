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

