<?php
/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$disabled_auto_featured_blog = get_field('disabled_auto_featured_blog');
$associated_featured_blog = get_field('associated_featured_blog');
$button_featured_blog = get_field('button_featured_blog');

if( !$disabled_auto_featured_blog  ) {
  // WP_Query arguments
  $args = array(
    'post_type'              => array( 'post' ),
    'posts_per_page'         => '4',
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'post_status'            => array( 'publish' ),
    'suppress_filters'       => true, // WPML
  );

  // The Query
  $query = new WP_Query( $args );

  // The Loop
  if ( $query->have_posts() ) {
    $associated_featured_blog = $query->posts;
  } else {
    // no posts found
    $associated_featured_blog = [];
  }
  /* Restore original Post Data */
  wp_reset_postdata();
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

<section <?= get_block_wrapper_attributes(["class" => 'container-huge flex flex-col items-start md:items-center justify-center block-featured-blog max-md:mx-0']); ?>>
  <div class="max-md:mx-[30px]">
    <InnerBlocks
      class=" animateFade fadeOutAnimation
       [&_h2]:text-black [&_h2]:mb-0 [&_h2]:text-center
       [&_h2_sub]:m-0 max-md:text-center text-[20px] md:[&_h2_sub]:text-[32px] [&_h2_sub]:font-[400] [&_h2_sub]:text-primary [&_h2_sub]:font-arial
       [&_h2]:text-[24px] md:[&_h2]:text-[36px] [&_h2]:font-[700] [&_h2]:font-ivymode"
      template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="mt-[40px] md:mt-[87px] mb-[40px] md:w-full md:mx-[30px] md:mb-[63px] flex flex-row justify-center  gap-[22px]
  max-md:overflow-x-scroll max-md:max-w-full max-md:justify-start">
  <?php foreach( $associated_featured_blog as $post ): 
    $featuredImage = get_the_post_thumbnail_url($post->ID,'full');
    
    $taxonomy = tp_get_term_url_by_term_page( $post->ID);
    
    $urlPost = is_tax()? $taxonomy : get_permalink($post->ID);
    ?>
    <article class="animateFade fadeOutAnimation  max-md:first:ml-[15px] max-md:last:mr-[15px] post relative after:rounded-[20px] after:z-10 after:w-full after:h-full after:absolute after:content-[''] after:top-0 after:left-0 min-w-[250px] md:aspect-[2/3] max-md:min-h-[250px] md:min-w-[23%] rounded-[20px] bg-cover bg-center" style="background-image: url('<?= esc_url($featuredImage); ?>');">
      <a class="hover:no-underline w-full h-full flex items-end relative z-20 hover:translate-x-2 transition-all duration-300 max-md:justify-center" href="<?= $urlPost; ?>">
        <h3 class="post-title m-0 p-0 font-arial text-white text-center text-[14px] md:text-[20px] font-[700] mb-[42px] md:ml-[36px] border border-solid border-white py-[8px] px-[19px] rounded-[40px]
        mr-[15px]"><?= esc_html($post->post_title); ?></h3>
      </a>
    </article>
  <?php endforeach; ?>
  </div>
  <?php if(!$button_featured_blog['disable_button']): ?>
  <div class="text-center max-md:mx-auto">
    <?php if($button_featured_blog['link_title_button']): ?>
    <a href="<?= esc_url($button_featured_blog['link_title_button']['url']); ?>" class="button button--primary !text-[20px] animateFade fadeOutAnimation ">
      <?php if($button_featured_blog['link_title_button']): ?>
      <?= esc_html($button_featured_blog['link_title_button']['title']); ?>
      <?php endif; ?>
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>