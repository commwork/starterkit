<?php
/**
 * Load Commwork plugins bundled in the theme (cw-plugins/).
 *
 * Skips each plugin if it is already loaded from wp-content/plugins.
 * Copy this folder into any theme and require cw-plugins/cw-plugins.php from functions.php.
 *
 * @package Commwork_Cw_Plugins
 * 
 * Installation – put this in functions.php:
 * /**
 * require_once get_template_directory() . '/cw-plugins/cw-plugins.php';
 * */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute path to bundled Commwork plugins (this directory).
 */
if ( ! defined( 'COMMWORK_CW_PLUGINS_DIR' ) ) {
	define( 'COMMWORK_CW_PLUGINS_DIR', __DIR__ );
}

/**
 * Text domain for loader strings (falls back to theme text domain).
 *
 * @return string
 */
function commwork_cw_plugins_text_domain() {
	static $domain = null;

	if ( null !== $domain ) {
		return $domain;
	}

	$theme_domain = wp_get_theme()->get( 'TextDomain' );
	$domain       = ( is_string( $theme_domain ) && $theme_domain !== '' )
		? $theme_domain
		: 'commwork-cw-plugins';

	return $domain;
}

/**
 * Cache-busting version for bundled cw-plugins assets.
 *
 * @return string
 */
function commwork_cw_plugins_asset_version() {
	static $version = null;

	if ( null !== $version ) {
		return $version;
	}

	$version = file_exists( __FILE__ ) ? (string) filemtime( __FILE__ ) : '1';

	return $version;
}

/**
 * @return array<string, array{file: string, boot_fn: string}>
 */
function commwork_cw_plugins_registry() {
	$plugins = array(
		'cw-edit' => array(
			'file'    => 'cw-edit/cw-edit.php',
			'boot_fn' => 'cw_edit_user_wants_button',
		),
	);

	/**
	 * Filter bundled Commwork plugins loaded from the theme.
	 *
	 * @param array<string, array{file: string, boot_fn: string}> $plugins
	 */
	return apply_filters( 'commwork_cw_plugins_registry', $plugins );
}

/**
 * Require bundled plugin main files.
 */
function commwork_cw_plugins_load() {
	foreach ( commwork_cw_plugins_registry() as $slug => $config ) {
		if ( empty( $config['file'] ) || empty( $config['boot_fn'] ) ) {
			continue;
		}

		if ( function_exists( $config['boot_fn'] ) ) {
			continue;
		}

		$path = COMMWORK_CW_PLUGINS_DIR . '/' . ltrim( $config['file'], '/' );
		if ( ! is_readable( $path ) ) {
			continue;
		}

		require_once $path;
	}
}

add_action( 'after_setup_theme', 'commwork_cw_plugins_load', 0 );

/**
 * Plugin metadata for a bundled Commwork plugin entry.
 *
 * @param string                               $slug   Registry key.
 * @param array{file: string, boot_fn: string} $config Registry config.
 * @return array{
 *     slug: string,
 *     name: string,
 *     version: string,
 *     description: string,
 *     status: string,
 *     status_label: string
 * }|null
 */
function commwork_cw_plugins_get_entry( $slug, $config ) {
	if ( empty( $config['file'] ) || empty( $config['boot_fn'] ) ) {
		return null;
	}

	$path = COMMWORK_CW_PLUGINS_DIR . '/' . ltrim( $config['file'], '/' );
	if ( ! is_readable( $path ) ) {
		return null;
	}

	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$meta    = get_plugin_data( $path, false, false );
	$boot_fn = $config['boot_fn'];
	$wp_file = ltrim( str_replace( '\\', '/', $config['file'] ), '/' );
	$status  = 'inactive';
	$label   = __( 'Nicht geladen', commwork_cw_plugins_text_domain() );

	if ( function_exists( 'is_plugin_active' ) && is_plugin_active( $wp_file ) ) {
		$status = 'wp-plugin';
		$label  = __( 'Als WordPress-Plugin aktiv', commwork_cw_plugins_text_domain() );
	} elseif ( function_exists( $boot_fn ) ) {
		$status = 'theme';
		$label  = __( 'Im Theme integriert (aktiv)', commwork_cw_plugins_text_domain() );
	}

	return array(
		'slug'         => (string) $slug,
		'name'         => ! empty( $meta['Name'] ) ? (string) $meta['Name'] : (string) $slug,
		'version'      => ! empty( $meta['Version'] ) ? (string) $meta['Version'] : '',
		'description'  => ! empty( $meta['Description'] ) ? (string) $meta['Description'] : '',
		'status'       => $status,
		'status_label' => $label,
	);
}

/**
 * @return array<int, array{slug: string, name: string, version: string, description: string, status: string, status_label: string}>
 */
