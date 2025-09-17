<?php

/**
 * Masonry template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$disabled_auto_mansory = get_field('disabled_auto_mansory');
$select_cat_mansory = get_field('select_cat_mansory');

$items_associated = '';
if (!$disabled_auto_mansory) {
  $q = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
    'tax_query' => [
      [
        'taxonomy' => 'category',
        'field' => 'term_id',
        'terms' => $select_cat_mansory,
      ]
    ],
  ]);

  if ($q->have_posts()) {
    $items_associated = $q->posts;
  }
  wp_reset_postdata();
} else {
  $items_associated = get_field('items_associated');
}


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

<section <?= get_block_wrapper_attributes(["class" => 'block-mansonry max-w-[1100px] mx-auto ']); ?>>
  <div>
    <InnerBlocks
      class=" [&_h2]:text-black [&_h2]:mb-[67px] [&_h2]:text-left [&_h2]:pl-[38px] [&_p]:m-0 [&_p]:text-[16px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial [&_h2]:text-[24px] md:[&_h2]:text-[36px] [&_h2]:font-[700] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div
    class="md:grid grid-mansonyried max-md:grid-cols-2 max-md:flex max-md:overflow-x-scroll max-md:gap-[15px] max-md:ml-[15px]">
    <?php foreach ($items_associated as $post):
      $featuredImage = get_the_post_thumbnail_url($post->ID, 'full');
      ?>
      <article
        class="post relative after:rounded-[20px] after:z-10 after:w-full after:h-full after:absolute after:content-[''] after:top-0 after:left-0 min-h-[430px] rounded-[20px] bg-cover bg-center max-md:min-w-[340px]"
        style="background-image: url('<?= esc_url($featuredImage); ?>');">
        <a class="hover:no-underline w-full h-full flex items-end relative z-20 hover:translate-x-2 transition-all duration-300 "
          href="<?= esc_url(get_permalink($post->ID)); ?>">
          <h3
            class="post-title m-0 p-0 font-arial text-white text-center text-[20px] font-[700] mb-[42px] ml-[36px] border border-solid border-white py-[8px] px-[19px] rounded-[40px]">
            <?= esc_html($post->post_title); ?></h3>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
</section>