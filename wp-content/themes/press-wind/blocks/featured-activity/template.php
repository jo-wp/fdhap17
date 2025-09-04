<?php

/**
 * Featured-activity template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_featured_activity = get_field('items_featured_activity');

// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
      "color" => "foreground"
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
<section <?= get_block_wrapper_attributes(["class" => 'block-featured-activity bg-bgGreen mx-[30px] p-[15px] md:p-[60px] rounded-t-[20px]']); ?>>
  <div class="flex flex-row flex-wrap max-w-[1270px] mx-auto">
    <div class="max-w-[835px] mx-auto mb-[30px]">
      <InnerBlocks
        class="[&_h2]:mb-[30px] [&_h2]:text-center [&_p]:text-center [&_h2]:text-green [&_p]:mt-0 [&_p]:text-[14px] md:[&_p]:text-[15px] [&_p]:text-black [&_p]:font-arial [&_h2]:text-[24px] md:[&_h2]:text-[32px] [&_h2]:font-[700] [&_h2]:font-ivymode"
        template="<?= htmlspecialchars(json_encode($template)); ?>"
        allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
    </div>
    <div class="flex flex-col md:flex-row flex-wrap w-full gap-[30px]">
      <div class="flex-row flex flex-wrap w-full  gap-[60px] max-md:hidden">
        <div class="flex flex-col items-center justify-center flex-1">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-destination.svg" alt="Icon Destination">
          <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">Destination</p>
        </div>
        <div class="flex flex-col items-center justify-center flex-1">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-ambiance.svg" alt="Icon Ambiance">
          <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">Ambiance</p>
        </div>
        <div class="flex flex-col items-center justify-center flex-1">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-not-miss.svg"
            alt="Icon A ne pas manquer">
          <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">À ne pas manquer</p>
        </div>
        <div class="flex flex-col items-center justify-center flex-1">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-ideal.svg" alt="Icon idéal pour...">
          <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">Idéal pour…</p>
        </div>
      </div>
      <?php foreach ($items_featured_activity as $item): ?>
        <div class="max-md:flex-col md:flex-row flex md:flex-wrap w-full justify-center items-center gap-[60px]">
          <div
            class="max-md:relative bg-white rounded-[20px] flex flex-col items-center justify-center md:flex-1 max-md:w-[90%] px-[20px] py-[15px] md:px-[40px] md:py-[30px] text-center font-ivymode text-[20px] text-orange md:min-h-[115px]">
            <div class="md:hidden">
              <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-destination.svg"
                alt="Icon Destination">
              <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">Destination</p>
            </div>
            <?= $item['destination'] ?>
          </div>
          <div
            class="bg-white rounded-[20px] flex flex-col items-center justify-center md:flex-1 max-md:w-[90%]  px-[20px] py-[15px] md:px-[40px] md:py-[30px] text-center font-arial text-[16px] md:min-h-[115px]">
            <div class="md:hidden">
              <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-ambiance.svg" alt="Icon Ambiance">
              <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">Ambiance</p>
            </div>
            <?= $item['ambiance'] ?>
          </div>
          <div
            class="bg-white rounded-[20px] flex flex-col items-center justify-center md:flex-1 max-md:w-[90%]  py-[15px] md:px-[40px] md:py-[30px] text-center font-arial text-[16px] md:min-h-[115px]">
            <div class="md:hidden">
              <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-not-miss.svg"
                alt="Icon A ne pas manquer">
              <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">À ne pas manquer</p>
            </div>
            <?= $item['not_miss'] ?>
          </div>
          <div
            class="bg-white rounded-[20px] flex flex-col items-center justify-center md:flex-1 max-md:w-[90%]  py-[15px] md:px-[40px] md:py-[30px] text-center font-arial text-[16px] md:min-h-[115px]">
            <div class="md:hidden">
              <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-ideal.svg" alt="Icon idéal pour...">
              <p class=" font-ivymode text-[24px] font-[700] leading-[51px] text-green m-0">Idéal pour…</p>
            </div>
            <?= $item['ideal'] ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</section>