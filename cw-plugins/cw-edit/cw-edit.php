<?php
/**
 * Plugin Name: CW Edit
 * Plugin URI:  https://github.com/your-repo/cw-edit
 * Description: Shows a fixed "Edit" link at the bottom-left on singular posts/pages for logged-in users with edit permission. Opens the backend edit screen in a new tab.
 * Version:     1.0.6
 * Author:      CW
 * Author URI:  https://example.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cw-edit
 */

defined( 'ABSPATH' ) || exit;

const CW_EDIT_USER_META_KEY = 'cw_edit_show_button';

/**
 * Post ID for the current singular view (works without the main loop, e.g. flexible pages).
 *
 * @return int
 */
function cw_edit_get_context_post_id() {
	if ( ! is_singular() ) {
		return 0;
	}

	$post_id = get_queried_object_id();
	if ( $post_id ) {
		return (int) $post_id;
	}

	return (int) get_the_ID();
}

/**
 * Whether the current user has chosen to show the CW Edit button (default: true).
 *
 * @param int|null $user_id User ID or null for current user.
 * @return bool
 */
function cw_edit_user_wants_button( $user_id = null ) {
	if ( ! $user_id && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}
	if ( ! $user_id ) {
		return true;
	}
	$value = get_user_meta( $user_id, CW_EDIT_USER_META_KEY, true );
	// Default to true (show) when not set, like admin bar.
	return ( $value !== 'false' && $value !== false );
}

/**
 * Whether the fixed Edit link should be shown on this request.
 *
 * @return bool
 */
function cw_edit_should_show_link() {
	if ( ! is_singular() || ! is_user_logged_in() || ! cw_edit_user_wants_button() ) {
		return false;
	}

	$post_id = cw_edit_get_context_post_id();

	return $post_id > 0 && current_user_can( 'edit_post', $post_id );
}

/**
 * Output the Edit link in the footer (works with any theme).
 */
function cw_edit_render_link() {
	if ( ! cw_edit_should_show_link() ) {
		return;
	}

	$post_id = cw_edit_get_context_post_id();

	$edit_url = get_edit_post_link( $post_id, 'raw' );
	if ( ! $edit_url ) {
		return;
	}

	?>
	<div class="cw-edit-link" role="group" aria-label="<?php esc_attr_e( 'Bearbeiten', 'cw-edit' ); ?>">
		<a href="<?php echo esc_url( admin_url() ); ?>" class="cw-edit-link__action cw-edit-link__action--same-window">
			<?php esc_html_e( 'Dashboard', 'cw-edit' ); ?>
			<span class="cw-edit-link__icon" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><title>gauge-2</title><g fill="#ffffff"><circle cx="10" cy="10" r="1" fill="#ffffff" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></circle><path d="m3.244,8.194c-.154.577-.244,1.181-.244,1.806,0,3.866,3.134,7,7,7s7-3.134,7-7-3.134-7-7-7c-.626,0-1.229.09-1.806.244" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><line x1="10" y1="10" x2="5" y2="5" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></line></g></svg>
			</span>
		</a>

		<a href="<?php echo esc_url( $edit_url ); ?>" class="cw-edit-link__action cw-edit-link__action--same-window">
			<?php esc_html_e( 'Bearbeiten', 'cw-edit' ); ?>
			<span class="cw-edit-link__icon" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><title>compose-2</title><g fill="#ffffff"><path d="m9.555,3h-3.555c-1.657,0-3,1.343-3,3v8c0,1.657,1.343,3,3,3h8c1.657,0,3-1.343,3-3v-3.545" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="m8,12l.5-3,5.707-5.699c.391-.391,1.024-.391,1.414,0l1.086,1.086c.391.391.391,1.024,0,1.414l-5.707,5.699-3,.5Z" fill="none" stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></g></svg>
			</span>
		</a>
	</div>
	<?php
}

add_action( 'wp_footer', 'cw_edit_render_link', 5 );

/**
 * Enqueue styles for the fixed Edit link.
 */
