<?php

/** IMPORTS DES CPTS & DATAS & METABOXES **/
include ('cpt/camping.php');
add_filter( 'use_block_editor_for_post_type', function( $use_block_editor, $post_type ) {
    if ( $post_type === 'camping' ) {
        return false; // Désactive Gutenberg pour le CPT camping
    }
    return $use_block_editor;
}, 10, 2 );

// Création du Custom Post Type "Offre d'emploi"
function cpt_offre_emploi() {

    $labels = array(
        'name'               => _x( 'Offres d’emploi', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'Offre d’emploi', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'Offres d’emploi', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'Offre d’emploi', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'Ajouter', 'offre d’emploi', 'textdomain' ),
        'add_new_item'       => __( 'Ajouter une nouvelle offre', 'textdomain' ),
        'new_item'           => __( 'Nouvelle offre', 'textdomain' ),
        'edit_item'          => __( 'Modifier l’offre', 'textdomain' ),
        'view_item'          => __( 'Voir l’offre', 'textdomain' ),
        'all_items'          => __( 'Toutes les offres', 'textdomain' ),
        'search_items'       => __( 'Rechercher des offres', 'textdomain' ),
        'parent_item_colon'  => __( 'Offres parentes :', 'textdomain' ),
        'not_found'          => __( 'Aucune offre trouvée.', 'textdomain' ),
        'not_found_in_trash' => __( 'Aucune offre dans la corbeille.', 'textdomain' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'offre-emploi' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 57,
        'menu_icon'          => 'dashicons-businessperson',
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'show_in_rest'       => true, // pour Gutenberg
    );

    register_post_type( 'offre_emploi', $args );
}
add_action( 'init', 'cpt_offre_emploi' );


// Création du Custom Post Type "Partenaire"
function cpt_partenaire()
{

    $labels = array(
        'name' => _x('Partenaires', 'post type general name', 'textdomain'),
        'singular_name' => _x('Partenaire', 'post type singular name', 'textdomain'),
        'menu_name' => _x('Partenaires', 'admin menu', 'textdomain'),
        'name_admin_bar' => _x('Partenaire', 'add new on admin bar', 'textdomain'),
        'add_new' => _x('Ajouter', 'partenaire', 'textdomain'),
        'add_new_item' => __('Ajouter un nouveau partenaire', 'textdomain'),
        'new_item' => __('Nouveau partenaire', 'textdomain'),
        'edit_item' => __('Modifier le partenaire', 'textdomain'),
        'view_item' => __('Voir le partenaire', 'textdomain'),
        'all_items' => __('Tous les partenaires', 'textdomain'),
        'search_items' => __('Rechercher des partenaires', 'textdomain'),
        'parent_item_colon' => __('Partenaires parents :', 'textdomain'),
        'not_found' => __('Aucun partenaire trouvé.', 'textdomain'),
        'not_found_in_trash' => __('Aucun partenaire dans la corbeille.', 'textdomain')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => false, // pas de page individuelle
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => false, // pas d’URL publique
        'capability_type' => 'post',
        'has_archive' => false, // pas de page archive
        'hierarchical' => false,
        'menu_position' => 58,
        'menu_icon' => 'dashicons-groups',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => false, // pour Gutenberg
    );

    register_post_type('partenaire', $args);
}

add_action('init', 'cpt_partenaire');
