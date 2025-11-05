<?php
/**
 * Block: Presentation
 * Clean rewrite — robuste avec WPML/ACF, fallbacks, et gestion d’images flexible.
 *
 * Champs ACF attendus:
 * - type_block_presentation: 'default' | 'image' | 'image_out' | 'image_background'
 * - carousel_images: array d'URLs ou d'objets image ACF
 * - image_presentation: URL ou objet image ACF
 * - inverse_presentation: bool
 *
 * Note: on évite les fonctions d’escaping HTML non souhaitées par l’intégrateur;
 * on ne fait qu’un minimum (URLs) si nécessaire.
 */

// === Helpers =================================================================

/**
 * Retourne une URL d’image à partir d’un champ ACF image (string URL, array ACF, ou null).
 * @param mixed $img
 * @param string|null $size Clé de taille optionnelle si $img est un array ACF avec sizes[]
 * @return string
 */
function pres_img_url($img, $size = null) {
  if (!$img) return '';
  if (is_string($img)) return $img;
  if (is_array($img)) {
    if ($size && isset($img['sizes'][$size])) return $img['sizes'][$size];
    if (!empty($img['url'])) return $img['url'];
  }
  return '';
}

/**
 * Normalise la valeur du select ACF (WPML peut renvoyer le label traduit).
 * On ne garde que les valeurs prévues.
 * @param mixed $v
 * @return string
 */
function pres_normalize_type($v) {
  $allowed = ['default', 'image', 'image_out', 'image_background'];
  if (in_array($v, $allowed, true)) return $v;

  // Tentative de rattrapage si libellé traduit (on simplifie au mieux)
  $map_like = [
    'default'          => ['défaut', 'standard'],
    'image'            => ['image', 'photo'],
    'image_out'        => ['image out', 'image extérieure', 'image à l’extérieur'],
    'image_background' => ['image background', 'image de fond', 'arrière-plan'],
  ];
  $v_l = is_string($v) ? mb_strtolower(trim($v)) : '';

  foreach ($map_like as $key => $labels) {
    foreach ($labels as $lbl) {
      if ($v_l === mb_strtolower($lbl)) return $key;
    }
  }
  // Fallback sûr
  return 'image';
}

/**
 * Transforme une liste ACF d’images (mixte string/array) en URLs prêtes à l’emploi.
 * @param mixed $list
 * @return array<string>
 */
function pres_images_list($list) {
  if (!is_array($list)) return [];
  $out = [];
  foreach ($list as $item) {
    $u = pres_img_url($item);
    if ($u) $out[] = $u;
  }
  return $out;
}

// === Données ACF =============================================================

$type_block_presentation = pres_normalize_type(get_field('type_block_presentation'));
$carousel_images_raw     = get_field('carousel_images') ?: [];
$image_presentation_raw  = get_field('image_presentation');
$inverse_presentation    = (bool) get_field('inverse_presentation');

// Matérialisation des URLs
$carousel_images  = pres_images_list($carousel_images_raw);
$image_presentation = pres_img_url($image_presentation_raw);

// === InnerBlocks =============================================================

$allowedBlocks = ['core/heading', 'core/paragraph', 'core/list', 'core/list-item'];
$template = [
  [
    'core/heading',
    [
      'placeholder' => 'Titre du bloc',
      'level'       => 2,
      'color'       => 'orange',
    ]
  ],
  [
    'core/paragraph',
    [ 'placeholder' => 'Description ...' ]
  ],
  [
    'core/list',
    [ 'placeholder' => 'Liste ...' ]
  ]
];

// === Classes & layout ========================================================

$section_class     = 'max-md:flex-col-reverse md:flex-row max-md:max-w-full md:max-w-[1400px]';
$inverse_container = 'bg-bgOrange max-md:max-w-full md:max-w-[90%] rounded-t-[20px] md:rounded-r-[200px] min-h-[680px]';
$block_image_class = 'max-md:max-w-full md:min-w-[570px]';
$block_text_class  = '';
$bg_url            = '';

if ($type_block_presentation === 'image') {
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

// === Renderers ==============================================================

// Toujours définir une closure (même no-op) pour éviter "Value of type null is not callable"
$block_image = function ($data) { /* no-op by default */ };

if ($type_block_presentation === 'default') {
  // Carousel infini « center » basé sur data-pos ; fonctionne même si < 5 images (on rend ce qu'on a)
  $block_image = function ($urls) {
    if (!is_array($urls) || count($urls) === 0) return;
    // on centre sur l’élément 0 si pas de notion d’actif
    ?>
    <div class="carousel">
      <ul class="carousel__list m-0 p-0 list-none max-md:mb-[20px]">
        <?php
        $i = 0;
        foreach ($urls as $u) {
          $pos = $i - 2; // -2,-1,0,1,2 si on a 5; sinon valeurs relatives
          ?>
          <li class="carousel__item" data-pos="<?= $pos ?>" style="background-image:url('<?= $u ?>')"></li>
          <?php
          $i++;
        }
        ?>
      </ul>
      <?php if (count($urls) > 1): ?>
      <div class="carousel__dots">
        <?php foreach (array_values($urls) as $idx => $u): ?>
          <button class="carousel__dot<?= $idx === 0 ? ' active' : '' ?>" data-index="<?= $idx ?>"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php
  };
} elseif ($type_block_presentation === 'image' || $type_block_presentation === 'image_out' || $type_block_presentation === 'image_background') {
  // Pour image_background, on n’affichera rien ici (fond via style)
  $block_image = function ($url) use ($type_block_presentation) {
    if ($type_block_presentation === 'image_background') return;
    if (!$url) return; ?>
    <img src="<?= $url; ?>" alt="Image mise en avant" class="max-md:w-full w-full object-cover md:aspect-square max-w-full rounded-[10px] max-md:mb-[20px]" />
    <?php
  };
}

// === Wrapper attributes ======================================================

$wrapper_classes = 'block-presentation ' . $inverse_container . ' py-[20px] max-md:py-[30px] md:py-[88px] max-md:px-[15px]';
$wrapper_style   = $bg_url ? "background-image:url('{$bg_url}')" : '';

?>
<section <?= get_block_wrapper_attributes([
  'class' => $wrapper_classes,
  'style' => $wrapper_style
]); ?>>
  <div class="flex <?= $section_class; ?> flex-wrap md:gap-[45px] lg:gap-[90px] mx-auto">
    <div class="flex-1 flex justify-center items-center max-md:mb-[0px] <?= $block_image_class; ?>">
      <?php
      if ($type_block_presentation === 'default') {
        $block_image($carousel_images);
      } elseif ($type_block_presentation === 'image_background') {
        // image posée en background via $wrapper_style : rien à rendre ici
      } else {
        $block_image($image_presentation);
      }
      ?>
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
          <?= $inverse_presentation ? 'md:pl-[70px]' : '' ?>"
        template='<?= json_encode($template) ?>'
        allowedBlocks='<?= json_encode($allowedBlocks) ?>'
      />
    </div>
  </div>
</section>
