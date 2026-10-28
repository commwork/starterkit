<?php
/**
 * Custom post type: AWO Vorpommern Veranstaltungen (slug veranstaltungen).
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register CPT and taxonomy.
 */
function starterkit_register_veranstaltungen_cpt() {
	register_post_type(
		'veranstaltungen',
		array(
			'labels'              => array(
				'name'               => 'AWO Vorpommern Veranstaltungen',
				'singular_name'      => 'AWO Vorpommern Veranstaltung',
				'menu_name'          => 'Veranstaltungen',
				'add_new'            => 'Hinzufügen',
				'add_new_item'       => 'Neue Veranstaltung hinzufügen',
				'edit_item'          => 'Veranstaltung bearbeiten',
				'new_item'           => 'Neue Veranstaltung',
				'view_item'          => 'Veranstaltung anzeigen',
				'search_items'       => 'Veranstaltungen suchen',
				'not_found'          => 'Keine Veranstaltungen gefunden',
				'not_found_in_trash' => 'Keine Veranstaltungen im Papierkorb',
				'all_items'          => 'Alle Veranstaltungen',
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'veranstaltungen' ),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'veranstaltung_kategorie',
		array( 'veranstaltungen' ),
		array(
			'labels'            => array(
				'name'              => 'Kategorien',
				'singular_name'     => 'Kategorie',
				'search_items'      => 'Kategorien durchsuchen',
				'all_items'         => 'Alle Kategorien',
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
			'rewrite'           => array( 'slug' => 'veranstaltung-kategorie' ),
		)
	);
}
add_action( 'init', 'starterkit_register_veranstaltungen_cpt' );

/**
 * Parse ACF date value into a DateTimeImmutable in site timezone.
 *
 * @param mixed $date_raw Raw ACF date field value.
 * @return DateTimeImmutable|null
 */
function starterkit_veranstaltung_parse_date( $date_raw ) {
	$date_raw = trim( (string) $date_raw );
	if ( '' === $date_raw ) {
		return null;
	}

	$timezone = wp_timezone();
	$formats  = array( 'Ymd', 'Y-m-d', 'd/m/Y', 'd.m.Y' );

	foreach ( $formats as $format ) {
		$date = DateTimeImmutable::createFromFormat( $format, $date_raw, $timezone );
		if ( $date instanceof DateTimeImmutable ) {
			return $date;
		}
	}

	$timestamp = strtotime( $date_raw );
	if ( false !== $timestamp ) {
		return ( new DateTimeImmutable( '@' . $timestamp ) )->setTimezone( $timezone );
	}

	return null;
}

/**
 * Parse ACF time_picker value (return format H:i, e.g. 14:00).
 *
 * @param mixed $time_raw Raw ACF time field value.
 * @return array{h: int, i: int}|null
 */
function starterkit_veranstaltung_parse_time( $time_raw ) {
	$time_raw = trim( (string) $time_raw );
	if ( '' === $time_raw ) {
		return null;
	}

	$parsed = DateTimeImmutable::createFromFormat( 'H:i', $time_raw, wp_timezone() );
	if ( $parsed instanceof DateTimeImmutable ) {
		return array(
			'h' => (int) $parsed->format( 'G' ),
			'i' => (int) $parsed->format( 'i' ),
		);
	}

	// Legacy text values (e.g. "14:00 Uhr").
	if ( preg_match( '/(\d{1,2}):(\d{2})/', $time_raw, $matches ) ) {
		return array(
			'h' => (int) $matches[1],
			'i' => (int) $matches[2],
		);
	}

	return null;
}

/**
 * Event end timestamp (date + optional time). Without time, end of that calendar day.
 *
 * @param int $post_id Post ID.
 * @return int|null Unix timestamp in site timezone, or null if no date.
 */
function starterkit_veranstaltung_get_end_timestamp( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return null;
	}

	$date = starterkit_veranstaltung_parse_date( get_field( 'veranstaltung_datum', $post_id ) );
	if ( ! $date ) {
		return null;
	}

	$time = starterkit_veranstaltung_parse_time( get_field( 'veranstaltung_uhrzeit', $post_id ) );
	if ( null !== $time ) {
		$date = $date->setTime( $time['h'], $time['i'], 0 );
	} else {
		$date = $date->setTime( 23, 59, 59 );
	}

	return $date->getTimestamp();
}

/**
 * Whether the veranstaltung date/time is in the past (hidden on frontend lists).
 *
 * @param int $post_id Post ID.
 */
function starterkit_veranstaltung_is_past( $post_id ) {
	$end = starterkit_veranstaltung_get_end_timestamp( $post_id );
	if ( null === $end ) {
		return false;
	}

	return current_time( 'timestamp' ) > $end;
}

/**
 * Formatted date for display (e.g. 21. April 2026).
 *
 * @param int $post_id Post ID.
 */
function starterkit_veranstaltung_get_date_label( $post_id ) {
	$date = starterkit_veranstaltung_parse_date( get_field( 'veranstaltung_datum', $post_id ) );
	if ( ! $date ) {
		return '';
	}

	return date_i18n( 'j. F Y', $date->getTimestamp() );
}

/**
 * Formatted time for display (e.g. 14:00 Uhr).
 *
 * @param int $post_id Post ID.
 */
function starterkit_veranstaltung_get_time_label( $post_id ) {
	$time = starterkit_veranstaltung_parse_time( get_field( 'veranstaltung_uhrzeit', $post_id ) );
	if ( null === $time ) {
		return '';
	}

	return sprintf( '%02d:%02d Uhr', $time['h'], $time['i'] );
}

/**
 * Location / venue text (ACF veranstaltung_location).
 *
 * @param int $post_id Post ID.
 */
function starterkit_veranstaltung_get_location_label( $post_id ) {
	return trim( (string) get_field( 'veranstaltung_location', $post_id ) );
}

/**
 * Primary category term name.
 *
 * @param int $post_id Post ID.
 */
function starterkit_veranstaltung_get_category_label( $post_id ) {
	$terms = get_the_terms( $post_id, 'veranstaltung_kategorie' );
	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return '';
	}

	$term = array_shift( $terms );
	return $term instanceof WP_Term ? $term->name : '';
}

