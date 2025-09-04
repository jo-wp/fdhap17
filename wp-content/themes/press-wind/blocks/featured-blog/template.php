<?php
/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$associated_featured_blog = get_field('associated_featured_blog');
$button_featured_blog = get_field('button_featured_blog');



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

<section <?= get_block_wrapper_attributes(["class" => 'container-huge flex flex-col items-start md:items-center justify-center block-featured-blog max-md:mx-0']); ?>>
  <div class="max-md:mx-[30px]">
    <InnerBlocks
      class=" [&_h2]:text-black [&_h2]:mb-0 [&_h2]:text-center [&_p]:m-0 max-md:text-center text-[20px] md:[&_p]:text-[32px] [&_p]:font-[400] [&_p]:text-primary [&_p]:font-arial [&_h2]:text-[24px] md:[&_h2]:text-[36px] [&_h2]:font-[700] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="mt-[40px] md:mt-[87px] mb-[40px] md:mb-[63px] flex flex-row md:flex-wrap gap-[22px]
  max-md:overflow-x-scroll max-md:max-w-full">
  <?php foreach( $associated_featured_blog as $post ): 
    $featuredImage = get_the_post_thumbnail_url($post->ID,'full');
    ?>
    <article class=" max-md:first:ml-[15px] max-md:last:mr-[15px] post relative after:rounded-[20px] after:z-10 after:w-full after:h-full after:absolute after:content-[''] after:top-0 after:left-0 min-h-[385px] md:min-h-[625px] min-w-[340px] md:min-w-[450px] rounded-[20px] bg-cover bg-center" style="background-image: url('<?= esc_url($featuredImage); ?>');">
      <a class="hover:no-underline w-full h-full flex items-end relative z-20 hover:translate-x-2 transition-all duration-300 " href="<?= esc_url(get_permalink($post->ID)); ?>">
        <h3 class="post-title m-0 p-0 font-arial text-white text-center text-[20px] font-[700] mb-[42px] ml-[36px] border border-solid border-white py-[8px] px-[19px] rounded-[40px]"><?= esc_html($post->post_title); ?></h3>
      </a>
    </article>
  <?php endforeach; ?>
  </div>
  <?php if(!$button_featured_blog['disable_button']): ?>
  <div class="text-center max-md:mx-auto">
    <a href="<?= esc_url($button_featured_blog['link_title_button']['url']); ?>" class="button button--primary">
      <?= esc_html($button_featured_blog['link_title_button']['title']); ?>
    </a>
  </div>
  <?php endif; ?>
</section>