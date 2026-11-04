<?php
/**
 * Empty-state message.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">
	<h2><?php esc_html_e( 'Nothing found', 'greendawn' ); ?></h2>
	<p><?php esc_html_e( 'No content matched your request. Try a different search.', 'greendawn' ); ?></p>
	<?php get_search_form(); ?>
</section>
