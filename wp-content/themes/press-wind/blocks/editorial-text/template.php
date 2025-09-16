<?php
/**
 * Editorial-Text template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$content_texte_editorial = get_field('content_texte_editorial');
$content_one_col = get_field('one_col');

// INNERBLOCKS
$allowedBlocks = ['core/heading'];
$template = [
	[
		'core/heading',
		[
			"placeholder" => "Titre du bloc",
			"level" => 2,
      "color" => "foreground"
		]
	]
];

if($content_one_col) {
    $class = "max-w-[1000px]  text-center mb-[50px]";
    $class_container = "";
    $class_text = "mb-[20px] [&_p]:text-center";
} else {
    $class = 'max-w-[1260px] px-[15px] md:px-[30px] flex max-md:flex-col md:flex-row flex-wrap justify-between items-start md:gap-[70px]';
    $class_container = "w-full md:max-w-[40%]";
    $class_text = "mb-[40px] after-underline";
}


?>
<section <?= get_block_wrapper_attributes(["class" => 'block-editorial-text  mx-auto '.$class.' ']); ?>>
  <div class="<?php echo $class_container ?>">
      <InnerBlocks class="[&_h2]:relative max-md:[&_h2]:text-center [&_h1]:relative max-md:[&_h1]:text-center 
      [&_h2::after]:block [&_h2]:text-orange [&_h2]:text-[20px] [&_h1::after]:block [&_h1]:text-orange [&_h1]:text-[20px]
      md:[&_h2]:text-[30px] [&_h2]:font-[700] [&_h2_span]:font-arial [&_h2_span]:text-[32px] [&_h2_span]:font-[400] 
       md:[&_h1]:text-[30px] [&_h1]:font-[700] [&_h1_span]:font-arial [&_h1_span]:text-[32px] [&_h1_span]:font-[400]
      <?php echo $class_text ?>" 
      template="<?php echo esc_attr(wp_json_encode($template)) ?>"
      allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <div class="flex-1 max-md:[&_p]:text-center [&_p]:m-0 [&_p:not(:last-child)]:mb-[28px] [&_p]:text-[15px] [&_p]:text-black [&_p]:font-[400] [&_a]:underline">
    <?= $content_texte_editorial; ?>
  </div>
</section>