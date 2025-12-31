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

  // Taxonomie + terms utilisés pour la requête
  $taxonomy = $term->taxonomy;
  $term_ids = [$term->term_id];

  /**
   * Si on n’est pas déjà sur la taxo "liste", on regarde si le term courant
   * possède un champ ACF (ex: "apidae_list_selection") qui pointe vers
   * un ou plusieurs termes de la taxonomie "liste".
   * Si oui, on compte les campings liés à cette/ces "liste(s)" à la place.
   */
  if ('liste' !== $taxonomy && function_exists('get_field')) {
    // Clé ACF pour un term : "taxonomy_termId"
    $field_key   = $taxonomy . '_' . $term->term_id;
    $liste_value = get_field('apidae_list_selection', $field_key);

    if (!empty($liste_value)) {
      $liste_ids = [];

      if (is_array($liste_value)) {
        foreach ($liste_value as $val) {
          if (is_object($val) && isset($val->term_id)) {
            $liste_ids[] = (int) $val->term_id;
          } else {
            $liste_ids[] = (int) $val;
          }
        }
      } elseif (is_object($liste_value) && isset($liste_value->term_id)) {
        $liste_ids[] = (int) $liste_value->term_id;
      } else {
        $liste_ids[] = (int) $liste_value;
      }

      $liste_ids = array_values(array_unique(array_filter($liste_ids)));

      if (!empty($liste_ids)) {
        $taxonomy = 'liste';   // ⚠️ slug de ta taxonomie liste
        $term_ids = $liste_ids;
      }
    }
  }

  $q = new WP_Query([
    'post_type'           => 'camping',
    'post_status'         => 'publish',
    'posts_per_page'      => 1,
    'no_found_rows'       => false,
    'fields'              => 'ids',
    'ignore_sticky_posts' => true,
    'tax_query'           => [[
      'taxonomy'         => $taxonomy,
      'field'            => 'term_id',
      'terms'            => $term_ids,
      'include_children' => true,
      'operator'         => 'IN',
    ]],
  ]);

  $campings_count = (int) $q->found_posts;
  wp_reset_postdata();
}
?>
<section <?= get_block_wrapper_attributes(["class" => 'container-huge block-campings bg-bgOrange rounded-[100px] p-[40px] md:p-[100px]']); ?>>
  <div class="flex flex-col md:flex-row gap-[30px] md:gap-[150px] ">
    <div class="md:min-w-[20%] 
    max-md:px-[20px] max-md:py-[10px]  rounded-[10px] max-md:border max-md:border-solid max-md:border-green
    ">
      <?php
      echo '<span class="text-orange text-[14px] font-arial md:hidden max-md:block text-center max-md:mb-[0px] active-filters-block-campings">';
      echo __('Afficher / Masquer les filtres', 'fdhpa17');
      echo '<span class="bg-green rounded-full text-white text-[11px] w-[16px] h-[16px] inline-flex items-center justify-center ml-[10px]">+</span>';
      echo '</span>';
      $facets = FWP()->helper->get_facets();
      if (!empty($facets)) {
        foreach ($facets as $facet) {
          if (
            'pager' === $facet['type']
            || 'date_range' === $facet['type']
            || 'classement_block' === $facet['name']
            || 'destination' === $facet['name']
            || 'services_block' === $facet['name']
            || 'hebergements_block' === $facet['name']
            || 'expriences_block' === $facet['name']
            || 'input_text_block' === $facet['name']
             || 'ctoutvert_checkbox' === $facet['name']
          ) {
            continue;
          }

          echo '<div class="facet-block max-md:!border-0 max-md:!mb-0 max-md:!pb-0">';
          echo '<p class="ctitle text-orange font-arial text-[15px] font-[700] m-0 mb-[15px]
            max-md:text-center max-md:bg-white max-md:rounded-full max-md:max-w-max
            max-md:px-[30px] max-md:py-[15px] max-md:text-black max-md:font-[400]
            max-md:mx-auto max-md:mb-0 max-md:text-[13px] max-md:min-w-[150px]
            max-md:hidden
            ">' . esc_html($facet['label']) . '</p>';
          echo '<div class="facet-wrapper [&_span]:text-[#7F7F7F] [&_span]:font-arial [&_span]:text-[13px] max-md:hidden">';
          echo do_shortcode('[facetwp facet="' . esc_attr($facet['name']) . '"]');
          echo '</div>';
          echo '</div>';
        }
      }
      ?>
    </div>
    <div class="md:min-w-[70%]">
      <h2 class="text-center text-[36px] leading-0"><?= esc_html($campings_count); ?>
        camping<?= $campings_count > 1 ? 's' : '' ?><br/><span class="font-arial text-[14px] font-[300] block mt-[20px] mb-[20px]"><?= get_field('titre_du_bloc_selection_campings'); ?></span></h2>
      <?= do_shortcode('[facetwp template="full"]'); ?>
    </div>
  </div>
  <div class="flex flew-col justify-center items-center mt-[50px]">
    <?= do_shortcode('[facetwp facet="pagination"]') ?>
  </div>
</section>