function commwork_cw_plugins_get_integrated_entries() {
	$entries = array();

	foreach ( commwork_cw_plugins_registry() as $slug => $config ) {
		$entry = commwork_cw_plugins_get_entry( $slug, $config );
		if ( $entry !== null ) {
			$entries[] = $entry;
		}
	}

	return $entries;
}

/**
 * Styles for the integrated-plugins bar on plugins.php.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function commwork_cw_plugins_admin_assets( $hook_suffix ) {
	if ( $hook_suffix !== 'plugins.php' ) {
		return;
	}

	$css = '
		.cw-plugins-admin-bar {
			margin: 50px 20px 20px 2px;
			padding: 16px 20px;
			border: 1px solid #c3c4c7;
			border-left: 4px solid #e2001a;
			background: #fff;
			box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
		}
		.cw-plugins-admin-bar__title {
			margin: 0 0 4px;
			font-size: 14px;
			font-weight: 600;
		}
		.cw-plugins-admin-bar__intro {
			margin: 0 0 12px;
			color: #50575e;
		}
		.cw-plugins-admin-bar__list {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			margin: 0;
			padding: 0;
			list-style: none;
		}
		.cw-plugins-admin-bar__item {
			flex: 1 1 220px;
			margin: 0;
			padding: 12px 14px;
			border: 1px solid #dcdcde;
			border-radius: 4px;
			background: #f6f7f7;
		}
		.cw-plugins-admin-bar__item-head {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
			gap: 8px;
			margin-bottom: 6px;
		}
		.cw-plugins-admin-bar__name {
			margin: 0;
			font-size: 13px;
			font-weight: 600;
		}
		.cw-plugins-admin-bar__version {
			display: inline-block;
			padding: 2px 6px;
			border-radius: 999px;
			background: #fff;
			border: 1px solid #dcdcde;
			font-size: 11px;
			line-height: 1.4;
			color: #50575e;
		}
		.cw-plugins-admin-bar__badge {
			display: inline-block;
			padding: 2px 8px;
			border-radius: 999px;
			font-size: 11px;
			font-weight: 600;
			line-height: 1.4;
		}
		.cw-plugins-admin-bar__badge--theme {
			background: #e8f4ea;
			color: #1e6b2d;
		}
		.cw-plugins-admin-bar__badge--wp-plugin {
			background: #e7f3ff;
			color: #135e96;
		}
		.cw-plugins-admin-bar__badge--inactive {
			background: #f0f0f1;
			color: #646970;
		}
		.cw-plugins-admin-bar__description {
			margin: 0;
			font-size: 12px;
			line-height: 1.45;
			color: #50575e;
		}
	';

	wp_register_style( 'commwork-cw-plugins-admin', false, array(), commwork_cw_plugins_asset_version() );
	wp_enqueue_style( 'commwork-cw-plugins-admin' );
	wp_add_inline_style( 'commwork-cw-plugins-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'commwork_cw_plugins_admin_assets' );

/**
 * Top bar on plugins.php listing theme-integrated plugins.
 */
function commwork_cw_plugins_render_plugins_page_bar() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->id !== 'plugins' ) {
		return;
	}

	$entries = commwork_cw_plugins_get_integrated_entries();
	if ( empty( $entries ) ) {
		return;
	}
	?>
	<div class="cw-plugins-admin-bar">
		<p class="cw-plugins-admin-bar__title">
			<?php esc_html_e( 'Im Theme integrierte Plugins', commwork_cw_plugins_text_domain() ); ?>
		</p>
		<p class="cw-plugins-admin-bar__intro">
			<?php esc_html_e( 'Diese Plugins liegen im Theme-Ordner cw-plugins und werden dort geladen, sofern sie nicht bereits als reguläres WordPress-Plugin aktiv sind.', commwork_cw_plugins_text_domain() ); ?>
		</p>
		<ul class="cw-plugins-admin-bar__list">
			<?php foreach ( $entries as $entry ) : ?>
				<li class="cw-plugins-admin-bar__item">
					<div class="cw-plugins-admin-bar__item-head">
						<p class="cw-plugins-admin-bar__name"><?php echo esc_html( $entry['name'] ); ?></p>
						<?php if ( $entry['version'] !== '' ) : ?>
							<span class="cw-plugins-admin-bar__version">
								<?php
								/* translators: %s: plugin version number */
								printf( esc_html__( 'Version %s', commwork_cw_plugins_text_domain() ), esc_html( $entry['version'] ) );
								?>
							</span>
						<?php endif; ?>
						<span class="cw-plugins-admin-bar__badge cw-plugins-admin-bar__badge--<?php echo esc_attr( $entry['status'] ); ?>">
							<?php echo esc_html( $entry['status_label'] ); ?>
						</span>
					</div>
					<?php if ( $entry['description'] !== '' ) : ?>
						<p class="cw-plugins-admin-bar__description"><?php echo esc_html( $entry['description'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
add_action( 'admin_notices', 'commwork_cw_plugins_render_plugins_page_bar', 5 );
