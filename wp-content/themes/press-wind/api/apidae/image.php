<?php

function add_images_to_acf_gallery(int $post_id, string $field, array $sources, string $mode = 'id')
{
    if (empty($sources)) {
        return new WP_Error('no_sources', 'Aucune image fournie.');
    }

    // On veut manipuler la valeur brute (array d’IDs), pas la valeur formatée ACF.
    $existing_ids = get_field($field, $post_id, false);
    if (! is_array($existing_ids)) {
        $existing_ids = [];
    }

    $new_ids = [];

    // Assure que les fonctions d’upload sont dispo (utile en front ou CRON).
    if (in_array($mode, ['url', 'path'], true)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    foreach ($sources as $item) {
        $attachment_id = 0;

        if ('id' === $mode) {
            $attachment_id = absint($item);
        } elseif ('url' === $mode) {
            $attachment_id = _acf_gallery_import_from_url($item, $post_id);
        } 

        if (is_wp_error($attachment_id)) {
            // À vous de logger si besoin: error_log( $attachment_id->get_error_message() );
            continue;
        }

        if ($attachment_id && 'attachment' === get_post_type($attachment_id)) {
            $new_ids[] = $attachment_id;
        }
    }

    // Fusion + dédoublonnage, en conservant l’ordre existant puis les nouveaux.
    $final_ids = array_values(array_unique(array_merge($existing_ids, $new_ids)));

    // Met à jour la galerie (utiliser la clé de champ "field_xxx" évite les collisions).
    $ok = update_field($field, $final_ids, $post_id);
    if (! $ok) {
        return new WP_Error('update_failed', 'La mise à jour du champ ACF a échoué.');
    }

    return $final_ids;
}


function _acf_gallery_import_from_url(string $url, int $parent_post_id = 0)
{
    // Télécharge dans un fichier temporaire
    $tmp = download_url($url);
    if (is_wp_error($tmp)) {
        return $tmp;
    }

    $filename = basename(parse_url($url, PHP_URL_PATH) ?: 'image');
    // Construit le tableau attendu par media_handle_sideload
    $file = [
        'name'     => $filename,
        'tmp_name' => $tmp,
    ];

    $id = media_handle_sideload($file, $parent_post_id);

    if (is_wp_error($id)) {
        @unlink($tmp);
        return $id;
    }

    return $id;
}
