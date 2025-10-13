<?php
/**
 * Plugin Name: Pixel Perfect x Ninja Forms (sync liste)
 * Description: Envoie les soumissions du formulaire (email/vousetes/acceptation) vers Pixel-Perfect avec delete+add.
 * Version: 1.3
 */

if ( ! defined('ABSPATH') ) exit;

// =====================
//  Config sécurisée
// =====================
if ( ! defined('PIXEL_PERFECT_LIST_ID') ) {
    define('PIXEL_PERFECT_LIST_ID', '654766e9cc5ad3d70e8c87247aad55fd');
}
if ( ! defined('PIXEL_PERFECT_API_KEY') ) {
    // ⚠️ Idéalement: define('PIXEL_PERFECT_API_KEY', 'xxx'); dans wp-config.php
    define('PIXEL_PERFECT_API_KEY', 'pm/o9HlSmtd4I/529xVy5FtxodrN68zIqObuyi7Fzb/tPxIn4XBmAbbLHFAOKcU4dkZqKBJijpAL6a9ynYyp6vbestGRImR9GoJbstVOcrNZD7qyEfFAbse3Qj+TRqT3ouQdCZDIeXWauodbVyR0pA=='); 
}

// =====================
//  Logger maison
// =====================
if ( ! function_exists('pixelperfect_log') ) {
    function pixelperfect_log( $msg ) {
        $log_dir  = WP_CONTENT_DIR . '/log';
        $log_file = $log_dir . '/pixelperfect.log';

        if ( ! file_exists( $log_dir ) ) {
            // crée récursivement si besoin
            wp_mkdir_p( $log_dir );
            // Optionnel: protège l'accès direct via .htaccess
            if ( ! file_exists( $log_dir . '/.htaccess' ) ) {
                @file_put_contents( $log_dir . '/.htaccess', "Deny from all\n" );
            }
        }

        $line = '[' . date('Y-m-d H:i:s') . '] [PixelPerfect] ' . (string)$msg . PHP_EOL;
        @file_put_contents( $log_file, $line, FILE_APPEND );
    }
}

