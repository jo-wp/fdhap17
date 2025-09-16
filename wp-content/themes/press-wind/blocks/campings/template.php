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
  $q = new WP_Query([
    'post_type'      => 'camping',          
    'post_status'    => 'publish',
    'posts_per_page' => 1,                  
    'no_found_rows'  => false,             
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

}
?>
<section <?= get_block_wrapper_attributes(["class" => 'block-cards']); ?>>
  <h2 class="text-center">Sélection de campings en cours de développement</h2>
  <p class=" text-center"><?= esc_html( $campings_count ); ?> camping<?= $campings_count > 1 ? 's' : '' ?> trouvé<?= $campings_count > 1 ? 's' : '' ?>.</p>
</section>
