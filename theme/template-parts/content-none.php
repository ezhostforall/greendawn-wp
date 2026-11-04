<?php
/**
 * Empty-state message.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">
	<h2><?php esc_html_e( 'Nothing found', 'wp-starter' ); ?></h2>
	<p><?php esc_html_e( 'No content matched your request. Try a different search.', 'wp-starter' ); ?></p>
	<?php get_search_form(); ?>
</section>
