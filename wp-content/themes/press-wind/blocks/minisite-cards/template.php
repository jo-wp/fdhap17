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
<section <?= get_block_wrapper_attributes(["class" => 'container-huge block-minisite-card relative md:after:rounded-[20px] md:after:bottom-[-50px] md:after:left-0 md:after:z-10 md:after:content-[""] md:after:absolute md:after:bg-bg'.$styleM.' md:after:w-full md:after:h-[30%] mb-[150px]']); ?>>
  <div class="max-w-[1650px] mx-auto style-<?php echo $style ?>">
    <InnerBlocks class="[&_h2]:text-<?php echo $style ?> [&_h2]:text-center [&_h2]:text-[32px]"
      template="<?php echo esc_attr(wp_json_encode($template)) ?>"
      allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
  </div>
  <div class="mt-[77px] md:mx-[100px] relative z-20">
    <div class="mt-[48px]">
      <div class="">
        <section class="">

            <?php if( have_rows('item') ): ?>
                <ul class="grid grid-cols-4 gap-[30px] list-none p-0">

                    <?php while( have_rows('item') ): the_row();
                        $title = get_sub_field('title');
                        $image  = get_sub_field('image');
                        ?>
                        <li class="">
                            <div
                                    class="relative max-md:shadow-md h-[480px] bg-cover box-border after:content-[''] after:rounded-[10px] after:absolute after:left-0 after:bottom-0 after:w-full after:h-[50%] after:bg-gradient-to-b after:from-transparent after:to-[#00000066] rounded-[10px] flex flex-row justify-start items-end md:pl-5 md:pb-5"
                                    style="background-image: url('<?php echo $image["url"]; ?>)">
                                <h3 class="text-white text-[20px] font-bold font-arial m-0 relative z-10 mb-[10px] max-md:rounded-b-[10px] max-md:flex max-md:flex-col max-md:gap-1 max-md:items-center max-md:justify-center max-md:text-black max-md:text-[16px] max-md:bg-white max-md:w-full max-md:text-center max-md:h-[65px] max-md:mb-0">
                                    <?php echo $title ?>

                                </h3>
                            </div>

                        </li>
                    <?php endwhile; ?>

                </ul>
            <?php endif; ?>

        </section>
      </div>
    </div>
  </div>
</section>