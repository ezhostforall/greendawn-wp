<?php
/**
 * WP Starter configuration template for separately hosted WordPress staging.
 *
 * Copy this file to the WordPress installation's wp-config.php and replace
 * every CHANGE_ME_* value with a site-specific secret or connection value.
 * DO NOT overwrite a working site's existing wp-config.php. Instead, copy
 * the relevant environment and updater settings into that site's file.
 *
 * This file is an example only. Never add real credentials to this repository.
 * The .gitignore excludes real wp-config.php files.
 *
 * Staging: https://wp-starter-theme.ezhostforall.com/
 * Guide: docs/staging.md
 */

// Site-specific database connection supplied by your hosting provider.
// These are deliberately placeholders, NOT credentials for the staging site.
define( 'DB_NAME', 'CHANGE_ME_DATABASE_NAME' );
define( 'DB_USER', 'CHANGE_ME_DATABASE_USER' );
define( 'DB_PASSWORD', 'CHANGE_ME_DATABASE_PASSWORD' );
define( 'DB_HOST', 'CHANGE_ME_DATABASE_HOST' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// Replace these placeholders with eight NEW, unique random values from:
// https://api.wordpress.org/secret-key/1.1/salt/
// Never copy salts between environments or publish real salts.
define( 'AUTH_KEY',         'CHANGE_ME_AUTH_KEY' );
define( 'SECURE_AUTH_KEY',  'CHANGE_ME_SECURE_AUTH_KEY' );
define( 'LOGGED_IN_KEY',    'CHANGE_ME_LOGGED_IN_KEY' );
define( 'NONCE_KEY',        'CHANGE_ME_NONCE_KEY' );
define( 'AUTH_SALT',        'CHANGE_ME_AUTH_SALT' );
define( 'SECURE_AUTH_SALT', 'CHANGE_ME_SECURE_AUTH_SALT' );
define( 'LOGGED_IN_SALT',   'CHANGE_ME_LOGGED_IN_SALT' );
define( 'NONCE_SALT',       'CHANGE_ME_NONCE_SALT' );

// Match the ACTUAL installed site's existing table prefix. Do not change
// the prefix on an existing WordPress installation.
$table_prefix = 'wp_';

// Candidate releases are permitted only when WordPress identifies the site
// as staging. Never use this example verbatim on a production installation.
define( 'WP_ENVIRONMENT_TYPE', 'staging' );

// HTTPS should be configured on the host, not forced by this template.
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISALLOW_FILE_EDIT', true );

// Private GitHub access: supply a Contents: Read-only fine-grained token,
// limited to ezhostforall/wp-starter. Prefer secure host environment storage.
// The updater reads EZHOST_GITHUB_TOKEN as a PHP constant or via getenv().
// If server environment variables are unavailable, uncomment the next line
// in the PRIVATE server copy of wp-config.php and insert the real token.
// define( 'EZHOST_GITHUB_TOKEN', 'CHANGE_ME_READ_ONLY_GITHUB_TOKEN' );

// Explicit update allowlist. Default: stable approved releases.
// To test a candidate using WordPress Admin's native update flow, add an
// exact candidate_tag to the selected entries on STAGING ONLY, after that
// prerelease exists. Replace vX.Y.Z with its actual tag; this is not a channel.
// Example inside a selected source entry: 'candidate_tag' => 'vX.Y.Z',
// Do NOT add candidate_tag to a production site's configuration.
define(
    'EZHOST_UPDATE_SOURCES',
    array(
        'themes' => array(
            'wp-starter' => array(
                'repo'      => 'ezhostforall/wp-starter',
                'token_env' => 'EZHOST_GITHUB_TOKEN',
            ),
        ),
        'plugins' => array(
            'agency-core/agency-core.php' => array(
                'repo'      => 'ezhostforall/wp-starter',
                'token_env' => 'EZHOST_GITHUB_TOKEN',
            ),
            'ezhost-updater/ezhost-updater.php' => array(
                'repo'      => 'ezhostforall/wp-starter',
                'token_env' => 'EZHOST_GITHUB_TOKEN',
            ),
        ),
    )
);

// Some shared hosts need direct filesystem access for plugin updates.
// Enable ONLY if it is appropriate for your hosting provider's permissions.
// define( 'FS_METHOD', 'direct' );

// Standard WordPress bootstrap.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
