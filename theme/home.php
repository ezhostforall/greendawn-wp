<?php
/**
 * Native posts index for the Page assigned under Settings → Reading.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="blog-index greendawn-editorial">
	<?php
	get_template_part(
		'template-parts/components/page-hero',
		null,
		array(
			'heading' => __( 'Our', 'greendawn' ),
			'accent'  => __( 'Content', 'greendawn' ),
		)
	);
	get_template_part( 'template-parts/editorial-listing' );
	?>
</div>
<?php
get_footer();
