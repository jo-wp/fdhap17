<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 */
$logos_footer = get_field('logos_footer', 'option');
$menu_footer = get_field('menu_footer', 'option');
$social_network = get_field('social_network', 'option');
$copyrights_items = get_field('copyrights_items', 'option');

?>
<footer id="colophon"
	class="container-huge px-[20px] flex flex-col gap-[30px] md:gap-[96px] site-footer bg-green py-[27px] rounded-b-[200px] mb-[30px] max-md:p-[15px]">
	<div class="">
		<?php if ($logos_footer): ?>
			<div
				class="footer-logos max-md:grid max-md:grid-cols-2 md:flex flex-row max-md:gap-[10px] md:gap-[42px] justify-center items-center ">
				<?php foreach ($logos_footer as $logo): ?>
					<div class="footer-logo hover:-translate-y-1 transition-all duration-300">
						<a href="<?= $logo['url']['url'] ?>">
							<img class="max-md:max-w-[70px]" src="<?php echo esc_url($logo['image']); ?>" />
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="flex max-md:flex-col md:flex-row items-start justify-around gap-[30px] md:gap-[70px]">
		<div class="logo-newsletter">
			<a href="<?= get_bloginfo('url') ?>">
				<img class=" max-md:max-w-full" src="<?= get_field('logo_footer', 'option') ?>"
					alt="Logo footer <?= get_bloginfo('title'); ?>">
			</a>
			<div>
				<div>
					<form class="js-cm-form" id="subForm" action="https://www.createsend.com/t/subscribeerror?description="
						method="post"
						data-id="A61C50BEC994754B1D79C5819EC1255CC3873E6F8B51876DCB5E2826F19E413141CE16AE237389AD09A628D12E1B8D3C0F461238AA3FD2E00BA0E01193774A16">
						<div>
							<div class="flex flex-row flex-wrap items-center justify-center max-md:gap-[20px]">
								<input autocomplete="Email" class="js-cm-email-input qa-input-email text-[16px] p-[20px] md:min-w-[300px] border-0 rounded-[50px] pr-[80px]" id="fieldEmail" maxlength="200"
									name="cm-fkjldy-fkjldy" placeholder="Votre email" required="" type="email">
								<button type="submit" class="bg-orange border-0 px-[40px] py-[15px] text-white md:-ml-[75px] text-[16px] rounded-[50px]">Envoyer </button></div>
							<div><input type="hidden" value="Newsletter" id="fielddtklkrk" maxlength="200" name="cm-f-dtklkrk"></div>
							<div>
								<div>
									<div><input aria-required="" id="cm-privacy-consent" name="cm-privacy-consent" required=""
											type="checkbox"><label class=" text-[14px] text-black" for="cm-privacy-consent">J’accepte de recevoir les informations de Campings
											Atlantique</label></div><input id="cm-privacy-consent-hidden" name="cm-privacy-consent-hidden"
										type="hidden" value="true">
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<script type="text/javascript"
				src="https://js.createsend1.com/javascript/copypastesubscribeformlogic.js"></script>
		</div>
		<?php if ($menu_footer): ?>
			<div class="menu-footer max-md:mx-auto">
				<div class="text-white font-ivymode text-[20px] md:text-[32px] font-[600] text-left  mb-[22px]">
					<?= $menu_footer['titre']; ?></div>
				<ul class=" list-none m-0 p-0 ">
					<?php foreach ($menu_footer['menu'] as $item): ?>
						<li class="text-left md:leading-[50px] hover:translate-x-2 transition-all duration-300 max-md:text-center"><a
								class="font-arial text-[16px] md:text-[24px] font-[400] text-white hover:no-underline"
								href="<?= $item['lien']['url'] ?>"><?= $item['lien']['title']; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php if ($social_network): ?>
			<div class="social-network max-md:mx-auto">
				<div class="text-white font-ivymode text-[20px] md:text-[32px] font-[600] text-center md:text-left mb-[22px]">
					<?= $social_network['titre']; ?></div>
				<ul class="list-none m-0 p-0 flex flex-row gap-[26px] items-center justify-center">
					<?php foreach ($social_network['items'] as $item): ?>
						<li class="text-left hover:-translate-y-1 transition-all duration-300"><a
								class="font-arial text-[24px] font-[400] text-white hover:no-underline" href="<?= $item['lien']['url'] ?>">
								<img src="<?= $item['icon'] ?>" alt="Icon">
							</a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
	<div class="copyrights flex max-md:flex-col md:flex-row items-center justify-start gap-[30px] md:pl-[200px]">
		<?php foreach ($copyrights_items as $item): ?>
			<a class="text-white font-arial text-[16px] font-[400]"
				href="<?= $item['lien']['url']; ?>"><?= $item['lien']['title'] ?></a>
		<?php endforeach; ?>
	</div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>