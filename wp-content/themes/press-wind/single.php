<?php
get_header();
$under_title = get_field('sous_titre');
?>
<div class="single-header mb-[50px] mx-[30px]">
  <div class="single-header__timeline flex flex-row flex-wrap gap-[60px] items-center justify-center md:mb-[50px]">
    <div class="time flex flex-row flex-wrap gap-3 items-center justify-center">
      <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-time.svg" />
      <p class=" font-arial text-[14px] text-black font-[700]">5 minutes</p>
    </div>
    <div class="date flex flex-row flex-wrap gap-3 items-center justify-center">
      <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-calendar.svg">
      <p class=" font-arial text-[14px] text-black font-[700]"><?= get_the_date('j F Y') ?></p>
    </div>
  </div>
  <div class="single-header__title">
    <h1 class=" text-black font-ivymode text-[43.5px] font-[600] leading-[55px] tracking-[0.25px] text-center"><?= get_the_title($post->ID) ?> <?php if($under_title): ?><span class="text-black font-arial m-0 mb-[50px] text-[32px] font-[600] leading-[55px] tracking-[0.25px] text-center block"><?= $under_title; ?></span><?php endif; ?></h1>
    <hr class="max-w-[420px]  ">  
  </div>
</div>
<div class="single-container max-w-[1280px] mx-auto max-[1340px]:mx-[30px] md:flex md:gap-[60px]">
  <div class="single-sidebar md:basis-[30%] md:shrink-0">
    <div class="single-sidebar__content bg-bgOrange p-[30px] rounded-[20px] md:sticky md:top-[20px] mb-[50px] md:mb-0">
      <p class="mb-[30px] uppercase text-center font-arial text-green text-[16.719px] font-[700] tracking-[0.25px]">
        <?= __('Sommaire', 'fdhpa17'); ?>
      </p>
      <div class="single-sidebar__content__summary
      [&_ul]:m-0 [&_ul]:list-none [&_ul]:p-0
      [&_li]:font-arial [&_li]:font-[400] [&_li]:text-[15px] [&_li]:text-center
      [&_li_a]:no-underline
      [&_li]:mb-[30px] [&_li]:transition-all [&_li]:duration-300
      [&_li:hover]:translate-x-1"></div>
      <!-- Generate with assets/js/articles/main.js -->
      <p class="mb-[30px] uppercase text-center font-arial text-green text-[16.719px] font-[700] tracking-[0.25px]">
        <?= __('Partager l\'article'); ?>
      </p>
      <ul class="m-0 list-none p-0 flex flex-row items-center justify-center gap-[15px]">
        <li class="hover:-translate-y-1 transition-all duration-300">
          <a class=" hover:no-underline "
            href="https://www.facebook.com/sharer/sharer.php?u=<?= get_permalink($post->ID); ?>" target="_blank">
            <img src="<?= get_bloginfo('template_directory') ?>/assets/media/logo-facebook.svg"
              alt="Icon de partager l'article <?= $post->post_title; ?> avec Facebook" />
          </a>
        </li>
        <li class="hover:-translate-y-1 transition-all duration-300">
          <a class=" hover:no-underline "
            href="https://www.facebook.com/sharer/sharer.php?u=<?= get_permalink($post->ID); ?>" target="_blank">
            <img src="<?= get_bloginfo('template_directory') ?>/assets/media/logo-telegram.svg"
              alt="Icon de partager l'article <?= $post->post_title; ?> par mail" />
          </a>
        </li>
        <li class="hover:-translate-y-1 transition-all duration-300">
          <a class=" hover:no-underline "
            href="https://www.facebook.com/sharer/sharer.php?u=<?= get_permalink($post->ID); ?>" target="_blank">
            <img src="<?= get_bloginfo('template_directory') ?>/assets/media/mdi_instagram.svg"
              alt="Icon de partager l'article <?= $post->post_title; ?> avec Instagram" />
          </a>
        </li>
      </ul>
      <a href="<?= get_author_posts_url($post->post_author); ?>" class="button button--white ">
        <?= __('Voir l\'auteur de l\'article', 'fdhpa17'); ?>
      </a>
    </div>
  </div>

  <div class="single-content md:basis-[70%]
  [&_h2]:font-ivymode [&_h2]:font-[600] [&_h2]:leading-[41px]
  [&_h2]:text-[32px] [&_h2]:text-green [&_h2]:tracking-[0.25px]
  [&_p]:text-black [&_p]:text-[15px]
   [&_p]:font-[400] [&_p]:font-arial
   [&_a]:font-arial [&_a]:text-green [&_a]:underline [&_a]:font-[700]
   [&_img]:rounded-[20px] [&_iframe]:rounded-[20px]">
    <?= apply_filters('the_content', $post->post_content); ?>
    <?= get_template_part('partials/single/article','navigation'); ?>
    <?= get_template_part('partials/single/article','campings',array('items' => '')); ?>
  </div>
</div>
<?php get_footer(); ?>