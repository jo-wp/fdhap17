<?php

add_filter('facetwp_query_args', function ($args, $class) {

    // N'agir que sur le template FacetWP "full" (supprime cette condition si inutile)

    // Taxonomies autorisées à être lues depuis l'URI
    // 👉 ajoute/retire ici selon ton projet
    $allowed_taxonomies = [
        'destination',
        'equipement',
        'atout',
        'etoile',
        'aquatique',
        'service',
        'label',
        'hebergement',
        'cible',
        'groupe',
        'confort'
        // 'activite',
        // 'services',
        // ...
    ];

    // S'assurer que tax_query est un tableau
    if (empty($args['tax_query']) || ! is_array($args['tax_query'])) {
        $args['tax_query'] = [];
    }

    // Lire l'URI passée par FacetWP (ex: "equipement/barbecue" ou "destination/camping-la-rochelle")
    $uri = isset($class->ajax_params['http_params']['uri']) ? $class->ajax_params['http_params']['uri'] : '';
    if (! empty($uri)) {
        $path  = trim(wp_parse_url($uri, PHP_URL_PATH), '/'); // normalise
        $parts = array_values(array_filter(explode('/', $path))); // explode propre

        // On parcourt les segments pour trouver des paires taxo/slug
        // Exemple: ["equipement","barbecue"] => taxo=equipement, slug=barbecue
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $maybe_tax = sanitize_key($parts[$i]);

            // On ne traite que les taxos autorisées ET existantes
            if (! in_array($maybe_tax, $allowed_taxonomies, true)) {
                continue;
            }
            if (! taxonomy_exists($maybe_tax)) {
                continue;
            }

            // Le segment suivant est le slug du terme
            $slug = sanitize_title(urldecode($parts[$i + 1]));
            if ('' === $slug) {
                continue;
            }

            $term = get_term_by('slug', $slug, $maybe_tax);
            if ($term && ! is_wp_error($term)) {
                $args['tax_query'][] = [
                    'taxonomy'         => $maybe_tax,
                    'field'            => 'term_id',
                    'terms'            => [(int) $term->term_id],
                    'include_children' => true,
                ];
            }
        }
    }

    // (Optionnel) Fallback spécifique si tu reçois encore un extras pour "destination"
    if (
        isset($class->ajax_params['extras']['destination_term_id']) &&
        (int) $class->ajax_params['extras']['destination_term_id'] > 0
    ) {
        // N'ajouter que si aucun filtre 'destination' n'existe déjà
        $has_destination = false;
        foreach ($args['tax_query'] as $q) {
            if (is_array($q) && isset($q['taxonomy']) && $q['taxonomy'] === 'destination') {
                $has_destination = true;
                break;
            }
        }

        if (! $has_destination) {
            $args['tax_query'][] = [
                'taxonomy'         => 'destination',
                'field'            => 'term_id',
                'terms'            => [(int) $class->ajax_params['extras']['destination_term_id']],
                'include_children' => true,
            ];
        }
    }

    // Relation par défaut si plusieurs conditions
    if (! empty($args['tax_query']) && is_array($args['tax_query']) && ! isset($args['tax_query']['relation'])) {
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