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

        $q = new WP_Query([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'tax_query'      => [[
                'taxonomy' => $taxonomy,
                'terms'    => (int) $term_id,
                'field'    => 'term_id',
                'include_children' => true,
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
        'Nombre de contenus "camping" liés au terme de taxonomie courant.'
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
    return $links;
});



/**
 * Remplace le texte de TOUTES les miettes par le champ "Titre du fil d’Ariane"
 * de Yoast (même vide), pour posts/pages et termes.
 */
/**
 * Yoast breadcrumbs : remplacer le texte UNIQUEMENT pour les TERMES,
 * en utilisant le "Titre du fil d’Ariane" (même vide) depuis :
 *  1) term meta 'wpseo_bctitle'
 *  2) term meta legacy 'wpseo' => ['bctitle']
 *  3) option legacy 'wpseo_taxonomy_meta'[$tax][$term_id]['bctitle']
 */
/**
 * Yoast breadcrumbs (TERMS ONLY) + WPML fallback :
 * - Utilise 'wpseo_bctitle' du terme courant si la méta existe (même si '').
 * - Sinon, tente le terme source (langue par défaut) via WPML.
 * - Gère aussi l'ancien stockage : term meta 'wpseo' => ['bctitle'] et très legacy 'wpseo_taxonomy_meta'.
 */
add_filter( 'wpseo_breadcrumb_links', function( $links ) {

    foreach ( $links as &$link ) {
        // Résoudre le terme
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
        if ( ! $term || is_wp_error( $term ) ) {
            continue; // pas un terme → on ne touche pas
        }

        $term_id = (int) $term->term_id;

        // --- 1) TERME COURANT : nouvelles/anciennes metas ---
        $bctitle = null;
        $has_meta = false;

        if ( metadata_exists( 'term', $term_id, 'wpseo_bctitle' ) ) {
            $bctitle = get_term_meta( $term_id, 'wpseo_bctitle', true );
            $has_meta = true;
        } elseif ( metadata_exists( 'term', $term_id, 'wpseo' ) ) {
            $legacy = get_term_meta( $term_id, 'wpseo', true );
            if ( is_array( $legacy ) && array_key_exists( 'bctitle', $legacy ) ) {
                $bctitle = (string) $legacy['bctitle'];
                $has_meta = true;
            }
        } else {
            // Très legacy : option globale
            $opt = get_option( 'wpseo_taxonomy_meta' );
            if ( is_array( $opt )
                && isset( $opt[ $term->taxonomy ][ $term_id ] )
                && array_key_exists( 'bctitle', $opt[ $term->taxonomy ][ $term_id ] ) ) {
                $bctitle = (string) $opt[ $term->taxonomy ][ $term_id ]['bctitle'];
                $has_meta = true;
            }
        }

        if ( $has_meta ) {
            $link['text'] = (string) $bctitle; // écrase même si '' (vide)
            continue;
        }

        // --- 2) FALLBACK WPML : chercher le bctitle du terme source ---
        if ( function_exists( 'apply_filters' ) ) {
            // langue par défaut (source)
            $default_lang = apply_filters( 'wpml_default_language', null );
            if ( $default_lang ) {
                $orig_term_id = apply_filters( 'wpml_object_id', $term_id, $term->taxonomy, true, $default_lang );
                if ( $orig_term_id && $orig_term_id !== $term_id ) {
                    // même logique de lecture côté terme source
                    if ( metadata_exists( 'term', $orig_term_id, 'wpseo_bctitle' ) ) {
                        $link['text'] = (string) get_term_meta( $orig_term_id, 'wpseo_bctitle', true );
                        continue;
                    }
                    if ( metadata_exists( 'term', $orig_term_id, 'wpseo' ) ) {
                        $legacy = get_term_meta( $orig_term_id, 'wpseo', true );
                        if ( is_array( $legacy ) && array_key_exists( 'bctitle', $legacy ) ) {
                            $link['text'] = (string) $legacy['bctitle'];
                            continue;
                        }
                    }
                    $opt = get_option( 'wpseo_taxonomy_meta' );
                    if ( is_array( $opt )
                        && isset( $opt[ $term->taxonomy ][ $orig_term_id ] )
                        && array_key_exists( 'bctitle', $opt[ $term->taxonomy ][ $orig_term_id ] ) ) {
                        $link['text'] = (string) $opt[ $term->taxonomy ][ $orig_term_id ]['bctitle'];
                        continue;
                    }
                }
            }
        }
        // Sinon : pas de méta trouvée nulle part → on laisse le nom du terme.
    }

    return $links;
}, 9999 );

