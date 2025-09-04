<?php
$author_id = get_queried_object_id();
$first_name = get_the_author_meta('first_name', $author_id);
$last_name = get_the_author_meta('last_name', $author_id);
get_header();
?>
<div class="max-w-[1280px] mx-auto max-[1340px]:mx-[30px]">
  <div class="single-author__header flex flex-row flex-wrap items-end justify-between mb-[50px]">
    <div class="single-author__header__title">
      <h1 class=" font-ivymode text-[50px] text-green font-[600]"><?= $first_name . ' ' . $last_name ?></h1>
      <p class="m-0 font-arial text-[24px] text-black">Exploratrice du littoral – Experte en Charente-Maritime</p>
    </div>
    <div class="single-author__header__breadcrumb font-arial !text-[13px] text-black">
      YOAST SEO BREADCRUM
    </div>
  </div>
  <div class="single-author__about bg-bgGreen mb-[50px] rounded-[20px] p-[50px] ">
    <h2 class="text-center text-[32px] mb-[50px]"><?= __('À propos de '); ?><?= $first_name; ?></h2>
    <div class="single-author__about__items max-w-[890px] mx-auto flex md:flex-row flex-wrap items-center justify-center md:items-start md:justify-between">
      <div class="single-author__about__items__item text-center max-md:mb-[20px]">
        <img class="md:mb-[30px]" src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-author-live.svg"
          alt="Icon Vit à">
        <p class="font-arial text-[20px] font-[700] text-black uppercase m-0 mb-[10px] "><?= __('Vit à', 'fdhpa17') ?>
        </p>
        <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]">Saint-Palais-sur-Mer (17)</p>
      </div>
      <div class="single-author__about__items__item text-center max-md:mb-[20px]">
        <img class="md:mb-[30px]" src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-explore.svg"
          alt="Icon Vit à">
        <p class="font-arial text-[20px] font-[700] text-black uppercase m-0 mb-[10px] "><?= __('Explore', 'fdhpa17') ?>
        </p>
        <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]">la Charente-Maritime toute l’année</p>
      </div>
      <div class="single-author__about__items__item text-center max-md:mb-[20px]">
        <img class="md:mb-[30px]" src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-speciality.svg"
          alt="Icon Vit à">
        <p class="font-arial text-[20px] font-[700] text-black uppercase m-0 mb-[10px] "><?= __('Vit à', 'fdhpa17') ?>
        </p>
        <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]">Randonnées, guides nature et
          découvertes locales</p>
      </div>
    </div>

  </div>
  <div
    class="single-author__description max-w-[800px] mx-auto max-[860px]:mx-[30px] mb-[90px] text-black text-[16px] text-center">
    <p class="font-arial">Installée depuis 8 ans en Charente-Maritime, je sillonne chaque recoin du littoral à pied, à
      vélo ou en kayak. Mon objectif ? Faire découvrir les pépites naturelles et les expériences authentiques que recèle
      la région. Passionnée de tourisme durable, je partage ici mes meilleurs itinéraires, conseils de camping et bons
      plans pour vivre la côte Atlantique autrement.</p>
  </div>
  <div
    class="single-author__description max-w-[1030px] mx-auto max-[1090px]:mx-[30px] mb-[90px] text-orange text-[24px] text-center">
    <p class="font-arial">Sur Campings Atlantique, je propose des contenus inspirants et pratiques pour explorer la
      région hors des sentiers battus.</p>
  </div>
  <div class="single-author__expetise flex flex-col md:flex-row flex-wrap gap-[30px] mb-[100px]">
    <div class="flex-1">
      <img class="w-full md:w-auto" src="<?= get_bloginfo('url') ?>/wp-content/uploads/2025/09/Image-15.png"
        alt="Photo de <?= $first_name . ' ' . $last_name; ?>">
    </div>
    <div class="flex-1">
      <h2 class="text-[32px] text-black mb-[30px]"><?= __('Mon expertise', 'fdhpa17'); ?></h2>
      <div class="single-author__expetise__items gap-[40px] flex flex-col">
        <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
          <p class="m-0 text-[15px] font-[700]">Plus de 8 années de repérages et de randonnées en solo sur le littoral
            charentais</p>
        </div>
        <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
          <p class="m-0 text-[15px] font-[700]">Connaissance fine du littoral et des zones naturelles sensibles :
            marais, estuaires, forêts littorales</p>
        </div>
        <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
          <p class="m-0 text-[15px] font-[700]">Création de guides pratiques pensés pour les voyageurs indépendants :
            itinéraires, sécurité, spots paisibles</p>
        </div>
        <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
          <p class="m-0 text-[15px] font-[700]">Rédactrice spécialisée tourisme & nature pour Campings Atlantique et
            plusieurs blogs écoresponsables</p>
        </div>
        <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
          <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
          <p class="m-0 text-[15px] font-[700]">Sensibilité accrue à l’expérience solo : conseils concrets pour se
            repérer, se ressourcer et rencontrer sans pression</p>
        </div>
      </div>
    </div>
  </div>
  <div class="single-author__quote bg-green rounded-[20px] p-[30px]">
    <div class="single-author__quote__title text-center mb-[50px]">
      <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-quote.svg" alt="Icon author quote">
      <h2 class="text-[32px] text-white"><?= __('Témoignage de lecteur', 'fdhpa17'); ?></h2>
    </div>
    <div class="single-author__quote__items">
      <div class="single-author__quote__items__item">
        <p class="text-white text-[20px] font-arial text-center mb-[50px]">Les articles de Léa m’ont donné la confiance
          pour partir seule en camping pour la première fois. Ses itinéraires sont clairs, rassurants et inspirants. On
          sent qu’elle parle d’expérience, avec justesse et bienveillance.</p>
        <p class="text-white text-[16px] font-arial text-center font-[700] mb-0">– Camille R.</p>
        <p class="text-white text-[16px] font-arial text-center mt-0">Campeuse solo débutante</p>
      </div>
    </div>
  </div>
</div>
<?php get_footer(); ?>