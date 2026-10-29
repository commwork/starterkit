<?php
/**
 * Shared theme helpers.
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version from a file under the theme directory.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return int|null Filemtime or null when the file is missing.
 */
function starterkit_theme_asset_version( $relative_path ) {
	$path = get_template_directory() . '/' . ltrim( $relative_path, '/' );

	return file_exists( $path ) ? filemtime( $path ) : null;
}

/**
 * Enqueue a theme script with filemtime-based cache busting.
 *
 * @param string $handle        Script handle.
 * @param string $relative_path Path relative to the theme root.
 * @param array  $deps          Script dependencies.
 * @param bool   $in_footer     Load in footer.
 */
function starterkit_enqueue_theme_script( $handle, $relative_path, $deps = array(), $in_footer = true ) {
	wp_enqueue_script(
		$handle,
		get_template_directory_uri() . '/' . ltrim( $relative_path, '/' ),
		$deps,
		starterkit_theme_asset_version( $relative_path ),
		$in_footer
	);
}

/**
 * Enqueue a theme stylesheet with filemtime-based cache busting.
 *
 * @param string $handle        Style handle.
 * @param string $relative_path Path relative to the theme root.
 * @param array  $deps          Style dependencies.
 */
function starterkit_enqueue_theme_style( $handle, $relative_path, $deps = array() ) {
	wp_enqueue_style(
		$handle,
		get_template_directory_uri() . '/' . ltrim( $relative_path, '/' ),
		$deps,
		starterkit_theme_asset_version( $relative_path ),
		'all'
	);
}

/**
 * Normalize ACF group/repeater raw values to a list of row arrays.
 *
 * @param mixed $raw Raw field value.
 * @return array<int, array<string, mixed>>
 */
function starterkit_normalize_acf_group_rows( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	if ( isset( $raw[0] ) && is_array( $raw[0] ) ) {
		return $raw;
	}

	return array( $raw );
}

/**
 * Sanitize a jump-link id for use as an HTML id attribute.
 *
 * @param mixed $raw Raw field value (editors may include a leading #).
 * @return string Empty string when invalid.
 */
function starterkit_sanitize_jump_link_id( $raw ) {
	$id = trim( (string) $raw );

	if ( $id === '' ) {
		return '';
	}

	$id = ltrim( $id, '#' );

	return sanitize_title( $id );
}

/**
 * Jump-link id from the current `layouts` row `header` clone (`id_for_jump_links`).
 *
 * @param string $fallback Used when the field is empty.
 * @return string
 */
function starterkit_get_layout_row_jump_link_id( $fallback = '' ) {
	foreach ( starterkit_normalize_acf_group_rows( get_sub_field( 'header' ) ) as $row ) {
		$id = starterkit_sanitize_jump_link_id( $row['id_for_jump_links'] ?? '' );
		if ( $id !== '' ) {
			return $id;
		}
	}

	return starterkit_sanitize_jump_link_id( $fallback );
}

/**
 * HTML id attribute for a layout section anchor.
 *
 * @param string $fallback Used when `id_for_jump_links` is empty.
 * @return string ` id="..."` or empty string.
 */
function starterkit_layout_row_jump_link_id_attr( $fallback = '' ) {
	$id = starterkit_get_layout_row_jump_link_id( $fallback );

	if ( $id === '' ) {
		return '';
	}

	return ' id="' . esc_attr( $id ) . '"';
}

/**
 * Render flexible content modules; `y-space` adds `--spacing-l` before the next sibling `.layout-module`.
 *
 * @param string $field_name ACF flexible content field name.
 * @param string $parts_base Template path prefix without trailing slash.
 */
