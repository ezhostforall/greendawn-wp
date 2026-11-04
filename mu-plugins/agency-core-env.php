<?php
/**
 * Plugin Name: Agency Core Environment
 * Description: Development/staging safeguards: routes mail to Mailpit and blocks outbound agency integrations outside production.
 * Version: 0.1.0
 * Author: EZ Host For All
 * License: GPL-2.0-or-later
 *
 * @package Agency_Core
 */

defined( 'ABSPATH' ) || exit;

// Never allow agency integrations (webhooks etc.) to fire from non-production environments.
if ( 'production' !== wp_get_environment_type() ) {
	add_filter( 'agency_core_allow_outbound', '__return_false' );
}

// Capture all mail in Mailpit when the local environment provides an SMTP host.
add_action(
	'phpmailer_init',
	static function ( $phpmailer ): void {
		$host = getenv( 'MAILPIT_SMTP_HOST' );
		if ( ! $host || 'local' !== wp_get_environment_type() ) {
			return;
		}
		$phpmailer->isSMTP();
		$port = (int) getenv( 'MAILPIT_SMTP_PORT' );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHPMailer API.
		$phpmailer->Host     = $host;
		$phpmailer->Port     = $port > 0 ? $port : 1025;
		$phpmailer->SMTPAuth = false;
		// phpcs:enable
	}
);
