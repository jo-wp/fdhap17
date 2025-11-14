<?php
/*
Template Name: Recherche
*/
get_header();
?>
<?= get_template_part('partials/search/bar','without'); ?>
<div class="container-huge block-search flex flex-col md:flex-row gap-[45px] justify-center">
  <div data-button="filters" class="max-md:block md:hidden text-center">
    <span class="bg-orange text-white text-[16px] px-[25px] py-[15px] rounded-full cursor-pointer"><?= __('Afficher les filtres','fdhpa17'); ?></span>
  </div>
  <div class="wrapper-filters -left-[110%] w-full md:w-[40%]
      max-md:fixed max-md:top-[60px] max-md:z-10 max-md:bg-white max-md:p-4
      max-md:h-[100vh]
      max-md:[&.active]:-left-0
      ">
    <div class="md:relative top-4 max-md:overflow-y-scroll max-md:max-h-[90vh]">
      <p>
        <?=  facetwp_display( 'counts' ); ?>
      </p>
      <p class="font-arial text-[16px] text-black font-[700]"><?= __('Affinez la recherche','fdhpa17'); ?></p>
      <?php
      $facets = FWP()->helper->get_facets();
      if (!empty($facets)) {
        foreach ($facets as $facet) {
          if ('pager' === $facet['type']
          || 'date_range' === $facet['type']
          || 'classement_block' === $facet['name']
          || 'destination' === $facet['name']
          || 'services_block' === $facet['name']
          || 'hebergements_block' === $facet['name']
          || 'expriences_block' === $facet['name']
          || 'input_text_block' === $facet['name']
             || 'ctoutvert_checkbox' === $facet['name']) {
            continue;
          }
          echo '<div class="facet-block">';
$label_i18n = apply_filters('facetwp_i18n', $facet['label'], [
  'source' => 'facet',
  'facet'  => $facet['name'], // slug FacetWP (ex: 'services', 'hebergement', etc.)
  'key'    => 'label',
]);

echo '<p class="ctitle text-orange font-arial text-[15px] font-[700] m-0 mb-[15px]">' . esc_html($label_i18n) . '</p>';
          echo '<div class="facet-wrapper [&_span]:text-[#7F7F7F] [&_span]:font-arial [&_span]:text-[13px]">';
          echo do_shortcode('[facetwp facet="' . esc_attr($facet['name']) . '"]');
          echo '</div>';
          echo '</div>';
        }
      }
      ?>
      <p class="bg-orange text-white text-[16px] px-[25px] py-[15px] rounded-full cursor-pointer text-center md:hidden" data-button="filters"><?= __('Appliquer et fermer','fdhpa17') ?></p>
    </div>
  </div>
  <div class="max-md:w-full md:min-w-[50%]">
    <?= do_shortcode('[facetwp template="search"]') ?>
  </div>
  <div class="w-full md:min-w-[20%] min-h-[100%]">
    <div id="campings-map" class="min-w-[100%] h-[600px] rounded-[16px] sticky top-4"></div>
  </div>
</div>
<div class="flex flew-col items-center justify-center mt-[50px] [&_.facetwp-page]:text-green [&_.facetwp-page.active]:text-white [&_.facetwp-page.active]:bg-green [&_.facetwp-page]:rounded-[50px]  [&_.facetwp-page]:py-[5px] [&_.facetwp-page]:px-[15px] ">
  <?= do_shortcode('[facetwp facet="pagination"]') ?>
</div>
<?php
get_footer();