<?php
/**
 * Grouped contact methods using explicit shared contact values and page-owned labels.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_id        = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-contact-' ) );
$greendawn_heading   = (string) ( $args['heading'] ?? '' );
$greendawn_copy      = (string) ( $args['copy'] ?? '' );
$greendawn_phone     = (string) ( $args['phone'] ?? '' );
$greendawn_phone_url = greendawn_phone_uri( $greendawn_phone );
$greendawn_email     = sanitize_email( $args['email'] ?? '' );
$greendawn_address   = (string) ( $args['address'] ?? '' );
?>
<section id="<?php echo esc_attr( $greendawn_id ); ?>" class="gd-section gd-contact-section" <?php echo '' !== $greendawn_heading ? 'aria-labelledby="' . esc_attr( $greendawn_id ) . '-title"' : ''; ?>>
	<div class="gd-shell">
		<?php if ( '' !== $greendawn_heading ) : ?>
			<h2 id="<?php echo esc_attr( $greendawn_id ); ?>-title"><?php echo esc_html( $greendawn_heading ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $greendawn_copy ) : ?>
			<div class="gd-contact-intro"><?php echo wpautop( esc_html( $greendawn_copy ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?></div>
		<?php endif; ?>
		<div class="gd-contact-grid">
			<?php if ( '' !== $greendawn_address ) : ?>
				<div>
					<?php if ( '' !== (string) ( $args['address_label'] ?? '' ) ) : ?>
					<h3><?php echo esc_html( $args['address_label'] ?? __( 'Address', 'greendawn' ) ); ?></h3>
					<?php endif; ?>
					<p><?php echo nl2br( esc_html( $greendawn_address ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped plain text, preserving address lines. ?></p>
				</div>
			<?php endif; ?>
			<?php if ( '' !== $greendawn_phone ) : ?>
				<div>
					<?php if ( '' !== (string) ( $args['phone_label'] ?? '' ) ) : ?>
					<h3><?php echo esc_html( $args['phone_label'] ?? __( 'Call Us', 'greendawn' ) ); ?></h3>
					<?php endif; ?>
					<p>
						<?php if ( '' !== $greendawn_phone_url ) : ?>
							<a href="<?php echo esc_url( $greendawn_phone_url ); ?>"><?php echo esc_html( $greendawn_phone ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $greendawn_phone ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>
			<?php if ( is_email( $greendawn_email ) ) : ?>
				<div>
					<?php if ( '' !== (string) ( $args['email_label'] ?? '' ) ) : ?>
					<h3><?php echo esc_html( $args['email_label'] ?? __( 'Email', 'greendawn' ) ); ?></h3>
					<?php endif; ?>
					<p><a href="<?php echo esc_url( 'mailto:' . $greendawn_email ); ?>"><?php echo esc_html( $greendawn_email ); ?></a></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
