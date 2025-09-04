<?php
$prev_post = get_adjacent_post(false, '', true);
$next_post = get_adjacent_post(false, '', false);

?>
<div class="single-article__navigation max-w-[485px] mx-auto flex flex-row flex-wrap items-center justify-between mt-[100px]">
  <?php if($next_post ): ?>
  <div class="single-article__navigation__prev text-center hover:-translate-x-1 transition-all duration-300">
    <a class="!no-underline" href="<?= get_permalink($prev_post->ID) ?>">
      <img src="<?= get_bloginfo('template_directory') ?>/assets/media/arrow-article-prev.svg">
      <p class="m-0 !font-[700] uppercase  !text-[15px]"><?= __('Article précédent', 'fdhpa17') ?></p>
      <p class="m-0 !text-[16px]"><?= $prev_post->post_title ?></p>
    </a>
  </div>
  <?php endif; ?>
  <?php if($next_post ): ?>
  <div class="single-article__navigation__next text-center hover:translate-x-1 transition-all duration-300">
    <a class="!no-underline" href="<?= get_permalink($next_post->ID) ?>">
      <img src="<?= get_bloginfo('template_directory') ?>/assets/media/arrow-article-next.svg">
      <p class="m-0 !font-[700] uppercase  !text-[15px]"><?= __('Article suivant', 'fdhpa17') ?></p>
      <p class="m-0 !text-[16px]"><?= $next_post->post_title ?></p>
    </a>
  </div>
  <?php endif; ?>
</div>