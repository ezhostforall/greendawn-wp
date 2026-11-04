<?php
/**
 * Small hardening defaults.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

// Do not advertise the WordPress version in markup.
remove_action( 'wp_head', 'wp_generator' );
