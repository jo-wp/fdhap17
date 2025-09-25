<?php


/**
 * Pannels template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS

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


<section <?= get_block_wrapper_attributes(["class" => 'block-search-campings container-huge bg-bgGreen rounded-t-[200px] mx-auto flex flex-col flex-wrap ']); ?>>
  <div class="max-w-[1066px] mx-auto flex flex-col flex-wrap py-[90px] px-[130px]">
    <InnerBlocks
      class="animateFade fadeOutAnimation text-center [&_h2]:text-green [&_h2_sub]:text-black [&_p]:m-0 [&_p]:text-[16px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial [&_h2]:text-[32px] [&_h2_sub]:text-[32px] md:[&_h2]:text-[32px] md:[&_h2_sub]:text-[32px] [&_h2]:font-[600] [&_h2_sub]:font-[400] [&_h2]:font-ivymode [&_h2_sub]:font-arial"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="max-w-full">
    <div class="mx-[130px] min-h-[230px] [&_select]:p-[20px_30px] [&_select]:border-0 [&_select]:rounded-full">
      <?= do_shortcode('[facetwp facet="classement_block"]'); ?>
      <?= do_shortcode('[facetwp template="block_search"]'); ?>
    </div>
  </div>
</section>