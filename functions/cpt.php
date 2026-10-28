<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Jobs custom post type.
 */
function starterkit_register_jobs_cpt() {
	register_post_type(
		'jobs',
		array(
			'labels'      => array(
				'name'          => 'Jobs',
				'singular_name' => 'Job',
			),
			'public'      => true,
			'supports'    => array( 'title', 'editor', 'excerpt', 'custom-fields', 'thumbnail' ),
			'has_archive' => true,
			'menu_icon'   => 'dashicons-businessman',
			'rewrite'     => array( 'slug' => 'jobs' ),
		)
	);
}
add_action( 'init', 'starterkit_register_jobs_cpt' );

/**
 * Job taxonomies (synced from XML import: selection1 → Kategorie, location → Ort, selection2 → Berufsbezeichnung).
 */
function starterkit_register_job_taxonomies() {
	register_taxonomy(
		'job_kategorie',
		array( 'jobs' ),
		array(
			'labels'            => array(
				'name'              => 'Kategorien',
				'singular_name'     => 'Kategorie',
				'search_items'      => 'Kategorien durchsuchen',
				'all_items'         => 'Alle Kategorien',
				'parent_item'       => 'Übergeordnete Kategorie',
				'parent_item_colon' => 'Übergeordnete Kategorie:',
				'edit_item'         => 'Kategorie bearbeiten',
				'update_item'       => 'Kategorie aktualisieren',
				'add_new_item'      => 'Neue Kategorie hinzufügen',
				'new_item_name'     => 'Neue Kategorie',
				'menu_name'         => 'Kategorien',
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'job-kategorie' ),
		)
	);

	register_taxonomy(
		'job_ort',
		array( 'jobs' ),
		array(
			'labels'            => array(
				'name'          => 'Orte',
				'singular_name' => 'Ort',
				'search_items'  => 'Orte durchsuchen',
				'all_items'     => 'Alle Orte',
				'edit_item'     => 'Ort bearbeiten',
				'update_item'   => 'Ort aktualisieren',
				'add_new_item'  => 'Neuen Ort hinzufügen',
				'new_item_name' => 'Neuer Ort',
				'menu_name'     => 'Orte',
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'job-ort' ),
		)
	);

	register_taxonomy(
		'job_berufsbezeichnung',
		array( 'jobs' ),
		array(
			'labels'            => array(
				'name'          => 'Berufsbezeichnungen',
				'singular_name' => 'Berufsbezeichnung',
				'search_items'  => 'Berufsbezeichnungen durchsuchen',
				'all_items'     => 'Alle Berufsbezeichnungen',
				'edit_item'     => 'Berufsbezeichnung bearbeiten',
				'update_item'   => 'Berufsbezeichnung aktualisieren',
				'add_new_item'  => 'Neue Berufsbezeichnung hinzufügen',
				'new_item_name' => 'Neue Berufsbezeichnung',
				'menu_name'     => 'Berufsbezeichnungen',
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'job-beruf' ),
		)
	);
}
add_action( 'init', 'starterkit_register_job_taxonomies' );

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
