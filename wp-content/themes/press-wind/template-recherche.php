<?php
/*
Template Name: Recherche
*/
get_header();
?>
<div class="container-huge block-search flex flex-row gap-[45px]">
  <div class="min-w-[20%]">
    <div class="sticky top-4">
      <?php
      $facets = FWP()->helper->get_facets();
      if (!empty($facets)) {
        foreach ($facets as $facet) {
          if ('pager' === $facet['type']) {
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
<?php
get_footer();