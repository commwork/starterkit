<?php
/**
 * Admin-only viewport breakpoint indicator (matches assets/scss/_2-mixins.scss).
 *
 * @package Baukasten
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current user should see the responsive breakpoint helper.
 */
function starterkit_user_can_see_responsive_control() {
	return ! is_admin() && is_user_logged_in() && current_user_can( 'manage_options' );
}

/**
 * Fixed label showing the active *-up breakpoint mixin.
 */
function starterkit_render_responsive_control() {
	if ( ! starterkit_user_can_see_responsive_control() ) {
		return;
	}

	$labels = array(
		'xs'   => 'xs-up (0+)',
		'sm'   => 'sm-up (576px+)',
		'md'   => 'md-up (768px+)',
		'lg'   => 'lg-up (992px+)',
		'xl'   => 'xl-up (1200px+)',
		'xxl'  => 'xxl-up (1400px+)',
		'xxxl' => 'xxxl-up (2000px+)',
	);
	?>
	<div class="responsive-control" aria-live="polite" aria-label="<?php esc_attr_e( 'Aktiver Breakpoint', 'starterkit' ); ?>">
		<?php foreach ( $labels as $slug => $label ) : ?>
			<span class="responsive-control__bp responsive-control__bp--<?php echo esc_attr( $slug ); ?>">
				<?php echo esc_html( $label ); ?>
			</span>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Styles for admin frontend debug tools.
 */
function starterkit_enqueue_responsive_control_assets() {
	if ( ! starterkit_user_can_see_responsive_control() ) {
		return;
	}

	starterkit_enqueue_theme_style( 'starterkit-admin-frontend', 'admin-frontend.css', array( 'starterkit_styles' ) );
}
add_action( 'wp_enqueue_scripts', 'starterkit_enqueue_responsive_control_assets' );
