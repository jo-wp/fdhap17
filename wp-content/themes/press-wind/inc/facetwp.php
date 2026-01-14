<?php
add_filter('facetwp_query_args', function ($args, $class) {

    // S'assurer que tax_query est un tableau
    if (empty($args['tax_query']) || !is_array($args['tax_query'])) {
        $args['tax_query'] = [];
    }

    $args['posts_per_page'] = 12;

    // 1) Essayer depuis l'URI : ex. "destination/camping-la-rochelle"
    $uri = isset($class->ajax_params['http_params']['uri']) ? $class->ajax_params['http_params']['uri'] : '';
    if (!empty($uri)) {
        $path  = trim(wp_parse_url($uri, PHP_URL_PATH), '/');
        $parts = explode('/', $path);

        $idx = array_search('destination', $parts, true);

        if ($idx !== false && isset($parts[$idx + 1])) {
            $slug = sanitize_title(urldecode($parts[$idx + 1]));

            if (!empty($slug)) {
                $term = get_term_by('slug', $slug, 'destination');
                if ($term && !is_wp_error($term)) {
                    $args['tax_query'][] = [
                        'taxonomy'         => 'destination',
                        'field'            => 'term_id',
                        'terms'            => [(int) $term->term_id],
                        'include_children' => true,
                    ];
                }
            }
        }
    }

    // 2) Fallback : utiliser destination_term_id UNIQUEMENT s'il correspond VRAIMENT à un terme 'destination'
    if (
        isset($class->ajax_params['extras']['destination_term_id']) &&
        (int) $class->ajax_params['extras']['destination_term_id'] > 0
    ) {
        $term_id = (int) $class->ajax_params['extras']['destination_term_id'];

        // vérifier que c'est bien un terme de la taxo 'destination'
        $dest_term = get_term($term_id, 'destination');

        if ($dest_term && !is_wp_error($dest_term)) {

            // N'ajoute le fallback que si aucun filtre 'destination' n'a déjà été ajouté
            $has_destination = false;
            foreach ($args['tax_query'] as $q) {
                if (is_array($q) && isset($q['taxonomy']) && $q['taxonomy'] === 'destination') {
                    $has_destination = true;
                    break;
                }
            }

            if (!$has_destination) {
                $args['tax_query'][] = [
                    'taxonomy'         => 'destination',
                    'field'            => 'term_id',
                    'terms'            => [$term_id],
                    'include_children' => true,
                ];
            }
        }
    }


    /*
     * 3) NOUVELLE VERSION : on se base UNIQUEMENT sur l’URI
     *    /aquatique/piscine/ → taxo = "aquatique", slug = "piscine"
     *    si le term a un ACF "apidae_list_selection" NON VIDE,
     *    on supprime le filtre sur cette taxo et on ajoute un filtre sur "liste".
     */

    if (!empty($uri) && function_exists('get_field')) {

        $path  = trim(wp_parse_url($uri, PHP_URL_PATH), '/');
        $parts = explode('/', $path);

        if (count($parts) >= 2) {
            $context_tax  = $parts[count($parts) - 2]; // ex: "aquatique" ou "atout"
            $context_slug = $parts[count($parts) - 1]; // ex: "piscine" ou "ville"

            // On évite destination / liste
            if (taxonomy_exists($context_tax) && !in_array($context_tax, ['liste'], true)) {

                $context_term = get_term_by('slug', $context_slug, $context_tax);

                if ($context_term && !is_wp_error($context_term)) {

                    $field_key   = $context_term->taxonomy . '_' . $context_term->term_id;
                    $liste_value = get_field('apidae_list_selection', $field_key);

                    if (!empty($liste_value)) {

                        $apidae_terms = [];

                        if (is_array($liste_value)) {
                            foreach ($liste_value as $val) {
                                if (is_object($val) && isset($val->term_id)) {
                                    $apidae_terms[] = (int) $val->term_id;
                                } else {
                                    $apidae_terms[] = (int) $val;
                                }
                            }
                        } elseif (is_object($liste_value) && isset($liste_value->term_id)) {
                            $apidae_terms[] = (int) $liste_value->term_id;
                        } else {
                            $apidae_terms[] = (int) $liste_value;
                        }

                        $apidae_terms = array_values(array_unique(array_filter(array_map('intval', $apidae_terms))));

                        if (!empty($apidae_terms)) {

                            // 1) On supprime les tax_query sur la taxo de contexte (aquatique / atout, etc.)
                            if (!empty($args['tax_query']) && is_array($args['tax_query'])) {
                                foreach ($args['tax_query'] as $k => $tax_query_item) {
                                    if (!is_array($tax_query_item)) {
                                        continue;
                                    }
                                    if (isset($tax_query_item['taxonomy']) && $tax_query_item['taxonomy'] === $context_tax) {
                                        unset($args['tax_query'][$k]);
                                    }
                                }
                            }

                            // 2) On supprime la query_var brute éventuelle (ex: $args['aquatique'] = 'piscine')
                            if (isset($args[$context_tax])) {
                                unset($args[$context_tax]);
                            }

                            // 3) On AJOUTE un filtre sur la taxo "liste" (sans toucher aux facets / destination)
                            $args['tax_query'][] = [
                                'taxonomy'         => 'liste',
                                'field'            => 'term_id',
                                'terms'            => $apidae_terms,
                                'include_children' => true,
                            ];
                        }
                    }
                }
            }
        }
    }


    // Si plusieurs conditions taxo existent, définir une relation par défaut
    if (!empty($args['tax_query']) && is_array($args['tax_query']) && !isset($args['tax_query']['relation'])) {
        $args['tax_query']['relation'] = 'AND';
    }

    // Tri par fraîcheur pour le CPT "camping"
    $post_types = (array) ($args['post_type'] ?? []);
    if (in_array('camping', $post_types, true)) {

        if (empty($args['meta_query']) || !is_array($args['meta_query'])) {
            $args['meta_query'] = [];
        }

        $args['meta_query'][] = [
            'key'     => 'apidae_update_date_modification',
            'compare' => 'EXISTS',
        ];

        $args['meta_key']  = 'apidae_update_date_modification';
        $args['orderby']   = 'meta_value';
        $args['order']     = 'DESC';
        $args['meta_type'] = 'CHAR';
    }

    return $args;
}, 999, 2);



