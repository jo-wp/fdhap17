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
    if ( empty( $link_info['id'] ) ) {
        return $link_info;
    }

    $id = (int) $link_info['id'];

    // Cas 1 : c’est un post ou une page
    if ( get_post_status( $id ) ) {
        // On récupère la méta _yoast_wpseo_bctitle (même si vide)
        $meta_exists = metadata_exists( 'post', $id, '_yoast_wpseo_bctitle' );
        $bctitle     = get_post_meta( $id, '_yoast_wpseo_bctitle', true );

        if ( $meta_exists ) {
            $link_info['text'] = $bctitle;
        }

        return $link_info;
    }

    // Cas 2 : c’est un terme de taxonomie
    $meta_exists_term = metadata_exists( 'term', $id, 'wpseo_bctitle' );
    $bctitle_term     = get_term_meta( $id, 'wpseo_bctitle', true );

    if ( $meta_exists_term ) {
        $link_info['text'] = $bctitle_term;
    }

    return $link_info;
}, 10 );


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