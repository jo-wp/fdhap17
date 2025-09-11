<?php
$author_id = get_queried_object_id();
$first_name = get_the_author_meta('first_name', $author_id);
$last_name = get_the_author_meta('last_name', $author_id);

$sous_titre_author = get_field('sous_titre_author', 'user_' . $author_id);
$vite_a_auteur = get_field('vite_a_auteur', 'user_' . $author_id);
$explore_auteur = get_field('explore_auteur', 'user_' . $author_id);
$specialite_auteur = get_field('specialite_auteur', 'user_' . $author_id);
$biographie_auteur = get_field('biographie_auteur', 'user_' . $author_id);
$presentation_auteur = get_field('presentation_auteur', 'user_' . $author_id);
$expertises_auteur = get_field('expertises_auteur', 'user_' . $author_id);
$temoignages_items = get_field('temoignages_items', 'user_' . $author_id);

$articles = get_posts(array(
  'author' => $author_id,
  'numberposts' => 4,
));

get_header();
?>
<div class="max-w-[1280px] mx-auto max-[1340px]:mx-[30px]">
  <div class="single-author__header flex flex-row flex-wrap items-end justify-between mb-[50px]">
    <div class="single-author__header__title">
      <h1 class=" font-ivymode text-[50px] text-green font-[600]"><?= $first_name . ' ' . $last_name ?></h1>
      <p class="m-0 font-arial text-[24px] text-black"><?= $sous_titre_author; ?></p>
    </div>
    <div class="single-author__header__breadcrumb font-arial !text-[13px] text-black">
      <?php
      if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
      }
      ?>
    </div>
  </div>
  <div class="single-author__about bg-bgGreen mb-[50px] rounded-[20px] p-[50px] ">
    <h2 class="text-center text-[32px] mb-[50px]"><?= __('À propos de '); ?><?= $first_name; ?></h2>
    <div
      class="single-author__about__items max-w-[890px] mx-auto flex md:flex-row flex-wrap items-center justify-center md:items-start md:justify-between">
      <div class="single-author__about__items__item text-center max-md:mb-[20px]">
        <img class="md:mb-[30px]" src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-author-live.svg"
          alt="Icon Vit à">
        <p class="font-arial text-[20px] font-[700] text-black uppercase m-0 mb-[10px] "><?= __('Vit à', 'fdhpa17') ?>
        </p>
        <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]"><?= $vite_a_auteur; ?></p>
      </div>
      <div class="single-author__about__items__item text-center max-md:mb-[20px]">
        <img class="md:mb-[30px]" src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-explore.svg"
          alt="Icon Vit à">
        <p class="font-arial text-[20px] font-[700] text-black uppercase m-0 mb-[10px] "><?= __('Explore', 'fdhpa17') ?>
        </p>
        <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]"><?= $explore_auteur; ?></p>
      </div>
      <div class="single-author__about__items__item text-center max-md:mb-[20px]">
        <img class="md:mb-[30px]" src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-speciality.svg"
          alt="Icon Vit à">
        <p class="font-arial text-[20px] font-[700] text-black uppercase m-0 mb-[10px] "><?= __('Vit à', 'fdhpa17') ?>
        </p>
        <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]"><?= $specialite_auteur; ?></p>
      </div>
    </div>

  </div>
  <div
    class="single-author__description max-w-[800px] mx-auto max-[860px]:mx-[30px] mb-[90px] text-black text-[16px] text-center">
    <p class="font-arial"><?= $biographie_auteur; ?></p>
  </div>
  <div
    class="single-author__description max-w-[1030px] mx-auto max-[1090px]:mx-[30px] mb-[90px] text-orange text-[24px] text-center">
    <p class="font-arial"><?= $presentation_auteur; ?></p>
  </div>
  <div class="single-author__expetise flex flex-col md:flex-row flex-wrap gap-[30px] mb-[100px]">
    <div class="flex-1">
      <img class="w-full md:w-auto" src="<?= get_bloginfo('url') ?>/wp-content/uploads/2025/09/Image-15.png"
        alt="Photo de <?= $first_name . ' ' . $last_name; ?>">
    </div>
    <div class="flex-1">
      <h2 class="text-[32px] text-black mb-[30px]"><?= __('Mon expertise', 'fdhpa17'); ?></h2>
      <div class="single-author__expetise__items gap-[40px] flex flex-col">
      <?php foreach ($expertises_auteur as $expertise) : ?>
        <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
          <p class="m-0 text-[15px] font-[700]"><?= $expertise['texte'] ?></p>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="single-author__quote bg-green rounded-[20px] p-[30px]">
    <div class="single-author__quote__title text-center mb-[50px]">
      <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-quote.svg" alt="Icon author quote">
      <h2 class="text-[32px] text-white"><?= __('Témoignage de lecteur', 'fdhpa17'); ?></h2>
    </div>
    <div class="single-author__quote__items md:w-[950px] mx-auto">
      <section class="splide splide_author-quote" aria-label="Témoignages de lecteurs">
        <div class="splide__track">
          <ul class="splide__list mb-[30px]">
            <?php foreach ($temoignages_items as $temoignage) : ?>
            <li class="splide__slide">
              <div class="single-author__quote__items__item">
                <p class="text-white text-[20px] font-arial text-center mb-[50px]"><?= $temoignage['temoignage']; ?></p>
                <p class="text-white text-[16px] font-arial text-center font-[700] mb-0">– <?= $temoignage['nom'] ?></p>
                <p class="text-white text-[16px] font-arial text-center mt-0"><?= $temoignage['description'] ?></p>
              </div>
            </li>
           <?php endforeach; ?>
          </ul>
        </div>
      </section>
    </div>
  </div>
</div>
<div class="single-author__newest bg-bgOrange mx-[30px] rounded-t-[20px] md:p-[50px] mt-[67px] ">
  <div class="single-author__newest__container max-w-[1200px] mx-auto">
    <h2 class="text-[32px] text-black mb-[50px] text-left font-ivymode max-md:p-[50px]">
      <?= __('Publications récentes', 'fdhpa17'); ?>
    </h2>
    <div class="mt-[40px] md:mt-[87px] mb-[40px]  md:mb-[63px] flex flex-row justify-center gap-[22px]
  max-md:overflow-x-scroll max-md:max-w-full max-md:justify-start">
      <article
        class=" max-md:first:ml-[15px] max-md:last:mr-[15px] post relative after:rounded-[20px] after:z-10 after:w-full after:h-full after:absolute after:content-[''] after:top-0 after:left-0 min-w-[250px] md:aspect-[2/3] max-md:min-h-[250px] md:min-w-[23%] rounded-[20px] bg-cover bg-center"
        style="background-image: url('');">
        <a class="hover:no-underline w-full h-full flex items-end relative z-20 hover:translate-x-2 transition-all duration-300 max-md:justify-center"
          href="">
          <h3
            class="post-title m-0 p-0 font-arial text-white text-left text-[14px] md:text-[19px] font-[700] mb-[42px] py-[8px] px-[19px] rounded-[40px]">
            Vivez Châtelaillon-Plage à vélo</h3>
        </a>
      </article>
    </div>
  </div>

</div>
<?php get_footer(); ?>