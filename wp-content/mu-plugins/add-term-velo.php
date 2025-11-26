<?php

if (defined('WP_CLI') && WP_CLI) {

    class Camping_Label_Command {

        /**
         * Assigne le term 88 aux campings dont apidae_id est dans la liste.
         *
         * Exemple :
         *   wp camping label
         *
         */
        public function label($args, $assoc_args) {

            $apidae_ids = [
                5752914,5752925,5752647,5752646,5772617,5752902,5752901,5752643,5752897,5752654,
                5772609,5752910,5752653,5794885,5772613,5752906,5752649,5772615,5752692,5752954,
                5752675,7231835,5752930,5752686,5752683,5752681,5752854,5752852,5752595,5752863,
                5752856,5752837,5752836,5752846,5752843,5752887,5752628,5794878,5752624,5752636,
                5772840,5772842,5752868,5772832,5772833,5752878,5772834,5752876,5772504,5772505,
                5772506,5752787,6376933,5752785,5752796,5837781,5752795,6319599,5752783,5752822,
                5772528,5752802,5772513,5752811,5772516,5752983,5794713,5752981,5794716,5794717,
                5752720,5794704,7293606,5794706,5752733,5794709,5794711,5752984,5794696,5794699,
                5752705,5794702,5752960,5752719,5794691,5752715,5794692,5752970,5794693,5794695,
                5752968,5752756,5753010,5752764,5752761,5753017,5794728,5752997,5752738,5752993,
                5794720,5752751,5794721,5753005,5794723,5752747,5753002,5794725,5794727,5753000,
            ];

            $term_id  = 88;
            $taxonomy = 'label';

            WP_CLI::line("Recherche des campings correspondants...");

            $posts = get_posts([
                'post_type'      => 'camping',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => [
                    [
                        'key'     => 'apidae_id',
                        'value'   => $apidae_ids,
                        'compare' => 'IN',
                        'type'    => 'NUMERIC',
                    ],
                ],
            ]);

            if (empty($posts)) {
                WP_CLI::warning("Aucun camping trouvé avec ces apidae_id.");
                return;
            }

            WP_CLI::success(count($posts) . " campings trouvés.");

            foreach ($posts as $post_id) {
                wp_set_post_terms($post_id, [$term_id], $taxonomy, true);
                WP_CLI::line("✔ Term ajouté pour le camping ID {$post_id}");
            }

            WP_CLI::success("Term ajouté à tous les campings concernés !");
        }
    }

    WP_CLI::add_command('camping', 'Camping_Label_Command');
}
