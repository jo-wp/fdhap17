<?php

add_filter('facetwp_query_args', function ($args, $class) {
	if (isset($class->ajax_params['extras']['destination_term_id'])) {
		$term_id = (int) $class->ajax_params['extras']['destination_term_id'];
		if ($term_id > 0) {
			$args['tax_query'][] = [
				'taxonomy' => 'destination',
				'field' => 'term_id',
				'terms' => [$term_id],
				'include_children' => false,
			];
		}
	}
	return $args;
}, 10, 2);

add_action('wp_footer', function () {
	if (is_tax()): ?>
		<script>
			document.addEventListener('facetwp-refresh', function() {
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
	if (empty($post_types) || ! in_array('camping', $post_types, true)) {
		return $args;
	}

	// --- 1) Appel API + cache (avec dates) ---
	// 1.a) Récup des dates (ex: via query string ?startDate=YYYY-MM-DD&endDate=YYYY-MM-DD)
	$start_raw = isset($_GET['trip-start']) ? trim($_GET['trip-start']) : null;
	$end_raw   = isset($_GET['trip-end'])   ? trim($_GET['trip-end'])   : null;

	// 1.b) Validation & normalisation (YYYY-MM-DD)
	$start = $start_raw ? DateTime::createFromFormat('Y-m-d', $start_raw) : null;
	$end   = $end_raw   ? DateTime::createFromFormat('Y-m-d', $end_raw)   : null;
	$start_valid = $start && $start->format('Y-m-d') === $start_raw;
	$end_valid   = $end   && $end->format('Y-m-d')   === $end_raw;

	// 1.c) Defaults si non fournis (ou invalides)
	if (! $start_valid || ! $end_valid) {
		$dateFilters = [
			'startDate' => date('Y-m-d', strtotime('+1 day')),
			'endDate'   => date('Y-m-d', strtotime('+10 days')),
		];
	} else {
		// S'assure que end >= start
		if ($end < $start) {
			// swap si inversées
			[$start, $end] = [$end, $start];
		}
		$dateFilters = [
			'startDate' => $start->format('Y-m-d'),
			'endDate'   => $end->format('Y-m-d'),
		];
	}

	// 1.d) Clé de cache dépendante des dates
	$cache_key = 'ctoutvert_available_establishment_ids_' . md5(wp_json_encode($dateFilters));
	$available_ids = get_transient($cache_key);

	if (false === $available_ids) {
		$available_ids = [];

		// Appel API avec dates
		$res = Ctoutvert::ctoutvert_search_holidays($dateFilters);

		// Sécurise l’accès à la structure renvoyée (stdClass imbriquées)
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

		// TTL : 5 min si on a des résultats, 60s si vide (pour éviter de "figer" un vide temporaire)
		$ttl = ! empty($available_ids) ? 5 * MINUTE_IN_SECONDS : 60;
		set_transient($cache_key, $available_ids, $ttl);
	}


	// --- 2) Construit la meta_query ---
	// On veut : (meta NOT EXISTS) OR (meta == '') OR (meta IN $available_ids)
	$meta_query = isset($args['meta_query']) && is_array($args['meta_query']) ? $args['meta_query'] : [];
	$or_group   = ['relation' => 'OR'];

	// A) Toujours inclure ceux SANS meta
	$or_group[] = [
		'key'     => 'id_reservation_direct',
		'compare' => 'NOT EXISTS',
	];

	// B) Toujours inclure ceux avec meta vide
	$or_group[] = [
		'key'     => 'id_reservation_direct',
		'value'   => '',
		'compare' => '=',
	];

	// C) Inclure ceux dont la meta (numérique) est dans la liste API
	if (! empty($available_ids)) {
		$or_group[] = [
			'key'     => 'id_reservation_direct',
			'value'   => $available_ids,
			'compare' => 'IN',
			'type'    => 'NUMERIC',
		];
	}

	$meta_query[]        = $or_group;
	$args['meta_query']  = $meta_query;

	// Laisse ton tri / pagination existants (exemple) :
	// $args['orderby']        = [ 'title' => 'ASC' ];
	// $args['posts_per_page'] = 6;

	return $args;
}, 10, 2);
