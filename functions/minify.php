<?php
/**
 * Conservative HTML minification for anonymous front-end visitors.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strip non-conditional HTML comments and whitespace between tags.
 */
class Baukasten_WP_Minifier {

	/**
	 * Start output buffering when the minifier is instantiated.
	 */
	public function __construct() {
		ob_start( array( $this, 'minify_buffer' ) );
	}

	/**
	 * @param string $buffer Full page HTML.
	 * @return string
	 */
	public function minify_buffer( $buffer ) {
		$is_logged_in_admin = is_user_logged_in() && current_user_can( 'manage_options' );

		if ( ! is_admin() && ! $is_logged_in_admin && ! is_customize_preview() && is_string( $buffer ) && strlen( $buffer ) > 0 ) {
			$buffer = preg_replace( '/<!--(?!\s*(?:\[if [^]]+\]|!|>))(?:(?!-->).)*-->/s', '', $buffer );
			$buffer = preg_replace( '/>\s+</', '><', $buffer );
		}

		return $buffer;
	}
}

add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
			return;
		}

		new Baukasten_WP_Minifier();
	},
	0
);
