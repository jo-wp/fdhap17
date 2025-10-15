<?php
/**
 * WP-CLI: tp sync-links
 * - Parcourt toutes les taxonomies de TP_TAXONOMIES
 * - Pour chaque terme, propage le lien vers la bonne term_page traduite
 *   uniquement si le terme source a déjà une page liée.
 *
 * Usage:
 *   wp tp sync-links
 *   wp tp sync-links --dry-run
 *   wp tp sync-links --taxonomy=destination --taxonomy=service
 */

const TP_TAXONOMIES = [
  'category',
  'destination',
  'atout',
  'etoile',
  'aquatique',
  'service',
  'label',
  'hebergement',
  'cible',
  'groupe',
];

if ( defined('WP_CLI') && WP_CLI ) {

  // Sécurité : exigences minimales
  if ( ! function_exists('tp_get_linked_post_id') || ! function_exists('tp_set_linked_post_id') ) {
    WP_CLI::warning( 'Les helpers tp_get_linked_post_id/tp_set_linked_post_id sont introuvables. Charge term_to_page.php avant cette commande.' );
  }

  if ( ! defined('ICL_SITEPRESS_VERSION') ) {
    WP_CLI::error( 'WPML n’est pas actif (ICL_SITEPRESS_VERSION non défini). Abandon.' );
    return;
  }

  if ( ! defined('TP_TAXONOMIES') || ! is_array(TP_TAXONOMIES) ) {
    WP_CLI::error( 'TP_TAXONOMIES est introuvable ou invalide.' );
    return;
  }

  // Helpers WPML (définis si absents)
  if ( ! function_exists('tp_wpml_get_translations_map') ) {
    /**
     * Retourne les traductions WPML d’un élément (post ou term) indexées par code langue.
     * @param int    $element_id
     * @param string $element_type 'post_term_page' pour le CPT, 'tax_{taxonomy}' pour un terme
     * @return array<string, object>
     */
    function tp_wpml_get_translations_map( $element_id, $element_type ) {
      $trid = apply_filters( 'wpml_element_trid', null, $element_id, $element_type );
      if ( empty( $trid ) ) return [];
      $translations = apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );
      return is_array($translations) ? $translations : [];
    }
  }

  class TP_WPML_Sync_CLI_Command {

    /**
     * (Re)lie les traductions de termes à la bonne term_page traduite.
     *
     * ## OPTIONS
     *
     * [--taxonomy=<slug>]
     * : Limite à une ou plusieurs taxonomies (option répétable).
     *
     * [--dry-run]
     * : Affiche ce qui serait modifié sans rien écrire.
     *
     * ## EXAMPLES
     *
     *     wp tp sync-links
     *     wp tp sync-links --dry-run
     *     wp tp sync-links --taxonomy=destination --taxonomy=service
     */
    public function sync_links( $args, $assoc_args ) {

      $dry_run = \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );
      $only_tax = isset( $assoc_args['taxonomy'] )
        ? (array) $assoc_args['taxonomy']
        : TP_TAXONOMIES;

      // Stats globales
      $stats = [
        'taxonomies' => [],
        'terms_seen' => 0,
        'set_links'  => 0,
        'updated'    => 0,
        'unchanged'  => 0,
        'skipped_no_src_page' => 0,
        'skipped_no_tr_page'  => 0,
      ];

      foreach ( $only_tax as $tax ) {
        if ( ! taxonomy_exists( $tax ) ) {
          WP_CLI::warning( "Taxonomie inconnue: {$tax}" );
          continue;
        }

        WP_CLI::log( "→ Taxonomie: {$tax}" );

        $term_ids = get_terms( [
          'taxonomy'   => $tax,
          'hide_empty' => false,
          'fields'     => 'ids',
        ] );

        if ( is_wp_error( $term_ids ) ) {
          WP_CLI::warning( "  Impossible de lister les termes: " . $term_ids->get_error_message() );
          continue;
        }

        $progress = \WP_CLI\Utils\make_progress_bar( "  Traitement des termes ({$tax})", count( $term_ids ) );

        foreach ( $term_ids as $term_id ) {
          $progress->tick();
          $res = $this->sync_one_term( (int) $term_id, $tax, $dry_run );

          $stats['terms_seen']++;

          if ( $res === 'no_src' ) {
            $stats['skipped_no_src_page']++;
          } elseif ( $res === 'no_translation_page' ) {
            $stats['skipped_no_tr_page']++;
          } elseif ( $res === 'set' ) {
            $stats['set_links']++;
          } elseif ( $res === 'updated' ) {
            $stats['updated']++;
          } elseif ( $res === 'same' ) {
            $stats['unchanged']++;
          }
        }

        $progress->finish();
        $stats['taxonomies'][] = $tax;
      }

      // Récap
      WP_CLI::log('');
      WP_CLI::log('=== Résumé ===');
      WP_CLI::log('Taxonomies: ' . implode(', ', $stats['taxonomies']));
      WP_CLI::log('Termes parcourus: ' . $stats['terms_seen']);
      WP_CLI::log('Liens ajoutés:    ' . $stats['set_links']);
      WP_CLI::log('Liens mis à jour: ' . $stats['updated']);
      WP_CLI::log('Inchangés:        ' . $stats['unchanged']);
      WP_CLI::log('Ignorés (pas de page source liée): ' . $stats['skipped_no_src_page']);
      WP_CLI::log('Ignorés (pas de page traduite dispo): ' . $stats['skipped_no_tr_page']);

      if ( $dry_run ) {
        WP_CLI::success( 'Dry-run terminé. Aucune modification écrite.' );
      } else {
        WP_CLI::success( 'Synchronisation terminée.' );
      }
    }

    /**
     * Applique la logique sur un terme donné:
     * - Si le terme n’a PAS de page source liée → no-op ('no_src').
     * - Pour chaque langue:
     *    - si terme traduit n’a pas de lien → set ('set')
     *    - si lien != page traduite → update ('updated')
     *    - sinon → 'same'
     * - S’il n’existe pas de traduction de la page dans la langue → 'no_translation_page'
     *
     * @param int    $term_id
     * @param string $taxonomy
     * @param bool   $dry_run
     * @return string One of: set|updated|same|no_src|no_translation_page (dernière action rencontrée)
     */
    protected function sync_one_term( $term_id, $taxonomy, $dry_run ) {
      // 1) Page liée au terme courant (langue source du terme)
      $src_post_id = tp_get_linked_post_id( $term_id );
      if ( ! $src_post_id ) {
        return 'no_src';
      }

      // 2) Récupérer les maps de traductions
      $term_translations = tp_wpml_get_translations_map( $term_id, 'tax_' . $taxonomy );
      if ( empty( $term_translations ) ) return 'same';

      $page_translations = tp_wpml_get_translations_map( $src_post_id, 'post_term_page' );
      if ( empty( $page_translations ) ) return 'no_translation_page';

      $last_action = 'same';

      foreach ( $term_translations as $lang => $t_trans ) {
        if ( empty( $t_trans->element_id ) ) continue;

        // Pas de page traduite pour cette langue → on ignore
        if ( empty( $page_translations[ $lang ] ) || empty( $page_translations[ $lang ]->element_id ) ) {
          $last_action = 'no_translation_page';
          continue;
        }

        $term_id_tr = (int) $t_trans->element_id;
        $page_id_tr = (int) $page_translations[ $lang ]->element_id;
        $current    = (int) tp_get_linked_post_id( $term_id_tr );

        // 1) Pas de page liée → set
        if ( ! $current ) {
          $this->maybe_set_link( $term_id_tr, $page_id_tr, $dry_run, $taxonomy, $lang, 'SET' );
          $last_action = 'set';
          continue;
        }

        // 2) Lien différent → update
        if ( $current !== $page_id_tr ) {
          $this->maybe_set_link( $term_id_tr, $page_id_tr, $dry_run, $taxonomy, $lang, 'UPDATE' );
          $last_action = 'updated';
          continue;
        }

        // 3) Sinon identique → no-op
        $last_action = ( $last_action === 'set' || $last_action === 'updated' ) ? $last_action : 'same';
      }

      return $last_action;
    }

    protected function maybe_set_link( $term_id, $page_id, $dry_run, $taxonomy, $lang, $action ) {
      $term = get_term( $term_id, $taxonomy );
      $t_name = $term && ! is_wp_error($term) ? $term->name : "#{$term_id}";
      $p_title = get_the_title( $page_id ) ?: "#{$page_id}";
      WP_CLI::log( sprintf( '  [%s][%s] %s → %s', $taxonomy, $lang, $t_name, $p_title ) );

      if ( ! $dry_run ) {
        tp_set_linked_post_id( $term_id, $page_id );
      }
    }
  }

  WP_CLI::add_command( 'tp sync-links', [ new TP_WPML_Sync_CLI_Command(), 'sync_links' ] );
}
