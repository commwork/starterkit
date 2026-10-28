<?php
/**
 * Normalize typographic quotes to straight ASCII quotes.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace German / typographic quotes with straight " and '.
 */
function starterkit_normalize_typographic_quotes( $text ) {
	if ( ! is_string( $text ) || $text === '' ) {
		return $text;
	}

	static $search  = null;
	static $replace = null;

	if ( $search === null ) {
		$search = array(
			"\u{201E}", // „
			"\u{201C}", // "
			"\u{201D}", // "
			"\u{201A}", // ‚
			"\u{2018}", // '
			"\u{2019}", // '
			'&bdquo;',
			'&ldquo;',
			'&rdquo;',
			'&sbquo;',
			'&lsquo;',
			'&rsquo;',
			'&#8222;',
			'&#8220;',
			'&#8221;',
			'&#8218;',
			'&#8216;',
			'&#8217;',
		);
		$replace = array( '"', '"', '"', "'", "'", "'", '"', '"', '"', "'", "'", "'", '"', '"', '"', "'", "'", "'" );
	}

	return str_replace( $search, $replace, $text );
}

/**
 * Recursively normalize strings inside arrays (ACF repeaters, flexible content, etc.).
 *
 * @param mixed $value Field or post meta value.
 * @return mixed
 */
function starterkit_normalize_typographic_quotes_deep( $value ) {
	if ( is_string( $value ) ) {
		return starterkit_normalize_typographic_quotes( $value );
	}

	if ( ! is_array( $value ) ) {
		return $value;
	}

	foreach ( $value as $key => $item ) {
		$value[ $key ] = starterkit_normalize_typographic_quotes_deep( $item );
	}

	return $value;
}

/**
 * ACF: normalize on save and when values are read (covers existing DB content in the editor).
 *
 * @param mixed $value   Field value.
 * @param mixed $post_id Post, term, user, or options ID.
 * @param array $field   ACF field settings.
 * @return mixed
 */
function starterkit_acf_normalize_typographic_quotes( $value, $post_id, $field ) {
	unset( $post_id, $field );

	return starterkit_normalize_typographic_quotes_deep( $value );
}
add_filter( 'acf/update_value', 'starterkit_acf_normalize_typographic_quotes', 20, 3 );
add_filter( 'acf/format_value', 'starterkit_acf_normalize_typographic_quotes', 20, 3 );
// WYSIWYG fields run wptexturize via acf_the_content after acf/format_value — normalize again afterwards.
add_filter( 'acf/format_value/type=wysiwyg', 'starterkit_acf_normalize_typographic_quotes', 99, 3 );
add_filter( 'acf_the_content', 'starterkit_normalize_typographic_quotes', 15 );

/**
 * WordPress core fields: title, content, excerpt.
 *
 * @param array<string, mixed> $data    Slashed post data.
 * @param array<string, mixed> $postarr Raw post data.
 * @return array<string, mixed>
 */
function starterkit_normalize_post_typographic_quotes( $data, $postarr ) {
	unset( $postarr );

	foreach ( array( 'post_title', 'post_content', 'post_excerpt' ) as $key ) {
		if ( ! isset( $data[ $key ] ) || ! is_string( $data[ $key ] ) ) {
			continue;
		}

		$data[ $key ] = starterkit_normalize_typographic_quotes( $data[ $key ] );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'starterkit_normalize_post_typographic_quotes', 20, 2 );

/**
 * Frontend / admin output after wptexturize may re-introduce curly quotes.
 */
function starterkit_filter_typographic_quotes( $text ) {
	return starterkit_normalize_typographic_quotes( $text );
}
add_filter( 'the_title', 'starterkit_filter_typographic_quotes', 15 );
add_filter( 'the_content', 'starterkit_filter_typographic_quotes', 15 );
add_filter( 'the_excerpt', 'starterkit_filter_typographic_quotes', 15 );
add_filter( 'get_the_excerpt', 'starterkit_filter_typographic_quotes', 15 );
