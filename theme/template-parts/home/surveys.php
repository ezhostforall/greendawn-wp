<?php
/**
 * Technical survey scope, starting price and next steps.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_enquiry_url = esc_url( (string) get_theme_mod( 'greendawn_header_cta_url', '' ) );
$greendawn_enquiry_url = '#' === $greendawn_enquiry_url ? '' : $greendawn_enquiry_url;
$greendawn_guide_url   = esc_url( greendawn_home_field( 'survey_guide_url' ) );
?>
<section id="surveys" class="gd-journey-section gd-surveys" aria-labelledby="gd-surveys-title">
	<div class="gd-home-shell">
		<div class="gd-surveys__intro">
			<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'survey_eyebrow' ) ); ?></p>
			<div>
				<h2 id="gd-surveys-title"><?php echo esc_html( greendawn_home_field( 'survey_title' ) ); ?></h2>
				<p><?php echo esc_html( greendawn_home_field( 'survey_intro' ) ); ?></p>
			</div>
		</div>
		<div class="gd-surveys__overview">
			<div class="gd-surveys__price">
				<p class="gd-surveys__price-label"><?php echo esc_html( greendawn_home_field( 'survey_price_label' ) ); ?></p>
				<p class="gd-surveys__price-value"><strong><?php echo esc_html( greendawn_home_field( 'survey_price' ) ); ?></strong><span><?php echo esc_html( greendawn_home_field( 'survey_tax_label' ) ); ?></span></p>
				<p><?php echo esc_html( greendawn_home_field( 'survey_price_scope' ) ); ?></p>
			</div>
			<div class="gd-surveys__outcomes">
				<h3><?php echo esc_html( greendawn_home_field( 'survey_outcomes_title' ) ); ?></h3>
				<ol role="list">
					<?php for ( $greendawn_outcome = 1; $greendawn_outcome <= 4; ++$greendawn_outcome ) : ?>
						<li>
							<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $greendawn_outcome ) ); ?></span>
							<p><?php echo esc_html( greendawn_home_field( 'survey_outcome_' . $greendawn_outcome ) ); ?></p>
						</li>
					<?php endfor; ?>
				</ol>
			</div>
		</div>
		<div class="gd-surveys__footer">
			<p><?php echo esc_html( greendawn_home_field( 'survey_summary' ) ); ?></p>
			<div class="gd-surveys__actions">
				<?php if ( '' !== $greendawn_enquiry_url ) : ?>
					<a class="gd-home-button gd-home-button--navy" data-lead-capture-open data-lead-entry-point="pricing" href="<?php echo esc_url( $greendawn_enquiry_url ); ?>">
						<?php echo esc_html( greendawn_home_field( 'survey_cta_label' ) ); ?>
						<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( '' !== $greendawn_guide_url ) : ?>
					<a class="gd-home-text-link gd-home-text-link--dark" href="<?php echo esc_url( $greendawn_guide_url ); ?>">
						<?php echo esc_html( greendawn_home_field( 'survey_guide_label' ) ); ?>
						<?php get_template_part( 'template-parts/home/arrow' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
