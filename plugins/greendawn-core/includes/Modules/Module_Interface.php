<?php
/**
 * Contract for optional modules.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

interface Module_Interface {

	/**
	 * Hooks the module into WordPress. Called once, only if the module is enabled.
	 */
	public function register(): void;
}
