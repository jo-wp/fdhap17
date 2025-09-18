<?php
get_header();
$relationned_404 = get_field('relationned_404', 'option');
?>
<div class="container-huge flex flex-col flex-wrap">
  <img src="<?= get_bloginfo('template_directory') ?>/assets/media/404.svg" alt="Icon 404 page">
  <h1 class=" text-[48px] font-[400] text-center"><?= get_field('titre_404', 'option') ?></h1>
</div>
<div class="container-huge bg-bgOrange rounded-[20px] p-[50px] mb-[80px] ">
  <div class="max-w-[1180px] mx-auto flex flex-col md:flex-row items-start flex-wrap gap-[95px]">
    <p class="flex-1 font-ivymode text-[36px] m-0"><?= get_field('texte_gauche_404','option'); ?></p>
    <p class="flex-1 font-arial text-[20px] m-0"><?= get_field('texte_droite_404','option'); ?></p>
  </div>
</div>
<div class="container-huge ">
  <div class="max-w-[1565px] mx-auto flex flex-col md:flex-row flex-wrap items-center justify-center gap-[50px] md:gap-[20px]">
    <?php foreach ($relationned_404 as $item): ?>
      <?php $imageBackground = get_the_post_thumbnail_url($item->ID, 'full'); ?>
      <a class="!no-underline aspect-[2/1] w-full md:w-[32%] h-full block" href="<?= get_permalink($item->ID) ?>">
        <div class=" h-[90%] md:h-full w-full rounded-[20px] bg-cover" style="background-image:url('<?= $imageBackground; ?>')"></div>
        <p class="no-underline font-arial text-[24px] font-[600]"><?= $item->post_title; ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php get_footer(); ?>