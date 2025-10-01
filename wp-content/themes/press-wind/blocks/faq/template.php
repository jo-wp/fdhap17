<?php
/**
 * Destinations template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS
$items_answer = get_field('items_answer');
if ($items_answer) {
  $count = count($items_answer);
  $half = ceil($count / 2);
  list($part1, $part2) = array_chunk($items_answer, $half);
} else {
  $part1 = [];
  $part2 = [];
}

$is_block = isset($block) && is_array($block);


$attributes = '';
$defaultClass = 'container-huge flex flex-col items-center justify-center block-faq  rounded-t-[20px] pt-[75px] pb-[54px] max-md:px-[15px] max-md:py-[50px] px-[30px]';
if (isset($block)) {
  $background_bg = ' bg-bgGreen';
  $attributes = get_block_wrapper_attributes(['class' => $defaultClass.$background_bg]);
} else {
  $background_bg = ' bg-bgOrange';
  $attributes = 'class="' . $defaultClass . ' '.$background_bg.'"';
}

// INNERBLOCKS
$allowedBlocks = ['core/heading', 'core/paragraph'];
$template = [
  [
    'core/heading',
    [
      "placeholder" => "Titre du bloc",
      "level" => 2,
      "color" => "orange"
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
<section <?= $attributes; ?>>
  <div class="">
    <?php if (isset($block)):
      get_template_part('blocks/faq/innerblocks/innerblocks', null, array('template' => $allowedBlocks, 'allowedBlocks' => $allowedBlocks));
    else:
      $term_name = '';
      $terms = get_the_terms(get_the_ID(), 'destination');

      if ($terms && !is_wp_error($terms)) {
        $first = array_shift($terms);
        $term_name = $first->name;
      }

      ?>
      <div
        class="animateFade fadeOutAnimation [&_h2]:text-black [&_h2]:mt-0 [&_h2]:mb-0 [&_h2]:text-center [&_p]:m-0 max-md:text-center [&_p]:text-[20px] md:[&_p]:text-[32px] [&_p]:font-[400] [&_p]:text-primary [&_p]:text-center [&_p]:font-arial max-md:[&_h2]:text-[24px] [&_h2]:text-[36px] [&_h2]:font-[700] [&_h2]:font-ivymode">
        <h2>Préparez votre séjour</h2>
        <p>en camping à <?= esc_html($term_name); ?></p>
      </div>
      <?php
    endif; ?>
  </div>
  <div class="max-w-[1100px] w-[100%] mx-[30px] flex items-start max-md:flex-col">
    <?php if ($part1): ?>
      <ul
        class="w-full md:w-1/2 md:mr-[30px] float-left m-0 p-0  list-none grid grid-cols-1 md:grid-cols-1 gap-x-[32px] gap-y-[20px] mt-[60px] mb-[20px] md:mb-[80px]">
        <?php foreach ($part1 as $item): ?>
          <li class="animateFade fadeOutAnimation py-[36px] px-[24px] bg-white rounded-[20px]">
            <h3 class="faq-toggle cursor-pointer max-md:text-center m-0 font-arial text-[16px] md:text-[20px] text-black mb-[0px] md:pr-[30px]
           relative after:content-[''] after:absolute after:right-0 max-md:after:left-0 max-md:after:mx-auto
           md:after:top-[25%] after:bg-more-icon after:w-[17px] after:h-[17px] max-md:after:-bottom-[45px]
           after:transition-transform after:duration-300" role="button" tabindex="0" aria-expanded="false">
              <?= esc_html($item['question']); ?>
            </h3>
            <div class="faq-answer transition-all duration-500 ease-in-out max-h-0 overflow-hidden opacity-0
            m-0 [&_p]:font-arial [&_p]:text-[12px]  md:[&_p]:text-[14px]  [&_p]:text-black max-md:text-center">
              <?= apply_filters('the_content',$item['reponse']) ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($part2): ?>
      <ul
        class="w-full md:w-1/2 float-left m-0 p-0 list-none grid grid-cols-1 md:grid-cols-1 gap-x-[32px] gap-y-[20px] md:mt-[60px] mb-[80px]">
        <?php foreach ($part2 as $item): ?>
          <li class="animateFade fadeOutAnimation py-[36px] px-[24px] bg-white rounded-[20px]">
            <h3 class="faq-toggle cursor-pointer max-md:text-center m-0 font-arial text-[16px] md:text-[20px] text-black mb-[0px] md:pr-[30px]
           relative after:content-[''] after:absolute after:right-0 max-md:after:left-0 max-md:after:mx-auto
           md:after:top-[25%] after:bg-more-icon after:w-[17px] after:h-[17px] max-md:after:-bottom-[45px]
           after:transition-transform after:duration-300" role="button" tabindex="0" aria-expanded="false">
              <?= esc_html($item['question']); ?>
            </h3>
  <div class="faq-answer transition-all duration-500 ease-in-out max-h-0 overflow-hidden opacity-0
            m-0 [&_p]:font-arial [&_p]:text-[12px] md:[&_p]:text-[14px]  [&_p]:text-black max-md:text-center">
              <?= apply_filters('the_content',$item['reponse']) ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const root = document.querySelector(".block-faq"); // limite l'accordéon à ta section
    if (!root) return;

    const toggles = root.querySelectorAll(".faq-toggle");
    const answers = root.querySelectorAll(".faq-answer");

    // ferme tout
    function closeAll() {
      answers.forEach(a => {
        a.classList.remove("max-h-[1000px]", "opacity-100", "mt-[30px]");
        a.classList.add("max-h-0", "opacity-0");
      });
      toggles.forEach(t => t.setAttribute("aria-expanded", "false"));
      toggles.forEach(t => t.classList.remove("after:rotate-45", "max-md:after:bottom-[-25px]"));
    }

    function openOne(toggle, answer) {
      closeAll();
      answer.classList.remove("max-h-0", "opacity-0");
      // valeur large pour permettre la transition de hauteur
      answer.classList.add("max-h-[1000px]", "opacity-100", "mt-[30px]");
      toggle.setAttribute("aria-expanded", "true");
      toggle.classList.add("after:rotate-45", "max-md:after:bottom-[-25px]");
    }

    toggles.forEach(toggle => {
      const answer = toggle.nextElementSibling;

      toggle.addEventListener("click", () => {
        const isOpen = answer.classList.contains("max-h-[1000px]");
        if (isOpen) {
          closeAll();
        } else {
          openOne(toggle, answer);
        }
      });

      // accessibilité clavier
      toggle.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          toggle.click();
        }
      });
    });
  });
</script>