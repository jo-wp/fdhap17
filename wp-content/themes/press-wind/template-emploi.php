<?php
/*
Template Name: Liste des Offres d'emploi
*/
get_header();

$current_id = get_the_ID();
$ref_id = wp_get_post_parent_id($current_id) ?: $current_id;

$sub_title = get_field('offers_subtitle');
$intro = get_field('offers_intro');

// Récupération de la page courante pour la pagination
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
?>
<section class="container-huge 2xl:mx-auto 2xl:w-[1280px] 2xl:max-w-full text-center">


    <h1 class="inline-block font-bold pb-4 text-orange text-center text-[32px]"><?php the_title() ?></h1>

    <?php if($sub_title) { ?>
        <span class="block text-2xl"><?php echo $sub_title ?></span>
    <?php } ?>

    <?php if($intro) { ?>
        <div class="block mx-auto max-w-[540px]">
            <p class="text-base font-arial mt-6"><?php echo $intro ?></p>
        </div>
    <?php } ?>


    <?php
    $args = array(
        'post_type'      => 'offre_emploi',
        'posts_per_page' => 9,          // 👉 9 éléments par page
        'orderby'        => 'title',
        'order'          => 'ASC',
        'paged'          => $paged     // 👉 nécessaire pour la pagination
    );
    $offers = new WP_Query($args);

    if ($offers->have_posts()) :
        ?>
        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-10 list-none m-0 p-0 mt-20">
            <?php while ($offers->have_posts()) : $offers->the_post(); ?>
                <li class="partenaire-item">
                    <a class=" flex flex-col gap-4 md:gap-8 hover:no-underline"  href="<?php echo get_field('link') ?>" target="_blank">
                        <div class="flex justify-center overflow-hidden items-center h-[60vw] lg:h-[50vw] md:max-h-[267px]  rounded-[20px]">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('full', ['class' => 'w-full h-auto object-contain']); ?>
                            <?php endif; ?>
                        </div>

                        <div class="bg-bgGreen px-4 py-6 rounded-[20px] ">
                            <h2 class="font-body text-[26px] font-medium"><?php the_title(); ?></h2>
                            <div class="text-base text-black/60 min-h-[50px]"><?php the_excerpt(); ?></div>
                            <span  class="button button--bg-green !border-green mt-4">Voir l'offre en détail</span>
                        </div>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>

        <!-- Pagination -->
        <div class="minisite-pagination mt-10">
            <?php
            echo paginate_links(array(
                'total'   => $offers->max_num_pages,
                'current' => $paged,
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
            ));
            ?>
        </div>

        <?php
        wp_reset_postdata();
    else :
        ?>
        <p>Aucune offre d'emploi trouvée.</p>
    <?php
    endif;
    ?>

</section>
<?php get_footer(); ?>
