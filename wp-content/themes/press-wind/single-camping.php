<?php
get_header();

$allowed_slugs = ['1-etoile', '2-etoiles', '3-etoiles', '4-etoiles'];

$terms = get_the_terms(get_the_ID(), 'etoile');
$star_term = null;


if ($terms && !is_wp_error($terms)) {
  foreach ($terms as $term) {
    if (in_array($term->slug, $allowed_slugs, true)) {
      $star_term = $term;
      break;
    }
  }
}
$stars = 0;
if ($star_term) {
  $stars = intval($star_term->name);
}

$commune = get_post_meta($post->ID, 'commune', true);

$image_featured_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
$image_featured_caption = get_the_post_thumbnail_caption(get_the_ID());
$galerie_photo_camping = get_field('galerie_photo_camping', get_the_ID());

$id_reservation_direct = get_post_meta($post->ID, 'id_reservation_direct', true);
$url_reservation_direct = get_post_meta($post->ID, 'url_reservation_direct', true);

$nb_real = get_post_meta($post->ID, 'nb_real', true);
$nb_mobilhomes = get_post_meta($post->ID, 'nb_mobilhomes', true);
$nb_bungalows = get_post_meta($post->ID, 'nb_bungalows', true);
$empl_campingcars = get_post_meta($post->ID, 'empl_campingcars', true);
$empl_caravanes = get_post_meta($post->ID, 'empl_caravanes', true);
$empl_tentes = get_post_meta($post->ID, 'empl_tentes', true);
$superficie = get_post_meta($post->ID, 'superficie', true);

$capacite_nombreLocationMobilhomes = get_post_meta($post->ID, 'capacite_nombreLocationMobilhomes', true);



$photos = [];
if (!empty($galerie_photo_camping)) {
  $i = 0;
  foreach ($galerie_photo_camping as $photo) {
    $photos[] = [
      'url_thumbnail' => $photo['sizes']['large'],
      'url' => $photo['url'],
      'caption' => $photo['title'],
      'type' => 'photo'
    ];
    $i++;
    // if ($i >= 5) {
    //   break;
    // }
  }
}

$texte_seo_camping = get_field('texte_seo_camping', get_the_ID());

$langues = get_post_meta($post->ID, 'langues', true);
$tabLangues = array_map(
  function ($item) {
    return sanitize_title(trim($item));
  },
  explode(",", $langues)
);

$periodes_dateDebut = get_post_meta($post->ID, 'periodes_dateDebut', true);
$periodes_dateFin = get_post_meta($post->ID, 'periodes_dateFin', true);
$periodes_type = get_post_meta($post->ID, 'periodes_type', true);

$price_mini_mobilhomes = get_post_meta($post->ID, 'price_mini_mobilhomes', true);

$post_id = get_the_ID();
$tax = 'destination';

$selected = get_the_terms($post_id, $tax);

$destination = '';
$destination_parent = '';
if ($selected) {
  foreach ($selected as $select):
    $destination = $select->name;
    if ($select->parent) {
      $parent = get_term($select->parent);
      $destination_parent = $parent->name;
    }
  endforeach;
}

//FAQ 
$items_answer = get_field('items_answer');

