<?php
/*
Template Name: Recherche
*/
get_header();
?>
<?= get_template_part('partials/search/bar'); ?>
<div class="container-huge block-search flex flex-row gap-[45px] justify-center">
  <div class="w-[15%]">
    <div class="sticky top-4">
      <p>
        <?=  facetwp_display( 'counts' ); ?>
      </p>
      <p class="font-arial text-[16px] text-black font-[700]">Affinez la recherche</p>
      <?php
      $facets = FWP()->helper->get_facets();
      if (!empty($facets)) {
        foreach ($facets as $facet) {
          if ('pager' === $facet['type'] || 'date_range' === $facet['type'] ) {
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
  </div>
  <div>
    <?= do_shortcode('[facetwp template="search"]') ?>
  </div>
  <div class="min-w-[30%] min-h-[100%]">
    <div id="campings-map" class="min-w-[100%] h-[600px] rounded-[16px] sticky top-4"></div>
  </div>
</div>
<div class="flex flew-col items-center justify-center mt-[50px] [&_.facetwp-page]:text-green [&_.facetwp-page.active]:text-white [&_.facetwp-page.active]:bg-green [&_.facetwp-page]:rounded-[50px]  [&_.facetwp-page]:py-[5px] [&_.facetwp-page]:px-[15px] ">
  <?= do_shortcode('[facetwp facet="pagination"]') ?>
</div>
<?php
get_footer();