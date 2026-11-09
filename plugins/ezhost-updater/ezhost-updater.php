<?php
/**
 * Plugin Name: EZ Host Updater
 * Description: Native WordPress update checks for explicitly configured GitHub Releases.
 * Version: 0.6.18
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Update URI: https://updates.ezhostforall.invalid/ezhost-updater
 * Author: EZ Host For All
 * License: GPL-2.0-or-later
 * Text Domain: ezhost-updater
 *
 * @package EZHost_Updater
 */

defined( 'ABSPATH' ) || exit;

define( 'EZHOST_UPDATER_VERSION', '0.6.18' );
require_once __DIR__ . '/includes/Updater.php';

add_action( 'plugins_loaded', array( \EZHost_Updater\Updater::class, 'register' ) );
