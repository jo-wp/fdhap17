<?php

/**
 * Galerie template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_galerie = get_field('items_galerie');

?>
<section <?= get_block_wrapper_attributes(["class" => 'block-galerie container-huge max-md:mx-[0px] max-md:gap-[15px] max-md:justify-start max-md:overflow-x-scroll max-md:mb-[50px] flex flex-row flex-nowrap md:flex-row md:flex-wrap items-center justify-center gap-[3px] md:[&_img:first-child]:rounded-l-[10px] md:[&_img:last-child]:rounded-r-[10px] max-md:[&_img]:rounded-[10px]']); ?>>
  <?php foreach($items_galerie as $item): ?>
    <img class="aspect-[1/1] w-full max-md:w-[90%] md:w-[33%] max-md:!rounded-none" src="<?= $item ?>" alt="Image de la galerie" />
  <?php endforeach; ?>
</section>