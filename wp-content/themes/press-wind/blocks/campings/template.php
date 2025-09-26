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
    'post_type' => 'camping',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'no_found_rows' => false,
    'tax_query' => [
      [
        'taxonomy' => $term->taxonomy,
        'field' => 'term_id',
        'terms' => [$term->term_id],
        'include_children' => true,
        'operator' => 'IN',
      ]
    ],
    'fields' => 'ids',
  ]);
  $campings_count = (int) $q->found_posts;
  wp_reset_postdata();

}
?>
<section <?= get_block_wrapper_attributes(["class" => 'container-huge block-campings bg-bgOrange rounded-[100px] p-[100px]']); ?>>
  <div class="flex flex-row gap-[150px] ">
    <div class="min-w-[20%]">
      <?php
      $facets = FWP()->helper->get_facets(); 
      if (!empty($facets)) {
        foreach ($facets as $facet) {
if ('pager' === $facet['type']
          || 'date_range' === $facet['type']
          || 'classement_block' === $facet['name']
          || 'destination' === $facet['name'] ) {
            continue;
          }
          echo '<div class="facet-block">';
            echo '<p class="ctitle text-orange font-arial text-[15px] font-[700] m-0 mb-[15px]">' . esc_html($facet['label']) . '</p>';
            echo '<div class="facet-wrapper [&_span]:text-[#7F7F7F] [&_span]:font-arial [&_span]:text-[13px]">';
            echo do_shortcode('[facetwp facet="' . esc_attr($facet['name']) . '"]');
            echo '</div>';
          echo '</div>';
        }
      }
      ?>
    </div>
    <div class="min-w-[70%]">
      <h2 class="text-center text-[36px] leading-0"><?= __('Notre sélection','fdhpa17'); ?> <span class="font-arial text-[16px] block">de
  
      <?= __('camping à','fdhpa17'); ?> <?= $term->name ?></span></h2>
      <p class=" text-center"><?= esc_html($campings_count); ?> camping<?= $campings_count > 1 ? 's' : '' ?>
        trouvé<?= $campings_count > 1 ? 's' : '' ?>.</p>
      <?= do_shortcode('[facetwp template="full"]'); ?>
    </div>
  </div>
  <div class="flex flew-col justify-center items-center mt-[50px]">
    <?= do_shortcode('[facetwp facet="pagination"]') ?>
  </div>
</section>