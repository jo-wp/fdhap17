<?php

/**
 * Hero template.
 *
 * @param array $block The block settings and attributes.
 */

$term = get_queried_object();
$id = null;
if($term && !empty($term->term_id)){
  $id = tp_get_linked_post_id($term->term_id);
}else{
  $id = get_the_ID();
}


//ACF FIELDS
$hero_type = get_field('hero_type', $id);
$activate_search = get_field('activate_search', $id);
$carousel_images = get_field('carousel_images', $id ) ?: [];
$first = $carousel_images[0] ?? [];
$firstImage = is_string($first['image'] ?? null) ? $first['image'] : ($first['image']['url'] ?? '');
$firstText = nl2br($first['texte'] ?? '');

//* LOGOS *//
$logo = get_field('logo_header_hero', 'option');
$logo_tiny = get_field('logo_color_header_hero', 'option');
$logo_mini_site = get_field('logo_mini_site_header_hero', 'option');


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


$mb_section = (is_front_page()) ? 'mb-[100px]' : 'mb-[30px]';

?>

<section class="block-hero w-full <?= $mb_section; ?>">
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
  <div id="hero-carousel"
    class="block-hero__content md:mx-[30px]  relative <?= $height_content . ' ' . $disabled_gradient ?> max-h-[1000px] md:rounded-b-[200px] bg-cover"
    data-index="0">
    <div class="bg-stack !absolute !inset-0 !z-0 md:rounded-b-[200px]">
      <div class="bg-layer bg-layer--current !absolute inset-0 md:rounded-b-[200px]"
        style="background-image:url('<?= esc_url($firstImage) ?>')"></div>
      <div class="bg-layer bg-layer--next !absolute inset-0 md:rounded-b-[200px]"></div>
    </div>
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
        max-[1320px]:gap-0 max-[1320px]:justify-between
        max-md:flex-col max-md:justify-start max-md:items-start max-md:ml-[15px]">
          <li class="relative max-[1080px]:leading-[20px] leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class=" <?= $text_color; ?> max-md:font-[700] max-[1140px]:text-[13px] text-[16px] relative block font-arial hover:no-underline after:content-[''] max-[1200px]:after:hidden after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Trouver
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
              class="<?= $text_color; ?> max-md:font-[700] max-[1140px]:text-[13px] text-[16px] relative  block font-arial hover:no-underline after:content-[''] max-[1200px]:after:hidden after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Vos
              envies</a>
          </li>
          <li class=" relative max-[1080px]:leading-[20px] leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700] max-[1140px]:text-[13px] text-[16px] relative  block font-arial hover:no-underline after:content-[''] max-[1200px]:after:hidden after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Votre
              façon de camper</a>
          </li>
          <li class=" relative max-[1080px]:leading-[20px] leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700] max-[1140px]:text-[13px]  text-[16px] relative  block font-arial hover:no-underline after:content-[''] max-[1200px]:after:hidden after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Explorer
              la Charente Maritime</a>
          </li>
          <li class=" relative max-[1080px]:leading-[20px] leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]">
            <a href="#"
              class="<?= $text_color; ?> max-md:font-[700]   text-[16px] relative  block font-arial hover:no-underline after:content-[''] max-[1200px]:after:hidden after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ">Nos
              offres spéciales</a>
          </li>
        </ul>
      </nav>
    </div>
    <?php if ($hero_type == 'full'): ?>
      <div class="block-hero__content__text max-w-[1440px] mx-auto max-[1570px]:mx-[30px] flex h-full items-center md:items-start justify-center flex-col relative z-10
               max-md:!absolute max-md:top-0  max-md:left-0 max-md:right-0 max-md:max-auto max-md:text-center

        ">
        <h1 id="hero-text" class="mb-[40px] max-md:!text-[30px] text-white whitespace-pre-line">
          <?= apply_filters('the_content', $firstText); ?>
        </h1>
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
<?php if(!is_front_page() && !is_singular('camping')): ?>
<section class="relative z-[9999] mb-[80px] [&_p]:font-arial [&_p]:m-[0] [&_p_span_span]:text-black [&_p]:text-[13.34px] [&_p_span]:text-orange [&_p_span]:font-[700] [&_p_span_span]:font-[400] [&_p]:text-center" >
  <?php
  if (function_exists('yoast_breadcrumb')) {
    yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
  }
  ?>
