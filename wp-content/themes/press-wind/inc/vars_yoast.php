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

    foreach ( $links as &$link ) {

        // --- TERME DE TAXONOMIE ---
        $term_id = 0;
        if ( isset( $link['term'] ) && $link['term'] instanceof WP_Term ) {
            $term_id = (int) $link['term']->term_id;
        } elseif ( ! empty( $link['term_id'] ) ) {
            $term_id = (int) $link['term_id'];
        } elseif ( ! empty( $link['id'] ) && ! get_post_status( (int) $link['id'] ) ) {
            // Parfois Yoast met l'id du terme dans 'id' (qui n'est pas un post)
            $t = get_term( (int) $link['id'] );
            if ( $t && ! is_wp_error( $t ) ) {
                $term_id = (int) $t->term_id;
            }
        }

        if ( $term_id ) {
            // Meta récente
            if ( metadata_exists( 'term', $term_id, 'wpseo_bctitle' ) ) {
                $link['text'] = (string) get_term_meta( $term_id, 'wpseo_bctitle', true );
                continue;
            }
            // Fallback ancien stockage Yoast
            $legacy = get_term_meta( $term_id, 'wpseo', true );
            if ( is_array( $legacy ) && array_key_exists( 'bctitle', $legacy ) ) {
                $link['text'] = (string) $legacy['bctitle'];
                continue;
            }
        }

        // --- POST / PAGE ---
        if ( ! empty( $link['id'] ) && get_post_status( (int) $link['id'] ) ) {
            $pid = (int) $link['id'];
            if ( metadata_exists( 'post', $pid, '_yoast_wpseo_bctitle' ) ) {
                $link['text'] = (string) get_post_meta( $pid, '_yoast_wpseo_bctitle', true );
                continue;
            }
        }
    }

    return $links;
}, 9999 ); // très tard pour écraser les autres réécritures
