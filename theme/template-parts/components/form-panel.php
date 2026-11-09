<?php
/**
 * Page-selected CF7 form with visible unavailable-state fallback.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_form_id         = shortcode_exists( 'contact-form-7' ) ? greendawn_contact_form_id( $args['form_id'] ?? 0 ) : 0;
$greendawn_form_heading_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-form-' ) ) . '-title';
?>
<section class="gd-section gd-form-section" aria-labelledby="<?php echo esc_attr( $greendawn_form_heading_id ); ?>">
	<div class="gd-shell gd-form-section__inner">
		<h2 id="<?php echo esc_attr( $greendawn_form_heading_id ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
		<div class="gd-form-panel starter-contact-form">
			<?php if ( $greendawn_form_id ) : ?>
				<?php echo do_shortcode( sprintf( '[contact-form-7 id="%d"]', $greendawn_form_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 renders a validated, published form ID. ?>
			<?php else : ?>
				<p><?php esc_html_e( 'The enquiry form is currently unavailable.', 'greendawn' ); ?></p>
				<a class="gd-page-text-link" href="<?php echo esc_url( home_url( '/enquire/' ) ); ?>"><?php esc_html_e( 'Contact our team', 'greendawn' ); ?><?php get_template_part( 'template-parts/home/arrow' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