</section>
<?php endif; ?>
<style>
  .bg-stack {
    pointer-events: none;

    border-end-end-radius: 200px;
  }

  .bg-layer {
    position: absolute;
    inset: 0;
    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    opacity: 0 !important;
    transition: opacity .45s ease !important;
    will-change: opacity !important;
  }

  .bg-layer--current {
    opacity: 1 !important;
    z-index: 0;
  }

  .bg-layer--next {
    opacity: 0 !important;
    z-index: 1;
  }

  .bg-layer--fadein {
    opacity: 1 !important;
  }

  #hero-text {
    transition: opacity .35s ease !important;
    will-change: opacity !important;
  }

  @media (prefers-reduced-motion: reduce) {

    .bg-layer,
    #hero-text {
      transition: none !important;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const slides = <?= json_encode($carousel_images ?? []) ?>;
    if (!Array.isArray(slides) || slides.length === 0) return;

    const hero = document.getElementById("hero-carousel");
    const heroText = document.getElementById("hero-text");
    const btnPrev = document.querySelector(".carousel-hero-button-prev");
    const btnNext = document.querySelector(".carousel-hero-button-next");
    const layerCurrent = hero.querySelector(".bg-layer--current");
    const layerNext = hero.querySelector(".bg-layer--next");

    const resolveUrl = (img) => {
      if (!img) return "";
      if (typeof img === "string") return img;
      if (typeof img === "object" && img.url) return img.url;
      return "";
    };

    let index = 0;
    let isAnimating = false;

    const firstUrl = resolveUrl(slides[0]?.image);
    if (firstUrl) layerCurrent.style.backgroundImage = `url(${firstUrl})`;
    heroText && (heroText.innerHTML = slides[0]?.texte || "");

    function preload(src) {
      return new Promise(resolve => {
        if (!src) return resolve();
        const img = new Image();
        img.onload = () => resolve();
        img.onerror = () => resolve();
        img.src = src;
      });
    }

    function waitTransition(el, prop = 'opacity', timeout = 700) {
      return new Promise(resolve => {
        let done = false;
        const onEnd = (e) => {
          if (e && e.propertyName !== prop) return;
          if (done) return;
          done = true;
          el.removeEventListener('transitionend', onEnd);
          resolve();
        };
        el.addEventListener('transitionend', onEnd);
        setTimeout(onEnd, timeout);
      });
    }

    async function goTo(newIndex) {
      if (isAnimating || slides.length < 2) return;
      const next = (newIndex + slides.length) % slides.length;
      if (next === index) return;

      isAnimating = true;

      const nextUrl = resolveUrl(slides[next]?.image);
      const nextTxt = slides[next]?.texte || "";

      await preload(nextUrl);

      layerNext.style.backgroundImage = `url(${nextUrl})`;
      if (heroText) heroText.style.opacity = '0';


      layerNext.offsetHeight;
      layerNext.classList.add('bg-layer--fadein');

      await waitTransition(layerNext);

      layerCurrent.style.backgroundImage = `url(${nextUrl})`;
      layerNext.classList.remove('bg-layer--fadein');

      if (heroText) {
        heroText.innerHTML = nextTxt;
        heroText.style.opacity = '1';
      }

      index = next;
      hero.dataset.index = String(index);
      isAnimating = false;
    }

    btnPrev?.addEventListener("click", () => goTo(index - 1));
    btnNext?.addEventListener("click", () => goTo(index + 1));

    hero.setAttribute('tabindex', '0');
    hero.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') goTo(index - 1);
      if (e.key === 'ArrowRight') goTo(index + 1);
    });
  });
</script>