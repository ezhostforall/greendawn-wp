<?php
/**
 * Curated commercial charging guidance from the homepage reference.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_insights_url = esc_url( greendawn_home_field( 'insights_url' ) );
?>
<section id="insights" class="gd-journey-section gd-insights" aria-labelledby="gd-insights-title">
	<div class="gd-home-shell">
		<div class="gd-insights__header">
			<div>
				<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'insights_eyebrow' ) ); ?></p>
				<h2 id="gd-insights-title"><?php echo esc_html( greendawn_home_field( 'insights_title' ) ); ?></h2>
			</div>
			<?php if ( '' !== $greendawn_insights_url ) : ?>
				<a class="gd-home-text-link gd-home-text-link--dark" href="<?php echo esc_url( $greendawn_insights_url ); ?>">
					<?php echo esc_html( greendawn_home_field( 'insights_link' ) ); ?>
					<?php get_template_part( 'template-parts/home/arrow' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<div class="gd-insights__grid">
			<?php foreach ( array( 'power', 'survey', 'fleet' ) as $greendawn_insight_key ) : ?>
				<?php
				$greendawn_insight_prefix = 'insight_' . $greendawn_insight_key . '_';
				$greendawn_insight_id     = 'gd-insight-title-' . $greendawn_insight_key;
				$greendawn_insight_image  = absint( greendawn_home_field( $greendawn_insight_prefix . 'image' ) );
				$greendawn_insight_sizes  = 'fleet' === $greendawn_insight_key
					? '(max-width: 42rem) calc(100vw - 1.5rem), (max-width: 61rem) calc(100vw - 2rem), 30vw'
					: '(max-width: 42rem) calc(100vw - 1.5rem), (max-width: 61rem) calc(50vw - 2rem), 30vw';
				$greendawn_insight_html   = $greendawn_insight_image ? wp_get_attachment_image(
					$greendawn_insight_image,
					'large',
					false,
					array(
						'loading' => 'lazy',
						'sizes'   => $greendawn_insight_sizes,
					)
				) : '';
				?>
				<a class="gd-insight-card gd-insight-card--<?php echo esc_attr( $greendawn_insight_key ); ?>" href="<?php echo esc_url( greendawn_home_field( $greendawn_insight_prefix . 'url' ) ); ?>" aria-labelledby="<?php echo esc_attr( 'gd-insight-label-' . $greendawn_insight_key . ' ' . $greendawn_insight_id ); ?>">
					<?php if ( '' !== $greendawn_insight_html ) : ?>
						<figure>
							<?php echo $greendawn_insight_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates escaped attachment markup. ?>
							<span aria-hidden="true"><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></span>
						</figure>
					<?php endif; ?>
					<p id="<?php echo esc_attr( 'gd-insight-label-' . $greendawn_insight_key ); ?>"><?php echo esc_html( greendawn_home_field( $greendawn_insight_prefix . 'label' ) ); ?></p>
					<h3 id="<?php echo esc_attr( $greendawn_insight_id ); ?>"><?php echo esc_html( greendawn_home_field( $greendawn_insight_prefix . 'title' ) ); ?></h3>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
