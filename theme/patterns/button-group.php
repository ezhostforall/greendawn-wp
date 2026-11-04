<?php
/**
 * Title: Button group
 * Slug: greendawn/button-group
 * Categories: theme-examples
 * Description: A primary and an outline button side by side.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Primary action', 'greendawn' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Secondary action', 'greendawn' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
