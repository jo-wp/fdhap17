<?php
/**
 * Editorial template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$image = get_field('image');
$type_block_editorial = get_field('type_block_editorial');
$buttons = get_field('button');

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

// Switch class for Type Block 
$class_block_editorial = '';
$class_block_editorial_texte = '';
$size_image = '';
$size_blocks = '';
switch ($type_block_editorial) {
	case 'default':
        $size_section = 'max-w-[1034px] max-[1090px]:mx-[30px] ';
        $size_blocks_content = ' md:flex-1 ';
        $size_blocks_image = ' md:flex-1 ';
		    $size_image = 'aspect-square lg:aspect-auto lg:min-w-[550px] w-full lg:min-h-[550px]';
        $class_innerblock = '[&_p]:text-center md:[&_p]:text-left [&_h2]:text-center md:[&_h2]:text-left [&_h1]:text-left  mb-[40px] [&_h2]:mb-[50px] [&_h1]:mb-[50px]';
		    $class_block_editorial = 'flex flex-col md:flex-row gap-0 md:gap-[53px]';
		break;
	case 'image_big_right':
        $size_section = 'max-w-[1034px] max-[1090px]:mx-[30px] ';
        $size_blocks_content = 'max-md:w-full md:w-[50%] ';
        $size_blocks_image = 'max-md:w-full md:w-[50%] ';
		    $size_image = 'max-md:min-w-full md:min-w-[720px] w-full md:min-h-[720px] object-cover object-center';
        $class_innerblock = '[&_p]:text-center md:[&_p]:text-left [&_h2]:text-center md:[&_h2]:text-left [&_h1]:text-center md:[&_h1]:text-left  mb-[40px] [&_h2]:mb-[50px] [&_h1]:mb-[50px]';
        $class_block_editorial_texte = '-m-[50px] bg-white px-[30px] md:px-[73px] py-[25px] md:py-[100px] rounded-[10px] shadow-[1px_8px_30px_0_rgba(186,186,186,0.18)]';
        $class_block_editorial = 'flex max-md:flex-col md:flex-row-reverse ';
		break;
    case 'image_big_btn':
        $size_section = 'max-w-[1260px] max-md:mx-[15px] max-xl:mx-[30px] ';
        $size_blocks_content = 'max-md:w-full md:w-[52%] ';
        $size_blocks_image = 'max-md:w-full md:w-[65%] ';
        $class_innerblock = '[&_p]:text-center [&_h2]:text-center [&_h1]:text-center [&_a]:underline mb-0 [&_h2]:mb-[35px] [&_h1]:mb-[35px]';
        $size_image = 'w-full h-full object-cover object-center';
        $class_block_editorial_texte = 'md:-ml-[17%] bg-white px-[15px] lg:px-[62px] py-[70px] rounded-[10px] shadow-[1px_8px_30px_0_rgba(186,186,186,0.18)]';
        $class_block_editorial = 'flex max-md:flex-col md:flex-row-reverse ';
        break;

}

?>


<section <?= get_block_wrapper_attributes(["class" => 'block-editorial '.$size_section.' mx-auto '.$class_block_editorial.'   justify-between items-center']); ?>>
  <div class="<?= $size_blocks_content; ?> <?= $class_block_editorial_texte; ?>" >
      <InnerBlocks class="<?php echo $class_innerblock; ?> animateFade fadeOutAnimation md:[&_p]:text-inherit md:[&_h2]:text-inherit md:[&_h1]:text-inherit [&_h2_sub]:text-center [&_h1_sub]:text-center md:[&_h2_sub]:text-inherit md:[&_h1_sub]:text-inherit text-[14px] md:[&_p]:text-[15px] [&_h2]:font-[600] [&_h1]:font-[600] [&_h2]:text-[24px] [&_h1]:text-[24px] md:[&_h2]:text-[32px] md:[&_h1]:text-[32px] [&_h2_sub]:font-arial [&_h1_sub]:font-arial [&_h2_sub]:text-[24px] [&_h1_sub]:text-[24px] md:[&_h2_sub]:text-[32px] md:[&_h1_sub]:text-[32px] [&_h2_sub]:float-none [&_h1_sub]:float-none  md:[&_h2_sub]:float-right md:[&_h1_sub]:float-right [&_h2_sub]:font-[400] [&_h1_sub]:font-[400] [&_h2]:font-[400] [&_h1]:font-[400] [&_p]:font-arial" template="<?php echo esc_attr(wp_json_encode($template)) ?>"
        allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <div class="<?= $size_blocks_image; ?>">
    <?php if ($image) : ?>
      <div class="block-editorial__image">
        <img class="<?= $size_image; ?> rounded-[20px] object-cover " src="<?= esc_url($image['url']) ?>" alt="<?= esc_attr($image['alt']) ?>" />
      </div>
    <?php endif; ?>
  </div>
</section>
<?php if( $buttons ): ?>
<section class="max-w-[1270px] md:max-[1270px]:mx-[30px] mx-auto -mt-[40px] lg:-mt-[80px]">
    <div class="max-md:w-full w-full xl:w-[61%] ">

        <div class="inline-flex justify-center items-center gap-[20px] max-xl:mt-[20px] flex-col md:flex-row w-full max-md:px-[30px] box-border">
            <?php while( have_rows('button') ): the_row();
                $title = get_sub_field('title');
                $style = get_sub_field('style');
                $link = get_sub_field( 'page' );
                $link_e = get_sub_field( 'link_extern' );
                if($link_e) {
                    $link = get_sub_field( 'url' );
                }
                ?>
                <a class="button <?php echo $style ?>  !font-bold !text-base !py-4 !px-5" href="<?php echo $link; ?>">
                    <?php echo $title ?>
                </a>
            <?php endwhile; ?>
        </div>

    </div>
</section>
<?php endif; ?>