<?php
/**
 * Plugin bootstrap and module registry.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core;

use Greendawn_Core\Modules\Module_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Loads only the modules a site explicitly enables.
 */
final class Plugin {

	/**
	 * Modules shipped with the plugin (slug => class). Sites may add their own via the
	 * `greendawn_core_available_modules` filter.
	 *
	 * @return array<string,class-string<Module_Interface>>
	 */
	public static function available_modules(): array {
		return (array) apply_filters(
			'greendawn_core_available_modules',
			array(
				'diagnostics' => Modules\Diagnostics::class,
				'services'    => Modules\Services::class,
			)
		);
	}

	/**
	 * Module slugs enabled for this site. Empty by default, so the plugin costs next to
	 * nothing until a project opts in, for example:
	 * add_filter( 'greendawn_core_enabled_modules', fn() => array( 'diagnostics' ) );
	 *
	 * @return string[]
	 */
	public static function enabled_modules(): array {
		$enabled = (array) apply_filters( 'greendawn_core_enabled_modules', array() );
		if ( get_option( 'greendawn_core_services_enabled', false ) ) {
			$enabled[] = 'services';
		}
		return array_values( array_unique( array_map( 'strval', $enabled ) ) );
	}

	/**
	 * Instantiates and registers the enabled modules.
	 */
	public static function boot(): void {
		if ( is_admin() ) {
			Settings::register();
		}

		( new Modules\Business_Homepage() )->register();
		( new Modules\Greendawn_Homepage() )->register();
		( new Modules\Greendawn_Footer() )->register();
		( new Modules\Lead_Capture() )->register();
		( new Modules\Contact_Page() )->register();
		( new Modules\About_Page() )->register();
		( new Modules\Greendawn_About() )->register();
		( new Modules\Greendawn_Workplace() )->register();
		( new Modules\Greendawn_Fleet() )->register();
		( new Modules\Greendawn_Public_Charging() )->register();
		( new Modules\Greendawn_Aftercare() )->register();
		( new Modules\Greendawn_Enquire() )->register();
		( new Modules\Greendawn_Business() )->register();
		( new Modules\Greendawn_Customer_Care() )->register();
		( new Modules\EV_Chargers() )->register();
		( new Modules\Team_Members() )->register();

		$available = self::available_modules();
		foreach ( self::enabled_modules() as $slug ) {
			$class_name = $available[ $slug ] ?? null;
			if ( $class_name && is_a( $class_name, Module_Interface::class, true ) ) {
				( new $class_name() )->register();
			}
		}
	}

	/**
	 * Whether outbound requests (webhooks, third-party delivery) are allowed. Modules that
	 * send data off-site must check this. Defaults to production only.
	 *
	 * @param string $context Short identifier of the caller, e.g. 'webhook'.
	 */
	public static function outbound_allowed( string $context = '' ): bool {
		return (bool) apply_filters(
			'greendawn_core_allow_outbound',
			'production' === wp_get_environment_type(),
			$context
		);
	}
}
