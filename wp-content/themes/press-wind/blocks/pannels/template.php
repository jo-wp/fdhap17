<?php

/**
 * Pannels template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$background_left = get_field('background_left');
$description_left = get_field('description_left');
$background_right = get_field('background_right');
$description_right = get_field('description_right');

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


<section <?= get_block_wrapper_attributes(["class" => 'block-pannels max-w-[1066px] mx-auto flex flex-col flex-wrap']); ?>>
  <div>
    <InnerBlocks
      class=" [&_h2]:text-black [&_h2_sub]:text-black [&_p]:m-0 [&_p]:text-[20px] md:[&_p]:text-[32px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial [&_h2]:text-[20px] [&_h2_sub]:text-[20px] md:[&_h2]:text-[36px] md:[&_h2_sub]:text-[32px] [&_h2]:font-[700] [&_h2_sub]:font-[400] [&_h2]:font-ivymode [&_h2_sub]:font-arial"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="flex flex-wrap flew-row gap-[32px] max-md:mt-[30px] md:mt-[77px]">
    <div class="flex-1 flex flex-wrap flew-row items-center justify-center p-[50px] [&_p]:m-0 [&_p]:text-[15px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial" style="background-color: <?= $background_left ?> ;">
      <?= $description_left;  ?>
    </div>
    <div class="flex-1 flex flex-wrap flew-row items-center justify-center p-[50px] [&_p]:m-0 [&_p]:text-[15px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial" style="background-color: <?= $background_right ?> ;">
      <?= $description_right;  ?>
    </div>
  </div>
</section>