function starterkit_render_modules_loop( $field_name = 'modules_content', $parts_base = 'template-parts/modules' ) {
	if ( ! have_rows( $field_name ) ) {
		return;
	}

	$apply_y_space = false;

	while ( have_rows( $field_name ) ) :
		the_row();

		$module_layout = get_row_layout();

		if ( $module_layout === 'y-space' ) {
			$apply_y_space = true;
			continue;
		}

		if ( ! is_string( $module_layout ) || $module_layout === '' ) {
			$apply_y_space = false;
			continue;
		}

		$module_slug = $parts_base . '/' . $module_layout . '/' . $module_layout;

		if ( ! locate_template( $module_slug . '.php', false, false ) ) {
			$apply_y_space = false;
			continue;
		}

		$wrapper_classes = array( 'layout-module' );
		if ( $apply_y_space ) {
			$wrapper_classes[] = 'layout-module--y-space-before';
			$apply_y_space     = false;
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
			<?php get_template_part( $module_slug ); ?>
		</div>
		<?php
	endwhile;
}

/**
 * Parse ACF time (H:i) into hour/minute hand rotation degrees (12 at top, clockwise).
 *
 * @param string $time Time string, e.g. "14:30".
 * @return array{hour: float, minute: float}|null
 */
function starterkit_get_clock_hand_angles_from_time( $time ) {
	$time = trim( (string) $time );
	if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', $time, $matches ) ) {
		return null;
	}

	$hour   = min( 23, max( 0, (int) $matches[1] ) );
	$minute = min( 59, max( 0, (int) $matches[2] ) );
	$h12    = $hour % 12;

	return array(
		'minute' => $minute * 6,
		'hour'   => ( $h12 * 30 ) + ( $minute * 0.5 ),
	);
}

/**
 * Inline analog clock SVG (based on assets/images/icons/clocks-with-time/clock-0000.svg).
 *
 * @param string $time  Time string, e.g. "14:30".
 * @param string $label Accessible label; defaults to $time.
 * @return string Markup or empty string.
 */
function starterkit_render_process_clock_svg( $time, $label = '' ) {
	$angles = starterkit_get_clock_hand_angles_from_time( $time );
	if ( null === $angles ) {
		return '';
	}

	$label = '' !== trim( (string) $label ) ? trim( (string) $label ) : trim( (string) $time );

	static $index = 0;
	++$index;
	$mask_id = 'process-clock-mask-' . $index;

	ob_start();
	?>
	<svg class="process-clock" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<mask id="<?php echo esc_attr( $mask_id ); ?>" maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox" x="0" y="0" width="1" height="1">
			<svg viewBox="0 0 24 24" width="1" height="1" preserveAspectRatio="xMidYMid meet">
			<circle cx="12" cy="12" r="10" fill="none" stroke="#fff" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></circle>
			<g class="process-clock__hand process-clock__hand--minute" style="--hand-angle: <?php echo esc_attr( (string) $angles['minute'] ); ?>deg;">
				<path d="M12 12 L12.00 7.00" fill="none" stroke="#fff" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
			</g>
			<g class="process-clock__hand process-clock__hand--hour" style="--hand-angle: <?php echo esc_attr( (string) $angles['hour'] ); ?>deg;">
				<path d="M12 12 L12.00 8.20" fill="none" stroke="#fff" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
			</g>
			</svg>
		</mask>
	</svg>
	<span class="process-clock__shape" style="<?php echo esc_attr( '--process-clock-mask: url(#' . $mask_id . ');' ); ?>" role="img" aria-label="<?php echo esc_attr( $label ); ?>"></span>
	<?php
	return ob_get_clean();
}

/**
 * Label for links to a single news post. Option field `to_article`.
 *
 * @return string
 */
function starterkit_to_article_label() {
	static $label = null;

	if ( null !== $label ) {
		return $label;
	}

	$label = trim( (string) get_field( 'to_article', 'option' ) );
	if ( $label === '' ) {
		$label = __( 'Zum Artikel', 'starterkit' );
	}

	return $label;
}

/**
 * URL of the news overview. Option field `url_news_page`, otherwise the posts page.
 *
 * @return string
 */
function starterkit_news_archive_url() {
	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	$url = trim( (string) get_field( 'url_news_page', 'option' ) );
	if ( $url !== '' ) {
		return $url;
	}

	$url           = home_url( '/' );
	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( $posts_page_id > 0 ) {
		$posts_page = get_post( $posts_page_id );
		if ( $posts_page instanceof WP_Post && 'publish' === $posts_page->post_status ) {
			$url = get_permalink( $posts_page_id );
		}
	}

	return $url;
}
