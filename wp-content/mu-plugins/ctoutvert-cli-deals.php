<?php

/**
 * Plugin Name: Ctoutvert Deals CLI
 */

if (! defined('WP_CLI') || ! WP_CLI) {
  return;
}

/**
 * Charge la classe Ctoutvert depuis le thème (WP-CLI ne charge pas forcément le thème).
 */
WP_CLI::add_hook('before_wp_load', function () {

  $file = WP_CONTENT_DIR . '/themes/press-wind/api/ctoutvert/ctoutvert.php';

  if (file_exists($file)) {
    require_once $file;
  } else {
    WP_CLI::warning("Fichier Ctoutvert introuvable : {$file}");
    return;
  }

  if (class_exists('Ctoutvert') && method_exists('Ctoutvert', 'init')) {
    // si ton init fait des hooks / setup
    Ctoutvert::init();
  }
});

/**
 * Commande CLI
 */
class Ctoutvert_Deals_CLI_Command
{

  public function sync_deals($args, $assoc_args)
  {

    // ---- Options mail (comme ton autre commande)
    $send_mail = isset($assoc_args['mail']);
    $mail_to   = $assoc_args['mail-to'] ?? '';
    $mail_to_list = [];

    if ($send_mail) {
      if (! empty($mail_to)) {
        $mail_to_list = array_filter(array_map('trim', explode(',', $mail_to)));
      }

      // fallback si --mail sans --mail-to
      if (empty($mail_to_list)) {
        $admin_email = get_option('admin_email');
        if (! empty($admin_email)) $mail_to_list = [$admin_email];
      }
    }

    $t0 = microtime(true);
    $errors = [];
    $total_deals = 0;

    if (! class_exists('Ctoutvert')) {
      $msg = "La classe Ctoutvert n'est pas chargée. Vérifie le require_once du fichier.";
      WP_CLI::error($msg);
      return;
    }

    if (! function_exists('update_field')) {
      $msg = "ACF n'est pas chargé (update_field introuvable). Active ACF sur ce WP ou charge-le.";
      WP_CLI::error($msg);
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

    if (! $query->have_posts()) {
      WP_CLI::warning("Aucun post trouvé avec id_reservation_ctoutvert.");

      // Mail "aucun post" (optionnel mais utile)
      if ($send_mail && ! empty($mail_to_list)) {
        wp_mail(
          $mail_to_list,
          "[SYNC DEALS] Aucun camping à traiter ({$post_type})",
          "Aucun post trouvé avec la meta id_reservation_ctoutvert.\n\nDate: " . date('Y-m-d H:i:s')
        );
      }
      return;
    }

    $updated = 0;

    foreach ($query->posts as $post) {
      $post_id = $post->ID;

      $ctv_id = get_post_meta($post_id, 'id_reservation_ctoutvert', true);
      if (empty($ctv_id)) {
        continue;
      }

      WP_CLI::log("Camping #{$post_id} -> Ctoutvert {$ctv_id}");

      // On capture les erreurs par camping sans casser tout le run
      try {
        $data = Ctoutvert::ctoutvert_get_specialoffer((int) $ctv_id);
      } catch (\Throwable $e) {
        $errors[] = "Camping #{$post_id} (Ctoutvert {$ctv_id}) : " . $e->getMessage();
        WP_CLI::warning(" - erreur Ctoutvert: " . $e->getMessage());
        continue;
      }

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
      if (is_object($offers)) $offers = [$offers];
      if (empty($offers)) {
        update_field('deals_camping', [], $post_id);
        $updated++;
        WP_CLI::log(" - aucune offre -> deals_camping vidé");
        continue;
      }

      $rows = [];

      foreach ($offers as $item) {
        $titre = (string) ($item->shortName ?? '');
        if ($titre === '') continue;

        // Description = dbName + conditions
        $desc = [];
        if (! empty($item->dbName)) $desc[] = (string) $item->dbName;

        if (! empty($item->conditionsOS->conditionOS)) {
          foreach ((array) $item->conditionsOS->conditionOS as $c) {
            $desc[] = (string) $c;
          }
        }
        $description = implode("\n", $desc);

        // Dates = min begin / max end
        $date_debut = '';
        $date_fin   = '';

        if (! empty($item->offerPeriods->offerPeriod)) {
          $periods = $item->offerPeriods->offerPeriod;
          if (is_object($periods)) $periods = [$periods];

          $min_begin = null;
          $max_end   = null;

          foreach ($periods as $p) {
            if (! empty($p->dateBegin)) {
              $d = substr((string)$p->dateBegin, 0, 10);
              if ($min_begin === null || $d < $min_begin) $min_begin = $d;
            }
            if (! empty($p->dateEnd)) {
              $d = substr((string)$p->dateEnd, 0, 10);
              if ($max_end === null || $d > $max_end) $max_end = $d;
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

      update_field('deals_camping', $rows, $post_id);

      $count_rows = count($rows);
      $total_deals += $count_rows;

      WP_CLI::log(' - ' . $count_rows . ' deal(s) enregistré(s)');
      $updated++;
    }

    $duration = round(microtime(true) - $t0, 2);

    WP_CLI::success("Terminé. {$updated} camping(s) mis à jour.");

    // ---- Mail récap
    if ($send_mail && ! empty($mail_to_list)) {
      $subject = "[SYNC DEALS] OK - {$updated} camping(s)";

      if (! empty($errors)) {
        $subject = "[SYNC DEALS] PARTIEL - {$updated} camping(s), " . count($errors) . " erreur(s)";
      }

      $body  = "Sync deals terminée.\n";
      $body .= "Post type: {$post_type}\n";
      $body .= "Campings traités: {$updated}\n";
      $body .= "Deals total enregistrés: {$total_deals}\n";
      $body .= "Durée: {$duration}s\n";
      $body .= "Date: " . date('Y-m-d H:i:s') . "\n";

      if (! empty($errors)) {
        $body .= "\nErreurs:\n- " . implode("\n- ", array_slice($errors, 0, 20));
        if (count($errors) > 20) $body .= "\n... (+" . (count($errors) - 20) . " autres)";
      }

      wp_mail($mail_to_list, $subject, $body);
    }
  }
}

WP_CLI::add_command('ctoutvert', 'Ctoutvert_Deals_CLI_Command');
