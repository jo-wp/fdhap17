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
