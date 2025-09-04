<?php
/**
 * Editorial-Text template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$content_texte_editorial = get_field('content_texte_editorial');

// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
	[
		'core/heading',
		[
			"placeholder" => "Titre du bloc",
			"level" => 2,
      "color" => "foreground"
		]
	],
	[
		'core/paragraph',
		[
			"placeholder" => "Description ..."
		]
	]
];




?>
<section <?= get_block_wrapper_attributes(["class" => 'block-editorial-text max-w-[1280px] mx-auto max-[1280px]:mx-[30px] flex max-md:flex-col md:flex-row flex-wrap justify-between items-start md:gap-[70px]']); ?>>
  <div class="flex-1 md:max-w-[40%]">
      <InnerBlocks class="mb-[40px]  [&_p]:text-[14px] md:[&_p]:text-[15px] [&_h2]:relative max-md:[&_h2]:text-center  [&_h2::after]:block [&_h2]:text-orange [&_h2]:text-[20px]  md:[&_h2]:text-[30px] [&_h2]:font-[700] [&_h2_span]:font-arial [&_h2_span]:text-[32px] [&_h2_span]:font-[400] after-underline" template="<?php echo esc_attr(wp_json_encode($template)) ?>"
        allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <div class="flex-1 max-md:[&_p]:text-center [&_p]:m-0 [&_p]:text-[15px] [&_p]:text-black [&_p]:font-[400]">
    <?= $content_texte_editorial; ?>
  </div>
</section>