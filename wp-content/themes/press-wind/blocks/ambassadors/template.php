<?php
/**
 * Block Ambassadors
 *
 * @param array $block The block settings and attributes.
 */

// Template
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
    ]
  ],
  [
    'core/paragraph',
    [
      "placeholder" => "Description ..."
    ]
  ]
];

// get posts ambassador
$args = [
  'post_type' => 'ambassador',
  'postsperpage' => 4
];

$ambassadors = get_posts($args);


?>
<section <?= get_block_wrapper_attributes(["class" => 'md:max-w-[1286px] mx-auto max-[1350px]:mx-[30px]   block-ambassadors']); ?>>
  <div class="block-ambassadors__header">
    <InnerBlocks
      class="animateFade fadeOutAnimation mb-[50px] md:gap-[20px] md:[&_h2]:min-w-[400px] items-center justify-center flex flex-col md:flex-row [&_h2]:font-ivymode [&_h2]:text-[24px] md:[&_h2]:text-[50px] [&_h2_sub]:font-arial [&_h2_sub]:text-black [&_h2]:text-green [&_h2_sub]:inline-block max-md:text-center [&_h2_sub]:align-middle [&_h2_sub]:text-[24px] md:[&_h2_sub]:text-[50px] [&_p]:text-black [&_p]:font-arial [&_p]:text-[14px] md:[&_p]:text-[15px] md:[&_p]:pl-[60px] md:[&_p]:border md:[&_p]:border-black md:[&_p]:border-solid md:[&_p]:border-r-0 md:[&_p]:border-t-0 md:[&_p]:border-b-0"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="block-ambassadors__content grid grid-cols-1 md:grid-cols-4 gap-[18px]">
    <?php foreach ($ambassadors as $item):
      $imageFeatured = get_the_post_thumbnail_url($item->ID, 'large');
      ?>
      <div
        class="block-ambassadors__content__item__content py-[30px] relative mt-[70px] pt-[90px] rounded-[13.85px] bg-bgOrange pb-[40px]">
        <a class="hover:no-underline" href="<?= get_permalink($item->ID) ?>">
          <img class=" aspect-square rounded-full max-w-[150px] absolute right-0 left-0 -top-[70px] mx-auto"
            src="<?= $imageFeatured; ?>" alt="Photo de <?= $item->post_title; ?>">
          <p class="font-arial text-[22px] font-[600] m-0 text-center text-black mt-[20px] mb-[22px]">
            <?= $item->post_title; ?></p>
          <p class="font-arial text-[15px] font-[400] m-0 text-center text-black">
            <?= get_field('sous_titre_ambassadors', $item->ID); ?></p>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>