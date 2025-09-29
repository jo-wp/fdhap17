<?php

/**
 * Hero template.
 *
 * @param array $block The block settings and attributes.
 */

$term = get_queried_object();
$id = null;
if ($term && !empty($term->term_id)) {
  $id = tp_get_linked_post_id($term->term_id);
} else {
  $id = get_the_ID();
}


//ACF FIELDS
$hero_type = get_field('hero_type', $id);

if(is_404()){
  $hero_type='tiny';
}

$activate_search = get_field('activate_search', $id);
$carousel_images = get_field('carousel_images', $id) ?: [];
$count_carousel_images = count($carousel_images);
$first = $carousel_images[0] ?? [];
$firstImage = is_string($first['image'] ?? null) ? $first['image'] : ($first['image']['url'] ?? '');
$firstText = nl2br($first['texte'] ?? '');
$description = nl2br($first['description'] ?? '');
$link = nl2br($first['link'] ?? '');
$center = get_field('center_hero', $id);



//* LOGOS *//
$logo = get_field('logo_header_hero', 'option');
$logo_tiny = get_field('logo_color_header_hero', 'option');
$logo_mini_site = get_field('logo_mini_site_header_hero', 'option');


$height_content = '';
$disabled_background = false;
$text_color = 'text-black md:text-white';
$disabled_gradient = '';
$border_color = 'border-[#ffffff36]';
$gradient_full = '';

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
  case 'search':
    $gradient_full = 'before:content-[\'\'] bg-bgGreen';
    $height_content = 'min-h-[300px]';
    break;
  case 'tiny':
    $disabled_gradient = 'before:!hidden';
    $border_color = 'border-black/37 ';
    $text_color = 'text-black';
    $disabled_background = true;
    $height_content = 'h-auto';
    break;
  case 'minisite':
    $height_content = 'h-[600px] md:h-[800px] max-h-[90vh]';
    break;
}


$mb_section = (is_front_page()) ? 'mb-[100px]' : 'mb-[30px]';


?>

