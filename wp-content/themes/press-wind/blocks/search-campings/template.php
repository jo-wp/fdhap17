<?php


/**
 * Pannels template.
 *
 * @param array $block The block settings and attributes.
 */

//ACF FIELDS

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


<section <?= get_block_wrapper_attributes(["class" => 'block-search-campings container-huge bg-bgGreen rounded-t-[20px] md:rounded-t-[200px] mx-auto flex flex-col flex-wrap ']); ?>>
  <div
    class="max-w-[1066px] mx-auto flex flex-col flex-wrap max-md:pt-[60px] max-md:pb-[30px] md:py-[90px] px-[15px] md:px-[60px] ">
    <InnerBlocks class="animateFade fadeOutAnimation text-center [&_h2]:text-green [&_h2_sub]:text-black [&_p]:m-0 [&_p]:text-[14px] md:[&_p]:text-[16px] [&_p]:font-[400] [&_p]:text-black [&_p]:font-arial [&_h2]:text-[20px] md:[&_h2]:text-[32px] [&_h2_sub]:text-[20px]  md:[&_h2_sub]:text-[32px] [&_h2]:font-[600] [&_h2_sub]:font-[400] [&_h2]:font-ivymode [&_h2_sub]:font-arial
      [&_h2]:mb-[20px]" template="<?= htmlspecialchars(json_encode($template)); ?>"
      allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />
  </div>
  <div class="max-w-full">
    <div
      class="mx-[30px] md:mx-[130px] min-h-[230px] [&_select]:p-[20px_30px] [&_select]:border-0 [&_select]:rounded-full">
      <div
        class="filters flex flex-col-reverse md:flex-row flex-wrap [&_.facetwp-facet]:mb-0 items-center justify-between mb-[70px] gap-[15px]">
        <div
          class="[&_.facetwp-dropdown]:max-w-[160px]
          [&_.facetwp-search]:max-w-[150px] [&_.facetwp-search]:!min-w-[50px] [&_.facetwp-search]:p-[20px_30px] [&_.facetwp-search]:border-0 [&_.facetwp-search]:rounded-[20px]
          flex flex-col md:flex-row flex-wrap [&_.facetwp-facet]:mb-0 items-center justify-center gap-[15px] max-md:px-[20px] max-md:py-[10px]  rounded-[10px] max-md:border max-md:border-solid max-md:border-green">
          <div class="md:hidden cursor-pointer active-filters "><span
              class="text-orange text-[14px] font-arial"><?= __('Afficher / Masquer les filtres', 'fdhpa17'); ?></span><span
              class="bg-green rounded-full text-white text-[13px] w-[16px] h-[16px] inline-flex items-center justify-center ml-[10px]">+</span>
          </div>
          <?= do_shortcode('[facetwp facet="classement_block"]'); ?>
          <?= do_shortcode('[facetwp facet="services_block"]'); ?>
          <?= do_shortcode('[facetwp facet="hebergements_block"]'); ?>
          <?= do_shortcode('[facetwp facet="expriences_block"]'); ?>
          <?= do_shortcode('[facetwp facet="input_text_block"]'); ?>

        </div>
        <div id="openMapBlockSearchCampings" data-button="map"
          class="button-map cursor-pointer flex flex-row flex-wrap justify-center hover:bg-green transition-all items-center gap-2 bg-orange rounded-[50px] px-[27px] py-[15px]">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-map.svg" alt="Icon map">
          <span class="font-arial text-[14px] font-[700] text-white"><?= __('Voir sur la carte', 'fdhpa17'); ?></span>
        </div>
      </div>
      <?= do_shortcode('[facetwp template="block_search"]'); ?>
    </div>
  </div>
</section>

<div id="modal" class=" hidden fixed inset-0 bg-black/50  items-center justify-center z-50 ">
  <div id="mapBlockSearchCampings" class="relative max-md:w-[80%]">
    <button id="closeModal"
      class="px-4 py-2  text-white absolute  -right-[20px] cursor-pointer -top-[20px] bg-orange border-none text-[20px] rounded-[50%] font-arial">X</button>
    <span><?= __('La carte','fdhpa17'); ?></span>
    <div id="block-render-campings-map" class="map h-[70vh] w-[100%] md:h-[70vh] md:w-[60vh]"></div>
  </div>
</div>

<script>

  const modal = document.getElementById("modal");
  const openModal = document.getElementById("openMapBlockSearchCampings");
  const closeModal = document.getElementById("closeModal");

  openModal.addEventListener("click", () => {
    modal.classList.remove("hidden");
    modal.classList.add('flex');
    rebuildMarkers()
  });

  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.classList.remove('flex');
  });

  // modal.addEventListener("click", () => {
  //   modal.classList.add("hidden");
  // });

  let map, markersLayer

  function ensureMap() {
    if (!map) {
      const el = document.getElementById('block-render-campings-map')

      if (!el) return

      map = L.map(el, { scrollWheelZoom: false })
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 12,
        attribution: '&copy; OpenStreetMap',
      }).addTo(map)

      markersLayer = L.layerGroup().addTo(map)
      map.setView([46.1603, -1.1511], 9)
    }
  }

  function rebuildMarkers() {
    ensureMap()
    if (!map || !markersLayer) return

    markersLayer.clearLayers()

    const markerSvg = `
  <svg xmlns="http://www.w3.org/2000/svg" width="30" height="39" viewBox="0 0 30 39">
  <path d="M17.5074 37.8874C22.1164 31.5693 29.8464 19.9367 29.8464 13.0156C29.8464 5.83806 23.2253 0 15.089 0C6.95256 0 0.331543 5.83806 0.331543 13.0156C0.331543 19.9367 8.06145 31.5693 12.6705 37.8874C13.7522 39.3709 16.4257 39.3709 17.5074 37.8874ZM5.2144 13.0156C5.2144 8.21258 9.64315 4.30654 15.089 4.30654C20.5347 4.30654 24.9635 8.21258 24.9635 13.0156C24.9635 17.8172 20.5347 21.7232 15.089 21.7232C9.64315 21.7232 5.2144 17.8157 5.2144 13.0156Z" fill="#51AB7E"/>
  </svg>
  `.trim()

    const icon = L.icon({
      iconUrl: 'data:image/svg+xml;base64,' + btoa(markerSvg),
      iconSize: [30, 39],
      iconAnchor: [15, 39],
      popupAnchor: [0, -39],
    })

    const $items = jQuery('.js-camping-item[data-lat][data-lng]')
    console.log($items);

    const bounds = L.latLngBounds([])
    $items.each(function () {
      const $it = jQuery(this)
      const lat = parseFloat($it.attr('data-lat'))
      const lng = parseFloat($it.attr('data-lng'))
      if (isFinite(lat) && isFinite(lng)) {
        const title = $it.attr('data-title') || ''
        const url = $it.attr('data-url') || '#'
        const marker = L.marker([lat, lng], { icon }).bindPopup(
          `<strong>${title}</strong><br><a href="${url}">Voir la fiche</a>`,
        )
        markersLayer.addLayer(marker)
        bounds.extend([lat, lng])
      }
    })

    if (bounds.isValid()) {
      map.fitBounds(bounds, { padding: [20, 20] })
    }
  }

</script>