<?php
/**
 * Plugin Name: Ctoutvert Deals CLI
 */

if ( ! defined('WP_CLI') || ! WP_CLI ) {
  return;
}

/**
 * Charge la classe Ctoutvert depuis le thème (WP-CLI ne charge pas forcément le thème).
 */
WP_CLI::add_hook( 'before_wp_load', function() {

  $file = WP_CONTENT_DIR . '/themes/press-wind/api/ctoutvert/ctoutvert.php';

  if ( file_exists( $file ) ) {
    require_once $file;
  } else {
    WP_CLI::warning("Fichier Ctoutvert introuvable : {$file}");
    return;
  }

  if ( class_exists('Ctoutvert') && method_exists('Ctoutvert', 'init') ) {
    // si ton init fait des hooks / setup
    Ctoutvert::init();
  }
});

/**
 * Commande CLI
 */
class Ctoutvert_Deals_CLI_Command {

  public function sync_deals( $args, $assoc_args ) {

    if ( ! class_exists('Ctoutvert') ) {
      WP_CLI::error("La classe Ctoutvert n'est pas chargée. Vérifie le require_once du fichier.");
      return;
    }

    if ( ! function_exists('update_field') ) {
      WP_CLI::error("ACF n'est pas chargé (update_field introuvable). Active ACF sur ce WP ou charge-le.");
      return;
    }

    $post_type = $assoc_args['post_type'] ?? 'camping';

    $query = new WP_Query([
      'post_type'      => $post_type,
      'posts_per_page' => -1,
      'post_status'    => 'any',
      'meta_query'     => [
        [
          'key'     => 'id_reservation_ctoutvert',
          'compare' => 'EXISTS',
        ],
      ],
    ]);

    if ( ! $query->have_posts() ) {
      WP_CLI::warning("Aucun post trouvé avec id_reservation_ctoutvert.");
      return;
    }

    $updated = 0;

    foreach ( $query->posts as $post ) {
      $post_id = $post->ID;

      $ctv_id = get_post_meta($post_id, 'id_reservation_ctoutvert', true);
      if ( empty($ctv_id) ) {
        continue;
      }

      WP_CLI::log("Camping #{$post_id} -> Ctoutvert {$ctv_id}");

      $data = Ctoutvert::ctoutvert_get_specialoffer( (int) $ctv_id );

      // Si pas d'offres : on vide deals_camping
      if (
        ! $data ||
        empty($data->establishment_returnSpecialOffersResult->establishmentsSpecialOfferList->establishmentSpecialOfferList)
      ) {
        update_field('deals_camping', [], $post_id);
        $updated++;
        WP_CLI::log(" - aucune offre -> deals_camping vidé");
        continue;
      }

      $establishments = $data->establishment_returnSpecialOffersResult->establishmentsSpecialOfferList->establishmentSpecialOfferList;
      $offers = $establishments[0]->SpecialOfferList->specialOffers->specialOffer ?? [];

      // Normalise array
      if ( is_object($offers) ) $offers = [ $offers ];
      if ( empty($offers) ) {
        update_field('deals_camping', [], $post_id);
        $updated++;
        WP_CLI::log(" - aucune offre -> deals_camping vidé");
        continue;
      }

      $rows = [];

      foreach ( $offers as $item ) {
        $titre = (string) ($item->shortName ?? '');
        if ( $titre === '' ) continue;

        // Description = dbName + conditions
        $desc = [];
        if ( ! empty($item->dbName) ) $desc[] = (string) $item->dbName;

        if ( ! empty($item->conditionsOS->conditionOS) ) {
          foreach ( (array) $item->conditionsOS->conditionOS as $c ) {
            $desc[] = (string) $c;
          }
        }
        $description = implode("\n", $desc);

        // Dates = min begin / max end
        $date_debut = '';
        $date_fin   = '';

        if ( ! empty($item->offerPeriods->offerPeriod) ) {
          $periods = $item->offerPeriods->offerPeriod;
          if ( is_object($periods) ) $periods = [ $periods ];

          $min_begin = null;
          $max_end   = null;

          foreach ( $periods as $p ) {
            if ( ! empty($p->dateBegin) ) {
              $d = substr((string)$p->dateBegin, 0, 10);
              if ( $min_begin === null || $d < $min_begin ) $min_begin = $d;
            }
            if ( ! empty($p->dateEnd) ) {
              $d = substr((string)$p->dateEnd, 0, 10);
              if ( $max_end === null || $d > $max_end ) $max_end = $d;
            }
          }

          $date_debut = $min_begin ?: '';
          $date_fin   = $max_end ?: '';
        }

        $rows[] = [
          'titre'       => $titre,
          'description' => $description,
          'code'        => '0',
          'date_debut'  => $date_debut,
          'date_fin'    => $date_fin,
        ];
      }

      // Supprime les anciens champs -> en pratique, update_field écrase le répéteur
      update_field('deals_camping', $rows, $post_id);

      WP_CLI::log(' - ' . count($rows) . ' deal(s) enregistré(s)');
      $updated++;
    }

    WP_CLI::success("Terminé. {$updated} camping(s) mis à jour.");
  }
}

WP_CLI::add_command('ctoutvert', 'Ctoutvert_Deals_CLI_Command');
