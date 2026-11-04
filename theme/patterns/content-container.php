<?php
/**
 * Title: Content container
 * Slug: wp-starter/content-container
 * Categories: theme-examples
 * Description: A wide, constrained container for page sections.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"wide","className":"container","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide container"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Section heading', 'wp-starter' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Section content goes here.', 'wp-starter' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
