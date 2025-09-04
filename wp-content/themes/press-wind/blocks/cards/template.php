<?php

/**
 * 3 Cards template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_cards = get_field('items_cards');
$disabled_space = get_field('disabled_space');
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

$gap = ($disabled_space) ? '' : 'gap-[10px]';

?>
<section <?= get_block_wrapper_attributes(["class" => 'block-cards max-w-[1850px] mx-auto max-[1850px]:mx-[30px]']); ?>>
  <div class="flex flex-col max-md:gap-[20px] md:flex-row flex-wrap <?= $gap ?>">
    <?php foreach($items_cards as $item): ?>
      <?php $text_color = ($item['color']) ? $item['color'] : '#fff'; ?>
      <div class="flex-1 bg-center bg-cover bg-no-repeat p-[30px] md:p-[55px] rounded-[20px]  max-md:min-h-[200px]" <?= ($item['type']=='image')? 'style="background-image:url(\''.$item['image'].'\')"' : 'style="background-color:'.$item['background'].';"' ?>>
        <?php if($item['type']=='texte'): ?>
        <h2 class=" font-ivymode max-md:text-[24px] md:text-[32px] md:leading-[45px] " style="color:<?= $text_color; ?>"><?php echo $item['titre']; ?></h2>
        <div class="content font-arial text-[15px] "  style="color:<?= $text_color; ?>">
          <?php echo $item['texte']; ?>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>