<?php
/**
 * Mini site Cards template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS

$style = get_field( 'style_color' );
$buttons = get_field('button');

?>

<?php if( $buttons ): ?>
    <section class="block-buttons max-w-[1270px] md:max-[1270px]:mx-[30px] mx-auto">
        <div class="max-md:w-full w-full ">

            <div class="inline-flex justify-center items-center gap-[20px] max-xl:mt-[20px] flex-col md:flex-row w-full max-md:px-[30px] box-border">
                <?php while( have_rows('button') ): the_row();
                    $title = get_sub_field('title');
                    $style = get_sub_field('style');
                    $link = get_sub_field( 'link' );
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