<?php
/**
 * Native FAQ disclosures alongside an explicitly selected enquiry form.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_heading      = (string) ( $args['heading'] ?? '' );
$greendawn_items        = is_array( $args['items'] ?? null ) ? array_values( array_filter( $args['items'], static fn( $item ): bool => is_array( $item ) && '' !== trim( (string) ( $item['question'] ?? '' ) ) ) ) : array();
$greendawn_id           = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-faq-' ) );
$greendawn_form         = absint( $args['form_id'] ?? 0 );
$greendawn_form_heading = (string) ( $args['form_heading'] ?? '' );
if ( ! $greendawn_items && ! $greendawn_form ) {
	return;
}
?>
<section class="gd-section gd-faq-section" <?php echo '' !== $greendawn_heading ? 'aria-labelledby="' . esc_attr( $greendawn_id ) . '-title"' : ''; ?>>
	<div class="gd-shell">
		<?php if ( '' !== $greendawn_heading ) : ?>
			<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_heading ); ?></h2>
		<?php endif; ?>
		<div class="gd-faq-layout<?php echo $greendawn_items && $greendawn_form ? ' gd-faq-layout--form' : ''; ?>">
			<?php if ( $greendawn_items ) : ?>
				<div class="gd-faq-list">
					<?php foreach ( $greendawn_items as $greendawn_index => $greendawn_item ) : ?>
						<details class="gd-faq-item" <?php echo 0 === $greendawn_index ? 'open' : ''; ?>>
							<summary><?php echo esc_html( $greendawn_item['question'] ); ?></summary>
							<div class="gd-faq-answer"><?php echo greendawn_page_prose( (string) ( $greendawn_item['answer'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted semantic content allowlist. ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $greendawn_form ) : ?>
				<div class="gd-faq-form">
					<?php if ( '' !== $greendawn_form_heading ) : ?>
						<h3><?php echo esc_html( $greendawn_form_heading ); ?></h3>
					<?php endif; ?>
					<?php get_template_part( 'template-parts/components/form-content', null, array( 'form_id' => $greendawn_form ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
