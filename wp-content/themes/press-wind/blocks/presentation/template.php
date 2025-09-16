<?php

/**
 * Presentation template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$type_block_presentation = get_field('type_block_presentation');
$carousel_images = get_field('carousel_images');
$image_presentation = get_field('image_presentation');
$inverse_presentation = get_field('inverse_presentation');


// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph', 'core/list', 'core/list-item'];
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
  ],
  [
    'core/list',
    [
      "placeholder" => "Liste ..."
    ]
  ]
];
$section_class = 'max-md:flex-col md:flex-row max-md:max-w-full md:max-w-[1400px]';
$inverse = 'bg-bgOrange max-md:max-w-full md:max-w-[90%] md:rounded-r-[200px] min-h-[680px] ';
$block_image_class = 'max-md:max-w-full md:min-w-[570px]';
$block_texte_class = '';
$bg_url = ($type_block_presentation == 'image_background') ? $image_presentation : '';
if ($type_block_presentation == 'default') {
  $block_image = function ($data) { ?>
    <div class="carousel">
      <ul class="carousel__list m-0 p-0 list-none max-md:mb-[50px]">
        <li class="carousel__item" data-pos="-2" style="background-image:url('<?= $data[0] ?>')"></li>
        <li class="carousel__item" data-pos="-1" style="background-image:url('<?= $data[1] ?>')"></li>
        <li class="carousel__item" data-pos="0" style="background-image:url('<?= $data[2] ?>')"></li>
        <li class="carousel__item" data-pos="1" style="background-image:url('<?= $data[3] ?>')"></li>
        <li class="carousel__item" data-pos="2" style="background-image:url('<?= $data[4] ?>')"></li>
      </ul>
      <div class="carousel__dots">
        <button class="carousel__dot" data-index="0"></button>
        <button class="carousel__dot" data-index="1"></button>
        <button class="carousel__dot active" data-index="2"></button>
        <button class="carousel__dot" data-index="3"></button>
        <button class="carousel__dot" data-index="4"></button>
      </div>
    </div>
  <?php };
} elseif ($type_block_presentation == 'image') {
  if ($inverse_presentation):
    $inverse = 'bg-bgOrange max-md:max-w-full md:max-w-[90%] md:rounded-l-[200px] min-h-[680px] ml-auto px-[100px]';
    $section_class = 'flex-col-reverse md:flex-row-reverse max-w-[1400px]';
    $block_image_class = '';
  else:
    $section_class = 'max-md:max-w-full max-md:flex-col md:flex-row max-w-[1400px]';
  endif;
  $block_image = function ($data) { ?>
    <img src="<?= $data; ?>" alt="Image mise en avant" class="max-md:w-full" />
    <?php
  };
} elseif ($type_block_presentation == 'image_out') {
  $section_class = 'flex-col md:flex-row relative z-20 max-w-[1270px] md:max-[1270px]:mx-[30px]';
  $block_image_class = 'max-md:px-[15px]';
  $inverse = 'max-w-full md:rounded-l-[200px] mx-auto relative max-md:before:left-0 md:before:-right-[30%] md:before:rounded-l-[20px] before:top-0 before:min-h-[100%] before:absolute before:content-[""] before:w-full before:bg-bgOrange overflow-hidden  max-md:!px-[0]';
  $block_image = function ($data) { ?>
    <img src="<?= $data; ?>" alt="Image mise en avant" class="max-w-full rounded-[20px]" />
    <?php
  };
} elseif ($type_block_presentation == 'image_background') {
  $section_class = '';
  $block_image_class = 'max-md:hidden';
  $block_texte_class = 'md:mr-[40px] max-md:py-[30px] md:[padding:73px_39px_119px_39px] bg-white rounded-[10px]';
  $inverse = 'rounded-[10px] max-w-[1270px] mx-auto max-[1270px]:mx-[30px] bg-no-repeat bg-cover';
  $block_image = function ($data) { ?>
    <img src="<?= $data; ?>" alt="Image mise en avant" class="max-md:w-full" />
    <?php
  };
}

?>
<section <?= get_block_wrapper_attributes(
  [
    "class" => 'block-presentation  ' . $inverse . ' py-[88px] max-md:px-[15px]',
    "style" => 'background-image:url(\'' . $bg_url . '\')'
  ]
); ?>>
  <div class="flex <?= $section_class; ?> flex-wrap md:gap-[45px] lg:gap-[90px]  mx-auto ">
    <div class="  flex-1 flex justify-center items-center max-md:mb-[50px] <?= $block_image_class; ?>">
      <?php if ($type_block_presentation == 'default'):
        $block_image($carousel_images);
      elseif ($type_block_presentation == 'image_background'):

      else:
        // if($block_image):
        $block_image($image_presentation);
        // endif;
      endif; ?>
    </div>
    <div class="flex-1 <?= $block_texte_class; ?>  max-md:px-[15px] lg:pr-[100px]">
      <InnerBlocks class="[&_h2]:leading-[36px]
        [&_h2]:mb-[50px] [&_h2]:text-black [&_li]:mt-0 max-md:[&_li]:text-[14px]
        md:[&_li]:text-[16px] [&_li]:text-[#333333] [&_li]:font-arial [&_p]:mt-0 text-[14px] 
        md:[&_p]:text-[16px] [&_p]:text-[#333333] [&_p]:font-arial [&_p_a]:underline md:[&_h2]:text-[36px] 
        [&_h2]:font-[600] [&_h2]:font-ivymode [&_h2_sub]:font-arial  [&_h2_sub]:font-[400]
         max-md:[&_h2]:text-center [&_h2_sub]:text-[24px] 
         md:[&_h2_sub]:text-[32px]" template="<?= htmlspecialchars(json_encode($template)); ?>"
        allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
    </div>
  </div>
</section>