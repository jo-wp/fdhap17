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
        // Normaliser puis chercher la partie après "destination/"
        $path = trim(wp_parse_url($uri, PHP_URL_PATH), '/'); // enlève / début/fin si présents
        $parts = explode('/', $path);

    

        $idx = array_search('destination', $parts, true);

        if ($idx !== false && isset($parts[$idx + 1])) {
            // slug attendu : "camping-la-rochelle"
            $slug = sanitize_title(urldecode($parts[$idx + 1]));

            if (!empty($slug)) {
                $term = get_term_by('slug', $slug, 'destination');
                if ($term && !is_wp_error($term)) {
                    $args['tax_query'][] = [
                        'taxonomy' => 'destination',
                        'field' => 'term_id',
                        'terms' => [(int) $term->term_id],
                        'include_children' => true,
                    ];
                }
            }
        }
    }

    // 2) Fallback : si un term_id est passé via extras et qu'on n'a rien ajouté
    if (
        isset($class->ajax_params['extras']['destination_term_id']) &&
        (int) $class->ajax_params['extras']['destination_term_id'] > 0
    ) {
        $term_id = (int) $class->ajax_params['extras']['destination_term_id'];

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
                'taxonomy' => 'destination',
                'field' => 'term_id',
                'terms' => [$term_id],
                'include_children' => true,
            ];
        }
    }

    // Si plusieurs conditions taxo existent, définir une relation par défaut
    if (!empty($args['tax_query']) && is_array($args['tax_query']) && !isset($args['tax_query']['relation'])) {
        $args['tax_query']['relation'] = 'AND';
    }

    return $args;
}, 10, 2);


add_action('wp_footer', function () {
    if (is_tax()): ?>
        <script>
            document.addEventListener('facetwp-refresh', function () {
                FWP.extras.destination_term_id = <?php echo (int) get_queried_object_id() ?>;
            });
        </script>
    <?php endif;
}, 100);



add_filter('facetwp_result_count', function ($output, $params) {
    $output = '<p class="font-arial text-[14px] text-[#7F7F7F]">' . $params['lower'] . ' à ' . $params['upper'] . ' résultat(s) sur ' . $params['total'] . '</p>';
    return $output;
}, 100, 2);


add_filter('gettext', function ($translated_text, $text, $domain) {
    if ('fwp-front' == $domain) {
        if ('See {num} more' == $text) {
            $translated_text = '+ Afficher plus';
        } elseif ('See less' == $text) {
            $translated_text = '- Afficher moins';
        }
    }
    return $translated_text;
}, 10, 3);


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


add_filter('facetwp_is_enabled', function ($enabled) {
    if (is_search()) {
        foreach ($_GET as $key => $val) {
            if (0 === strpos($key, 'fwp_')) {
                return true; 
            }
        }
        return false; 
    }
    return $enabled;
}, 10, 1);

add_filter('facetwp_is_main_query', function ($is_main, $query) {
    if ($query->is_search()) {
        foreach ($_GET as $key => $val) {
            if (0 === strpos($key, 'fwp_')) {
                return $is_main;
            }
        }
        return false;
    }
    return $is_main;
}, 10, 2);