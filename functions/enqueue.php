<?php
/**
 * Front-end and admin asset enqueue.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue theme scripts and styles.
 */
function starterkit_scripts() {
	wp_deregister_script( 'jquery' );
	wp_register_script(
		'jquery',
		get_template_directory_uri() . '/assets/js/jquery.min.js',
		array(),
		'4.0.0',
		false
	);
	wp_enqueue_script( 'jquery' );

	wp_enqueue_style(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css',
		array(),
		'12',
		'all'
	);
	wp_enqueue_script(
		'swiper',
		'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
		array(),
		'12',
		true
	);

	$theme_scripts = array(
		array( 'starterkit_appJs', 'assets/js/app.js', array( 'jquery', 'swiper' ) ),
		array( 'starterkit_chronicle', 'template-parts/layout-parts/heroes/chronicle/chronicle.js', array() ),
		array( 'starterkit_slider', 'template-parts/modules/slider/slider.js', array( 'swiper' ) ),
		array( 'starterkit_accordion', 'template-parts/modules/accordion/accordion.js', array() ),
		array( 'starterkit_process_module', 'template-parts/modules/process/process.js', array() ),
		array( 'starterkit_process', 'template-parts/layout-parts/heroes/process/process.js', array( 'swiper' ) ),
		array( 'starterkit_news_slider', 'template-parts/layout-parts/heroes/news_slider/news-slider.js', array( 'swiper' ) ),
		array( 'starterkit_jobs_filters', 'template-parts/layout-parts/heroes/jobs_with_filters/jobs-filters.js', array() ),
	);

	foreach ( $theme_scripts as $script ) {
		starterkit_enqueue_theme_script( $script[0], $script[1], $script[2] );
	}

	wp_enqueue_style(
		'starterkit-dm-sans',
		'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap',
		array(),
		null
	);

	starterkit_enqueue_theme_style( 'starterkit_bootstrap', 'assets/vendor/bootstrap/bootstrap-grid.min.css' );
	starterkit_enqueue_theme_style( 'starterkit_styles', 'style.css', array( 'starterkit-dm-sans' ) );
}

/**
 * Preconnect to Google Fonts before the stylesheet request.
 *
 * @param array<int, string|array<string, string>> $urls          Resource hints.
 * @param string                                    $relation_type Relation type.
 * @return array<int, string|array<string, string>>
 */
function starterkit_font_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type ) {
		return $urls;
	}

	$urls[] = array(
		'href' => 'https://fonts.googleapis.com',
	);
	$urls[] = array(
		'href'        => 'https://fonts.gstatic.com',
		'crossorigin' => 'anonymous',
	);

	return $urls;
}
add_filter( 'wp_resource_hints', 'starterkit_font_resource_hints', 10, 2 );
add_action( 'wp_enqueue_scripts', 'starterkit_scripts' );

/**
 * Admin ACF styles and empty-state script.
 */
function starterkit_admin_acf_empty_state_script() {
	if ( ! is_admin() || ! function_exists( 'get_field' ) ) {
		return;
	}

	if ( file_exists( get_template_directory() . '/admin.css' ) ) {
		starterkit_enqueue_theme_style( 'starterkit_admin_styles', 'admin.css', array( 'acf-pro-input' ) );
	}

	starterkit_enqueue_theme_script( 'starterkit_acf_empty_state', 'assets/js/admin.js', array( 'jquery', 'acf-input' ) );
}
add_action( 'admin_enqueue_scripts', 'starterkit_admin_acf_empty_state_script', 20 );

/**
 * Disable CF7 refill REST calls when page caching is enabled (WP_CACHE).
 */
function starterkit_cf7_disable_refill() {
	if ( is_admin() || ! function_exists( 'wpcf7' ) || ! wp_script_is( 'contact-form-7', 'enqueued' ) ) {
		return;
	}
	?>
	<script>
	if ( typeof wpcf7 !== 'undefined' ) {
		wpcf7.cached = 0;
	}
	</script>
	<?php
}
add_action( 'wp_footer', 'starterkit_cf7_disable_refill', 99 );
