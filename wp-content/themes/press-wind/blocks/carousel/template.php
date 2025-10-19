<?php

/**
 * Carousel template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$disabled_auto_carousel = get_field('disabled_auto_carousel');
$items_selected = '';
$type_color = (get_field('type_color') == 'default') ? 'text-green' : 'text-orange';


if (!$disabled_auto_carousel) {
  $term = get_queried_object();
  $items_selected = [];

  if ($term && !is_wp_error($term) && !empty($term->term_id) && !empty($term->taxonomy)) {
    $taxonomy = $term->taxonomy;

    $tax_obj = get_taxonomy($taxonomy);
    $post_types = ($tax_obj && !empty($tax_obj->object_type)) ? $tax_obj->object_type : ['post'];
    $post_type = reset($post_types) ?: 'post';

    $q = new WP_Query([
      'post_type' => $post_type,
      'post_status' => 'publish',
      'ignore_sticky_posts' => true,
      'posts_per_page' => 6,
      'orderby' => 'date',
      'order' => 'DESC',
      'tax_query' => [
        [
          'taxonomy' => $taxonomy,
          'field' => 'term_id',
          'terms' => (int) $term->term_id,
        ]
      ],
    ]);

    if ($q->have_posts()) {
      $items_selected = $q->posts; 
    }
    wp_reset_postdata();
  }

} else {
  $items_selected = get_field('items_selected');
}



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

//get id block
$block_id = $block['id'];

?>
<section <?= get_block_wrapper_attributes(["class" => 'block-carousel  max-w-[1260px]  md:mx-[90px] xl:mx-auto']); ?>>
  <div class="">
    <InnerBlocks
      class="mb-[40px] [&_p]:text-[15px] [&_h2]:relative [&_h2]:text-center  [&_h2::after]:block [&_h2]:text-orange [&_h2]:text-[24px] md:[&_h2]:text-[32px] [&_h2]:font-[600] [&_h2_sub]:font-arial [&_h2_sub]:text-[32px] [&_h2_sub]:font-[400] "
      template="<?php echo esc_attr(wp_json_encode($template)) ?>"
      allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <div class="block-carousel__items">
    <?php if ($items_selected): ?>
      <section class="splide splidejs-<?= $block_id; ?>">
        <div class="block-carousel__filters-controls splide__arrows">
          <button
            class="splide__arrow splide__arrow--prev block-carousel__filters-controls-prev !bg-transparent !border-none !cursor-pointer !-left-[60px]">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/media/arrow-prev-green.svg"
              alt="Flèche droite">
          </button>
          <button
            class="splide__arrow splide__arrow--next block-carousel__filters-controls-next !bg-transparent !border-none !cursor-pointer !-right-[60px]">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/media/arrow-next-green.svg"
              alt="Flèche gauche">
          </button>
        </div>
        <div class="splide__track">
          <ul class="splide__list max-md:!flex max-md:flex-row max-md:flex-nowrap max-md:gap-[15px] max-md:overflow-x-scroll max-md:w-full">
            <?php foreach ($items_selected as $item):
              $image_featured = get_the_post_thumbnail_url($item->ID, 'full');
              $url = ($disabled_auto_carousel)?  tp_get_term_url_by_term_page($item->ID) : get_permalink($item->ID) ;
              ?>
              <li class="splide__slide h-[62vw] md:h-[40vw] lg:h-[25vw] lg:max-h-[385px] max-md:w-[70%] max-md:h-[320px]">
                <div class="image_featured min-h-[calc(100%-40px)] bg-cover  bg-no-repeat rounded-[20px] relative"
                  style="background-image:url('<?= $image_featured ?>');">
                  <a class="absolute w-full h-full -bottom-[30px] md:-bottom-[43px] left-0 flex items-end justify-start hover:no-underline no-underline "
                    href="<?= $url; ?>">
                    <span
                      class="px-[40px] text-center flex items-center h-[60px] md:h-[85px] box-border bg-bgGreen max-w-[260px] font-arial text-[14px] md:text-[20px] font-[700] rounded-ee-[20px] <?= $type_color; ?> mt-[20px]"><?= get_the_title($item->ID); ?></span>
                  </a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </section>
    <?php else: ?>
      <p class=" text-center">Aucune relations selectionnées</p>
    <?php endif; ?>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('.splidejs-<?= $block_id; ?>')
    const slidesCount = carousel.querySelectorAll('.splide__slide').length;
    const arrowDisplay = slidesCount > 3; // flèches seulement si plus que 3 slide
    console.log(arrowDisplay);
    if (carousel) {
      const carouselSplide = new Splide(carousel, {
        focus: 'center',
        perPage: 3,
        perMove: 1,
        gap: '32px',
        pagination: false,
        arrows: arrowDisplay,
        breakpoints: {
          1200: {
            perPage: 2,
            arrows: slidesCount > 2 // adapte pour tablette
          },
          768: {
            destroy:true
          }
        }
      })

      carouselSplide.mount()
    }
  })
</script>