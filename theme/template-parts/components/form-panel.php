<?php
/**
 * Page-selected CF7 form with visible unavailable-state fallback.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_form_heading    = (string) ( $args['heading'] ?? '' );
$greendawn_form_heading_id = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-form-' ) ) . '-title';
?>
<section class="gd-section gd-form-section"
<?php
if ( '' !== $greendawn_form_heading ) :
	?>
	aria-labelledby="<?php echo esc_attr( $greendawn_form_heading_id ); ?>"
	<?php
else :
	?>
	aria-label="<?php esc_attr_e( 'Project enquiry', 'greendawn' ); ?>"<?php endif; ?>>
	<div class="gd-shell gd-form-section__inner<?php echo '' === $greendawn_form_heading ? ' gd-form-section__inner--form-only' : ''; ?>">
		<?php
		if ( '' !== $greendawn_form_heading ) :
			?>
			<h2 id="<?php echo esc_attr( $greendawn_form_heading_id ); ?>"><?php echo esc_html( $greendawn_form_heading ); ?></h2><?php endif; ?>
		<?php get_template_part( 'template-parts/components/form-content', null, array( 'form_id' => $args['form_id'] ?? 0 ) ); ?>
	</div>
</section>