// =====================
//  Hook Ninja Forms
// =====================
add_action('ninja_forms_after_submission', function( $form_data ){

    $fields = isset($form_data['fields']) ? $form_data['fields'] : [];

    // Helpers pour récupérer par key/id
    $getByKey = function($key) use ($fields) {
        foreach ($fields as $f) {
            if (isset($f['key']) && $f['key'] === $key) return $f;
        }
        return null;
    };
    $getById = function($id) use ($fields) {
        foreach ($fields as $f) {
            if (isset($f['id']) && (string)$f['id'] === (string)$id) return $f;
        }
        return null;
    };

    // --- Email (key=email, id=22) ---
    $emailField = $getByKey('email') ?: $getById(22);
    $email = '';
    if ($emailField && isset($emailField['value'])) {
        $email = is_array($emailField['value']) ? reset($emailField['value']) : trim((string)$emailField['value']);
    }
    if (!is_email($email)) {
        pixelperfect_log('Email manquant/invalide (key=email/id=22). Abandon sync.');
        if ( function_exists('Ninja_Forms') ) {
            Ninja_Forms()->logger()->debug('[PixelPerfect] Email manquant/invalide (key=email/id=22).');
        }
        return;
    }

    // --- Radio "vousetes" (id=23) → CustomField Profil ---
    $profilField = $getByKey('vousetes') ?: $getById(23);
    $profil = '';
    if ($profilField && isset($profilField['value'])) {
        $profil = is_array($profilField['value']) ? implode(', ', $profilField['value']) : trim((string)$profilField['value']);
        // Valeurs: camping-cariste | entre-amis | en-couple | en-famille
    }

    // --- Checkbox consentement "acceptation" (id=25) doit être cochée ---
    $consentField = $getByKey('acceptation') ?: $getById(25);
    $consented = false;
    if ($consentField) {
        $val = isset($consentField['value']) ? (is_array($consentField['value']) ? reset($consentField['value']) : (string)$consentField['value']) : '';
        $consented = ( $val === 'Coché' || $val === '1' || $val === 1 || $val === true );
    }
    if (!$consented) {
        pixelperfect_log('Consentement non coché (key=acceptation/id=25). Aucune inscription envoyée.');
        if ( function_exists('Ninja_Forms') ) {
            Ninja_Forms()->logger()->debug('[PixelPerfect] Consentement non coché (key=acceptation/id=25).');
        }
        return;
    }

    // --- Payload Pixel-Perfect ---
    $custom = [];
    $pushCF = function($key, $val) use (&$custom){
        if ($val !== '' && $val !== null) $custom[] = ['Key' => $key, 'Value' => $val];
    };

     $firstnameField = $getByKey('firstname') ?: $getById(26); // <-- mets l'ID réel si besoin
    $lastnameField  = $getByKey('lastname')  ?: $getById(27); // <-- mets l'ID réel si besoin
    $messageField   = $getByKey('message')   ?: $getById(28); // <-- mets l'ID réel si besoin

    // Valeurs nettoyées
    $firstname = '';
    if ($firstnameField && isset($firstnameField['value'])) {
        $firstname = is_array($firstnameField['value']) ? reset($firstnameField['value']) : (string)$firstnameField['value'];
        $firstname = sanitize_text_field($firstname);
    }

    $lastname = '';
    if ($lastnameField && isset($lastnameField['value'])) {
        $lastname = is_array($lastnameField['value']) ? reset($lastnameField['value']) : (string)$lastnameField['value'];
        $lastname = sanitize_text_field($lastname);
    }

    $messageTxt = '';
    if ($messageField && isset($messageField['value'])) {
        $messageTxt = is_array($messageField['value']) ? implode("\n", (array)$messageField['value']) : (string)$messageField['value'];
        // pour un textarea : supprime balises, garde le texte
        $messageTxt = sanitize_textarea_field($messageTxt);
    }

    // Concat pour le champ "Name" Campaign Monitor / Pixel-Perfect
    $fullName = trim($firstname . ' ' . $lastname);

    // --- CustomFields à pousser (doivent exister côté Pixel-Perfect) ---
    $pushCF('Profil', $profil);           // (déjà présent)
    $pushCF('FirstName', $firstname);     // crée/assure ces 3 champs personnalisés dans PP
    $pushCF('LastName',  $lastname);
    $pushCF('Message',   $messageTxt);

    $payload = [
        'EmailAddress' => $email,
        'Name'         => $fullName,
        'CustomFields' => $custom,
        'Resubscribe'  => true,
        'RestartSubscriptionBasedAutoresponders' => true,
        'ConsentToTrack' => 'Yes',
    ];

    $base = 'https://mailing.pixel-perfect.fr/api/v3.3';
    $list = urlencode(PIXEL_PERFECT_LIST_ID);

    $headers = [
        'Authorization' => 'Basic ' . base64_encode( PIXEL_PERFECT_API_KEY . ':x' ), // username=API KEY, password=x
        'Content-Type'  => 'application/json',
    ];

    // 1) DELETE avant ADD
    $delete_url = "$base/subscribers/$list.json?email=" . rawurlencode($email);
    $del = wp_remote_request($delete_url, [
        'method'   => 'DELETE',
        'headers'  => $headers,
        'timeout'  => 15,
        'sslverify'=> true,
    ]);
    if (is_wp_error($del)) {
        pixelperfect_log('DELETE erreur: ' . $del->get_error_message());
        if ( function_exists('Ninja_Forms') ) {
            Ninja_Forms()->logger()->debug('[PixelPerfect] DELETE erreur: ' . $del->get_error_message());
        }
        // on continue quand même
    } else {
        pixelperfect_log('DELETE OK pour ' . $email);
    }

    // 2) POST d’ajout
    $post_url = "$base/subscribers/$list.json";
    $res = wp_remote_post($post_url, [
        'headers'  => $headers,
        'body'     => wp_json_encode($payload),
        'timeout'  => 20,
        'sslverify'=> true,
    ]);

    if (is_wp_error($res)) {
        pixelperfect_log('POST erreur: ' . $res->get_error_message());
        if ( function_exists('Ninja_Forms') ) {
            Ninja_Forms()->logger()->debug('[PixelPerfect] POST erreur: ' . $res->get_error_message());
        }
        return;
    }

    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);

    if ($code !== 200) {
        pixelperfect_log('POST non-200: ' . $code . ' — ' . $body);
        if ( function_exists('Ninja_Forms') ) {
            Ninja_Forms()->logger()->debug('[PixelPerfect] POST non-200: ' . $code . ' — ' . $body);
        }
    } else {
        pixelperfect_log('POST OK pour ' . $email);
    }
}, 10, 1);
