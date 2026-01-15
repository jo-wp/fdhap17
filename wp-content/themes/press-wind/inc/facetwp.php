<?php
add_filter('facetwp_query_args', function ($args, $class) {

    if (empty($args['tax_query']) || !is_array($args['tax_query'])) {
        $args['tax_query'] = [];
    }

    $args['posts_per_page'] = 12;

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

    if (
        isset($class->ajax_params['extras']['destination_term_id']) &&
        (int) $class->ajax_params['extras']['destination_term_id'] > 0
    ) {
        $term_id = (int) $class->ajax_params['extras']['destination_term_id'];

        $dest_term = get_term($term_id, 'destination');

        if ($dest_term && !is_wp_error($dest_term)) {

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




    if (!empty($uri) && function_exists('get_field')) {

        $path  = trim(wp_parse_url($uri, PHP_URL_PATH), '/');
        $parts = explode('/', $path);

        if (count($parts) >= 2) {
            $context_tax  = $parts[count($parts) - 2]; 
            $context_slug = $parts[count($parts) - 1]; 

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

                            if (isset($args[$context_tax])) {
                                unset($args[$context_tax]);
                            }

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


    if (!empty($args['tax_query']) && is_array($args['tax_query']) && !isset($args['tax_query']['relation'])) {
        $args['tax_query']['relation'] = 'AND';
    }


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




add_filter('facetwp_query_args', function ($args, $class) {

    $post_types = (array) ($args['post_type'] ?? []);
    if (empty($post_types) || !in_array('camping', $post_types, true)) {
        return $args;
    }

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

    $meta_query = isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : [];
    $or_group = ['relation' => 'OR'];

    $or_group[] = [
        'key' => 'id_reservation_ctoutvert',
        'compare' => 'NOT EXISTS',
    ];

    $or_group[] = [
        'key' => 'id_reservation_ctoutvert',
        'value' => '',
        'compare' => '=',
    ];

    if (!empty($available_ids)) {
        $or_group[] = [
            'key' => 'id_reservation_ctoutvert',
            'value' => $available_ids,
            'compare' => 'IN',
            'type' => 'NUMERIC',
        ];
    }

    $meta_query[] = $or_group;
    $args['meta_query'] = $meta_query;

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
            $output['settings']['date_start']['range']['min']['minDate'] = '2023-01-01'; 
        }
        if (0 == $output['settings']['date_start']['range']['min']['maxDate']) {
            $output['settings']['date_start']['range']['min']['maxDate'] = '2100-12-30'; 
        }
        if (0 == $output['settings']['date_start']['range']['max']['minDate']) {
            $output['settings']['date_start']['range']['max']['minDate'] = '2023-01-02'; 
        }
        if (0 == $output['settings']['date_start']['range']['max']['maxDate']) {
            $output['settings']['date_start']['range']['max']['maxDate'] = '2100-12-31'; 
        }
    }

    return $output;
}, 10, 2);


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

add_filter('facetwp_index_row', function ($params, $class) {
    if ('ctoutvert_checkbox' !== $params['facet_name']) {
        return $params;
    }

    $post_id = (int) $params['post_id'];
    $raw     = get_post_meta($post_id, 'id_reservation_ctoutvert', true);

    if (is_array($raw)) {
        $raw = implode(',', array_filter($raw));
    }
    $value = trim((string) $raw);

    if ($value !== '') {
        $params['facet_value']         = 'has';
        $params['facet_display_value'] = __('Réservable sur Campings.online','fdhpa17');
        return $params;
    }

    return false;
}, 10, 2);


add_action( 'facetwp_scripts', function() {
  ?>
  <script>
    (function() {

      function addDays(dateStr, days) {
        var parts = dateStr.split('-');
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        d.setDate(d.getDate() + days);

        var y = d.getFullYear();
        var m = ('0' + (d.getMonth() + 1)).slice(-2);
        var day = ('0' + d.getDate()).slice(-2);
        return y + '-' + m + '-' + day;
      }

      document.addEventListener('facetwp-refresh', function() {
        if ('object' !== typeof FWP || !FWP.active_facet) return;

        var activeName = fUtil(FWP.active_facet.nodes[0]).attr('data-name');
        if (activeName !== 'date_start') return;

        var startFacet = FWP.facets['date_start'] || [];
        var startVal   = startFacet[0] || startFacet[1] || '';
        if (!startVal) return;

        var endPlus7 = addDays(startVal, 7);

        var endWrap = document.querySelector('.facetwp-type-date_range[data-name="date_end"]');
        var hasMax  = endWrap && endWrap.querySelector('input.facetwp-date-max');

        var endFacet = FWP.facets['date_end'] || [];
        if (!Array.isArray(endFacet) || endFacet.length < 2) {
          endFacet = ['', ''];
        }

        if (hasMax) {
          endFacet[1] = endPlus7;
        } else {
          endFacet[0] = endPlus7;
        }

        FWP.facets['date_end'] = endFacet;
      });

      document.addEventListener('facetwp-loaded', function() {
        if ('object' !== typeof FWP) return;

        var endFacet = FWP.facets['date_end'] || [];
        var endVal   = endFacet[1] || endFacet[0] || '';
        if (!endVal) return;

        var endInput = document.querySelector('.facetwp-type-date_range[data-name="date_end"] input');
        if (endInput && endInput.value !== endVal) {
          endInput.value = endVal;
        }
      });

    })();
  </script>
  <?php
}, 100 );


