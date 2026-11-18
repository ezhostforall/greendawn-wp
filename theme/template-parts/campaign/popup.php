<?php
/**
 * Featured campaign dialog shared by all public pages.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<dialog class="gd-campaign-popup" aria-labelledby="gd-campaign-popup-title" data-gd-campaign-popup="<?php echo esc_attr( (string) $args['id'] ); ?>" data-gd-campaign-timeline="<?php echo esc_attr( greendawn_campaign_timeline( $args ) ); ?>">
	<div class="gd-campaign-popup__header">
		<div>
			<p class="gd-campaign-kicker"><?php echo esc_html( get_the_title( $args['id'] ) ); ?></p>
			<h2 id="gd-campaign-popup-title"><?php echo esc_html( $args['popup_title'] ); ?></h2>
		</div>
		<form method="dialog">
			<button type="submit" class="gd-campaign-close" aria-label="<?php esc_attr_e( 'Close campaign notice', 'greendawn' ); ?>" autofocus>×</button>
		</form>
	</div>
	<div class="gd-campaign-popup__dates">
		<?php if ( $args['popup_planning'] ) : ?>
		<div>
			<strong><?php greendawn_campaign_variant_text( $args['popup_planning'], $args['late_popup'], $args['phase'] ); ?></strong>
			<p><?php greendawn_campaign_variant_text( $args['popup_note'], $args['late_note'], $args['phase'] ); ?></p>
		</div>
		<?php endif; ?>
		<div>
			<strong><?php echo esc_html( $args['popup_expiry'] ); ?></strong>
			<p><?php echo esc_html( $args['expiry_hint'] ); ?></p>
		</div>
	</div>
	<p><?php echo esc_html( $args['popup_copy'] ); ?></p>
	<div class="gd-campaign-actions">
		<a class="gd-campaign-button" data-lead-capture-open data-lead-entry-point="campaign-popup" data-lead-campaign="<?php echo esc_attr( (string) $args['id'] ); ?>" href="<?php echo esc_url( is_singular( 'gdcampaign' ) && get_queried_object_id() === $args['id'] ? greendawn_campaign_enquiry_fallback( $args ) : $args['url'] ); ?>">
			<?php greendawn_campaign_variant_text( greendawn_campaign_copy( $args['popup_cta'], $args['cta'] ), $args['late_cta'], $args['phase'] ); ?>
			<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
		</a>
		<a class="gd-campaign-textlink" href="<?php echo esc_url( $args['url'] . '#campaign-guide' ); ?>">
			<?php echo esc_html( greendawn_campaign_copy( $args['popup_guide'], __( 'Read the full guide', 'greendawn' ) ) ); ?>
			<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
		</a>
	</div>
</dialog>
