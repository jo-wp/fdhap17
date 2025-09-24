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
			document.addEventListener('facetwp-refresh', function () {
				FWP.extras.destination_term_id = <?php echo (int) get_queried_object_id() ?>;
			});
		</script>
	<?php endif;
}, 100);