/**
 * Normalize veranstaltung_kategorie term IDs from ACF taxonomy field value.
 *
 * @param mixed $categories ACF taxonomy value (IDs, term objects, or mixed array).
 * @return int[]
 */
function starterkit_parse_veranstaltung_kategorie_ids( $categories ) {
	if ( empty( $categories ) ) {
		return array();
	}

	if ( ! is_array( $categories ) ) {
		$categories = array( $categories );
	}

	$term_ids = array();
	foreach ( $categories as $category ) {
		if ( is_object( $category ) && isset( $category->term_id ) ) {
			$term_ids[] = (int) $category->term_id;
		} elseif ( is_numeric( $category ) ) {
			$term_ids[] = (int) $category;
		}
	}

	return array_values( array_unique( array_filter( $term_ids ) ) );
}

/**
 * Query upcoming veranstaltungen (past date/time entries excluded).
 *
 * @param null|int|array $query_args Single term ID (legacy), or args: kategorie_term_id, kategorie_term_ids.
 * @return WP_Post[]
 */
function starterkit_get_upcoming_veranstaltungen( $query_args = null ) {
	if ( is_int( $query_args ) ) {
		$query_args = array( 'kategorie_term_id' => $query_args );
	} elseif ( ! is_array( $query_args ) ) {
		$query_args = array();
	}

	$query_args = wp_parse_args(
		$query_args,
		array(
			'kategorie_term_id'  => 0,
			'kategorie_term_ids' => array(),
		)
	);

	$term_ids = starterkit_parse_veranstaltung_kategorie_ids( $query_args['kategorie_term_ids'] );
	if ( empty( $term_ids ) && (int) $query_args['kategorie_term_id'] > 0 ) {
		$term_ids = array( (int) $query_args['kategorie_term_id'] );
	}

	$today = current_time( 'Ymd' );

	$args = array(
		'post_type'              => 'veranstaltungen',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => 'meta_value',
		'meta_key'               => 'veranstaltung_datum',
		'order'                  => 'ASC',
		'meta_type'              => 'NUMERIC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
		'meta_query'             => array(
			array(
				'key'     => 'veranstaltung_datum',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		),
	);

	if ( ! empty( $term_ids ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'veranstaltung_kategorie',
				'field'    => 'term_id',
				'terms'    => $term_ids,
				'operator' => 'IN',
			),
		);
	}

	$query   = new WP_Query( $args );
	$upcoming = array();

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			if ( ! starterkit_veranstaltung_is_past( $post->ID ) ) {
				$upcoming[] = $post;
			}
		}
	}

	wp_reset_postdata();

	return $upcoming;
}

/**
 * Admin list table: Datum column.
 *
 * @param string[] $columns Existing columns.
 * @return string[]
 */
function starterkit_veranstaltungen_admin_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;

		if ( 'title' === $key ) {
			$new_columns['veranstaltung_datum'] = 'Datum';
		}
	}

	return $new_columns;
}
add_filter( 'manage_veranstaltungen_posts_columns', 'starterkit_veranstaltungen_admin_columns' );

/**
 * Admin list table: render Datum column.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function starterkit_veranstaltungen_admin_column_content( $column, $post_id ) {
	if ( 'veranstaltung_datum' !== $column ) {
		return;
	}

	$date_label     = starterkit_veranstaltung_get_date_label( $post_id );
	$time_label     = starterkit_veranstaltung_get_time_label( $post_id );
	$location_label = starterkit_veranstaltung_get_location_label( $post_id );

	if ( '' === $date_label ) {
		echo '<span aria-hidden="true">—</span><span class="screen-reader-text">Kein Datum</span>';
		return;
	}

	echo esc_html( $date_label );

	if ( '' !== $time_label ) {
		echo '<br /><span class="veranstaltung-admin-time">' . esc_html( $time_label ) . '</span>';
	}

	if ( '' !== $location_label ) {
		echo '<br /><span class="veranstaltung-admin-location">' . esc_html( $location_label ) . '</span>';
	}
}
add_action( 'manage_veranstaltungen_posts_custom_column', 'starterkit_veranstaltungen_admin_column_content', 10, 2 );

/**
 * Admin list table: make Datum sortable.
 *
 * @param string[] $columns Sortable columns.
 * @return string[]
 */
function starterkit_veranstaltungen_sortable_columns( $columns ) {
	$columns['veranstaltung_datum'] = 'veranstaltung_datum';
	return $columns;
}
add_filter( 'manage_edit-veranstaltungen_sortable_columns', 'starterkit_veranstaltungen_sortable_columns' );

/**
 * Admin list table: order by veranstaltung_datum (default: ascending).
 *
 * @param WP_Query $query Main query.
 */
function starterkit_veranstaltungen_admin_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'veranstaltungen' !== $query->get( 'post_type' ) ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( ! $orderby || 'veranstaltung_datum' === $orderby ) {
		$query->set( 'meta_key', 'veranstaltung_datum' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_type', 'NUMERIC' );

		if ( ! $query->get( 'order' ) ) {
			$query->set( 'order', 'ASC' );
		}
	}
}
add_action( 'pre_get_posts', 'starterkit_veranstaltungen_admin_orderby' );
