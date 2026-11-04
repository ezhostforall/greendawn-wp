<?php
/**
 * Diagnostics module: an administrator-only REST status endpoint. It also serves as the
 * reference for secure REST registration (explicit permission callback, no public data).
 *
 * @package Agency_Core
 */

namespace Agency_Core\Modules;

use Agency_Core\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Administrator-only status endpoint.
 */
final class Diagnostics implements Module_Interface {

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers GET /agency-core/v1/status.
	 */
	public function register_routes(): void {
		register_rest_route(
			'agency-core/v1',
			'/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => static fn(): bool => current_user_can( 'manage_options' ),
			)
		);
	}

	/**
	 * Returns non-sensitive environment information.
	 */
	public function get_status(): \WP_REST_Response {
		return rest_ensure_response(
			array(
				'plugin_version'   => AGENCY_CORE_VERSION,
				'environment'      => wp_get_environment_type(),
				'php_version'      => PHP_VERSION,
				'enabled_modules'  => Plugin::enabled_modules(),
				'outbound_allowed' => Plugin::outbound_allowed( 'diagnostics' ),
			)
		);
	}
}
