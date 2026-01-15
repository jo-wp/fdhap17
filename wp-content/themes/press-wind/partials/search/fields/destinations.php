<?php if (!is_page('carte-camping') && !is_front_page()):

$destinations_featured = [
    'camping-royan' => 'Royan',
    'camping-ile-oleron' => "Île d'Oléron",
    'camping-ile-de-re' => "Île de Ré",
    'camping-pays-du-cognac' => 'Pays du Cognac',
    'camping-marais-poitevin' => 'Marais Poitevin',
    'camping-la-rochelle' => 'La Rochelle',
    'camping-rochefort' => 'Rochefort',
    'camping-futuroscope' => 'Futuroscope',
];

?>

<div class="facetwp-facet facetwp-facet-destination facetwp-type-dropdown" data-name="destination" data-type="dropdown">
    <select class="facetwp-dropdown">
        <option value="">Destination</option>

        <?php foreach ($destinations_featured as $slug => $label): ?>

            <?php
            $query = new WP_Query([
                'post_type'      => 'camping',
                'posts_per_page' => 1,
                'tax_query'      => [
                    [
                        'taxonomy' => 'destination',
                        'field'    => 'slug',
                        'terms'    => $slug,
                    ],
                ],
            ]);

            $count = $query->found_posts;
            wp_reset_postdata();
            ?>

            <?php if ($count > 0): ?>
                <option value="<?= $slug ?>">
                    <?= $label ?> (<?= $count ?>)
                </option>
            <?php endif; ?>

        <?php endforeach; ?>

    </select>
</div>

<?php else: ?>
    <?= do_shortcode('[facetwp facet="destination"]'); ?>
<?php endif; ?>
