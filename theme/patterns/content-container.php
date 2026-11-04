<?php
/**
 * Title: Content container
 * Slug: greendawn/content-container
 * Categories: theme-examples
 * Description: A wide, constrained container for page sections.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"wide","className":"container","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide container"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Section heading', 'greendawn' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Section content goes here.', 'greendawn' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
