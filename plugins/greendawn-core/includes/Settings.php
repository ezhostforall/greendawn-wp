<?php
/**
 * Greendawn Core settings screen.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core;

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
		add_action( 'add_option_greendawn_core_services_enabled', array( self::class, 'invalidate_rewrites' ) );
		add_action( 'update_option_greendawn_core_services_enabled', array( self::class, 'invalidate_rewrites' ) );
	}

	/**
	 * Adds the page to Settings.
	 */
	public static function add_page(): void {
		add_options_page(
			__( 'Greendawn Core', 'greendawn-core' ),
			__( 'Greendawn Core', 'greendawn-core' ),
			'manage_options',
			'greendawn-core',
			array( self::class, 'render' )
		);
	}

	/**
	 * Registers the Services setting with WordPress.
	 */
	public static function register_settings(): void {
		register_setting(
			'greendawn_core_settings',
			'greendawn_core_services_enabled',
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
			<h1><?php esc_html_e( 'Greendawn Core', 'greendawn-core' ); ?></h1>
			<?php if ( $enabled && ! function_exists( 'acf_add_local_field_group' ) ) : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'Install and activate Secure Custom Fields to edit Service descriptions, benefits, FAQs and calls to action.', 'greendawn-core' ); ?></p>
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
										"><?php esc_html_e( 'Find Secure Custom Fields', 'greendawn-core' ); ?></a>
						<?php endif; ?>
						<?php if ( current_user_can( 'activate_plugins' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'View installed plugins', 'greendawn-core' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php settings_fields( 'greendawn_core_settings' ); ?>
				<p>
					<label>
						<input type="checkbox" name="greendawn_core_services_enabled" value="1" <?php checked( get_option( 'greendawn_core_services_enabled', false ) ); ?> />
						<?php esc_html_e( 'Enable Services', 'greendawn-core' ); ?>
					</label>
				</p>
				<?php if ( $enabled && ! get_option( 'greendawn_core_services_enabled', false ) ) : ?>
					<p><?php esc_html_e( 'Services is enabled by this site’s configuration.', 'greendawn-core' ); ?></p>
				<?php endif; ?>
				<?php submit_button(); ?>
			</form>
			<h2><?php esc_html_e( 'Callback enquiry drawer', 'greendawn-core' ); ?></h2>
			<?php if ( ! class_exists( '\WPCF7_ContactForm' ) ) : ?>
				<p><?php esc_html_e( 'Activate Contact Form 7 to configure callback requests.', 'greendawn-core' ); ?></p>
			<?php else : ?>
				<?php $callback_id = absint( get_option( 'greendawn_core_callback_form', 0 ) ); ?>
				<?php if ( ! $callback_id || ! wpcf7_contact_form( $callback_id ) ) : ?>
					<p><?php esc_html_e( 'Create the dedicated callback form, check its Mail recipient in Contact Form 7, then enable the drawer. Existing contact forms are preserved.', 'greendawn-core' ); ?></p>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="greendawn_core_create_callback">
						<?php wp_nonce_field( 'greendawn_core_create_callback' ); ?>
						<?php submit_button( __( 'Create callback form', 'greendawn-core' ), 'secondary' ); ?>
					</form>
				<?php else : ?>
					<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcf7&post=' . $callback_id . '&action=edit' ) ); ?>"><?php esc_html_e( 'Review callback email recipient and delivery settings', 'greendawn-core' ); ?></a></p>
					<p><?php esc_html_e( 'Enable callback requests only after checking the recipient, sender and privacy policy. The initial recipient is the WordPress administration email. Delivery uses the existing Contact Form 7 mail and spam integrations.', 'greendawn-core' ); ?></p>
					<form action="options.php" method="post">
						<?php settings_fields( 'greendawn_core_callback_settings' ); ?>
						<input type="hidden" name="greendawn_core_callback_enabled" value="0">
						<label><input type="checkbox" name="greendawn_core_callback_enabled" value="1" <?php checked( get_option( 'greendawn_core_callback_enabled', false ) ); ?>> <?php esc_html_e( 'Enable the sitewide callback drawer', 'greendawn-core' ); ?></label>
						<?php submit_button( __( 'Save callback settings', 'greendawn-core' ) ); ?>
					</form>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}
}