/**
 * FacetWP : filtrer les CPT "camping"
 * - Afficher TOUJOURS les campings SANS meta 'id_reservation_direct'
 * - Pour ceux QUI ONT la meta, n'afficher que si la valeur est dans la liste renvoyée par Ctoutvert::ctoutvert_search_holidays()
 */
add_filter('facetwp_query_args', function ($args, $class) {

    // Ne cible que le CPT "camping"
    $post_types = (array) ($args['post_type'] ?? []);
    if (empty($post_types) || !in_array('camping', $post_types, true)) {
        return $args;
    }

    // --- 1) Appel API + cache (avec dates) ---
    $start_raw = isset($_GET['_date_arrive']) ? trim($_GET['_date_arrive']) : null;
    $end_raw = isset($_GET['_date_depart']) ? trim($_GET['_date_depart']) : null;


    $start = $start_raw ? DateTime::createFromFormat('Y-m-d', $start_raw) : null;
    $end = $end_raw ? DateTime::createFromFormat('Y-m-d', $end_raw) : null;
    $start_valid = $start && $start->format('Y-m-d') === $start_raw;
    $end_valid = $end && $end->format('Y-m-d') === $end_raw;

    if (!$start_valid || !$end_valid) {
        $dateFilters = [
            'startDate' => date('Y-m-d', strtotime('+1 day')),
            'endDate' => date('Y-m-d', strtotime('+10 days')),
        ];
    } else {
        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }
        $dateFilters = [
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
        ];
    }

    $cache_key = 'ctoutvert_available_establishment_ids_' . md5(wp_json_encode($dateFilters));
    $available_ids = get_transient($cache_key);

    if (false === $available_ids) {
        $available_ids = [];
        $res = Ctoutvert::ctoutvert_search_holidays($dateFilters);

        if (
            is_object($res)
            && isset($res->engine_returnAvailabilityAdvancedResult)
            && is_object($res->engine_returnAvailabilityAdvancedResult)
            && isset($res->engine_returnAvailabilityAdvancedResult->availabilityInformationList)
            && is_object($res->engine_returnAvailabilityAdvancedResult->availabilityInformationList)
            && isset($res->engine_returnAvailabilityAdvancedResult->availabilityInformationList->availabilityInformations)
            && is_array($res->engine_returnAvailabilityAdvancedResult->availabilityInformationList->availabilityInformations)
        ) {
            foreach ($res->engine_returnAvailabilityAdvancedResult->availabilityInformationList->availabilityInformations as $info) {
                if (
                    is_object($info)
                    && isset($info->establishmentInformation)
                    && is_object($info->establishmentInformation)
                    && isset($info->establishmentInformation->establishmentId)
                    && is_numeric($info->establishmentInformation->establishmentId)
                ) {
                    $available_ids[] = (int) $info->establishmentInformation->establishmentId;
                }
            }
        }

        $available_ids = array_values(array_unique($available_ids));
        $ttl = !empty($available_ids) ? 5 * MINUTE_IN_SECONDS : 60;
        set_transient($cache_key, $available_ids, $ttl);
    }

    // --- 2) Construit la meta_query : (NOT EXISTS) OR ('' vide) OR (IN API) ---
    $meta_query = isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : [];
    $or_group = ['relation' => 'OR'];

    $or_group[] = [
        'key' => 'id_reservation_direct',
        'compare' => 'NOT EXISTS',
    ];

    $or_group[] = [
        'key' => 'id_reservation_direct',
        'value' => '',
        'compare' => '=',
    ];

    if (!empty($available_ids)) {
        $or_group[] = [
            'key' => 'id_reservation_direct',
            'value' => $available_ids,
            'compare' => 'IN',
            'type' => 'NUMERIC',
        ];
    }

    $meta_query[] = $or_group;
    $args['meta_query'] = $meta_query;

    // -- Nettoyage : retirer post__in s'il est vide ou ne contient que des valeurs "falsy"
    if (isset($args['post__in'])) {
        $post__in = (array) $args['post__in'];
        $post__in = array_filter($post__in, static function ($v) {
            return !empty($v);
        });
        if (empty($post__in)) {
            unset($args['post__in']);
        } else {
            $args['post__in'] = array_values($post__in);
        }
    }

    return $args;
}, 100, 2);


