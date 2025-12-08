
<?php
/**
 * Variable Yoast: %%campings_count%%
 * 
 */
add_action('wpseo_register_extra_replacements', function () {

    $count_campings = function ($term_id, $taxonomy, $post_type = 'camping') {

        if (empty($term_id) || empty($taxonomy)) {
            return 0;
        }

        /**
         * SURSURFACE "liste"
         * ------------------
         * Si on n’est pas déjà sur la taxo "liste", on vérifie si le terme courant
         * a un champ ACF (par ex. "apidae_list_selection") qui pointe vers un terme
         * de la taxonomie "liste". Si oui, on compte les campings liés à cette "liste"
         * plutôt que ceux liés au terme d’origine.
         */
        if ('liste' !== $taxonomy && function_exists('get_field')) {

            // format attendu par ACF pour un term : "taxonomy_termId"
            $field_key   = $taxonomy . '_' . $term_id;
            $liste_value = get_field('apidae_list_selection', $field_key);

            if (!empty($liste_value)) {

                // Normaliser en ID de terme de la taxo "liste"
                $liste_term_id = null;

                if (is_array($liste_value)) {
                    // Si plusieurs valeurs, on prend la première
                    $first = reset($liste_value);
                    if (is_object($first) && isset($first->term_id)) {
                        $liste_term_id = (int) $first->term_id;
                    } else {
                        $liste_term_id = (int) $first;
                    }
                } elseif (is_object($liste_value) && isset($liste_value->term_id)) {
                    $liste_term_id = (int) $liste_value->term_id;
                } else {
                    $liste_term_id = (int) $liste_value;
                }

                if ($liste_term_id > 0) {
                    // On bascule le comptage sur la taxo "liste"
                    $term_id  = $liste_term_id;
                    $taxonomy = 'liste';
                }
            }
        }

        $q = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'tax_query'      => [[
                'taxonomy'        => $taxonomy,
                'terms'           => (int) $term_id,
                'field'           => 'term_id',
                'include_children'=> true,
            ]],
            'fields'         => 'ids',
            'posts_per_page' => 1,
            'no_found_rows'  => false,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'ignore_sticky_posts'    => true,
        ]);

        return (int) $q->found_posts;
    };

    $resolve_current_term = function () {
        
        if (function_exists('is_tax') && (is_tax() || is_category() || is_tag())) {
            $obj = get_queried_object();
 
            if ($obj && !empty($obj->term_id) && !empty($obj->taxonomy)) {
                return [$obj->term_id, $obj->taxonomy];
            }
        }

        if (is_admin()) {
            $term_id = isset($_GET['tag_ID']) ? (int) $_GET['tag_ID'] : 0;
            $tax     = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
            if ($term_id && $tax) {
                return [$term_id, $tax];
            }
        }

        return [0, ''];
    };

    wpseo_register_var_replacement(
        '%%campings_count%%',
        function () use ($resolve_current_term, $count_campings) {
            list($term_id, $taxonomy) = $resolve_current_term();

            $post_type = 'camping';

            $count = $count_campings($term_id, $taxonomy, $post_type);

            return (string) $count;
        },
        'advanced',
        'Nombre de contenus "camping" liés au terme de taxonomie courant (ou à sa liste associée).'
    );

    wpseo_register_var_replacement(
        '%%campings_count_label%%',
        function () use ($resolve_current_term, $count_campings) {
            list($term_id, $taxonomy) = $resolve_current_term();
            $post_type = 'camping';
            $count = $count_campings($term_id, $taxonomy, $post_type);

            if ($count === 1) {
                return '1 camping';
            }
            return $count . ' campings';
        },
        'advanced',
        'Nombre de "camping(s)" avec gestion singulier/pluriel.'
    );
});



add_filter( 'wpseo_breadcrumb_links', function( $links ) {
    // Si on est sur un single de "camping"
    if ( is_singular( 'camping' ) ) {
        // Supprime l’élément archive (toujours en 1 après l’accueil)
        unset( $links[1] );
        // Réindexe proprement le tableau
        $links = array_values( $links );
    }
    // Si on est sur un single de "ambassador"
        if ( is_singular( 'ambassador' ) ) {
        // Supprime l’élément archive (toujours en 1 après l’accueil)
        unset( $links[1] );
        $links = array_values( $links );
    }
    return $links;
});



/**
 * Remplace le texte de TOUTES les miettes par le champ "Titre du fil d’Ariane"
 * de Yoast (même vide), pour posts/pages et termes.
 */
/**
 * Yoast breadcrumbs (TERMS ONLY) — utilise le breadcrumb_title précompilé
 * depuis la table {prefix}_yoast_indexable, et écrase le texte même si vide.
 * - Ne touche PAS aux posts/pages.
 * - Fait une seule requête SQL pour tous les termes du breadcrumb.
 */
add_filter( 'wpseo_breadcrumb_links', function( $links ) {
    global $wpdb;

    // 1) Collecter tous les termes présents dans les liens
    $term_refs = []; // [ index_du_lien => [term_id, taxonomy] ]
    $term_ids  = [];

    foreach ( $links as $i => $link ) {
        $term = null;

        if ( isset( $link['term'] ) && $link['term'] instanceof WP_Term ) {
            $term = $link['term'];
        } elseif ( ! empty( $link['term_id'] ) ) {
            $term = get_term( (int) $link['term_id'] );
        } elseif ( ! empty( $link['id'] ) && ! get_post_status( (int) $link['id'] ) ) {
            $maybe = get_term( (int) $link['id'] );
            if ( $maybe && ! is_wp_error( $maybe ) ) {
                $term = $maybe;
            }
        }

        if ( $term && ! is_wp_error( $term ) ) {
            $term_refs[ $i ] = [ (int) $term->term_id, $term->taxonomy ];
            $term_ids[] = (int) $term->term_id;
        }
    }

    if ( empty( $term_ids ) ) {
        return $links; // aucun terme → rien à faire
    }

    // 2) Récupérer les breadcrumb_title depuis wp_yoast_indexable en un seul coup
    $in_ids = implode( ',', array_map( 'intval', array_unique( $term_ids ) ) );
    $table  = $wpdb->prefix . 'yoast_indexable';

    // Note: on filtre aussi par object_sub_type = taxonomy pour être 100% sûr
    // (même si term_id est global).
    $rows = $wpdb->get_results("
        SELECT object_id, object_sub_type, breadcrumb_title
        FROM {$table}
        WHERE object_type = 'term'
          AND object_id IN ( {$in_ids} )
    ");

    if ( empty( $rows ) ) {
        return $links; // pas d’index Yoast pour ces termes (réindexation nécessaire ?)
    }

    // 3) Indexer les résultats: clé "term_id|taxonomy" → breadcrumb_title (peut être '' ou NULL)
    $by_term = [];
    foreach ( $rows as $r ) {
        $key = (int) $r->object_id . '|' . (string) $r->object_sub_type;
        $by_term[ $key ] = $r->breadcrumb_title; // on garde tel quel (incl. '')
    }

    // 4) Appliquer aux liens (écraser même si vide dès qu’une ligne existe)
    foreach ( $term_refs as $i => list( $tid, $tax ) ) {
        $key = $tid . '|' . $tax;
        if ( array_key_exists( $key, $by_term ) ) {
            // Important : on écrase même si NULL/'' pour respecter "même vide"
            $links[ $i ]['text'] = (string) $by_term[ $key ];
        }
    }

    return $links;
}, 9999); 
