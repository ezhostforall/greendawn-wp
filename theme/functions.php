<?php
/**
 * Theme bootstrap.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

define( 'WP_STARTER_VERSION', '0.5.0' );
define( 'WP_STARTER_DIR', get_template_directory() );

require_once WP_STARTER_DIR . '/inc/setup.php';
require_once WP_STARTER_DIR . '/inc/header.php';
require_once WP_STARTER_DIR . '/inc/footer.php';
require_once WP_STARTER_DIR . '/inc/brand.php';
require_once WP_STARTER_DIR . '/inc/vite.php';
require_once WP_STARTER_DIR . '/inc/assets.php';
require_once WP_STARTER_DIR . '/inc/template-tags.php';
require_once WP_STARTER_DIR . '/inc/patterns.php';
require_once WP_STARTER_DIR . '/inc/security.php';
require_once WP_STARTER_DIR . '/inc/integrations/contact-form-7.php';
require_once WP_STARTER_DIR . '/inc/integrations/rank-math.php';
require_once WP_STARTER_DIR . '/inc/integrations/scf.php';
require_once WP_STARTER_DIR . '/inc/react.php';
