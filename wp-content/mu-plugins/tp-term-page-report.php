<?php
/**
 * Plugin Name: TP Term Page Report (MU)
 * Description: Liste toutes les liaisons Term -> term_page (meta _linked_term_page_id) dans un tableau admin + export CSV.
 * Author: You
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Fallbacks si tes constantes ne sont pas chargées au moment où ce MU-plugin s'exécute.
 * (Il se synchronisera automatiquement si les constantes existent déjà.)
 */
if (!defined('TP_META_KEY')) {
  define('TP_META_KEY', '_linked_term_page_id');
}

if (!defined('TP_TAXONOMIES')) {
  // Mets ici la même liste que dans ton plugin principal si besoin
  define('TP_TAXONOMIES', [
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
  ]);
}

/**
 * Petit helper sûr
 */
function tp_report_get_taxonomies() {
  $tax = TP_TAXONOMIES;
  if (!is_array($tax)) $tax = [];
  // On ne garde que les taxos existantes
  $tax = array_values(array_filter($tax, function($t){
    return is_string($t) && $t && taxonomy_exists($t);
  }));
  return $tax;
}

add_action('admin_menu', function () {
  add_management_page(
    'Term Pages – Liaison',
    'Term Pages – Liaison',
    'manage_options',
    'tp-term-page-report',
    'tp_report_render_page'
  );
});

/**
 * Export CSV (GET ?page=tp-term-page-report&tp_export=1...)
 */
add_action('admin_init', function () {
  if (!is_admin()) return;
  if (!current_user_can('manage_options')) return;
  if (empty($_GET['page']) || $_GET['page'] !== 'tp-term-page-report') return;
  if (empty($_GET['tp_export'])) return;

  $taxonomies = tp_report_get_taxonomies();

  $tax = isset($_GET['tax']) ? sanitize_text_field($_GET['tax']) : 'all';
  $only_linked = !empty($_GET['only_linked']) ? 1 : 0;
  $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

  $rows = tp_report_collect_rows($taxonomies, [
    'tax' => $tax,
    'only_linked' => $only_linked,
    's' => $s,
    'paged' => 1,
    'per_page' => 999999, // export complet
  ]);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=tp-term-page-report.csv');

  $out = fopen('php://output', 'w');
  fputcsv($out, [
    'taxonomy',
    'term_id',
    'term_name',
    'term_slug',
    'term_parent_id',
    'term_parent_name',
    'term_link',
    'linked_term_page_id',
    'linked_term_page_title',
    'linked_term_page_status',
    'linked_term_page_edit',
  ]);

  foreach ($rows['items'] as $r) {
    fputcsv($out, [
      $r['taxonomy'],
      $r['term_id'],
      $r['term_name'],
      $r['term_slug'],
      $r['term_parent_id'],
      $r['term_parent_name'],
      $r['term_link'],
      $r['post_id'],
      $r['post_title'],
      $r['post_status'],
      $r['post_edit'],
    ]);
  }

  fclose($out);
  exit;
});

function tp_report_render_page() {
  if (!current_user_can('manage_options')) return;

  $taxonomies = tp_report_get_taxonomies();
  $tax = isset($_GET['tax']) ? sanitize_text_field($_GET['tax']) : 'all';
  $only_linked = !empty($_GET['only_linked']) ? 1 : 0;
  $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

  $paged = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
  $per_page = isset($_GET['per_page']) ? max(10, (int)$_GET['per_page']) : 50;

  $data = tp_report_collect_rows($taxonomies, [
    'tax' => $tax,
    'only_linked' => $only_linked,
    's' => $s,
    'paged' => $paged,
    'per_page' => $per_page,
  ]);

  $base_url = admin_url('tools.php?page=tp-term-page-report');
  $query_base = [
    'page' => 'tp-term-page-report',
    'tax' => $tax,
    'only_linked' => $only_linked ? 1 : 0,
    's' => $s,
    'per_page' => $per_page,
  ];

  ?>
  <div class="wrap">
    <h1>Term Pages – Liaison (terms ↔ term_page)</h1>

    <form method="get" style="margin: 12px 0;">
      <input type="hidden" name="page" value="tp-term-page-report" />

      <label style="margin-right:10px;">
        Taxonomie :
        <select name="tax">
          <option value="all" <?php selected($tax, 'all'); ?>>Toutes</option>
          <?php foreach ($taxonomies as $t): ?>
            <option value="<?php echo esc_attr($t); ?>" <?php selected($tax, $t); ?>>
              <?php echo esc_html($t); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label style="margin-right:10px;">
        <input type="checkbox" name="only_linked" value="1" <?php checked($only_linked, 1); ?> />
        Liés uniquement
      </label>

      <label style="margin-right:10px;">
        Recherche :
        <input type="search" name="s" value="<?php echo esc_attr($s); ?>" placeholder="nom ou slug" />
      </label>

      <label style="margin-right:10px;">
        Par page :
        <input type="number" name="per_page" value="<?php echo esc_attr($per_page); ?>" min="10" step="10" style="width:90px;" />
      </label>

      <button class="button button-primary">Filtrer</button>

      <?php
        $export_url = add_query_arg(array_merge($query_base, ['tp_export' => 1]), admin_url('tools.php'));
      ?>
      <a class="button" href="<?php echo esc_url($export_url); ?>">Exporter CSV</a>
    </form>

    <p>
      Total lignes : <strong><?php echo (int)$data['total']; ?></strong>
      (page <?php echo (int)$paged; ?> / <?php echo (int)$data['total_pages']; ?>)
    </p>

    <table class="widefat striped">
      <thead>
        <tr>
          <th>Taxo</th>
          <th>Term</th>
          <th>Slug</th>
          <th>Parent</th>
          <th>Lien term</th>
          <th>term_page</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data['items'])): ?>
          <tr><td colspan="8">Aucun résultat.</td></tr>
        <?php else: ?>
          <?php foreach ($data['items'] as $r): ?>
            <tr>
              <td><?php echo esc_html($r['taxonomy']); ?></td>
              <td>
                <strong><?php echo esc_html($r['term_name']); ?></strong>
                <div style="color:#666;">#<?php echo (int)$r['term_id']; ?></div>
              </td>
              <td><?php echo esc_html($r['term_slug']); ?></td>
              <td>
                <?php if ($r['term_parent_id']): ?>
                  <?php echo esc_html($r['term_parent_name']); ?>
                  <div style="color:#666;">#<?php echo (int)$r['term_parent_id']; ?></div>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <?php if ($r['term_link']): ?>
                  <a href="<?php echo esc_url($r['term_link']); ?>" target="_blank" rel="noopener">Voir</a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td>
                <?php if ($r['post_id']): ?>
                  <strong><?php echo esc_html($r['post_title']); ?></strong>
                  <div style="color:#666;">#<?php echo (int)$r['post_id']; ?></div>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td><?php echo $r['post_status'] ? esc_html($r['post_status']) : '—'; ?></td>
              <td>
                <?php if ($r['term_edit']): ?>
                  <a class="button button-small" href="<?php echo esc_url($r['term_edit']); ?>">Éditer term</a>
                <?php endif; ?>
                <?php if ($r['post_edit']): ?>
                  <a class="button button-small" href="<?php echo esc_url($r['post_edit']); ?>">Éditer term_page</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <?php
      // Pagination
      if ($data['total_pages'] > 1) {
        echo '<div style="margin-top:12px;">';
        for ($p=1; $p <= $data['total_pages']; $p++) {
          $url = add_query_arg(array_merge($query_base, ['paged' => $p]), admin_url('tools.php'));
          if ($p === $paged) {
            echo '<span class="button button-primary" style="margin-right:4px;">'.$p.'</span>';
          } else {
            echo '<a class="button" style="margin-right:4px;" href="'.esc_url($url).'">'.$p.'</a>';
          }
        }
        echo '</div>';
      }
    ?>
  </div>
  <?php
}

