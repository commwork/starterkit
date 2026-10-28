<?php
/**
 * ACF customizations.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hide blockquote and alignment buttons in the Basic WYSIWYG toolbar.
 *
 * @param array<string, array<int, array<int, string>>> $toolbars ACF toolbars.
 * @return array<string, array<int, array<int, string>>>
 */
function starterkit_acf_remove_blockquote_from_toolbars( $toolbars ) {
	$remove = array( 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'fullscreen', 'strikethrough' );
	if ( ! isset( $toolbars['Basic'] ) ) {
		return $toolbars;
	}

	foreach ( $toolbars['Basic'] as $row => $buttons ) {
		if ( ! is_array( $buttons ) ) {
			continue;
		}
		foreach ( $remove as $button ) {
			$key = array_search( $button, $buttons, true );
			if ( false !== $key ) {
				unset( $toolbars['Basic'][ $row ][ $key ] );
			}
		}
	}

	return $toolbars;
}
add_filter( 'acf/fields/wysiwyg/toolbars', 'starterkit_acf_remove_blockquote_from_toolbars' );

/**
 * Normalize any backend label text: strip tags and trim.
 */
function starterkit_normalize_layout_label_text( $value ) {
	return trim( wp_strip_all_tags( (string) $value ) );
}

/**
 * @param mixed $titel_rows ACF `titel` flexible rows.
 */
function starterkit_get_titel_label_from_rows( $titel_rows ) {
	if ( ! is_array( $titel_rows ) ) {
		return '';
	}

	$parts = array();
	foreach ( $titel_rows as $titel_row ) {
		if ( ! is_array( $titel_row ) ) {
			continue;
		}
		if ( (string) ( $titel_row['acf_fc_layout'] ?? '' ) === 'line_break' ) {
			continue;
		}

		$text = starterkit_normalize_layout_label_text( $titel_row['text'] ?? '' );
		if ( $text !== '' ) {
			$parts[] = $text;
		}
	}

	return implode( ' ', $parts );
}

/**
 * Read label text from `header` group/repeater structures used in flexible layouts.
 *
 * @param mixed $header_raw Raw `header` field value.
 */
function starterkit_get_titel_label_from_header( $header_raw ) {
	foreach ( starterkit_normalize_acf_group_rows( $header_raw ) as $header_row ) {
		$titel = $header_row['titel'] ?? null;
		if ( is_string( $titel ) ) {
			$label = starterkit_normalize_layout_label_text( $titel );
			if ( $label !== '' ) {
				return $label;
			}
			continue;
		}

		if ( ! is_array( $titel ) ) {
			continue;
		}

		$label = starterkit_get_titel_label_from_rows( $titel );
		if ( $label !== '' ) {
			return $label;
		}
	}

	return '';
}

/**
 * Flexible content layout title in the WordPress admin.
 *
 * @param string               $title  Default title.
 * @param array<string, mixed> $field  ACF field.
 * @param array<string, mixed> $layout Layout config.
 * @param int                  $i      Layout index.
 */
function starterkit_flexible_content_layout_title( $title, $field, $layout, $i ) {
	$field_name   = (string) ( $field['name'] ?? '' );
	$layout_name  = (string) ( $layout['name'] ?? '' );
	$layout_label = (string) ( $layout['label'] ?? $layout_name );
	$suffix       = $layout_label !== '' ? ' <span style="opacity: .7;font-weight:normal;">(' . esc_html( $layout_label ) . ')</span>' : '';

	if ( $layout_name === 'event' ) {
		$date_label = starterkit_normalize_layout_label_text( get_sub_field( 'date' ) );
		return $date_label !== '' ? esc_html( $date_label ) . $suffix : $title;
	}

	if ( $field_name === 'modules_content' && $layout_name === 'module_headline' ) {
		$label = starterkit_normalize_layout_label_text( get_sub_field( 'text' ) );
		return $label !== '' ? esc_html( $label ) . $suffix : $title;
	}

	if ( $field_name !== 'layouts' ) {
		return $title;
	}

	$titel = get_sub_field( 'titel' );
	$label = is_string( $titel )
		? starterkit_normalize_layout_label_text( $titel )
		: starterkit_get_titel_label_from_rows( $titel );
	if ( $label === '' ) {
		$label = starterkit_get_titel_label_from_header( get_sub_field( 'header' ) );
	}
	if ( $label === '' ) {
		$label = starterkit_normalize_layout_label_text( get_sub_field( 'headline' ) );
	}

	return $label !== '' ? esc_html( $label ) . $suffix : $title;
}
add_filter( 'acf/fields/flexible_content/layout_title', 'starterkit_flexible_content_layout_title', 10, 4 );
