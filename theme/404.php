<?php
/**
 * 404 template.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="not-found">
	<h1 class="page-title"><?php esc_html_e( 'Page not found', 'wp-starter' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for does not exist. Try searching instead.', 'wp-starter' ); ?></p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