/**
 * Collecte lignes (terms + post lié) avec pagination.
 */
function tp_report_collect_rows(array $taxonomies, array $args) {
  $tax = isset($args['tax']) ? $args['tax'] : 'all';
  $only_linked = !empty($args['only_linked']);
  $s = isset($args['s']) ? (string)$args['s'] : '';
  $paged = isset($args['paged']) ? max(1, (int)$args['paged']) : 1;
  $per_page = isset($args['per_page']) ? max(1, (int)$args['per_page']) : 50;

  $use_taxonomies = ($tax !== 'all' && taxonomy_exists($tax)) ? [$tax] : $taxonomies;

  $all = [];

  foreach ($use_taxonomies as $t) {
    $q = [
      'taxonomy'   => $t,
      'hide_empty' => false,
      'fields'     => 'all',
      'number'     => 0,
    ];

    // Filtre search sur name/slug
    if ($s !== '') {
      $q['search'] = $s;
    }

    // Filtre uniquement liés
    if ($only_linked) {
      $q['meta_query'] = [[
        'key'     => TP_META_KEY,
        'compare' => 'EXISTS',
      ]];
    }

    $terms = get_terms($q);
    if (is_wp_error($terms) || empty($terms)) continue;

    foreach ($terms as $term) {
      $post_id = (int) get_term_meta($term->term_id, TP_META_KEY, true);

      // Si "liés uniquement" et valeur vide (meta existe mais vide), on skip
      if ($only_linked && !$post_id) continue;

      $post_title = '';
      $post_status = '';
      $post_edit = '';
      if ($post_id) {
        $post_title = get_the_title($post_id);
        if (!$post_title) $post_title = '#'.$post_id;
        $post_status = get_post_status($post_id);
        $post_edit = get_edit_post_link($post_id, '');
      }

      $term_link = '';
      $tl = get_term_link($term);
      if (!is_wp_error($tl)) $term_link = $tl;

      $term_edit = get_edit_term_link($term->term_id, $term->taxonomy);

      $parent_name = '';
      if (!empty($term->parent)) {
        $p = get_term((int)$term->parent, $term->taxonomy);
        if ($p && !is_wp_error($p)) $parent_name = $p->name;
      }

      $all[] = [
        'taxonomy' => $term->taxonomy,
        'term_id' => (int)$term->term_id,
        'term_name' => (string)$term->name,
        'term_slug' => (string)$term->slug,
        'term_parent_id' => (int)$term->parent,
        'term_parent_name' => $parent_name ?: '—',
        'term_link' => $term_link,
        'term_edit' => $term_edit,
        'post_id' => $post_id,
        'post_title' => $post_title,
        'post_status' => $post_status,
        'post_edit' => $post_edit,
      ];
    }
  }

  // Tri: taxo -> term_name
  usort($all, function($a, $b){
    $t = strcmp($a['taxonomy'], $b['taxonomy']);
    if ($t !== 0) return $t;
    return strcasecmp($a['term_name'], $b['term_name']);
  });

  $total = count($all);
  $total_pages = (int) ceil($total / $per_page);
  $offset = ($paged - 1) * $per_page;
  $items = array_slice($all, $offset, $per_page);

  return [
    'total' => $total,
    'total_pages' => max(1, $total_pages),
    'items' => $items,
  ];
}
