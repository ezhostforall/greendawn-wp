<?php
/**
 * Theme bootstrap.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

define( 'GREENDAWN_VERSION', '0.6.18' );
define( 'GREENDAWN_DIR', get_template_directory() );

require_once GREENDAWN_DIR . '/inc/setup.php';
require_once GREENDAWN_DIR . '/inc/header.php';
require_once GREENDAWN_DIR . '/inc/footer.php';
require_once GREENDAWN_DIR . '/inc/brand.php';
require_once GREENDAWN_DIR . '/inc/vite.php';
require_once GREENDAWN_DIR . '/inc/assets.php';
require_once GREENDAWN_DIR . '/inc/template-tags.php';
require_once GREENDAWN_DIR . '/inc/patterns.php';
require_once GREENDAWN_DIR . '/inc/security.php';
require_once GREENDAWN_DIR . '/inc/integrations/contact-form-7.php';
require_once GREENDAWN_DIR . '/inc/integrations/rank-math.php';
require_once GREENDAWN_DIR . '/inc/integrations/scf.php';
require_once GREENDAWN_DIR . '/inc/react.php';
require_once GREENDAWN_DIR . '/inc/lead-capture.php';
