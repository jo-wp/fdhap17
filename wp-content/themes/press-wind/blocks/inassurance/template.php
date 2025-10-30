<?php
/**
 * Inassurance template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_reassurance = get_field('items_reassurance');  

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
<section <?= get_block_wrapper_attributes(["class" => 'flex flex-col md:flex-row gap-0 md:gap-[180px] items-center justify-center rounded-[20px] block-inassurance max-w-[1480px] mx-auto md:px-[30px] max-[1540px]:mx-[30px] bg-bgGreen py-[60px]']); ?>>
  <div class=" ">
    <InnerBlocks class="animateFade fadeOutAnimation
    [&_h2]:mb-0 [&_p]:mt-0 [&_p]:text-[24px]
    max-md:[&_p]:pl-[25px] md:[&_p]:text-[32px] [&_p]:text-[#333333] [&_p]:font-arial 
    [&_h2]:text-[24px] md:[&_h2]:text-[45px] [&_h2]:font-[600] [&_h2]:font-ivymode
    [&_h2_sub]:font-arial [&_h2_sub]:text-[24px] md:[&_h2_sub]:text-[32px] [&_h2_sub]:font-[400]" template="<?= htmlspecialchars(json_encode($template)); ?>" allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="">
    <?php if ($items_reassurance): ?>
      <ul class="max-md:mx-[35px] max-md:p-0">
        <?php foreach ($items_reassurance as $item): ?>
          <li class="animateFade fadeOutAnimation flex flex-row items-center justify-start gap-[34px] mb-[60px] last:mb-0">
            <img src="<?= esc_url($item['image']); ?>" alt="Icon Réassurance <?= $item['titre']; ?>" />
            <h3 class="font-arial text-[14px] md:text-[20px] font-[700] max-w-[305px]"><?= esc_html($item['titre']); ?></h3>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
