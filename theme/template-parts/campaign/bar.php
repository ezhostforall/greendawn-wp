<?php
/**
 * Featured campaign announcement.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="gd-campaign-bar" aria-label="<?php esc_attr_e( 'Featured campaign', 'greendawn' ); ?>" data-gd-campaign-notice data-gd-campaign-timeline="<?php echo esc_attr( greendawn_campaign_timeline( $args ) ); ?>" <?php echo 'upcoming' === $args['phase'] ? 'hidden' : ''; ?>>
	<div class="gd-campaign-shell">
		<p><strong><?php echo esc_html( $args['bar_title'] ); ?></strong>
			<?php greendawn_campaign_variant_text( $args['bar_copy'], $args['late_bar'], $args['phase'] ); ?>
		</p>
		<a class="gd-campaign-textlink" data-gd-campaign-bar-link href="<?php echo esc_url( $args['url'] ); ?>">
			<?php echo esc_html( greendawn_campaign_copy( $args['bar_cta'], __( 'View campaign', 'greendawn' ) ) ); ?>
			<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
		</a>
	</div>
</aside>
