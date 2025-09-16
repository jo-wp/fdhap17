<?php
/**
 * Mini site Two cols equal template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS

$image = get_field( 'image' );


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
    ],
    [
        'core/paragraph',
        [
            "placeholder" => "Description ..."
        ]
    ]
];
?>
<section <?= get_block_wrapper_attributes(["class" => 'container-huge block-minisite-twocolsequal relative mt-[100px]']); ?>>
    <div class="md:grid md:grid-cols-2 gap-[50px]  max-w-[1270px] mx-auto">
        <?php if ($image) : ?>
            <div class="block-editorial__image">
                <img class="w-full rounded-[20px] object-cover " src="<?= esc_url($image['url']) ?>" alt="<?= esc_attr($image['alt']) ?>" />
            </div>
        <?php endif; ?>

        <div class="py-[10px]">
            <InnerBlocks class="[&_h2]:text-[32px] [&_h2]:mb-[35px] [&_p]:text-[15px] [&_p]:font-light [&_a]:underline"
                         template="<?php echo esc_attr(wp_json_encode($template)) ?>"
                         allowedBlocks="<?php echo esc_attr(wp_json_encode($allowedBlocks)) ?>" templateLock="all" />
        </div>

    </div>

</section>