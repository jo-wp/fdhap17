<?php
/**
 * Presentation template (version simple & robuste).
 */

// ACF
$type_block_presentation = get_field('type_block_presentation');
$carousel_images         = get_field('carousel_images');   // attendu: array d'URL
$image_presentation      = get_field('image_presentation'); // attendu: URL
$inverse_presentation    = get_field('inverse_presentation');

// --- Sécuriser le type (WPML peut renvoyer null / label traduit) ---
$allowed_types = ['default','image','image_out','image_background'];
if (!in_array($type_block_presentation, $allowed_types, true)) {
  $type_block_presentation = 'image'; // fallback simple
}

// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph', 'core/list', 'core/list-item'];
$template = [
  ['core/heading', ['placeholder' => 'Titre du bloc', 'level' => 2, 'color' => 'orange']],
  ['core/paragraph', ['placeholder' => 'Description ...']],
  ['core/list', ['placeholder' => 'Liste ...']],
];

// Classes par défaut
$section_class     = 'max-md:flex-col-reverse md:flex-row max-md:max-w-full md:max-w-[1400px]';
$inverse_container = 'bg-bgOrange max-md:max-w-full md:max-w-[90%] rounded-t-[20px] md:rounded-r-[200px] min-h-[680px]';
$block_image_class = 'max-md:max-w-full md:min-w-[570px]';
$block_text_class  = '';
$bg_url            = '';

// Ajustements selon le type
if ($type_block_presentation === 'default') {
  // rien de spécial (carousel)
} elseif ($type_block_presentation === 'image') {
  if ($inverse_presentation) {
    $inverse_container = 'bg-bgOrange max-md:max-w-full md:max-w-[90%] md:rounded-l-[200px] min-h-[680px] ml-auto px-[100px]';
    $section_class     = 'flex-col-reverse md:flex-row-reverse max-w-[1400px]';
    $block_image_class = '';
  } else {
    $section_class     = 'max-md:max-w-full max-md:flex-col md:flex-row max-w-[1400px]';
  }
} elseif ($type_block_presentation === 'image_out') {
  $section_class     = 'flex-col md:flex-row relative z-20 max-w-[1270px] md:max-[1270px]:mx-[30px]';
  $block_image_class = 'max-md:px-[15px]';
  $inverse_container = 'max-w-full md:rounded-l-[200px] mx-auto relative max-md:before:left-0 md:before:-right-[30%] md:before:rounded-l-[20px] before:top-0 before:min-h-[100%] before:absolute before:content-[""] before:w-full before:bg-bgOrange overflow-hidden max-md:!px-[0]';
} elseif ($type_block_presentation === 'image_background') {
  $section_class     = '';
  $block_image_class = 'max-md:hidden';
  $block_text_class  = 'md:mr-[40px] max-md:py-[30px] md:[padding:73px_39px_119px_39px] bg-white rounded-[10px]';
  $inverse_container = 'rounded-[10px] max-w-[1270px] mx-auto max-[1270px]:mx-[30px] bg-no-repeat bg-cover';
  $bg_url            = $image_presentation ?: '';
}

// Wrapper
$wrapper_classes = 'block-presentation '.$inverse_container.' py-[20px] max-md:py-[30px] md:py-[88px] max-md:px-[15px]';
$wrapper_style   = $bg_url ? "background-image:url('{$bg_url}')" : '';

?>
<section <?= get_block_wrapper_attributes([
  'class' => $wrapper_classes,
  'style' => $wrapper_style
]); ?>>
  <div class="flex <?= $section_class; ?> flex-wrap md:gap-[45px] lg:gap-[90px] mx-auto">
    <div class="flex-1 flex justify-center items-center max-md:mb-[0px] <?= $block_image_class; ?>">

      <?php if ($type_block_presentation === 'default'): ?>
        <?php if (is_array($carousel_images) && count($carousel_images) > 0): ?>
          <div class="carousel">
            <ul class="carousel__list m-0 p-0 list-none max-md:mb-[20px]">
              <?php
              // on rend simplement tout ce qui est fourni (plus simple que 5 fixes)
              $i = 0;
              foreach ($carousel_images as $url):
                $pos = $i - 2; // sert à tes styles/data-pos si besoin
              ?>
                <li class="carousel__item" data-pos="<?= $pos ?>" style="background-image:url('<?= $url ?>')"></li>
              <?php $i++; endforeach; ?>
            </ul>
            <?php if (count($carousel_images) > 1): ?>
              <div class="carousel__dots">
                <?php foreach (array_values($carousel_images) as $idx => $u): ?>
                  <button class="carousel__dot<?= $idx === 0 ? ' active' : '' ?>" data-index="<?= $idx ?>"></button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php elseif ($type_block_presentation === 'image_background'): ?>
        <!-- Rien ici, l’image est en background via $wrapper_style -->

      <?php else: ?>
        <?php if (!empty($image_presentation)): ?>
          <img src="<?= $image_presentation; ?>" alt="Image mise en avant"
               class="max-md:w-full w-full object-cover md:aspect-square max-w-full rounded-[10px] max-md:mb-[20px]" />
        <?php endif; ?>
      <?php endif; ?>

    </div>

    <div class="flex-1 <?= $block_text_class; ?> max-md:px-[15px]">
      <InnerBlocks
        class="animateFade fadeOutAnimation
          md:[&_h2]:leading-[36px] md:[&_h1]:leading-[36px]
          [&_h2]:mb-[20px] md:[&_h2]:mb-[50px] [&_h1]:mb-[50px]
          [&_h2]:text-black [&_h1]:text-black
          [&_li]:mt-0 max-md:[&_li]:text-[14px] md:[&_li]:text-[16px] [&_li]:text-[#333333] [&_li]:font-arial
          [&_p]:mt-0 text-[14px] md:[&_p]:text-[16px] [&_p]:text-[#333333] [&_p_a]:underline
          [&_h2]:text-[24px] md:[&_h2]:text-[36px] [&_h1]:text-[20px] md:[&_h1]:text-[36px]
          [&_h2]:font-[600] [&_h1]:font-[600] [&_h2]:font-ivymode [&_h1]:font-ivymode
          [&_h2_sub]:font-arial [&_h1_sub]:font-arial [&_h2_sub]:font-[400] [&_h1_sub]:font-[400]
          max-md:[&_h2]:text-center [&_h2_sub]:text-[20px] max-md:[&_h1]:text-center [&_h1_sub]:text-[20px]
          max-md:text-center md:[&_h2_sub]:text-[32px] md:[&_h1_sub]:text-[32px]
          <?= ($inverse_presentation) ? 'md:pl-[70px]' : '' ?>"
        template='<?= json_encode($template) ?>'
        allowedBlocks='<?= json_encode($allowedBlocks) ?>'
      />
    </div>
  </div>
</section>
