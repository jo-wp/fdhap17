<?php
/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_answer = get_field('items_answer');

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
<section <?= get_block_wrapper_attributes(['class' => 'container-huge flex flex-col items-center justify-center block-faq bg-bgGreen rounded-t-[20px] pt-[75px] pb-[54px] max-md:px-[15px] max-md:py-[50px]']); ?>>
  <div class="">
    <InnerBlocks
      class=" [&_h2]:text-black [&_h2]:mt-0 [&_h2]:mb-0 [&_h2]:text-center [&_p]:m-0 max-md:text-center [&_p]:text-[20px] md:[&_p]:text-[32px] [&_p]:font-[400] [&_p]:text-primary [&_p]:font-arial max-md:[&_h2]:text-[24px] [&_h2]:text-[36px] [&_h2]:font-[700] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="max-w-[1100px]">
    <?php if( $items_answer ): ?>
      <ul class="m-0 p-0 list-none grid grid-cols-1 md:grid-cols-2 gap-x-[32px] gap-y-[20px] mt-[60px] mb-[80px]">
        <?php foreach( $items_answer as $item ): ?>
          <li class="py-[36px] px-[24px] bg-white rounded-[20px]">
            <h3 class="cursor-pointer max-md:text-center m-0 font-arial text-[20px] text-black mb-[0px] md:pr-[30px] relative after:content-[''] after:absolute after:right-0 max-md:after:left-0 max-md:after:mx-auto md:after:top-[25%] after:bg-more-icon after:w-[17px] after:h-[17px] max-md:after:-bottom-[45px] after:transition-all after:duration-300 after:scale-100 hover:after:scale-125"><?= esc_html($item['question']); ?></h3>
            <p  class="m-0 font-arial text-[14px] text-black h-0 invisible max-md:text-center"><?= esc_html($item['reponse']); ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>