function cw_edit_enqueue_styles() {
	if ( ! cw_edit_should_show_link() ) {
		return;
	}

	wp_register_style( 'cw-edit', false, array() );
	wp_enqueue_style( 'cw-edit' );

	// High specificity + !important so theme styles don't override the fixed button.
	$css = '
		body .cw-edit-link {
			position: fixed !important;
			bottom: 10px !important;
			left: 10px !important;
			z-index: 999999 !important;
			display: inline-flex !important;
			align-items: center !important;
			flex-wrap: wrap !important;
			gap: 0 .4rem !important;
			padding: 0.4rem 0.4rem !important;
			font-size: 14px !important;
			line-height: 1.4 !important;
			background: #23282d !important;
			color: #fff !important;
			border-radius: 8px !important;
			box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
			font-family: inherit !important;
		}
		body .cw-edit-link a::after {
			content: none !important;
		}
		body a.cw-edit-link__action {
			display: inline-flex !important;
			align-items: center !important;
			gap: 0.5rem !important;
			padding: .35em .5em !important;
			color: #fff !important;
			text-decoration: none !important;
			border: none !important;
			border-radius: 3px !important;
		}
		body .cw-edit-link__icon {
			display: inline-flex !important;
			line-height: 0 !important;
		}
		body .cw-edit-link__icon svg {
			width: 1.15em !important;
			height: 1.15em !important;
			flex-shrink: 0 !important;
		}
		body a.cw-edit-link__action:hover,
		body a.cw-edit-link__action:focus {
			color: #fff !important;
			background: rgb(72, 72, 72) !important;
			border-radius: 3px !important;
		}
		@media screen and (max-width: 782px) {
			body.admin-bar .cw-edit-link {
				bottom: 3.5rem !important;
			}
		}
	';

	wp_add_inline_style( 'cw-edit', trim( $css ) );
}

add_action( 'wp_enqueue_scripts', 'cw_edit_enqueue_styles' );

/**
 * Enqueue styles for the user profile (admin) CW Edit field.
 */
function cw_edit_admin_styles( $hook ) {
	if ( $hook !== 'profile.php' && $hook !== 'user-edit.php' ) {
		return;
	}
	wp_add_inline_style( 'wp-admin', '.cw-backendedit { background: #1e1e1e; padding: 20px; margin-top: 30px; color: #fff; } .cw-backendedit strong { display: block; margin-bottom: 10px; }' );
}

add_action( 'admin_enqueue_scripts', 'cw_edit_admin_styles' );

/**
 * Add checkbox to user profile (Personal Options) to show/hide the CW Edit button.
 */
function cw_edit_user_profile_field( $user ) {
	$value = get_user_meta( $user->ID, CW_EDIT_USER_META_KEY, true );
	$checked = ( $value !== 'false' && $value !== false );
	?>
	<div class="cw-backendedit">
		<label for="cw_edit_show_button">
			<input name="cw_edit_show_button" type="checkbox" id="cw_edit_show_button" value="true" <?php checked( $checked ); ?>>
			<?php esc_html_e( 'Bearbeitungs-Button im Frontend anzeigen', 'cw-edit' ); ?>
		</label>
	</div>
	<?php
}

add_action( 'show_user_profile', 'cw_edit_user_profile_field' );
add_action( 'edit_user_profile', 'cw_edit_user_profile_field' );

/**
 * Save the CW Edit button preference when profile is updated.
 *
 * @param int $user_id User ID being updated.
 */
function cw_edit_save_user_profile_field( $user_id ) {
	if ( ! $user_id || ! get_userdata( $user_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
		return;
	}
	$value = isset( $_POST['cw_edit_show_button'] ) ? 'true' : 'false';
	update_user_meta( $user_id, CW_EDIT_USER_META_KEY, $value );
}

add_action( 'personal_options_update', 'cw_edit_save_user_profile_field' );
add_action( 'edit_user_profile_update', 'cw_edit_save_user_profile_field' );
