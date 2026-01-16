<?php if (!is_page('carte-camping') && !is_front_page()):
	$destinations_featured = ['camping-royan', 'camping-ile-oleron', 'camping-ile-de-re', 'camping-pays-du-cognac', 'camping-marais-poitevin', 'camping-la-rochelle', 'camping-rochefort', 'camping-futuroscope',]; ?>
	<div class="facetwp-facet facetwp-facet-destination facetwp-type-dropdown" data-name="destination" data-type="dropdown">
		<select class="facetwp-dropdown">
			<option value="">Destination</option>
			<option value="camping-royan">Royan (82)</option>
			<option value="camping-ile-oleron">Île d'Oléron (62)</option>
			<option value="camping-ile-de-re">Île de Ré (36)</option>
			<option value="camping-pays-du-cognac">Pays du Cognac (18)</option>
			<option value="camping-marais-poitevin">Marais Poitevin (13)</option>
			<option value="camping-la-rochelle">La Rochelle (7)</option>
			<option value="camping-rochefort">Rochefort (7)</option>
			<option value="camping-futuroscope">Futuroscope (4)</option>
		</select>
	</div>
<?php else: ?> <?= do_shortcode('[facetwp facet="destination"]'); ?> <?php endif; ?>