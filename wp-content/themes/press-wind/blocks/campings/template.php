<?php
/**
 * Campings Selection
 *
 * @param array $block The block settings and attributes.
 */
$term = null;
if (is_tax() || is_category() || is_tag()) {
  $term = get_queried_object();
}

// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
    ]
  ],
  [
    'core/paragraph',
    [
      "placeholder" => "Description ..."
    ]
  ]
];

$campings_count = 0;

if ($term && isset($term->term_id, $term->taxonomy)) {
  // OPTION A : compter uniquement les posts du CPT "camping" dans ce terme
  $q = new WP_Query([
    'post_type'      => 'camping',          // <-- adapte si ton CPT a un autre slug
    'post_status'    => 'publish',
    'posts_per_page' => 1,                  // on ne récupère rien, on lit juste found_posts
    'no_found_rows'  => false,              // nécessaire pour remplir found_posts
    'tax_query'      => [[
      'taxonomy'         => $term->taxonomy,
      'field'            => 'term_id',
      'terms'            => [$term->term_id],
      'include_children' => true,
      'operator'         => 'IN',
    ]],
    'fields'         => 'ids',
  ]);
  $campings_count = (int) $q->found_posts;
  wp_reset_postdata();

  // OPTION B (alternative rapide) : si la taxo n'est *que* pour "camping"
  // $campings_count = (int) $term->count;
}
?>
<section <?= get_block_wrapper_attributes(["class" => 'block-cards']); ?>>
  <h2 class="text-center">Sélection de campings en cours de développement</h2>
  <p class=" text-center"><?= esc_html( $campings_count ); ?> camping<?= $campings_count > 1 ? 's' : '' ?> trouvé<?= $campings_count > 1 ? 's' : '' ?>.</p>
</section>
