<?php
/**
 * Section titles, teaser headline levels, and admin heading debug tools.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current user should see frontend heading debug tools.
 */
function starterkit_user_can_see_heading_labels() {
	if ( is_admin() || ! is_user_logged_in() ) {
		return false;
	}

	$user = wp_get_current_user();

	return $user->exists() && $user->user_login === 'awo-vorp-henning';
}

/**
 * Do not serve a cached HTML document for the heading-labels debug user (toggle + body classes are user-specific).
 */
function starterkit_heading_labels_nocache_for_admins() {
	if ( starterkit_user_can_see_heading_labels() && ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
}
add_action( 'template_redirect', 'starterkit_heading_labels_nocache_for_admins', 0 );

/**
 * Admin-only heading level labels (H1–H4) in #main — toggled via footer button.
 */
function starterkit_body_class_heading_labels( $classes ) {
	if ( starterkit_user_can_see_heading_labels() ) {
		$classes[] = 'starterkit-heading-labels';
		$classes[] = 'starterkit-heading-labels--on';
	}
	return $classes;
}
add_filter( 'body_class', 'starterkit_body_class_heading_labels' );

/**
 * Fixed toggle button in the page footer (same pattern as cw-edit).
 */
function starterkit_render_heading_labels_toggle() {
	if ( ! starterkit_user_can_see_heading_labels() ) {
		return;
	}
	?>
	<div class="starterkit-heading-labels-toggle">
		<button
			type="button"
			class="starterkit-heading-labels-toggle__button"
			id="starterkit-heading-labels-toggle"
			aria-pressed="true"
		>
			<?php esc_html_e( 'Überschriften-Labels ausblenden', 'starterkit' ); ?>
		</button>
	</div>
	<?php
}
add_action( 'wp_footer', 'starterkit_render_heading_labels_toggle', 5 );

/**
 * Enqueue heading label toggle assets for the debug user.
 */
function starterkit_enqueue_heading_labels_tools() {
	if ( ! starterkit_user_can_see_heading_labels() ) {
		return;
	}

	starterkit_enqueue_theme_style( 'starterkit-admin-frontend', 'admin-frontend.css', array( 'starterkit_styles' ) );
	starterkit_enqueue_theme_script( 'starterkit-heading-labels-toggle', 'assets/js/heading-labels-toggle.js' );
}
add_action( 'wp_enqueue_scripts', 'starterkit_enqueue_heading_labels_tools' );

/**
 * h1 for the first section `titel` on the page, h2 for every later one (not on the front page).
 *
 * @param bool $consume When false, peek without reserving the page h1 slot.
 * @return string `h1` or `h2`
 */
function starterkit_section_titel_heading_tag( $consume = true ) {
	static $page_titel_h1_used = false;

	if ( is_front_page() ) {
		return 'h2';
	}

	if ( ! $page_titel_h1_used ) {
		if ( $consume ) {
			$page_titel_h1_used = true;
		}
		return 'h1';
	}

	return 'h2';
}

/**
 * @param string $tag `h1` or `h2`.
 * @return string
 */
function starterkit_section_titel_heading_class( $tag ) {
	return $tag === 'h1' ? 'h1 section-title' : 'section-title';
}

/**
 * Uses get_sub_field( 'header' ) only (does not call have_rows, so the partial can loop safely).
 *
 * @return bool
 */
function starterkit_layout_row_has_header_content() {
	foreach ( starterkit_normalize_acf_group_rows( get_sub_field( 'header' ) ) as $row ) {
		if ( ! empty( $row['subheadline'] ) || ! empty( $row['intro'] ) || ! empty( $row['titel'] ) ) {
			return true;
		}

		if ( empty( $row['buttons'] ) || ! is_array( $row['buttons'] ) ) {
			continue;
		}

		foreach ( $row['buttons'] as $button_row ) {
			$url = is_array( $button_row ) && isset( $button_row['url'] ) ? $button_row['url'] : $button_row;
			if ( is_array( $url ) && ! empty( $url['url'] ) ) {
				return true;
			}
		}
	}

	return false;
}
