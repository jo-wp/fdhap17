<?php
$campings_associated_article = get_field('campings_associated_article');
?>

<?php if ($campings_associated_article): ?>
  <div class="bloc-camping-associated flex flex-col flex-wrap">
    <h2 class="text-center mt-[100px] !text-orange text-[50px] font-[600] leading-[57px] mb-[40px]">
      <?= _e('Les campings à proximité', 'fdhpa17') ?>
    </h2>
    <div class="flex flex-row gap-[20px]">
      <?php foreach ($campings_associated_article as $item): ?>
        <div class="bloc-camping-associated__items flex flex-row gap-[15px] md:gap-[40px] max-md:overflow-x-scroll ">
          <?php
          $image_featured_url = get_the_post_thumbnail_url($item->ID, 'full');
          $stars = 5;
          ?>
          <div class="md:flex-1 bloc-camping-associated__items__item ">
            <div class="image-featured min-h-[290px] min-w-auto bg-center bg-cover rounded-[10px] max-md:min-w-[250px]"
              style="background-image:url('<?= $image_featured_url; ?>');">
              <div class="flex flex-row justify-between items-center py-[12px] px-[14px]">
                <span class="bg-green text-white font-arial text-[14px] px-[20px] py-[8px] rounded-full">À partir de
                </span>
                <a href="#"><img src="<?= esc_url(get_theme_file_uri('/assets/media/heart.png')) ?>"
                    alt="icon ajouter aux favoris"></a>
              </div>
            </div>
            <div class="informations mt-[20px]">
              <h3 class=" font-arial text-[22px] font-[700] text-black m-0 mb-[5px]"><?= get_the_title($item->ID) ?></h3>
              <div class="stars">
                <?php for ($i = 0; $i < $stars; $i++): ?>
                  <img class="max-w-[13px]" src="<?= get_template_directory_uri() ?>/assets/media/star.svg"
                    alt="Etoile du camping <?= get_the_title(); ?>" />
                <?php endfor; ?>
              </div>
              <div class="location flex flex-row justify-between mb-[30px] items-center">
                <div class="flex flex-row gap-[8px]">
                  <img src="<?= esc_url(get_theme_file_uri('/assets/media/marker-v2.svg')) ?>" alt="icon localisation">
                  <p class="text-[#000] font-arial text-[14px]"><?= get_post_meta($item->ID, 'commune', true); ?> -
                    <?= get_post_meta($item->ID, 'code_postal', true); ?>
                  </p>
                </div>
                <div>
                  <p class="text-black font-arial text-[12px]">La nouvelle aquitaine</p>
                </div>
              </div>
              <div class="">
                <a href="<?= get_permalink($item->ID); ?>"
                  class="button button--grey button button--grey hover:!text-[#fff] !text-[14px] !text-black !no-underline"><?= _e('Voir le camping', 'fdhpa17'); ?></a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>