<?php
/**
 * Mini site Cards template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS

$style = get_field( 'style_color' );
$styleM = ucfirst($style);




// INNERBLOCKS
$allowedBlocks = ['core/heading'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
      "color" => "green"
    ]
  ],
];
?>
<section class="block-minisite-card relative md:after:rounded-[20px] md:after:bottom-[-100px] md:after:left-0 md:after:z-10 md:after:content-[''] md:after:absolute  after-bg<?php echo $styleM ?> md:after:w-full md:after:h-[70%] lg:mb-[200px] mx-[15px] lg:mx-[30px]">
  <div class="max-w-[1640px] mx-auto style-<?php echo $style ?>">
    <InnerBlocks class="[&_h2]:text-<?php echo $style ?> [&_h2]:text-center [&_h2]:text-[32px]"
      template="<?php echo esc_attr(wp_json_encode($template)) ?>"
      allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <div class="lg:mt-[77px] md:max-w-[1640px] md:mx-[30px] min-[1700px]:mx-auto relative z-20">
    <div class="mt-[48px]">
        <section>
            <?php if( have_rows('item') ): ?>
                <ul class="grid grid-cols-2 lg:grid-cols-4 justify-center gap-[10px] md:gap-[30px] list-none p-0">

                    <?php while( have_rows('item') ): the_row();
                        $title = get_sub_field('title');
                        $image  = get_sub_field('image');
                        $link = get_sub_field( 'link' );
                        $link_e = get_sub_field( 'link_extern' );
                        if($link_e) {
                            $link = get_sub_field( 'url' );
                        }
                        ?>
                        <li>
                            <a href="<?php echo $link ?>" <?php if($link_e) { ?>target="_blank" <?php } ?>
                                    class="hover:no-underline relative max-md:shadow-md h-[50vw] lg:h-[25vw] max-h-[480px] bg-cover box-border after:content-[''] after:rounded-[10px] after:absolute after:left-0 after:bottom-0 after:w-full after:h-[50%] after:bg-gradient-to-b after:from-transparent after:to-[#00000066] rounded-[10px] flex flex-row justify-start items-end md:pl-5 md:pb-5"
                                    style="background-image: url('<?php echo $image["url"]; ?>)">
                                <div class="m-0 relative z-10 mb-[10px] max-lg:rounded-b-[10px] max-lg:flex max-md:flex-col max-md:items-center max-md:justify-center max-md:bg-white max-md:w-full max-md:text-center max-md:h-[65px] max-md:mb-0">
                                    <h3 class="text-black md:text-white text-[16px] md:text-[20px] font-[400] md:font-bold font-arial my-0"><?php echo $title ?></h3>
                                    <span class="text-[12px] md:hidden text-green items-center after:content-[''] after:bg-arrow-mini-green after:w-[7px] after:h-[8px] after:inline-block after:ml-2">Explorer</span>
                                </div>
                            </a>

                        </li>
                    <?php endwhile; ?>

                </ul>
            <?php endif; ?>

        </section>
    </div>
  </div>
</section>