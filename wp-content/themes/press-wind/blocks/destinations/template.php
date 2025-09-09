<?php
/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_destinations = get_field('items_associated');

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
      class="[&_h2]:mb-[50px] [&_h2]:text-orange [&_p]:mt-0 [&_p]:text-[14px] md:[&_p]:text-[16px] [&_p]:text-[#333333] [&_p]:font-body [&_h2]:text-[20px] md:[&_h2]:text-[32px] [&_h2]:font-[600] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="min-w-full mt-[55px]">
    <?php if ($items_destinations): ?>
      <div class="grid grid-cols-2 md:grid-cols-4 max-md:gap-x-[10px] max-md:gap-y-[40px]  md:gap-[6px] mb-[60px] last:mb-0  md:[&_div:nth-child(-n+4)]:rounded-t-[39px] md:[&_div:nth-child(n+5):nth-child(-n+8)]:rounded-b-[39px]">
        <?php foreach ($items_destinations as $item):
          $pageTitle = get_the_title($item->ID);
          $pageThumb = get_the_post_thumbnail_url($item->ID, 'full');
          $pageLink = get_permalink($item->ID);
          ?>
          <div class="bg-cover max-md:shadow-lg max-md:bg-top max-md:rounded-t-[20px] md:bg-center relative aspect-square after:w-full after:h-full after:content-[''] after:top-0 after:left-0 after:absolute 
          " style="background-image: url('<?= esc_url($pageThumb); ?>');">
            <a class="relative z-10 block h-full hover:no-underline" href="<?= esc_url($pageLink); ?>">
              <h3 class="m-0 text-black  md:text-white max-md:bg-white top-0 right-0 font-arial max-md:text-[16px] md:text-[26px] font-bold text-right px-[37px] py-[66px]
              max-md:absolute max-md:bottom-0 max-md:h-[60px] max-md:p-0 max-md:w-full max-md:top-[initial] max-md:flex max-md:items-center max-md:justify-center"><?= esc_html($pageTitle); ?></h3>
              <p class="!text-white !text-[16px] !font-arial px-[69px] max-md:invisible text-center">
                Ville emblématique du littoral Atlantique, La Rochelle charme par son vieux port, son aquarium renommé et son ambiance animée, parfaite pour un camping alliant culture et mer.
              </p>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>