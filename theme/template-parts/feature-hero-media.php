<?php
/**
 * Above-the-fold featured image for a split business hero.
 *
 * @package WP_Starter
 */

defined( 'ABSPATH' ) || exit;

if ( ! has_post_thumbnail() ) {
	return;
}
$wp_starter_hero_image_attributes = array(
	'loading' => 'eager',
	'sizes'   => '(min-width: 72rem) 33rem, (min-width: 44rem) calc(50vw - 2.5rem), calc(100vw - 2rem)',
);
if ( ! empty( $args['priority'] ) ) {
	$wp_starter_hero_image_attributes['fetchpriority'] = 'high';
}
?>
<div class="feature-hero__media">
	<?php the_post_thumbnail( 'large', $wp_starter_hero_image_attributes ); ?>
</div>
