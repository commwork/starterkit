<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Personen custom post type.
 */
function starterkit_register_personen_cpt() {
	register_post_type(
		'personen',
		array(
			'labels'             => array(
				'name'               => 'Personen',
				'singular_name'      => 'Person',
				'menu_name'          => 'Personen',
				'add_new'            => 'Hinzufügen',
				'add_new_item'       => 'Neue Person hinzufügen',
				'edit_item'          => 'Person bearbeiten',
				'new_item'           => 'Neue Person',
				'view_item'          => 'Person anzeigen',
				'search_items'       => 'Personen suchen',
				'not_found'          => 'Keine Personen gefunden',
				'not_found_in_trash' => 'Keine Personen im Papierkorb',
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'personen' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-groups',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'author' ),
		)
	);
}
add_action( 'init', 'starterkit_register_personen_cpt' );