//Deals
$deals_camping = get_field('deals_camping');
?>
<div class="container-huge">
  <div class="title-stars flex max-md:flex-col md:flex-row items-center justify-start gap-[0px] md:gap-[30px]">
    <div class="title ">
      <h1 class="max-md:text-center text-[32px]  md:text-[50px] font-ivymode text-green "><?= get_the_title(); ?></h1>
    </div>
    <div class="stars">
      <?php for ($i = 0; $i < $stars; $i++): ?>
        <img src="<?= get_template_directory_uri() ?>/assets/media/star.svg"
          alt="Etoile du camping <?= get_the_title(); ?>" />
      <?php endfor; ?>
    </div>
  </div>
  <div class="commune flex flex-col md:flex-row items-center max-md:justify-center md:justify-between">
    <div class="flex flex-row items-center max-md:justify-center md:justify-start gap-[12px]">
      <img src="<?= get_template_directory_uri() ?>/assets/media/marker.svg"
        alt="Marker de la commune du camping <?= get_the_title(); ?>" />
      <p class="font-arial text-[20px] text-green"><?= $commune ?></p>
    </div>
    <div class="font-arial text-[14px] [&_a]:text-[14px] [&_span]:text-orange [&_span_span]:text-black">
      <?php
      if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<p id="breadcrumbs ">', '</p>');
      }
      ?>
    </div>
  </div>
  <div class="galerie-photo relative ">
    <a id="open-all"
      class=" hover:no-underline cursor-pointer rounded-[5px] font-arial px-[15px] py-[10px] flex flex-row items-center gap-2 justify-center bg-white/80 text-[16px] text-black absolute left-[15px] top-[30px]"><img
        src="<?= get_template_directory_uri() ?>/assets/media/icon-gallery.svg">Voir les photos
      (<?= count($photos) ?>)</a>

    <div data-featherlight-gallery class="grid md:grid-cols-[2fr_1fr_1fr] md:grid-rows-2 gap-[15px] mb-[50px]"
      id="gallery">

      <?php $i = 0;
      foreach ($photos as $photo): ?>
        <img href="<?= $photo['url']; ?>" src="<?= $photo['url_thumbnail']; ?>" alt="<?= $photo['caption']; ?>" class="fl-item w-full h-full cursor-pointer object-cover rounded-[20px]
      <?= ($i == 0) ? 'col-span-1 row-span-2 md:max-h-[500px]' : ''; ?>
      <?= ($i >= 1) ? 'max-md:hidden md:max-h-[240px]' : ''; ?>
      <?= ($i >= 5) ? 'hidden' : ''; ?>">
        <?php $i++;
      endforeach; ?>
    </div>

  </div>
  <div class="blocs flex max-md:flex-col md:flex-row items-start justify-between gap-[70px]">
    <div class="bloc-content-camping ">
      <div
        class="bloc-camping-navigation mb-[50px] inline-flex flex-row flex-wrap gap-[15px] md:gap-[50px] border-t-0 border-l-0 border-r-0 border-b border-[#DDD] border-solid pb-[15px]">
        <div>
          <a href=""
            class="text-green font-montserrat text-[16px] font-[500] pb-[19px] hover:no-underline active  [&.active]:border-t-0 [&.active]:border-l-0 [&.active]:border-r-0 [&.active]:border-b [&.active]:border-[#333] [&.active]:border-solid">Présentation</a>
        </div>
        <?php if ($id_reservation_direct): ?>
          <div>
            <a href=""
              class="text-green font-montserrat text-[16px] font-[500] pb-[19px] hover:no-underline"><?= __('Disponibilités', 'fdhpa17'); ?></a>
          </div>
        <?php endif; ?>
        <div>
          <a href="#informations"
            class="text-green font-montserrat text-[16px] font-[500] pb-[19px] hover:no-underline"><?= __('Informations', 'fdhpa17'); ?></a>
        </div>
        <?php if ($items_answer): ?>
          <div>
            <a href=""
              class="text-green font-montserrat text-[16px] font-[500] pb-[19px] hover:no-underline"><?= __('Foire aux questions', 'fdhpa17'); ?></a>
          </div>
        <?php endif; ?>
      </div>

      <div
        class="bloc-camping-description py-[40px] px-[60px] bg-bgOrange rounded-[20px] [&_p]:font-arial [&_p]:text-[15px] mb-[50px]">
        <?php if ($texte_seo_camping): ?>
          <?= $texte_seo_camping; ?>
        <?php else: ?>
          <?= apply_filters('the_content', get_the_content()); ?>
        <?php endif; ?>
      </div>
      <?php if ($id_reservation_direct): ?>
        <div>

          <script>
            setTimeout(function () {
              document.getElementById("ctv-gp1xa2z0ihv1rc5hog1yrs").innerHTML = "<ctv-availability></ctv-availability>"
            });
          </script>
          <div id="ctv-gp1xa2z0ihv1rc5hog1yrs"></div>

          <script>
            window.ctoutvert = {
              id: <?= $id_reservation_direct; ?>,
              lang: 'auto',
              url: 'https://bookingpremium.secureholiday.net/widgets/'
            };

            (function (w, d, s, ctv, r, js, fjs) {
              r = new XMLHttpRequest();
              r.open('GET', w[ctv].url + 'js/src.json');
              r.responseType = 'json';
              r.json = true;
              r.send();
              r.onload = function () {
                w[ctv].src = r.responseType == 'json' ? r.response : JSON.parse(r.response);
                js.src = w[ctv].src[0];
                fjs.parentNode.insertBefore(js, fjs);
              }
              js = d.createElement(s), fjs = d.getElementsByTagName(s)[0];
              js.id = 'ctvwidget';
              js.async = 1;
            }(window, document, 'script', 'ctoutvert'));
          </script>


        </div>
      <?php endif; ?>
      <div id="informations"
        class="bloc-camping-informations md:gap-[60px] flex flex-wrap flex-col md:flex-row py-[40px] max-md:px-[20px] md:px-[60px] bg-bgGreen rounded-[20px] [&_p]:font-body [&_p]:text-[15px]">
        <div class="flex-1 flex flex-wrap flex-col md:flex-row">
          <div class="bloc-camping-informations__item">
            <h3 class="font-arial text-[23px] text-black"><?= __('Disposition', 'fdhpa17'); ?></h3>
            <ul
              class="list-none [&_li]:font-body [&_li]:text-[16px] [&_li]:text-black [&_li]:font-[300] md:grid grid-cols-2 gap-x-[60px] ">
              <?php if ($capacite_nombreLocationMobilhomes): ?>
                <li
                  class="relative  before:content-[''] before:absolute before:-left-[30px] before:top-1 before:w-5 before:h-5 before:bg-check before:bg-contain before:bg-no-repeat">
                  <?= $capacite_nombreLocationMobilhomes ?> Mobil homes
                </li>
              <?php endif; ?>
            </ul>
          </div>
          <?php
          //get terms from taxonomy confort 
          $confort_terms = get_the_terms(get_the_ID(), 'confort');
          if ($confort_terms && !is_wp_error($confort_terms)):
            ?>
            <div class="bloc-camping-informations__item">
              <h3 class="font-arial text-[23px] text-black"><?= __('Confort', 'fdhpa17'); ?></h3>
              <ul
                class="list-none [&_li]:font-body [&_li]:text-[16px] [&_li]:text-black [&_li]:font-[300] md:grid grid-cols-2 gap-x-[60px] ">
                <?php foreach ($confort_terms as $confort_term): ?>
                  <li
                    class="relative  before:content-[''] before:absolute before:-left-[30px] before:top-1 before:w-5 before:h-5 before:bg-check before:bg-contain before:bg-no-repeat">
                    <?= $confort_term->name; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <?php
          //get terms from taxonomy confort 
          $confort_terms = get_the_terms(get_the_ID(), 'equipement');
          if ($confort_terms && !is_wp_error($confort_terms)):
            ?>
            <div class="bloc-camping-informations__item">
              <h3 class="font-arial text-[23px] text-black"><?= __('Equipements', 'fdhpa17'); ?></h3>
              <ul
                class="list-none [&_li]:font-body [&_li]:text-[16px] [&_li]:text-black [&_li]:font-[300] md:grid grid-cols-2 gap-x-[60px] ">
                <?php foreach ($confort_terms as $confort_term): ?>
                  <li
                    class="relative  before:content-[''] before:absolute before:-left-[30px] before:top-1 before:w-5 before:h-5 before:bg-check before:bg-contain before:bg-no-repeat">
                    <?= $confort_term->name; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php
          //get terms from taxonomy confort 
          $confort_terms = get_the_terms(get_the_ID(), 'service');
          if ($confort_terms && !is_wp_error($confort_terms)):
            ?>
            <div class="bloc-camping-informations__item">
              <h3 class="font-arial text-[23px] text-black"><?= __('Services', 'fdhpa17'); ?></h3>
              <ul
                class="list-none [&_li]:font-body [&_li]:text-[16px] [&_li]:text-black [&_li]:font-[300] md:grid grid-cols-2 gap-x-[60px] ">
                <?php foreach ($confort_terms as $confort_term): ?>
                  <li
                    class="relative  before:content-[''] before:absolute before:-left-[30px] before:top-1 before:w-5 before:h-5 before:bg-check before:bg-contain before:bg-no-repeat">
                    <?= $confort_term->name; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
        <div class="flex-1 flex flex-wrap flex-col">
          <div class="bloc-camping-informations__item">
            <h3 class="font-arial text-[23px] text-black"><?= __('Périodes d\'ouverture', 'fdhpa17'); ?></h3>
            <div class="bloc-camping-informations__item__content">
              <p>
                <?php if ($periodes_dateDebut && $periodes_dateFin): ?>
                  <?php
                  $dstart = new DateTime($periodes_dateDebut);
                  $dend = new DateTime($periodes_dateFin);

                  // Formatter en français
                  $formatter = new IntlDateFormatter(
                    'fr_FR',
                    IntlDateFormatter::LONG,
                    IntlDateFormatter::NONE
                  );
                  ?>
                  Du <?= $formatter->format($dstart); ?> au <?= $formatter->format($dend); ?>
                <?php endif; ?><br>
                <?php if ($periodes_type == 'OUVERTURE_TOUS_LES_JOURS'): ?>
                  Ouvert Tous les jours<br>
                <?php endif; ?>
              </p>
            </div>
          </div>
          <?php
          //get terms from taxonomy confort 
          $confort_terms = get_the_terms(get_the_ID(), 'atout');
          if ($confort_terms && !is_wp_error($confort_terms)):
            ?>
            <div class="bloc-camping-informations__item">
              <h3 class="font-arial text-[23px] text-black"><?= __('Environnement', 'fdhpa17'); ?></h3>
              <div class="bloc-camping-informations__item__content">
                <?php foreach ($confort_terms as $confort_term): ?>
                  <p><?= $confort_term->name; ?></p>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <div class="bloc-camping-informations__item">
            <h3 class="font-arial text-[23px] text-black"><?= __('Capacité', 'fdhpa17'); ?></h3>
            <div class="bloc-camping-informations__item__content">
              <p>
                <?php if ($superficie): ?> <strong><?= __('Superficie :') ?></strong><?php endif; ?>
                <?php if ($superficie):
                  echo $superficie; ?> m2 <br /><?php endif; ?>
                <?php if ($nb_mobilhomes):
                  echo __('Mobil-homes : ', 'fdhpa17') . $nb_mobilhomes; ?>
                  <?= __('emplacements', 'fdhpa17') ?><br /><?php endif; ?>
                <?php if ($nb_bungalows):
                  echo __('Bungalows : ', 'fdhpa17') . $nb_bungalows; ?>
                  <?= __('emplacements', 'fdhpa17') ?><br /><?php endif; ?>
                <?php if ($empl_campingcars):
                  echo __('Campingcars : ') . $empl_campingcars; ?>
                  <?= __('emplacements', 'fdhpa17') ?><br /><?php endif; ?>
                <?php if ($empl_caravanes):
                  echo __('Caravanes : ') . $empl_caravanes; ?>
                  <?= __('emplacements', 'fdhpa17') ?><br /><?php endif; ?>
                <?php if ($empl_tentes):
                  echo __('Tentes : ') . $empl_tentes; ?>
                  <?= __('emplacements', 'fdhpa17') ?><br /><?php endif; ?>
              </p>
            </div>
          </div>
          <div class="bloc-camping-informations__item">
            <h3 class="font-arial text-[23px] text-black"><?= __('Langues parlées', 'fdhpa17'); ?></h3>
            <div class="bloc-camping-informations__item__content">
              <ul class="list-none m-0 p-0 flex flex-row gap-[17px]">
                <?php foreach ($tabLangues as $item): ?>
                  <li><img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-<?= $item; ?>.svg"></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <div class="bloc-camping-informations__item">
            <h3 class="font-arial text-[23px] text-black"><?= __('Moyens de paiement', 'fdhpa17'); ?></h3>
            <div class="bloc-camping-informations__item__content">
              <?php
              $terms = get_the_terms(get_the_ID(), 'paiement');

              if ($terms && !is_wp_error($terms)):
                echo '<div class="paiements-wrap flex flex-row flex-wrap gap-[5px]">';

                foreach ($terms as $term) {
                  if (function_exists('apply_filters')) {
                    $fr_term_id = apply_filters('wpml_object_id', $term->term_id, 'paiement', true, 'fr');
                    var_dump($fr_term_id);
                    $fr_term = get_term($fr_term_id, 'paiement');
                    var_dump($fr_term);
                  } else {
                    $icon_slug = $term->slug;
                  }

                  $icon_url = get_stylesheet_directory_uri() . '/assets/media/icon_' . $icon_slug . '.png';

                  echo '<div class="paiement-item">';
                  echo '<img title="' . $term->name . '" src="' . esc_url($icon_url) . '" alt="' . esc_attr($term->name) . '" class="paiement-icon" />';
                  echo '</div>';
                }

                echo '</div>';
              endif;
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="bloc-sidebar-camping max-md:w-full md:min-w-[460px]">
      <div class="bloc-sidebar-open-close mb-[30px]">
        <p
          class="relative m-0 ml-[20px] md:ml-[40px] before:content-[''] before:absolute before:-left-[20px] before:top-[40%] max-md:text-center before:w-2 before:h-2 before:bg-green before:rounded-full font-body text-[16px] text-green uppercase font-[500]">
          <?= __('Ouvert Aujourd\'hui', 'fdhpa17'); ?>
        </p>
      </div>
      <?php if ($deals_camping): ?>
        <?php foreach ($deals_camping as $deal): ?>
          <?php
          // Dates lisibles (d/m/Y -> "31 août 2026")
          $date_start = DateTime::createFromFormat('d/m/Y', $deal['date_debut']);
          $date_end = DateTime::createFromFormat('d/m/Y', $deal['date_fin']);
          $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');

          // Expiration -> timestamp (fin de journée si heure absente)
          $exp = DateTime::createFromFormat('d/m/Y H:i:s', $deal['date_fin'])
            ?: DateTime::createFromFormat('d/m/Y', $deal['date_fin']);
          if ($exp && $exp->format('H:i:s') === '00:00:00') {
            $exp->setTime(23, 59, 59);
          }
          $expiration_ts = $exp ? $exp->getTimestamp() : 0;

          // Nom de fichier PDF
          $pdf_filename = 'bon-' . sanitize_title(($deal['code'] ?? '') . '-' . ($deal['titre'] ?? ''));
          ?>

          <!-- Carte orange (infos offre) -->
          <div class="bloc-sidebar-promo-price bg-orange p-[40px] text-white text-center rounded-[20px] mb-[15px] ">
            <p class="font-ivymode text-[36px] font-[700] m-0"><?= esc_html($deal['titre']); ?></p>
            <p class="font-arial text-[16px] font-[700]"><?= wp_kses_post($deal['description']); ?></p>
            <div class="border-2 border-solid border-white rounded-full">
              Bon plan valable du<br>
              <?= $date_start ? esc_html($formatter->format($date_start)) : ''; ?>
              au
              <?= $date_end ? esc_html($formatter->format($date_end)) : ''; ?>
            </div>
          </div>

          <!-- Bon vert exportable (tout ce bloc sera capturé en PDF) -->
          <div
            class="bloc-sidebar-promo-date bg-green p-[40px] text-white text-center rounded-[20px] mb-[15px] flex flex-row items-start justify-center gap-[25px]"
            data-filename="<?= esc_attr($pdf_filename); ?>">

            <div class="flex flex-col">
              <div class="border border-solid border-white rounded-full px-[25px] py-[5px] font-[700] max-md:text-[14px]">
                n°<?= esc_html($deal['code']); ?>
              </div>
              <div class="mt-[10px]">
                <img src="<?= esc_url(get_bloginfo('template_directory') . '/assets/media/icon-time.svg'); ?>"
                  alt="Icon Expiration offre du camping <?= esc_attr(get_the_title()); ?>" />
                <p class="m-0 max-md:text-[13px] text-[13px]"><?= __('Cette offre expire dans :', 'fdhpa17') ?></p>
              </div>
            </div>

            <?php
            // Prépares tes valeurs
            $camping_name = get_the_title();
            $titre = $deal['titre'] ?? '';
            $desc_plain = wp_strip_all_tags($deal['description'] ?? '');
            $code = $deal['code'] ?? '—';

            $date_start = DateTime::createFromFormat('d/m/Y', $deal['date_debut']);
            $date_end = DateTime::createFromFormat('d/m/Y', $deal['date_fin']);
            $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'd MMMM yyyy');
            $du = $date_start ? $fmt->format($date_start) : '';
            $au = $date_end ? $fmt->format($date_end) : '';
            $dates_str = trim("du $du au $au");

            // (optionnel) nom de fichier
            $pdf_filename = 'bon-' . sanitize_title(($deal['code'] ?? '') . '-' . ($deal['titre'] ?? ''));
            ?>

            <div class="flex flex-col bloc-sidebar-promo-date js-coupon "
              data-filename="<?php echo esc_attr($pdf_filename); ?>" data-camping="<?php echo esc_attr($camping_name); ?>"
              data-title="<?php echo esc_attr($titre); ?>" data-desc="<?php echo esc_attr($desc_plain); ?>"
              data-code="<?php echo esc_attr($code); ?>" data-dates="<?php echo esc_attr($dates_str); ?>">
              <button type="button" class="js-pdf-btn cursor-pointer border border-solid border-white rounded-full bg-white text-green text-center px-[25px] py-[5px] max-md:text-[14px]
          ">
                <?= __('Imprimer ce bon', 'fdhpa17') ?>
              </button>

              <div class="flex flex-row items-center justify-center mt-[20px] gap-[5px] js-countdown"
                data-end="<?= (int) $expiration_ts; ?>">
                <div
                  class="border border-solid border-white rounded-[10px] p-[12px] font-ivymode max-md:text-[20px] md:text-[24px] font-[700] js-countdown-days">
                  00</div>
                <div
                  class="border border-solid border-white rounded-[10px] p-[12px] font-ivymode max-md:text-[20px] md:text-[24px] font-[700] js-countdown-time">
                  00:00:00</div>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
      <?php $website = get_post_meta($post->ID, 'site_web', true); ?>
      <?php if ($price_mini_mobilhomes || $url_reservation_direct || $website): ?>
        <div class="bloc-sidebar-price p-[40px] border border-solid border-[#DDD] rounded-[20px] mb-[26px]">
          <div>
            <?php if ($price_mini_mobilhomes): ?>
              <p class="m-0 text-center font-arial text-[15px] font-[400] text-black leading-[30px] mb-[10px]"><?= __('À partir
              de : ', 'fdhpa17'); ?><span
                  class="font-arial text-[50px] font-[700] text-green"><?= $price_mini_mobilhomes; ?><sup
                    class="font-arial text-[37px] font-[700] text-green">€</span></p>
              <p class="m-0 text-center font-arial text-[20px] font-[400] mb-[10px]">
                <?= __('Location Mobil-Home / semaine', 'fdhpa17') ?>
              </p>
            <?php endif; ?>
            <div class="flex flex-row flex-wrap items-center justify-center gap-[20px]">
              <?php if ($website): ?>
                <a href="<?= $website; ?>" target="_blank"
                  class="button button--bg-green"><?= __('Voir tous les tarifs', 'fdhpa17'); ?></a>
              <?php endif; ?>
              <?php if ($url_reservation_direct): ?>
                <a href="<?= $url_reservation_direct; ?>" target="_blank"
                  class="button button--bg-orange"><?= __('Réserver', 'fdhpa17'); ?></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="bloc-sidebar-contact px-[40px] py-[12px] border border-solid border-[#DDD] rounded-[20px] mb-[26px]">
        <div>
          <p class="m-0 text-center font-arial text-[16px] font-[400] text-[#777777] leading-[24px] mb-[10px]">
            <?= __('Contactez', 'fdhpa17'); ?>
          </p>
          <p class="m-0 text-center font-arial text-[24px] font-[400] mb-[10px] text-green"><?= get_the_title(); ?></p>
          <div class="flex flex-row flex-wrap items-center justify-center gap-[20px]">
            <a href="#" class="button button--bg-orange max-md:px-[20px]"
              data-featherlight="#contactFeatherlight"><?= __('Envoyer un message', 'fdhpa17'); ?></a>
            <div class=" hidden ">
              <div id="contactFeatherlight">
                <?= do_shortcode('[ninja_form id=2]'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="bloc-sidebar-map rounded-[20px]">
        <div id="map" class="max-w-full min-h-[290px] rounded-[20px]"
          data-longitude="<?= get_post_meta($post->ID, 'longitude', true) ?>"
          data-latitude="<?= get_post_meta($post->ID, 'latitude', true) ?>"></div>
      </div>
      <div class="bloc-sidebar-informations">
        <div class="bloc-sidebar-informations__item">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/localisation.svg"
            alt="Icon localisation du camping <?= get_the_title(); ?>">
          <div class="bloc-sidebar-informations__item__content">
            <p><?= get_post_meta($post->ID, 'adresse', true); ?></p>
            <p><?= get_post_meta($post->ID, 'code_postal', true); ?> <?= get_post_meta($post->ID, 'commune', true); ?>
            </p>
          </div>
        </div>
        <div class="bloc-sidebar-informations__item">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/phone.svg"
            alt="Icon Téléphone du camping <?= get_the_title(); ?>">
          <div class="bloc-sidebar-informations__item__content">
            <p class="text-[16px] underline"><a
                href="tel:<?= get_post_meta($post->ID, 'telephone', true); ?>"><?= get_post_meta($post->ID, 'telephone', true); ?></a>
            </p>
          </div>
        </div>
        <div class="bloc-sidebar-informations__item">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/www.svg"
            alt="Icon www du camping <?= get_the_title(); ?>">
          <div class="bloc-sidebar-informations__item__content">
            <p><a href="<?= get_post_meta($post->ID, 'site_web', true); ?>"
                target="_blank"><?= get_post_meta($post->ID, 'site_web', true); ?></a></p>
          </div>
        </div>
        <div class="bloc-sidebar-informations__item">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/tripadvisor.svg"
            alt="Icon tripadvisor du camping <?= get_the_title(); ?>">
          <div class="bloc-sidebar-informations__item__content">
            <p><a href="https://www.tripadvisor.fr" target="_blank">www.tripadvisor.fr</a></p>
          </div>
        </div>
        <div class="bloc-sidebar-informations__item">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-facebook.svg"
            alt="Icon tripadvisor du camping <?= get_the_title(); ?>">
          <img src="<?= get_bloginfo('template_directory') ?>/assets/media/icon-instagram.svg"
            alt="Icon tripadvisor du camping <?= get_the_title(); ?>">
        </div>
        <div class="bloc-sidebar-informations__item !flex-col flex-wrap items-center justify-center !border-none">
          <div>
            <img src="<?= get_bloginfo('template_directory') ?>/assets/media/famille-plus.png" alt="Icon" />
            <img src="<?= get_bloginfo('template_directory') ?>/assets/media/qualite-tourisme.png" alt="Icon" />
          </div>
          <div>
            <img src="<?= get_bloginfo('template_directory') ?>/assets/media/apidae.svg" alt="Icon" />
          </div>
          <p class="text-center !font-body !text-[13px]"><?= __('Mis à jour le ', 'fdhpa17'); ?>
            <?= get_the_modified_date('r', $post->ID); ?><br />
            <?= __('par Fédération de l\'Hôtellerie de Plein Air de Charente', 'fdhpa17') ?><br />
            <?= __('Maritime', 'fdhpa17') ?><br />
            (<?= __('Identifiant de l\'offre', 'fdhpa17') ?>: <?= get_post_meta($post->ID, 'apidae_id', true); ?>)
          </p>
        </div>
      </div>
    </div>
  </div>
  <div class="bloc-camping-associated max-w-[1630px] mx-auto mt-[50px] md:mt-[100px]">
    <h2 class="text-center text-green text-[24px] md:text-[50px] font-[600] leading-[57px] mb-[80px]">
      <?= _e('Les campings à proximité', 'fdhpa17') ?>
    </h2>
    <div
      class="bloc-camping-associated__items grid grid-cols-4 max-md:flex justify-center flex-row flex-wrap gap-[40px] max-md:overflow-x-scroll">
      <?php
      $current_id = get_the_ID();

      $terms = wp_get_post_terms($current_id, 'destination', array('fields' => 'ids'));

      if (!empty($terms) && !is_wp_error($terms)) {
        $args = array(
          'post_type' => 'camping',
          'posts_per_page' => 4,
          'post__not_in' => array($current_id),
          'tax_query' => array(
            array(
              'taxonomy' => 'destination',
              'field' => 'term_id',
              'terms' => $terms,
            ),
          ),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
          while ($query->have_posts()) {
            $query->the_post();

            $lat = get_post_meta(get_the_ID(), 'latitude', true);   // ou get_field('latitude')
            $lng = get_post_meta(get_the_ID(), 'longitude', true);  // ou get_field('longitude')
            $url = get_permalink();
            $title = get_the_title();
            $prix_mini = get_post_meta(get_the_ID(), 'price_mini', true);

            $tax = 'destination';

            $selected = get_the_terms(get_the_ID(), $tax);

            $destination = '';
            $destination_parent = '';
            foreach ($selected as $select):
              $destination = $select->name;
              //parent : 
              if ($select->parent) {
                $parent = get_term($select->parent);
                $destination_parent = $parent->name;
              }
            endforeach;

            $allowed_slugs = ['1-etoile', '2-etoiles', '3-etoiles', '4-etoiles'];

            $terms = get_the_terms(get_the_ID(), 'etoile');
            $star_term = null;


            if ($terms && !is_wp_error($terms)) {
              foreach ($terms as $term) {
                if (in_array($term->slug, $allowed_slugs, true)) {
                  $star_term = $term;
                  break; // on prend le premier match
                }
              }
            }
            $stars = 0;
            if ($star_term) {
              // Affiche le nombre d’étoiles (par ex. "★★★")
              $stars = intval($star_term->name); // si "3 étoiles" → intval = 3
            }

            $image_featured_item = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
            ?>
            <div class="bloc-camping-associated__items__item ">
              <div class="image-featured min-h-[290px] min-w-[250px] bg-center bg-cover rounded-[10px]"
                style="background-image:url('<?= $image_featured_item; ?>');">
                <div class="flex flex-row justify-between items-center py-[12px] px-[14px]">
                  <?php if ($prix_mini): ?>
                    <span class="bg-green text-white font-arial text-[14px] px-[20px] py-[8px] rounded-full">
                      <?= __('À partir de', 'fdhpa17'); ?>         <?= $prix_mini ?>€/<?= __('nuits', 'fdhpa17'); ?>
                    </span>
                  <?php endif; ?>
                  <a href="#" class="camping-fav-btn" data-camping-id="<?php echo get_the_ID(); ?>"
                    data-label-add="Ajouter aux favoris" data-label-remove="Retirer des favoris" aria-pressed="false">
                    <img src="<?= esc_url(get_theme_file_uri('/assets/media/heart.png')) ?>" alt="icon ajouter aux favoris">
                    <span class="txt" style="display:none;">Ajouter aux favoris</span>
                  </a>
                </div>
              </div>
              <div class="informations mt-[20px]">
                <h3 class=" font-arial text-[22px] font-[700] text-black m-0 mb-[5px]"><?= get_the_title($post->ID) ?></h3>
                <div class="stars">
                  <?php for ($i = 0; $i < $stars; $i++): ?>
                    <img class="max-w-[13px]" src="<?= get_template_directory_uri() ?>/assets/media/star.svg"
                      alt="Etoile du camping <?= get_the_title(); ?>" />
                  <?php endfor; ?>
                </div>
                <div class="location flex flex-row justify-between mb-[30px] items-center">
                  <div class="flex flex-row gap-[8px]">
                    <img src="<?= esc_url(get_theme_file_uri('/assets/media/marker-v2.svg')) ?>" alt="icon localisation">
                    <p class="text-[#000] font-arial text-[14px]"><?= get_post_meta($post->ID, 'commune', true); ?> -
                      <?= get_post_meta($post->ID, 'code_postal', true); ?>
                    </p>
                  </div>
                  <div>
                    <p class="text-black font-arial text-[12px]"><?= $destination_parent; ?></p>
                  </div>
                </div>
                <div class="">
                  <a href="<?= get_permalink($post->ID); ?>"
                    class="button button--grey"><?= _e('Voir le camping', 'fdhpa17'); ?></a>
                </div>
              </div>
            </div>
            <?php
          }
        }

        wp_reset_postdata();
      }
      ?>
    </div>
  </div>
  <?php
  if ($items_answer):
    ?>
    <div class="mt-[50px] md:mt-[100px]">
      <?= get_template_part('blocks/faq/template'); ?>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>