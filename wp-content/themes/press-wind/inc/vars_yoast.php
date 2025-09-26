<?php
/**
 * Variable Yoast: %%campings_count%%
 * -> Renvoie le nombre d'articles "camping" reliés au terme de taxonomie courant.
 */
add_action('wpseo_register_extra_replacements', function () {

    // Compte les campings pour un term + taxonomy donnés
    $count_campings = function ($term_id, $taxonomy, $post_type = 'camping') {


        if (empty($term_id) || empty($taxonomy)) {
            return 0;
        }


        // On fait une requête très légère uniquement pour obtenir le total
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
            'posts_per_page' => 1,      // minimal
            'no_found_rows'  => false,  // on veut $q->found_posts
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'ignore_sticky_posts'    => true,
        ]);


        return (int) $q->found_posts;
    };

    // Essaie de déterminer le term en contexte (front ou admin Yoast sur un term)
    $resolve_current_term = function () {
        // Front: page d’archive de taxonomie
        if (function_exists('is_tax') && (is_tax() || is_category() || is_tag())) {
            $obj = get_queried_object();
 
            if ($obj && !empty($obj->term_id) && !empty($obj->taxonomy)) {
       
                return [$obj->term_id, $obj->taxonomy];
            }
        }

        // Admin: écran d’édition d’un terme (utilisé par l’aperçu Yoast)
        if (is_admin()) {
            // Quand on édite un terme: wp-admin/term.php?taxonomy=xxx&tag_ID=123
            $term_id = isset($_GET['tag_ID']) ? (int) $_GET['tag_ID'] : 0;
            $tax     = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
            if ($term_id && $tax) {
                return [$term_id, $tax];
            }
        }

        return [0, ''];
    };

    // Enregistre la variable %%campings_count%%
    wpseo_register_var_replacement(
        '%%campings_count%%',
        function () use ($resolve_current_term, $count_campings) {
            list($term_id, $taxonomy) = $resolve_current_term();

      

            // ⚙️ Si ta taxonomie n’est pas attachée qu’au CPT "camping",
            // on force le post_type ici.
            $post_type = 'camping';

            $count = $count_campings($term_id, $taxonomy, $post_type);

            return (string) $count;
        },
        'advanced',
        'Nombre de contenus "camping" liés au terme de taxonomie courant.'
    );

    // (Optionnel) Une variante avec libellé singulier/pluriel: %%campings_count_label%%
    wpseo_register_var_replacement(
        '%%campings_count_label%%',
        function () use ($resolve_current_term, $count_campings) {
            list($term_id, $taxonomy) = $resolve_current_term();
            $post_type = 'camping';
            $count = $count_campings($term_id, $taxonomy, $post_type);

            // FR: gestion simple du singulier/pluriel
            if ($count === 1) {
                return '1 camping';
            }
            return $count . ' campings';
        },
        'advanced',
        'Nombre de "camping(s)" avec gestion singulier/pluriel.'
    );
});
