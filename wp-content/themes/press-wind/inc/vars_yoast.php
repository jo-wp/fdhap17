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
add_filter( 'wpseo_breadcrumb_links', function( $links ) {

    // Active le debug ciblé si tu ajoutes `define('WP_DEBUG_BCTITLE', true);` dans wp-config.php
    $do_debug = defined('WP_DEBUG_BCTITLE') && WP_DEBUG_BCTITLE === true;

    foreach ( $links as &$link ) {

        // --- Détecter le terme ---
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
        $used    = null;

        // 1) Nouveau stockage : meta directe 'wpseo_bctitle'
        if ( metadata_exists( 'term', $term_id, 'wpseo_bctitle' ) ) {
            $link['text'] = (string) get_term_meta( $term_id, 'wpseo_bctitle', true );
            $used = 'wpseo_bctitle';
        }
        // 2) Ancien stockage : meta groupée 'wpseo' => ['bctitle' => ...]
        elseif ( metadata_exists( 'term', $term_id, 'wpseo' ) ) {
            $legacy = get_term_meta( $term_id, 'wpseo', true );
            if ( is_array( $legacy ) && array_key_exists( 'bctitle', $legacy ) ) {
                // on écrase même si c'est ''
                $link['text'] = (string) $legacy['bctitle'];
                $used = "legacy_wpseo['bctitle']";
            }
        }
        // 3) API Yoast (au cas où) : WPSEO_Taxonomy_Meta
        elseif ( class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
            // Cette API retourne un tableau de metas normalisées (dont 'bctitle' si présent)
            $meta = WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy );
            if ( is_array( $meta ) && array_key_exists( 'bctitle', $meta ) ) {
                $link['text'] = (string) $meta['bctitle'];
                $used = 'WPSEO_Taxonomy_Meta::get_term_meta';
            }
        }

        // --- Debug optionnel ---
        if ( $do_debug ) {
            // On log une seule fois par page par terme pour éviter le bruit
            $marker = 'bctitle_debug_done_' . $term_id;
            if ( ! did_action( $marker ) ) {
                do_action( $marker );

                $keys = array_keys( (array) get_term_meta( $term_id ) );
                error_log( sprintf(
                    '[BCTITLE] term_id=%d tax=%s used=%s | meta_keys=%s | wpseo_bctitle=%s | legacy=%s',
                    $term_id,
                    $term->taxonomy,
                    $used ?: 'none',
                    implode(',', $keys),
                    var_export( get_term_meta( $term_id, 'wpseo_bctitle', true ), true ),
                    var_export( get_term_meta( $term_id, 'wpseo', true ), true )
                ) );
            }
        }
    }

    return $links;
}, 9999 ); // ultra tard pour gagner la dernière main