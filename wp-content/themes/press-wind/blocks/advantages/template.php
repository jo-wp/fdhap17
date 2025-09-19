<?php

/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$type_advantages = get_field('type_advantages');
$number_color = get_field('number_color');
$items_advantages = get_field('items_advantages');
$footer_description_advantages = get_field('footer_description_advantages');

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

$display_item_advantages = function ($i, $item, $type_advantages,$number_color) { ?>
  <div class="item flex flex-col justify-start items-start">
    <?php if ($type_advantages == 'default'): ?>
      <div class="animateFade fadeOutAnimation font-arial text-[20px] text-black font-[700] mb-[15px]"><span class="font-arial text-[20px] text-<?= $number_color; ?> font-[700] mr-[7px]"><?= sprintf("%02d", $i); ?></span><?= $item['title'] ?></div>
      <div class="animateFade fadeOutAnimation font-arial text-[14px] text-black font-[400] "><?= $item['description'] ?></div>
    <?php elseif ($type_advantages == 'order'): ?>
      <div class="animateFade fadeOutAnimation font-arial text-[15px] text-black font-[400] relative before:absolute before:top-0 before:-left-[15px] before:content-[''] before:bg-arrow-list before:w-[12px] before:h-[24px] before:bg-contain"><?= $item['description'] ?></div>
    <?php endif; ?>
  </div>
<?php };

$display_icon_advantages = function ($i, $item, $type_advantages) { ?>
  <?php if ($type_advantages == 'default'): ?>
    <div class="animateFade fadeOutAnimation item flex flex-col justify-center items-center">
      <img src="<?= $item['icon'] ?>" alt="Icon " />
    </div>
  <?php endif; ?>
<?php };

$class_section = ($type_advantages == 'default') ? '': 'justify-between' ;

?>
<section <?= get_block_wrapper_attributes(["class" => 'block-advantages max-w-[1020px] mx-auto max-[1020px]:mx-[30px] max-md:flex max-md:flex-col grid grid-cols-2 gap-0
            [&>*:nth-child(1)]:col-start-1 [&>*:nth-child(1)]:row-start-1
            [&>*:nth-child(2)]:col-start-2 [&>*:nth-child(2)]:row-start-1
            [&>*:nth-child(3)]:col-start-1 [&>*:nth-child(3)]:row-start-2 [&>*:nth-child(3)]:col-span-2 gap-0 '.$class_section.' max-md:gap-[40px] md:gap-[80px]']); ?>>
  <div class="flex-1">
    <InnerBlocks
      class=" animateFade fadeOutAnimation [&_h2]:text-black [&_h2]:mb-[30px] md:[&_h2]:mb-[67px] md:[&_h2]:text-left [&_p]:m-0 max-md:[&_p]:text-[14px] md:[&_p]:text-[16px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial max-md:[&_h2]:text-[24px] md:[&_h2]:text-[36px] max-md:text-center [&_h2]:font-[700] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <?php $class_bloc_items = ($type_advantages=='default')? 'grid grid-cols-2 gap-y-[30px]' : 'flex flex-col gap-y-[24px]' ; ?>
  <div class="flex-1 <?= $class_bloc_items; ?> gap-x-[58px]  ">
    <?php
    if (!empty($items_advantages) && is_array($items_advantages)) {
      $i = 1;
      foreach ($items_advantages as $item) {
        if ($item['type_item'] === 'default') {
          $display_item_advantages($i, $item, $type_advantages,$number_color);
        } else {
          $display_icon_advantages($i, $item, $type_advantages);
        }
        $i++;
      }
    } ?>
  </div>
  <?php if($type_advantages=='default') : ?>
  <div class="animateFade fadeOutAnimation mt-[40px] font-arial max-md:text-[14px] md:text-[16px] text-black font-[400]">
    <?= $footer_description_advantages; ?>
  </div>
  <?php endif; ?>
</section>