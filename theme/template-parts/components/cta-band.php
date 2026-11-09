<?php
/**
 * Fixed CTA band with page-owned content and destination.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_cta_id  = sanitize_html_class( $args['instance_id'] ?? wp_unique_id( 'gd-cta-' ) );
$greendawn_cta_url = esc_url( $args['url'] ?? '' );
?>
<section class="gd-section gd-cta-band" aria-labelledby="<?php echo esc_attr( $greendawn_cta_id . '-title' ); ?>">
	<div class="gd-shell gd-cta-band__inner">
		<div>
			<h2 id="<?php echo esc_attr( $greendawn_cta_id . '-title' ); ?>"><?php echo esc_html( $args['heading'] ?? '' ); ?></h2>
			<?php
			if ( ! empty( $args['copy'] ) ) :
				?>
				<p><?php echo esc_html( $args['copy'] ); ?></p><?php endif; ?>
		</div>
		<?php if ( '' !== $greendawn_cta_url && ! empty( $args['label'] ) ) : ?>
			<a class="gd-home-button gd-page-button--navy" href="<?php echo esc_url( $greendawn_cta_url ); ?>"><?php echo esc_html( $args['label'] ); ?><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a>
		<?php endif; ?>
	</div>
</section>
