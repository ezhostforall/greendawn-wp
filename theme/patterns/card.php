<?php
/**
 * Title: Card
 * Slug: greendawn/card
 * Categories: theme-examples
 * Description: A bordered content card with heading, text and button.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"card","layout":{"type":"constrained"}} -->
<div class="wp-block-group card"><!-- wp:heading {"level":3,"className":"card__title"} -->
<h3 class="wp-block-heading card__title"><?php esc_html_e( 'Card title', 'greendawn' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Short supporting text for the card.', 'greendawn' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Learn more', 'greendawn' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
