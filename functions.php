<?php
/**
 * Theme bootstrap.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $content_width ) ) {
	$content_width = 1000;
}

/**
 * Theme setup.
 */
function starterkit_setup() {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		)
	);

	register_nav_menus(
		array(
			'primary'            => esc_html__( 'Main', 'starterkit' ),
			'primary-additional' => esc_html__( 'Main Additional', 'starterkit' ),
			'sidebar'            => esc_html__( 'Sidebar', 'starterkit' ),
			'footer'             => esc_html__( 'Footer', 'starterkit' ),
		)
	);
}
add_action( 'after_setup_theme', 'starterkit_setup' );

/**
 * Add `has-post-thumbnail` when the current singular post has a featured image.
 */
function starterkit_body_class_has_thumbnail( $classes ) {
	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$classes[] = 'has-post-thumbnail';
	}
	return $classes;
}
add_filter( 'body_class', 'starterkit_body_class_has_thumbnail' );

/**
 * Register custom image sizes.
 */
function starterkit_add_image_sizes() {
	add_image_size( 'img-large', 2048, 1152, true );
	add_image_size( 'img-medium', 1300, 731, true );
	add_image_size( 'img-small', 640, 360, true );
	add_image_size( 'img-square', 500, 500, true );
	add_image_size( 'img-square-large', 1000, 1000, true );
	add_image_size( 'img-s', 992, 627, true );
}
add_action( 'after_setup_theme', 'starterkit_add_image_sizes' );

/**
 * Email Address Encoder: make it work with ACF and all theme output.
 *
 * @see https://wordpress.org/plugins/email-address-encoder/
 */
function starterkit_email_encoder_buffer_start() {
	if ( ! is_admin() && ! is_customize_preview() && function_exists( 'eae_encode_emails' ) ) {
		ob_start( 'eae_encode_emails' );
	}
}
if ( function_exists( 'eae_encode_emails' ) ) {
	add_filter( 'acf_the_content', 'eae_encode_emails' );
	add_action( 'template_redirect', 'starterkit_email_encoder_buffer_start', 0 );
}

/**
 * Replace UpdraftPlus custom PNG menu icon with a Dashicon.
 */
function starterkit_updraftplus_menu_icon() {
	global $menu;

	if ( empty( $menu ) || ! is_array( $menu ) ) {
		return;
	}

	foreach ( $menu as $key => $item ) {
		if ( empty( $item[2] ) || 'options-general.php?page=updraftplus' !== $item[2] ) {
			continue;
		}

		$menu[ $key ][6] = 'dashicons-update';
		break;
	}
}
add_action( 'admin_menu', 'starterkit_updraftplus_menu_icon', 999 );

/**
 * Replace the eight default ACF color-picker swatches with the CI colors from assets/scss/_1-vars.scss.
 */
function starterkit_acf_color_picker_palette() {
	$colors = array(
		'#9e74d9', // primary
		'#2ab35e', // secondary
		'#90cee2', // tertiary
		'#ffffff', // white
		'#191919', // black
		'#f3f3f3', // grey
		'#dcdcdc', // grey-medium
		'#c5c5c5', // grey-dark
	);
	?>
	<script>
		acf.addFilter( 'color_picker_args', function( args ) {
			args.palettes = <?php echo wp_json_encode( $colors ); ?>;
			return args;
		} );
	</script>
	<?php
}
add_action( 'acf/input/admin_footer', 'starterkit_acf_color_picker_palette' );

$theme_functions = get_template_directory() . '/functions/';

require_once $theme_functions . 'helpers.php';
require_once $theme_functions . 'quotes.php';
require_once $theme_functions . 'responsive-control.php';
require_once $theme_functions . 'headings.php';
require_once $theme_functions . 'enqueue.php';
require_once $theme_functions . 'cpt.php';
require_once $theme_functions . 'acf.php';
require_once $theme_functions . 'search.php';
require_once $theme_functions . 'veranstaltungen.php';
require_once get_template_directory() . '/cw-plugins/cw-plugins.php';
require_once $theme_functions . 'minify.php';
