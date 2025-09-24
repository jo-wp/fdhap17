<?php
// wp-content/mu-plugins/campings-purge.php
if ( defined('WP_CLI') && WP_CLI ) {
    /**
     * Purge tous les contenus du CPT "camping".
     *
     * ## OPTIONS
     *
     * [--delete-attachments]
     * : Supprime aussi les médias *attachés* aux campings (par défaut: true).
     *
     * [--keep-attachments]
     * : Ne touche pas aux médias.
     *
     * [--dry-run]
     * : Affiche ce qui serait supprimé sans rien supprimer.
     *
     * ## EXAMPLES
     *     wp campings purge --dry-run
     *     wp campings purge --keep-attachments
     *     wp campings purge
     */
    class Campings_Purge_Command {
        public function purge( $args, $assoc_args ) {
            $post_type          = 'camping';
            $delete_attachments = ! isset( $assoc_args['keep-attachments'] );
            $dry_run            = isset( $assoc_args['dry-run'] );

            $taxonomies = get_object_taxonomies( $post_type, 'names' );

            // Récupère tous les posts (y compris brouillons / privés / etc.)
            $posts = get_posts([
                'post_type'      => $post_type,
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]);

            if ( empty( $posts ) ) {
                WP_CLI::success("Aucun post $post_type à supprimer.");
                return;
            }

            WP_CLI::log("Trouvé ".count($posts)." $post_type(s).");

            $candidate_term_ids = [];
            $candidate_terms_by_tax = [];

            // 1) Recenser termes + médias
            foreach ( $posts as $post_id ) {
                foreach ( $taxonomies as $tax ) {
                    $terms = wp_get_object_terms( $post_id, $tax, ['fields' => 'ids'] );
                    if ( ! is_wp_error($terms) && $terms ) {
                        foreach ( $terms as $tid ) {
                            $candidate_term_ids[$tid] = true;
                            $candidate_terms_by_tax[$tax][$tid] = true;
                        }
                    }
                }
            }

            // 2) Suppression des posts (et médias attachés si demandé)
            $deleted_posts = 0;
            $deleted_attachments = 0;

            foreach ( $posts as $post_id ) {
                if ( $delete_attachments ) {
                    // Image à la une
                    $thumb_id = get_post_thumbnail_id( $post_id );
                    if ( $thumb_id ) {
                        if ( $dry_run ) {
                            WP_CLI::log("DRY-RUN: wp_delete_attachment #$thumb_id (thumbnail du post #$post_id)");
                        } else {
                            if ( wp_delete_attachment( $thumb_id, true ) ) {
                                $deleted_attachments++;
                            }
                        }
                    }

                    // Médias "attachés" (post_parent == $post_id)
                    $media = get_children([
                        'post_parent' => $post_id,
                        'post_type'   => 'attachment',
                        'fields'      => 'ids',
                        'numberposts' => -1,
                        'post_status' => 'any',
                    ]);

                    if ( $media ) {
                        foreach ( $media as $att_id ) {
                            if ( $dry_run ) {
                                WP_CLI::log("DRY-RUN: wp_delete_attachment #$att_id (attaché à #$post_id)");
                            } else {
                                if ( wp_delete_attachment( $att_id, true ) ) {
                                    $deleted_attachments++;
                                }
                            }
                        }
                    }
                }

                if ( $dry_run ) {
                    WP_CLI::log("DRY-RUN: wp_delete_post #$post_id (force)");
                } else {
                    wp_delete_post( $post_id, true ); // force delete (bypass trash)
                    $deleted_posts++;
                }
            }

            // 3) Nettoyage des termes non utilisés (toutes taxos du CPT)
            $deleted_terms = 0;

            foreach ( $taxonomies as $tax ) {
                $term_ids = isset($candidate_terms_by_tax[$tax]) ? array_keys($candidate_terms_by_tax[$tax]) : [];
                foreach ( $term_ids as $term_id ) {
                    // Vérifie si le terme est encore utilisé par AU MOINS un autre objet
                    $has_posts = self::term_has_objects( $tax, $term_id );
                    if ( ! $has_posts ) {
                        if ( $dry_run ) {
                            WP_CLI::log("DRY-RUN: wp_delete_term #$term_id (tax: $tax)");
                        } else {
                            $res = wp_delete_term( $term_id, $tax );
                            if ( ! is_wp_error( $res ) ) {
                                $deleted_terms++;
                            }
                        }
                    }
                }
            }

            if ( $dry_run ) {
                WP_CLI::success("DRY-RUN terminé.");
            } else {
                WP_CLI::success("Suppression terminée: posts supprimés=$deleted_posts, médias supprimés=$deleted_attachments, termes supprimés=$deleted_terms.");
            }
        }

        private static function term_has_objects( $taxonomy, $term_id ) {
            // Sécurité: requête rapide pour voir s'il reste des contenus avec ce terme
            $q = new WP_Query([
                'post_type'      => 'any',
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'tax_query'      => [[
                    'taxonomy' => $taxonomy,
                    'terms'    => [$term_id],
                    'field'    => 'term_id',
                    'include_children' => false,
                    'operator' => 'IN',
                ]],
                'no_found_rows'  => true,
            ]);
            return $q->have_posts();
        }
    }

    WP_CLI::add_command( 'campings purge', [ new Campings_Purge_Command(), 'purge' ] );
}
