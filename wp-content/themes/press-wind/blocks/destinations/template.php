<?php
/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$disabled_items_associated = get_field('disabled_items_associated');
$items_destinations = get_field('items_associated');

if (!$disabled_items_associated) {

  $destinations = get_terms([
    'post_type' => 'camping',
    'taxonomy' => 'destination',
    'hide_empty' => false,
  ]);


  foreach ($destinations as $destination) {
    $linked_page_id = tp_get_linked_post_id($destination->term_id);
    if ($linked_page_id) {
      $items_destinations[] = [
        'description' => $destination->description,
        'url_linked_page' => get_term_link($destination),
        'post_data' => get_post($linked_page_id)
      ];
    }
  }

}



// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
      "color" => "orange"
    ]
  ],
  [
    'core/paragraph',
    [
      "placeholder" => "Description ..."
    ]
  ]
];
?>

<section <?= get_block_wrapper_attributes(["class" => 'container-huge flex flex-col items-center justify-center block-destinations']); ?>>
  <div class="max-w-[915px] mx-auto ">
    <InnerBlocks
      class="[&_h2]:mb-[50px] [&_h2]:text-orange [&_p]:mt-0 [&_p]:text-[14px] md:[&_p]:text-[16px] [&_p]:text-[#333333] [&_p]:font-arial [&_h2]:text-[20px] md:[&_h2]:text-[32px] [&_h2]:font-[600] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="min-w-full mt-[55px]">
    <?php if ($items_destinations): ?>
      <div
        class="grid grid-cols-2 md:grid-cols-4 max-md:gap-x-[10px] max-md:gap-y-[40px]  md:gap-[6px] mb-[60px] last:mb-0  md:[&_div:nth-child(-n+4)]:rounded-t-[39px] md:[&_div:nth-child(n+5):nth-child(-n+8)]:rounded-b-[39px]">
        <?php foreach ($items_destinations as $item):
          $pageTitle = (!$disabled_items_associated) ? get_the_title($item['post_data']->ID) : get_the_title($item->ID);
          $pageThumb = (!$disabled_items_associated) ? get_the_post_thumbnail_url($item['post_data']->ID, 'full') : get_the_post_thumbnail_url($item->ID, 'full');
          $pageLink = (!$disabled_items_associated) ? $item['url_linked_page'] : get_permalink($item->ID);
          ?>
          <div
            class="card-yellow group relative aspect-square bg-cover md:bg-center max-md:bg-top max-md:shadow-lg max-md:rounded-[20px] overflow-hidden"
            style="background-image: url('<?= esc_url($pageThumb); ?>');">

            <span aria-hidden="true" class="overlay-yellow"></span>

            <a class="relative z-10 block h-full hover:no-underline" href="<?= esc_url($pageLink); ?>">

              <span
                class="absolute inset-x-0 top-0 m-0 text-black md:text-white font-arial max-md:text-[16px] md:text-[26px] font-bold text-right px-[37px] py-[66px]
             transform-gpu translate-x-0 opacity-100 transition-all duration-500 ease-out
             group-hover:translate-x-full group-hover:opacity-0
             max-md:absolute max-md:bottom-0 max-md:p-[20px_10px] max-md:text-center max-md:top-[inherit] max-md:bg-white max-md:rounded-b-[20px]">
                <?= esc_html($pageTitle); ?>
        </span>


              <p class="absolute inset-0 flex items-center justify-center text-center px-[69px]
             !text-white !text-[16px] !font-arial
             opacity-0 translate-y-4 transition-all duration-500 ease-out delay-100
             group-hover:opacity-100 group-hover:translate-y-0">
                <?= esc_html($item['description']); ?>
              </p>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>