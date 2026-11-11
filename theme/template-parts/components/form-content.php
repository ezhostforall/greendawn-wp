<?php
/**
 * Validated page-selected CF7 content, shared by full sections and embedded panels.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_fallback_url = (string) ( $args['fallback_url'] ?? home_url( '/enquire/' ) );
$greendawn_form_id      = shortcode_exists( 'contact-form-7' ) ? greendawn_contact_form_id( $args['form_id'] ?? 0 ) : 0;
?>
<div class="gd-form-panel starter-contact-form">
<?php if ( $greendawn_form_id ) : ?>
	<?php echo do_shortcode( sprintf( '[contact-form-7 id="%d"]', $greendawn_form_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CF7 renders a validated, published form ID. ?>
<?php else : ?>
	<p><?php echo esc_html( $args['unavailable_message'] ?? __( 'The enquiry form is currently unavailable.', 'greendawn' ) ); ?></p>
	<?php if ( '' !== $greendawn_fallback_url ) : ?>
	<a class="gd-page-text-link" href="<?php echo esc_url( $greendawn_fallback_url ); ?>"><?php echo esc_html( $args['fallback_label'] ?? __( 'Contact our team', 'greendawn' ) ); ?><?php get_template_part( 'template-parts/home/arrow' ); ?></a>
	<?php endif; ?>
<?php endif; ?>
</div>
