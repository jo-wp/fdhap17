<?php get_header(); ?>

<main class="container-huge">
  <h1 class="text-orange font-arial text-[32px]"><?= __('Résultats de recherche pour : ','fdhpa17'); ?><?php echo get_search_query(); ?></h1>
  <?php if (have_posts()): ?>
    <div class="search-results grid grid-cols-1 md:grid-cols-3 gap-[20px]">
      <?php while (have_posts()):
        the_post(); ?>
        <article class=" shadow-lg transition-all hover:scale-105 ">
          <a href="<?php the_permalink(); ?>" class="hover:no-underline text-[24px] font-arial m-0 ">
          <img class=" aspect-square w-full h-[150px] object-cover" src="<?php the_post_thumbnail_url(get_the_ID()) ?>"
            alt="Image mise en avant de : <?php the_title() ?>" />
          <div class="p-[15px]">
            <h2 class="text-[24px] font-arial text-green mb-[15px]"><?php the_title(); ?></h2>
            <div class="[&_p]:m-0 text-[15px]"><?php the_excerpt(); ?></div>
            <div class="flex flex-row flex-wrap items-center justify-between">
              <?php if(the_date()): ?>
              <span class="text-[15px] italic"><?= __('Publié le : ','fdhpa17'); ?> <?php the_date(); ?></span>
              <?php endif; ?>
              <span><img class=" -rotate-90" src="<?= get_bloginfo('template_directory') ?>/assets/media/arrow-menu-orange.svg"></span>
            </div>
          </div>
          </a>
        </article>
      <?php endwhile; ?>
    </div>
    <div class="text-center mt-[20px]">
    <?php the_posts_pagination(); ?>
    </div> 
  <?php else: ?>
    <p>Aucun résultat trouvé.</p>
  <?php endif; ?>

</main>

<?php get_footer(); ?>