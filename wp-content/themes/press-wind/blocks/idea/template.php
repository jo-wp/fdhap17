<?php
/** 
 * Idea template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$filters = get_field('display_filter');
$select_idea = get_field('select_idea');

$outputItems = [];
if($select_idea):
foreach($select_idea as $items){
  foreach($items['items'] as $article){
    $outputItems[] = [
      'filter-title' => sanitize_title($items['title']),
      'article' => $article
    ];
  }
}
shuffle($outputItems);
endif;


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
<section <?= get_block_wrapper_attributes(["class" => 'container-huge block-idea relative md:after:rounded-[20px] md:after:bottom-[-50px] md:after:left-0 md:after:z-10 md:after:content-[""] md:after:absolute md:after:bg-bgOrange md:after:w-full md:after:h-[30%] mb-[150px]']); ?>>
  <div class="block-idea__content max-w-[1030px] mx-auto">
    <InnerBlocks
      class="animateFade fadeOutAnimation max-md:[&_h2]:mb-[35px] [&_h2]:font-[600] [&_h2]:text-[24px] md:[&_h2]:text-[32px] [&_p]:text-[14px] md:[&_p]:text-[15px]"
      template="<?php echo esc_attr(wp_json_encode($template)) ?>"
      allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <?php if($outputItems): ?>
  <div class="mt-[77px] md:mx-[100px] relative z-20">
    <div class="block-idea__filters flex flex-row items-center justify-center md:justify-between">
      <ul
        class="block-idea__filters-list m-0 p-0 flex flex-col md:flex-row items-center gap-[30px] [&_li]:cursor-pointer [&_li]:font-bold [&_li]:text-[16px] [&_li]:text-[#333333] [&_li]:min-w-[150px] [&_li]:text-center [&_li]:rounded-full [&_li]:list-none [&_li]:bg-[#F6F6F6] [&_li]:px-[10px] [&_li]:py-[10px] md:[&_li]:px-[29px] md:[&_li]:py-[23px] [&_li:hover]:bg-orange [&_li:hover]:text-white [&_li.active]:bg-orange [&_li.active]:text-white">
        <?php $i = 0;
        foreach ($select_idea as $filter): ?>
          <?php
            $slug = sanitize_title($filter['title']);
          ?>
          <li <?= ($i === 1) ? 'class=" filters__button animateFade fadeOutAnimation "' : ' class="filters__button animateFade fadeOutAnimation"' ?>
            data-filter="<?php echo esc_attr ($slug); ?>">
            <?php echo esc_html($filter['title']); ?>
          </li>
          <?php $i++; endforeach; ?>
      </ul>
      <div class="block-idea__filters-controls hidden md:block">
        <button class="block-idea__filters-controls-prev bg-transparent border-none cursor-pointer">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/media/ideas-carousel-next.png"
            alt="Flèche droite">
        </button>
        <button class="block-idea__filters-controls-next bg-transparent border-none cursor-pointer">
          <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/media/ideas-carousel-prev.png"
            alt="Flèche gauche">
        </button>
      </div>
    </div>
    <div class="block-idea__carousels mt-[48px]">
      <div class="block-idea__carousels__carousel ">
        <section class="splide splide__carousel__block_idea">
          <div class="splide__track">
            <ul class="splide__list max-md:!grid max-md:!grid-cols-2 max-md:!gap-[10px]">
              <?php foreach($outputItems as $item):
                $backgroundImage = get_the_post_thumbnail_url($item['article']->ID,'full');
                ?>
              <li class="splide__slide splide__slide-item max-md:min-h-[200px] animateFade fadeOutAnimation" data-taxonomie="<?= $item['filter-title']; ?>">
                <div
                  class=" max-md:shadow-md min-h-full bg-cover  after:content-[''] after:rounded-[10px] after:absolute after:left-0 after:bottom-0 after:w-full after:h-[50%] after:bg-gradient-to-b after:from-transparent after:to-[#00000066] rounded-[10px] flex flex-row justify-start items-end md:pl-5 "
                  style="background-image: url('<?= $backgroundImage; ?>')">
                  <h3
                    class="text-white text-[20px] font-bold font-arial m-0 relative z-10 mb-[20px] max-md:rounded-b-[10px] max-md:flex max-md:flex-col max-md:gap-1 max-md:items-center max-md:justify-center max-md:text-black max-md:text-[16px] max-md:bg-white max-md:w-full max-md:text-center max-md:h-[65px] max-md:mb-0">
                    <?= $item['article']->post_title; ?>
                    <span class="md:hidden block text-green text-[12px]">Explorer ></span>
                  </h3>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </section>
      </div>
    </div>
  </div>
  <?php endif; ?>
</section>

<style>
  .splide__slide-item:not(.splide__slide) {
    display: none;
  }
</style>