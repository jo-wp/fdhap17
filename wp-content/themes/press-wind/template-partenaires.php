<?php
/*
Template Name: Liste des Partenaires
*/
get_header();
?>
<section class="partenaires-wrapper max-w-[1034px] max-[1090px]:mx-[30px] mx-auto text-center">
    <h1 class="inline-block font-bold pb-4 text-green text-center text-[32px] relative after:absolute after:bottom-0 after:block after:bg-green after:h-[1px] after:-left-4 after:-right-4 after:content-['']"><?php the_title() ?></h1>

    <?php
    $args = array(
        'post_type' => 'partenaire',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $partenaires = new WP_Query($args);

    if ($partenaires->have_posts()) :
        ?>
        <ul class="grid grid-cols-1 sm:grid-cols-3 gap-4 list-none">
            <?php while ($partenaires->have_posts()) : $partenaires->the_post(); ?>
                <li class="partenaire-item">
                    <div class="flex justify-center items-center h-[267px]">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('full', ['class' => 'w-full h-auto object-contain']); ?>
                        <?php endif; ?>
                    </div>

                    <div class="bg-bgOrange p-4">
                        <h2 class="font-arial text-[26px] font-normal text-orangeGlow"><?php the_title(); ?></h2>
                        <div class="text-base"><?php the_content(); ?></div>
                    </div>

                </li>
            <?php endwhile; ?>
        </ul>
        <?php
        wp_reset_postdata();
    else :
        ?>
        <p>Aucun partenaire trouvé.</p>
    <?php
    endif;
    ?>

</section>
<?php get_footer(); ?>
