<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>



	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<?php if(WP_ENV!='development'): ?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=AW-1017019547"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag() { dataLayer.push(arguments); }
		gtag('js', new Date());

		gtag('config', 'AW-1017019547');
	</script>
	<?php endif; ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<header id="masthead" class="site-header">
			<?php

			$current_id = get_the_ID();
			$ref_id = wp_get_post_parent_id($current_id) ?: $current_id;
			$mini_site = get_field('mini_site', $ref_id);
			$logo = get_field('mini_site_logo', $ref_id);

      $hero_is = get_field('hero_type', $current_id);

			if ($mini_site) {
				?>
				<section class="max-md:hidden minisite-subheader bg-orange container-huge mb-[4px] flex justify-end items-center <?php  if($hero_is != "none") { ?>absolute left-0 right-0  z-[1000]<?php } ?>">
					<?php
					wp_nav_menu([
						'theme_location' => 'minisite-preheader',
						'container' => 'nav',
						'menu_class' => 'flex justify-end gap-4 px-4 py-[14px] list-none m-0 text-white text-[15px] font-montserrat',
					]);
					?>
                    <div class="mr-4">
                        <?php do_action( 'wpml_add_language_selector' ); ?>
                    </div>

				</section>
				<section
					class="max-md:bg-white px-[15px] minisite-header mb-6 max-w-[914px] mx-auto flex justify-between md:justify-center items-center md:items-end border-solid border-l-0 border-t-0 border-r-0 border-b-[2px] border-black/37 pb-3 <?php  if($hero_is != "none") { ?>minisite-header-fix absolute left-0 right-0 z-[1000] md:top-[90px]<?php } ?>">

					<a class="flex" href="<?php echo the_permalink($ref_id) ?>">
						<img src="<?php echo $logo['url'] ?>" width="140" class="max-md:w-[100px]" />
					</a>

                    <div class="md:hidden">

                        <div class="absolute right-14 top-2">
                            <?php do_action( 'wpml_add_language_selector' ); ?>
                        </div>

                        <a href="#" class="open-menu-mobile block">
                            <img class="" src="<?= get_bloginfo('template_directory') ?>/assets/media/button-mobile-menu.svg"
                                 alt="button mobile menu">
                        </a>
                    </div>

					<a href="#" class="close-menu-mobile hidden ">
						<img class="mt-2" src="<?= get_bloginfo('template_directory') ?>/assets/media/close-menu-mobile.svg"
							alt="button mobile menu ">
					</a>

					<div class="block-minisite__menu
										max-md:p-[15px] max-md:shadow-md max-md:absolute max-md:top-[104px] max-md:left-0 max-md:right-0 max-md:z-[100] max-md:bg-white max-md:border-t-2 max-md:border-solid max-md:border-l-0 max-md:border-r-0 max-md:border-b-0 max-md:border-[#ddd]

										max-md:-translate-x-full
										max-md:[&.active]:translate-x-0
										max-md:transition-transform max-md:duration-300 max-md:ease-in-out
										">
						<?php
						wp_nav_menu([
							'theme_location' => 'minisite-primary', // doit correspondre à ce que tu as mis dans register_nav_menus
							'container' => 'nav',     // balise wrapper <nav>
							'menu_class' => 'font-montserrat font-semibold text-base flex gap-10 lg:gap-16 list-none m-0 max-md:p-0 max-md:flex-col ', // classes Tailwind si tu veux
						]);
						?>
						<div class="md:hidden">
							<?php
							wp_nav_menu([
								'theme_location' => 'minisite-preheader',
								'container' => 'nav',
								'menu_class' => 'flex justify-end md:gap-4  py-[14px] list-none m-0 p-0 md:px-4 text-orange max-md:font-bold md:text-white text-[15px] font-montserrat max-md:flex-col',
							]);
							?>
						</div>
					</div>


				</section>
				<?php
			}

            if($hero_is != "none") {
                get_template_part('partials/hero');
            } else {
                if ($mini_site) {
                    ?>
                    <nav aria-label="Fil d’Ariane" class="minisite-breadcrumb mb-12">
                        <ol class="list-none flex gap-4 font-arial text-[13px] justify-center m-0 p-0">
                            <li>
                                <a href="<?php the_permalink($ref_id); ?>">fdhpa-17</a>
                            </li>
                            <li class="font-bold tracking-wider">
                                <?php the_title() ?>
                            </li>
                        </ol>
                    </nav>
                    <?php
                }
            }

			?>
			<a href="/carte-camping" id="cta-button" class="cta-button opacity-0 translate-y-5 md:hidden z-[999]
         max-md:fixed max-md:right-0 max-md:left-0 max-md:mx-auto max-md:max-w-[250px] max-md:bg-orange
         max-md:text-white max-md:text-[16px] max-md:flex max-md:flex-row max-md:flex-wrap max-md:gap-[10px]
         max-md:items-center max-md:justify-center max-md:border-0 max-md:rounded-[20px]
         max-md:py-[10px] max-md:bottom-[20px]
         transition-all duration-500 ease-out [&.show]:opacity-100 [&.show]:translate-y-0">
				<img src="<?= get_stylesheet_directory_uri() ?>/assets/media/icon-button-calendar.svg" alt="Button Voir dispo">
				<?= __('Voir les disponibilités','fdhpa17'); ?>
					</a>
		</header>