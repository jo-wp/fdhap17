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
 * Force Yoast à utiliser le champ "Titre du fil d’Ariane" défini dans la metabox,
 * même s’il est vide (pour écraser le titre par défaut).
 */
add_filter( 'wpseo_breadcrumb_single_link_info', function( $link_info ) {

    // 1) Cas Post/Page (Yoast fournit un id de post valide)
    if ( ! empty( $link_info['id'] ) && get_post_status( (int) $link_info['id'] ) ) {
        $post_id     = (int) $link_info['id'];
        $meta_exists = metadata_exists( 'post', $post_id, '_yoast_wpseo_bctitle' );
        $bctitle     = get_post_meta( $post_id, '_yoast_wpseo_bctitle', true );

        if ( $meta_exists ) {               // on écrase même si la valeur est '' (vide)
            $link_info['text'] = $bctitle;
        }
        return $link_info;
    }

    // 2) Cas Taxonomie
    // Yoast peut passer l'objet terme dans $link_info['term'],
    // ou parfois un id qui n'est pas un post_id.
    $term = null;

    if ( isset( $link_info['term'] ) && $link_info['term'] instanceof WP_Term ) {
        $term = $link_info['term'];
    } elseif ( ! empty( $link_info['term_id'] ) ) {
        $term = get_term( (int) $link_info['term_id'] );
    } elseif ( ! empty( $link_info['id'] ) && ! get_post_status( (int) $link_info['id'] ) ) {
        // dernier recours : tenter de résoudre comme terme si 'id' n'est pas un post
        $maybe_term = get_term( (int) $link_info['id'] );
        if ( $maybe_term && ! is_wp_error( $maybe_term ) ) {
            $term = $maybe_term;
        }
    }

    if ( $term && ! is_wp_error( $term ) ) {
        // Meta récente
        $meta_exists_term = metadata_exists( 'term', $term->term_id, 'wpseo_bctitle' );
        $bctitle_term     = get_term_meta( $term->term_id, 'wpseo_bctitle', true );

        if ( $meta_exists_term ) {
            $link_info['text'] = $bctitle_term;   // écrase même si ''
            return $link_info;
        }

        // Fallback ancien Yoast : meta groupée 'wpseo' => ['bctitle' => ...]
        $legacy = get_term_meta( $term->term_id, 'wpseo', true );
        if ( is_array( $legacy ) && array_key_exists( 'bctitle', $legacy ) ) {
            $link_info['text'] = (string) $legacy['bctitle']; // peut être vide, on respecte
            return $link_info;
        }
    }

    return $link_info;
}, 100 ); // priorité haute pour passer après d'éventuels autres filtres



// function yoast_term_bctitle( $term_id ) {
//     // Cas récent (Yoast stocke directement la meta du terme)
//     $bctitle = get_term_meta( $term_id, 'wpseo_bctitle', true );


//     // Fallback anciens Yoast (regroupé dans 'wpseo')
//     if ( ! $bctitle ) {
//         $yoast = get_term_meta( $term_id, 'wpseo', true );
//               var_dump($bctitle);
//             die();
//         if ( is_array( $yoast ) && ! empty( $yoast['bctitle'] ) ) {
//             $bctitle = $yoast['bctitle'];
//         }
//     }
//     return $bctitle;
// }

// add_filter( 'wpseo_breadcrumb_links', function( $links ) {
//     $taxos = [ 'destination','equipement','atout','etoile','aquatique','service',
//                'label','hebergement','cible','groupe','confort','paiement' ];

//     if ( is_tax( $taxos ) ) {
//         $term = get_queried_object();
//         if ( $term && isset( $term->term_id ) ) {
//             $bctitle = yoast_term_bctitle( $term->term_id );
          
//             if ( $bctitle ) {
//                 $last = count( $links ) - 1;
//                 $links[ $last ]['text'] = $bctitle;
//             }
//         }
//     }
//     return $links;
// }, 10 );