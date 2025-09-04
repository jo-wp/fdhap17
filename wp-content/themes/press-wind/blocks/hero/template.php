<?php

/**
 * Hero template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$hero_type = get_field('hero_type');
$activate_search = get_field('activate_search');
$carousel_images = get_field('carousel_images');
$firstCarousel = $carousel_images[0];
$logo = get_field('logo');
$logo_tiny = get_field('logo_tiny');

// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
      "color" => "orange",
      "fontSize" => "normal",
      "fontFamily" => "arial"
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

$height_content = '';
$disabled_background = false;
$text_color = 'text-black md:text-white';
$disabled_gradient = '';
$border_color = 'border-[#ffffff36]';
switch ($hero_type) {
  case 'full':
    $height_content = 'h-[90vh]';
    break;
  case 'middle':
    $height_content = 'h-[70vh]';
    break;
  case 'little':
    $height_content = 'h-[40vh]';
    break;
  case 'tiny':
    $disabled_gradient = 'before:!hidden';
    $border_color = 'border-black/37 ';
    $text_color = 'text-black';
    $disabled_background = true;
    $height_content = 'h-auto';
    break;
}



?>

<section <?= get_block_wrapper_attributes(["class" => 'block-hero w-full ']); ?>>
  <div class="max-md:hidden p-[15px] flex flex-row gap-[30px]  content-center justify-end bg-green top-bar">
    <div class="wrapper-search">
      <form action="<?= esc_url(home_url('/')) ?>">
        <input name="s" type="text"
          class="search-input hover:<?= $text_color; ?> <?= $text_color; ?> focus:<?= $text_color; ?> bg-transparent border border-b-1 border-white border-l-0 border-t-0 border-r-0" />
        <input type="submit" class="search-submit" value="Rechercher" />
      </form>
    </div>
    <a href="" class="flex items-center">
      <img class="w-[22px] h-[22px]" src="<?= esc_url(get_theme_file_uri('/assets/media/heart.png')) ?>"
        alt="Icon wishlist">
    </a>
  </div>
  <div
    class="block-hero__content container-huge max-md:mx-0  relative <?= $height_content . ' ' . $disabled_gradient ?> max-h-[1000px] md:rounded-b-[200px] bg-cover"
    <?= (!$disabled_background) ? 'style="background-image:url(' . esc_url($firstCarousel) . ');"' : ''; ?>>
    <div class="md:hidden block-hero__content__mobile bg-white px-[15px] flex flex-row justify-between items-center">
      <a href="<?= get_bloginfo('url') ?>" class="max-w-[20%]">
        <img src="<?= $logo_tiny; ?>" alt="Logo" class="max-w-[170px]" />
      </a>
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
    <div class="block-hero__content__navigation
  relative !z-30 max-w-[1440px] mx-auto max-[1570px]:mx-[30px]
  border-b <?= $border_color; ?> border-solid border-t-0 border-l-0 border-r-0
  flex flex-row gap-[1%] md:items-center md:justify-center

  max-md:fixed max-md:top-0 max-md:-left-[30px]       
  max-md:bg-white max-md:h-screen max-md:w-full   
  max-md:items-start max-md:overflow-y-auto       

  max-md:-translate-x-full                        
  max-md:[&.active]:translate-x-0                
  max-md:transition-transform max-md:duration-300 max-md:ease-in-out">
      <a href="<?= get_bloginfo('url') ?>" class="max-w-[20%] max-md:hidden">
        <img src="<?= ($hero_type != 'tiny') ? $logo : $logo_tiny; ?>" alt="Logo" class="max-w-full" />
      </a>
      <nav class="flex items-center justify-center w-full">
        <ul class="flex items-center justify-center w-full list-none m-0 p-0 gap-[5%]
        max-md:flex-col max-md:justify-start max-md:items-start max-md:ml-[15px]">
          <li class="relative leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class=" <?= $text_color; ?> max-md:font-[700] text-[16px] relative block font-arial hover:no-underline after:content-[''] after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Trouver
              son camping</a>
            <ul class="max-md:submenu md:hidden absolute leading-[20px] ">
              <li class="md:hidden flex flex-row flex-wrap gap-[15px] before:!right-[initial] before:rotate-180">
                <a href="#" class="button-back-mobile pl-[20px] font-[400]">Retour</a>
                <div class="text-green">Trouver votre camping</div>
              </li>
              <li class=" md:active [&.active]:text-green max-md:[&.active]:text-black "><a href="#"
                  class=" no-underline">Destination</a>
                <ul class="submenu-child active">
                  <li><a href="/camping/camping-des-deux-plages/">Campings à La Rochelle</a></li>

                </ul>
              </li>
              <li class="  [&.active]:text-green "><a href="#">S'inspirer</a>
              </li>
              <li>Voir tous les campings</li>
            </ul>
          </li>
          <li
            class=" relative after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[4px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700] text-[16px] relative  block font-arial hover:no-underline after:content-[''] after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Vos
              envies</a>
          </li>
          <li class=" relative leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700] text-[16px] relative  block font-arial hover:no-underline after:content-[''] after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Votre
              façon de camper</a>
          </li>
          <li class=" relative leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700]  text-[16px] relative  block font-arial hover:no-underline after:content-[''] after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Explorer
              la Charente Maritime</a>
          </li>
          <li class=" relative leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700]  text-[16px] relative  block font-arial hover:no-underline after:content-[''] after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Nos
              offres spéciales</a>
          </li>
        </ul>
      </nav>
    </div>
    <?php if ($hero_type == 'full'): ?>
      <div
        class="block-hero__content__text max-w-[1440px] mx-auto max-[1570px]:mx-[30px] flex h-full items-start justify-center flex-col relative z-10
							 max-md:!absolute max-md:top-0  max-md:left-0 max-md:right-0 max-md:max-auto max-md:text-center

        ">
        <InnerBlocks class="mb-[40px] max-md:[&_h1]:!text-[30px]" template="<?php echo esc_attr(wp_json_encode($template)) ?>"
          allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
        <div class="block-hero__content__carousel flex flex-row gap-[28px] max-md:mx-auto">
          <span class="carousel-hero-button-prev cursor-pointer">
            <img src="<?= esc_url(get_theme_file_uri('/assets/media/hero-carousel-prev.png')) ?>" alt="Previous">
          </span>
          <span class="carousel-hero-button-next cursor-pointer">
            <img src="<?= esc_url(get_theme_file_uri('/assets/media/hero-carousel-next.png')) ?>" alt="Next">
          </span>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>