<?php
/**
 * Fallback template.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/breadcrumbs' );
get_template_part( 'template-parts/post-list' );

get_footer();
