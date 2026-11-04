<?php
/**
 * Agency Core settings screen.
 *
 * @package Agency_Core
 */

namespace Agency_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Allows administrators to enable Services and discover its editing dependency.
 */
final class Settings {

	/**
	 * Registers the admin settings hooks.
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'add_page' ) );
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
		add_action( 'add_option_agency_core_services_enabled', array( self::class, 'invalidate_rewrites' ) );
		add_action( 'update_option_agency_core_services_enabled', array( self::class, 'invalidate_rewrites' ) );
	}

	/**
	 * Adds the page to Settings.
	 */
	public static function add_page(): void {
		add_options_page(
			__( 'Agency Core', 'agency-core' ),
			__( 'Agency Core', 'agency-core' ),
			'manage_options',
			'agency-core',
			array( self::class, 'render' )
		);
	}

	/**
	 * Registers the Services setting with WordPress.
	 */
	public static function register_settings(): void {
		register_setting(
			'agency_core_settings',
			'agency_core_services_enabled',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);
	}

	/**
	 * Lets WordPress rebuild the rules on the next request with the new module state.
	 */
	public static function invalidate_rewrites(): void {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Renders the settings form and the SCF setup link.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$enabled = in_array( 'services', Plugin::enabled_modules(), true );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Agency Core', 'agency-core' ); ?></h1>
			<?php if ( $enabled && ! function_exists( 'acf_add_local_field_group' ) ) : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'Install and activate Secure Custom Fields to edit Service descriptions, benefits, FAQs and calls to action.', 'agency-core' ); ?></p>
					<p>
						<?php if ( current_user_can( 'install_plugins' ) ) : ?>
							<a href="
							<?php
							echo esc_url(
								add_query_arg(
									array(
										's'    => 'Secure Custom Fields',
										'tab'  => 'search',
										'type' => 'term',
									),
									admin_url( 'plugin-install.php' )
								)
							);
							?>
										"><?php esc_html_e( 'Find Secure Custom Fields', 'agency-core' ); ?></a>
						<?php endif; ?>
						<?php if ( current_user_can( 'activate_plugins' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'View installed plugins', 'agency-core' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php settings_fields( 'agency_core_settings' ); ?>
				<p>
					<label>
						<input type="checkbox" name="agency_core_services_enabled" value="1" <?php checked( get_option( 'agency_core_services_enabled', false ) ); ?> />
						<?php esc_html_e( 'Enable Services', 'agency-core' ); ?>
					</label>
				</p>
				<?php if ( $enabled && ! get_option( 'agency_core_services_enabled', false ) ) : ?>
					<p><?php esc_html_e( 'Services is enabled by this site’s configuration.', 'agency-core' ); ?></p>
				<?php endif; ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