<section class="block-hero w-full <?= $mb_section; ?> <?= $hero_type ?>">

  <?php if($hero_type != 'minisite') { ?>
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
  <?php } ?>
  <div id="hero-carousel"
    class="block-hero__content md:mx-[30px]  relative <?= $gradient_full . ' ' . $height_content . ' ' . $disabled_gradient ?> max-h-[1000px] md:rounded-b-[200px] bg-cover"
    data-index="0">
    <div class="bg-stack !absolute !inset-0 !z-0 md:rounded-b-[200px]">
      <div class="bg-layer bg-layer--current !absolute inset-0 md:rounded-b-[200px]"
        style="background-image:url('<?= esc_url($firstImage) ?>')"></div>
      <div class="bg-layer bg-layer--next !absolute inset-0 md:rounded-b-[200px]"></div>
    </div>

      <?php if($hero_type != 'minisite'): ?>
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
      <?php endif; ?>
      <?php if($hero_type != 'minisite'): ?>
    <div class="block-hero__content__navigation
      relative !z-30 max-w-[1440px] mx-auto max-[1570px]:mx-[30px]
      border-b <?= $border_color; ?> border-solid border-t-0 border-l-0 border-r-0
      flex flex-row gap-[1%] md:items-center md:justify-center

      max-md:!fixed max-md:!top-[53px] max-md:-left-[30px]       
      max-md:bg-white max-md:h-[40vh] max-md:w-full   
      max-md:items-start max-md:overflow-y-auto      
      
      

      max-md:-translate-x-full                        
      max-md:[&.active]:translate-x-0                
      max-md:transition-transform max-md:duration-300 max-md:ease-in-out">
      <a href="<?= get_bloginfo('url') ?>" class="max-w-[20%] max-md:hidden">
        <img src="<?= ($hero_type != 'tiny' && $hero_type != 'search' && $hero_type != 'minisite') ? $logo : $logo_tiny; ?>" alt="Logo"
          class="max-w-full" />
      </a>

      <nav class="flex items-center justify-center w-full">
        <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          // On impose tes classes EXACTES sur le <ul> racine :
          'menu_class' => 'flex items-center justify-center w-full list-none m-0 p-0 gap-[5%]
        max-[1320px]:gap-0 max-[1320px]:justify-between
        max-md:flex-col max-md:justify-start max-md:items-start max-md:ml-[15px]',
          // On force le wrapper pour ne pas avoir d'ID automatique
          'items_wrap' => '<ul class="%2$s">%3$s</ul>',
          // Le walker injecte tout le reste (li/a/sous-menus) avec les classes requises
          'walker' => new CM17_Menu_Walker($text_color),
          // Profondeur 3 pour gérer .submenu > .submenu-child
          'depth' => 3,
        ]);
        ?>
      </nav>

    </div>
      <?php endif; ?>

    <?php if ($hero_type == 'search'): ?>
      <div class="block-hero__content__text max-w-[1440px] mx-auto max-[1570px]:mx-[30px] flex h-[200px] items-center md:items-center justify-center flex-col relative z-10
               max-md:!absolute max-md:top-[50px]  max-md:left-0 max-md:right-0 max-md:max-auto max-md:text-center
          gap-[30px] 
        ">
        <h1 class="m-0 text-center text-green text-[30px] md:text-[50px]"><?= get_the_title() ?></h1>
        <section
          class="mb-0 relativez-[9999] [&_p]:font-arial [&_p]:m-[0] [&_p_span_span]:text-black [&_p]:text-[13.34px] [&_p_span]:text-orange [&_p_span]:font-[700] [&_p_span_span]:font-[400] [&_p]:text-center">
          <?php
          if (function_exists('yoast_breadcrumb')) {
            yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
          }
          ?>
        </section>
      </div>
    <?php endif; ?>
    <?php if ($hero_type == 'full'): ?>
      <div class="block-hero__content__text max-w-[1440px] mx-auto max-[1570px]:mx-[30px] flex max-md:h-[45vh] md:h-[60vh] items-center md:items-start justify-center flex-col relative z-10
               max-md:!absolute max-md:top-0  max-md:left-0 max-md:right-0 max-md:max-auto max-md:text-center

        ">
        <h1 id="hero-text"
          class="mb-[40px] max-md:!text-[30px] text-white whitespace-pre-line animateFade fadeOutAnimation">
          <?= $firstText ?>
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
      <?php if ($hero_type == 'minisite'): ?>
          <div class="block-hero__content__text  max-w-[1440px] mx-auto max-[1570px]:mx-[30px] flex  h-[90%] items-center md:items-start justify-end flex-col relative z-10
               max-md:!absolute max-md:top-0  max-md:left-0 max-md:right-0 max-md:max-auto max-md:text-center

        ">
              <div class="mb-[40px] <?php if($center) { ?> mx-auto <?php } ?>">
              <h1 id="hero-text"
                  class="max-md:!text-[30px] lg:text-[55px] text-white whitespace-pre-line animateFade fadeOutAnimation <?php if($center) { ?> text-center <?php } ?>">
                  <?= $firstText ?>
              </h1>

              <?php if($description): ?>
              <p id="hero-description" class="text-white font-bold lg:text-[32px] max-w-[900px] mx-auto leading-[38px] <?php if($center) { ?> text-center <?php } ?>"><?= $description ?></p>
                  <?php endif; ?>
              </div>

              <?php if($link) : ?>
              <a id="hero-link" href="<?php echo $link ?>" class="button button--bg-orange !border-orange !px-7 !py-2 mb-20 !text-sm">En savoir plus</a>
              <?php endif; ?>

              <?php if($count_carousel_images > 1 ) : ?>
              <div class="block-hero__content__carousel flex flex-row gap-[28px] max-md:mx-auto">
                  <span class="carousel-hero-button-prev cursor-pointer">
                    <img src="<?= esc_url(get_theme_file_uri('/assets/media/hero-carousel-prev.png')) ?>" alt="Previous">
                  </span>
                          <span class="carousel-hero-button-next cursor-pointer">
                    <img src="<?= esc_url(get_theme_file_uri('/assets/media/hero-carousel-next.png')) ?>" alt="Next">
                  </span>
              </div>
              <?php endif; ?>
          </div>
      <?php endif; ?>
    <?php if ($activate_search): ?>
      <?php get_template_part('partials/search/bar'); ?>
    <?php endif; ?>
  </div>
</section>


<?php if (!is_front_page() && !is_singular('camping') && !is_author() && $hero_type != 'search'  && !is_404()): ?>
  <section
    class="relative z-[9999] md:mb-[80px] [&_p]:font-arial [&_p]:m-[0] [&_p_span_span]:text-black [&_p]:text-[13.34px] [&_p_span]:text-orange [&_p_span]:font-[700] [&_p_span_span]:font-[400] [&_p]:text-center">
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

  #hero-text, #hero-description, #hero-link {
    transition: opacity .35s ease !important;
    will-change: opacity !important;
  }

  @media (prefers-reduced-motion: reduce) {

    .bg-layer,
    #hero-text,  #hero-description, #hero-link {
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
    const heroDesc = document.getElementById("hero-description");
    const heroLink = document.getElementById("hero-link");
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
    heroDesc && (heroDesc.innerHTML = slides[0]?.description || "");
    heroLink && (heroLink.href = slides[0]?.link || "");

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
      const nextDesc = slides[next]?.description || "";
      const nextLink = slides[next]?.link || "";

      await preload(nextUrl);

      layerNext.style.backgroundImage = `url(${nextUrl})`;
      if (heroText) heroText.style.opacity = '0';
      if (heroDesc) heroDesc.style.opacity = '0';
      if (heroLink) heroLink.style.opacity = '0';

      layerNext.offsetHeight;
      layerNext.classList.add('bg-layer--fadein');

      await waitTransition(layerNext);

      layerCurrent.style.backgroundImage = `url(${nextUrl})`;
      layerNext.classList.remove('bg-layer--fadein');

      if (heroText) {
        heroText.innerHTML = nextTxt;
        heroText.style.opacity = '1';
      }
        if (heroDesc) {
            heroDesc.innerHTML = nextDesc;
            heroDesc.style.opacity = '1';
        }
        if (heroLink) {
            heroLink.href = nextLink;
            heroLink.style.opacity = '1';
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