<?php
/**
 * Contract for optional modules.
 *
 * @package Agency_Core
 */

namespace Agency_Core\Modules;

defined( 'ABSPATH' ) || exit;

interface Module_Interface {

	/**
	 * Hooks the module into WordPress. Called once, only if the module is enabled.
	 */
	public function register(): void;
}
