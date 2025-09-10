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
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<header id="masthead" class="site-header">


			<?php

			// HEADER : mini site
			
			$current_id = get_the_ID();
			$ref_id = wp_get_post_parent_id($current_id) ?: $current_id;
			$mini_site = get_field('mini_site', $ref_id);
			$logo = get_field('mini_site_logo', $ref_id);


			if ($mini_site) {
				?>
				<section class="minisite-subheader bg-orange container-huge mb-[4px]">
					<?php
					wp_nav_menu([
						'theme_location' => 'minisite-preheader',
						'container' => 'nav',
						'menu_class' => 'flex justify-end gap-4 px-4 py-[14px] list-none m-0 text-white text-[15px] font-montserrat',
					]);
					?>
				</section>
				<section
					class="minisite-header mb-6 max-w-[914px] mx-auto flex justify-center items-end border-solid border-l-0 border-t-0 border-r-0 border-b-[2px] border-black/37 pb-3">

					<a class="flex" href="<?php echo the_permalink($ref_id) ?>">
						<img src="<?php echo $logo['url'] ?>" width="140" />
					</a>

					<?php
					wp_nav_menu([
						'theme_location' => 'minisite-primary', // doit correspondre à ce que tu as mis dans register_nav_menus
						'container' => 'nav',     // balise wrapper <nav>
						'menu_class' => 'font-montserrat font-semibold text-base flex gap-16 list-none m-0', // classes Tailwind si tu veux
					]);
					?>
				</section>
				<?php
			}
			?>


		</header>