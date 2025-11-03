<?php
$author_id = get_queried_object_id();

$sous_titre = get_field('sous_titre_ambassadors');
$vite_a_auteur = get_field('vite_a_auteur', 'user_' . $author_id);
$explore_auteur = get_field('explore_auteur', 'user_' . $author_id);
$specialite_auteur = get_field('specialite_auteur', 'user_' . $author_id);
$biographie_auteur = get_field('biographie_auteur', 'user_' . $author_id);
$presentation_auteur = get_field('presentation_auteur', 'user_' . $author_id);
$expertises_auteur = get_field('expertises_auteur', 'user_' . $author_id);
$temoignages_items = get_field('temoignages_items', 'user_' . $author_id);

//get field
$about = get_field('a_propos_ambassador');
$bio = get_field('biographie_ambassador');
$image = get_the_post_thumbnail_url(get_the_ID(), 'large');
$expertises_ambassador = get_field('expertises_ambassador');
$articles = get_field('articles_ambassador');

$paged = max(1, get_query_var('paged')); // sur une archive, c'est bien 'paged'
$author_id = get_queried_object_id();


$all_args = array(
  'author' => $author_id,
  'post_type' => 'post',
  'posts_per_page' => 9,
  'paged' => $paged,
  'orderby' => 'date',
  'order' => 'DESC',
);

$all_posts = new WP_Query($all_args);




get_header();
?>
<div class="max-w-[1280px] mx-auto max-[1340px]:mx-[30px]">
  <div class="single-author__header flex flex-row flex-wrap items-end justify-between mb-[50px]">
    <div class="single-author__header__title">
      <h1 class=" font-ivymode text-[50px] text-orange font-[600] mb-[0px]"><?= get_the_title() ?></h1>
      <p class="m-0 font-arial text-[24px] text-black"><?= $sous_titre; ?></p>
    </div>
    <div class="single-author__header__breadcrumb font-arial !text-[13px] text-black">
      <?php
      if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
      }
      ?>
    </div>
  </div>
  <div class="single-author__about bg-bgOrange mb-[50px] rounded-[20px] p-[50px] ">
    <h2 class="text-center text-[32px] mb-[50px]"><?= __('À propos de ','fdhpa17'); ?><?= $first_name; ?></h2>
    <div
      class="single-author__about__items max-w-[890px] mx-auto flex md:flex-row flex-wrap items-center justify-center md:items-start md:justify-between">
      <?php foreach ($about as $item): ?>
        <div class="single-author__about__items__item text-center max-md:mb-[20px]">
          <img class="md:mb-[30px]" src="<?= $item['image'] ?>" alt="Icon">
          <p class="m-0  text-[16px] text-black font-body font-[300] max-w-[200px]"><?= $item['texte'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
  <?php if ($bio): ?>
    <div
      class="single-author__description max-w-[800px] mx-auto max-[860px]:mx-[30px] mb-[90px] text-black text-[16px] text-center">
      <p class="font-arial"><?= $bio; ?></p>
    </div>
  <?php endif; ?>
  <div
    class="single-author__description max-w-[1030px] mx-auto max-[1090px]:mx-[30px] mb-[90px] text-orange text-[24px] text-center">
    <p class="font-arial"><?= $presentation_auteur; ?></p>
  </div>
  <div class="single-author__expetise flex flex-col md:flex-row flex-wrap gap-[30px] mb-[100px]">
    <div class="flex-1">
      <img class="w-full max-w-[620px] rounded-[20px] object-cover aspect-square" src="<?= $image ?>"
        alt="Photo de <?= get_the_title() ?>">
    </div>
    <div class="flex-1">
      <h2 class="text-[32px] text-black mb-[30px]"><?= get_field('titre_expertise_ambassador') ?></h2>
      <div class="single-author__expetise__items gap-[40px] flex flex-col">
        <?php foreach ($expertises_ambassador as $expertise): ?>
          <div class="single-author__expetise__items__item flex flex-row gap-[15px] items-center justify-start">
            <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/big-star.png" alt="Icon">
            <div class="[&_p]:m-0 [&_p]:text-[15px] [&_p]:font-arial [&_p]:font-[700] [&_strong]:text-green">
              <?= $expertise['texte'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php if ($articles): ?>
  <div class="single-author__newest bg-bgOrange mx-[30px] rounded-t-[20px] md:p-[50px] mt-[67px] ">
    <div class="single-author__newest__container max-w-[1200px] mx-auto">
      <h2 class="text-[32px] text-black mb-[50px] text-left font-ivymode max-md:p-[50px]">
        <?= get_field('titre_article_ambassador') ?>
      </h2>
      <div class="mt-[40px] md:mt-[87px] mb-[40px]  md:mb-[63px] flex flex-row  md:grid md:grid-cols-3  gap-[22px]
  max-md:overflow-x-scroll max-md:max-w-full max-md:justify-start relative">

        <?php foreach ($articles as $article): ?>
          <article
            class=" max-md:first:ml-[15px] max-md:last:mr-[15px] post relative after:rounded-[20px] after:z-10 after:w-full after:h-full after:absolute after:content-[''] after:top-0 after:left-0 min-w-[250px] md:aspect-[2/3] max-md:min-h-[250px] md:min-w-[23%] rounded-[20px] bg-cover bg-center"
            style="background-image: url('<?= $article['image']; ?>');">
            <a target="_blank" class="hover:no-underline w-full h-full flex items-end relative z-20 hover:translate-x-2 transition-all duration-300 max-md:justify-center"
              href="<?= $article['lien']['url'] ?>">
              <h3
                class="post-title m-0 p-0 font-arial text-white text-left text-[14px] md:text-[19px] font-[700] mb-[42px] py-[8px] px-[19px] rounded-[40px]">
                <?= $article['lien']['title'] ?>
              </h3>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>

  <?php endif; ?>
</div>
<?php get_footer(); ?>