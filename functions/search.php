<?php
/**
 * Frontend search extensions.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param WP_Query $query Main search query.
 */
function starterkit_search_should_extend( $query ) {
	return ! is_admin() && $query->is_main_query() && $query->is_search();
}

/**
 * Include tag names in frontend search results.
 */
function starterkit_search_join_terms( $join, $query ) {
	global $wpdb;

	if ( ! starterkit_search_should_extend( $query ) ) {
		return $join;
	}

	$join .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON ({$wpdb->posts}.ID = tr.object_id)";
	$join .= " LEFT JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)";
	$join .= " LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id)";

	return $join;
}
add_filter( 'posts_join', 'starterkit_search_join_terms', 10, 2 );

/**
 * Match post tags in addition to default post fields.
 */
function starterkit_search_include_tags( $search, $query ) {
	global $wpdb;

	if ( ! starterkit_search_should_extend( $query ) ) {
		return $search;
	}

	$search_term = $query->get( 's' );
	if ( ! is_string( $search_term ) || $search_term === '' ) {
		return $search;
	}

	$tag_like = '%' . $wpdb->esc_like( $search_term ) . '%';
	$tag_sql  = $wpdb->prepare( "(tt.taxonomy = 'post_tag' AND t.name LIKE %s)", $tag_like );

	if ( $search !== '' ) {
		$trimmed_search = trim( preg_replace( '/^\s*AND\s*/', '', $search ) );
		if ( $trimmed_search !== '' ) {
			return " AND (({$trimmed_search}) OR {$tag_sql})";
		}
	}

	return " AND ({$tag_sql})";
}
add_filter( 'posts_search', 'starterkit_search_include_tags', 10, 2 );

/**
 * Avoid duplicate posts when a tag join matches multiple terms.
 */
function starterkit_search_distinct( $distinct, $query ) {
	if ( ! starterkit_search_should_extend( $query ) ) {
		return $distinct;
	}

	return 'DISTINCT';
}
add_filter( 'posts_distinct', 'starterkit_search_distinct', 10, 2 );
