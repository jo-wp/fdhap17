<?php

/**
 * Mini site Hero template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$carousel_images = get_field('carousel_images');
$firstCarousel = $carousel_images[0];


// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 1,
      "color" => "white",
      "fontSize" => "55px",
      "fontFamily" => "IvyMode"
    ]
  ],
  [
    'core/paragraph',
    [
      "placeholder" => "Description ...",
      "color" => "primary"
    ]
  ]
];

$carousel_images = get_field('carousel_images');
$disabled_background = false;





?>

<section <?= get_block_wrapper_attributes(["class" => 'block-hero w-full ']); ?>>
  <div
    class="block-hero__content container-huge max-md:mx-0  relative max-h-[1000px] md:rounded-b-[200px] bg-cover"
    <?= (!$disabled_background) ? 'style="background-image:url(' . esc_url($firstCarousel) . ');"' : ''; ?>>
    <div class="md:hidden block-hero__content__mobile bg-white px-[15px] flex flex-row justify-between items-center">
      <div class="block-hero__content__mobile__actions flex flex-row gap-[15px]">
        <a href="" class="flex items-center">
          <img class="w-[22px] h-[22px]" src="<?= esc_url(get_theme_file_uri('/assets/media/heart.png')) ?>"
            alt="Icon wishlist">
        </a>
        <a href="#" class="open-menu-mobile block">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/button-mobile-menu.svg"
            alt="button mobile menu">
        </a>
        <a href="#" class="close-menu-mobile hidden ">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/close-menu-mobile.svg"
            alt="button mobile menu ">
        </a>
      </div>
    </div>


      <div
        class="block-hero__content__text max-w-[1440px] mx-auto max-[1570px]:mx-[30px] flex h-full items-start justify-center flex-col relative z-10
							 max-md:!absolute max-md:top-0  max-md:left-0 max-md:right-0 max-md:max-auto max-md:text-center

        ">
        <InnerBlocks class="mb-[40px] max-md:[&_h1]:!text-[30px]" template="<?php echo esc_attr(wp_json_encode($template)) ?>"
          allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />


          <a href="#" class="button button--bg-orange">En savoir plus</a>


        <div class="block-hero__content__carousel flex flex-row gap-[28px] max-md:mx-auto">
          <span class="carousel-hero-button-prev cursor-pointer">
            <img src="<?= esc_url(get_theme_file_uri('/assets/media/hero-carousel-prev.png')) ?>" alt="Previous">
          </span>
          <span class="carousel-hero-button-next cursor-pointer">
            <img src="<?= esc_url(get_theme_file_uri('/assets/media/hero-carousel-next.png')) ?>" alt="Next">
          </span>
        </div>
      </div>

  </div>
</section>