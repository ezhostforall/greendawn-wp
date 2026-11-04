<?php
/**
 * 404 template.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="not-found">
	<h1 class="page-title"><?php esc_html_e( 'Page not found', 'greendawn' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for does not exist. Try searching instead.', 'greendawn' ); ?></p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
