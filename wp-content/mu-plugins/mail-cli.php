<?php
if (defined('WP_CLI') && WP_CLI) {

    WP_CLI::add_command('mail test', function ($args, $assoc_args) {

        $to = $assoc_args['to'] ?? get_option('admin_email');
        $subject = 'Test email WP-CLI';
        $message = 'Ceci est un email de test envoyé depuis WP-CLI via wp_mail().';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (wp_mail($to, $subject, $message, $headers)) {
            WP_CLI::success("Email envoyé avec succès à {$to}");
        } else {
            WP_CLI::error("Échec de l'envoi de l'email");
        }
    });
}