add_filter('facetwp_render_output', function ($output, $params) {


    if (isset($output['settings']['date_start'])) {
        if (0 == $output['settings']['date_start']['range']['min']['minDate']) {
            $output['settings']['date_start']['range']['min']['minDate'] = '2023-01-01'; // start date min
        }
        if (0 == $output['settings']['date_start']['range']['min']['maxDate']) {
            $output['settings']['date_start']['range']['min']['maxDate'] = '2100-12-30'; // start date max
        }
        if (0 == $output['settings']['date_start']['range']['max']['minDate']) {
            $output['settings']['date_start']['range']['max']['minDate'] = '2023-01-02'; // End date min
        }
        if (0 == $output['settings']['date_start']['range']['max']['maxDate']) {
            $output['settings']['date_start']['range']['max']['maxDate'] = '2100-12-31'; // End date max
        }
    }

    return $output;
}, 10, 2);


add_filter('facetwp_is_enabled', function ($enabled) {
    if (is_search()) {
        foreach ($_GET as $key => $val) {
            if (0 === strpos($key, 'fwp_')) {
                return true; // l'utilisateur utilise une facet -> ON
            }
        }
        return false; // aucune facet -> OFF
    }
    return $enabled;
}, 10, 1);

// Ceinture et bretelles : FacetWP n’interfère pas avec la WP_Query principale des recherches
add_filter('facetwp_is_main_query', function ($is_main, $query) {
    if ($query->is_search()) {
        // si aucune facet n’est active, ne pas traiter la requête principale
        foreach ($_GET as $key => $val) {
            if (0 === strpos($key, 'fwp_')) {
                return $is_main;
            }
        }
        return false;
    }
    return $is_main;
}, 10, 2);

add_filter('facetwp_index_row', function ($params, $class) {
    if ('ctoutvert_checkbox' !== $params['facet_name']) {
        return $params;
    }

    $post_id = (int) $params['post_id'];
    $raw     = get_post_meta($post_id, 'id_reservation_ctoutvert', true);

    // Normalisation (au cas où ce soit un array)
    if (is_array($raw)) {
        $raw = implode(',', array_filter($raw));
    }
    $value = trim((string) $raw);

    if ($value !== '') {
        // On indexe une valeur constante "has" si la méta est renseignée
        $params['facet_value']         = 'has';
        $params['facet_display_value'] = __('Réservable sur Campings.online','fdhpa17');
        return $params;
    }

    // IMPORTANT : ne rien indexer pour ce post
    return false;
}, 10, 2);
