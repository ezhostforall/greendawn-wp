<?php
/**
 * Reusable campaign information, enquiry and evergreen sections.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_campaign = $args;
$greendawn_phase    = $greendawn_campaign['phase'];
$greendawn_metrics  = array_filter( $greendawn_campaign['metrics'], static fn( array $greendawn_row ): bool => '' !== trim( $greendawn_row['value'] ) );
$greendawn_checks   = array_filter( $greendawn_campaign['eligibility'], static fn( array $greendawn_row ): bool => '' !== trim( $greendawn_row['title'] . $greendawn_row['copy'] ) );
$greendawn_points   = array_filter( $greendawn_campaign['panel_points'], static fn( array $greendawn_row ): bool => '' !== trim( $greendawn_row['title'] ) );
$greendawn_steps    = array_filter( $greendawn_campaign['process_steps'], static fn( array $greendawn_row ): bool => '' !== trim( $greendawn_row['title'] . $greendawn_row['copy'] . $greendawn_row['closed_title'] . $greendawn_row['closed_copy'] ) );
$greendawn_faqs     = array_filter( $greendawn_campaign['faqs'], static fn( array $greendawn_row ): bool => '' !== trim( $greendawn_row['question'] ) && '' !== trim( $greendawn_row['answer'] ) );
// Older campaign records remain usable before a content upgrade, including without SCF.
if ( ! metadata_exists( 'post', $greendawn_campaign['id'], 'greendawn_campaign_faqs' ) ) {
	foreach ( array(
		'faq'     => 'active',
		'voucher' => 'always',
	) as $greendawn_prefix => $greendawn_visibility ) {
		if ( $greendawn_campaign[ $greendawn_prefix . '_question' ] && $greendawn_campaign[ $greendawn_prefix . '_answer' ] ) {
			$greendawn_faqs[] = array(
				'question'   => $greendawn_campaign[ $greendawn_prefix . '_question' ],
				'answer'     => $greendawn_campaign[ $greendawn_prefix . '_answer' ],
				'visibility' => $greendawn_visibility,
			);
		}
	}
}
$greendawn_faq_phases   = implode( ' ', array_map( static fn( array $greendawn_row ): string => greendawn_campaign_faq_phases( $greendawn_row['visibility'] ), $greendawn_faqs ) );
$greendawn_closed_steps = array_filter( $greendawn_steps, static fn( array $greendawn_row ): bool => '' !== trim( $greendawn_row['closed_title'] . $greendawn_row['closed_copy'] ) );

?>
<?php if ( $greendawn_metrics ) : ?>
	<section class="gd-campaign-metrics" aria-label="<?php esc_attr_e( 'Grant at a glance', 'greendawn' ); ?>" <?php greendawn_campaign_visibility( 'live late', $greendawn_phase ); ?>>
		<div class="gd-campaign-shell gd-campaign-metrics__grid">
			<?php foreach ( $greendawn_metrics as $greendawn_metric ) : ?>
				<div><span><?php echo esc_html( $greendawn_metric['prefix'] ); ?></span><strong><?php echo esc_html( $greendawn_metric['value'] ); ?></strong><p><?php echo esc_html( $greendawn_metric['copy'] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
<?php if ( $greendawn_campaign['archive_title'] || $greendawn_campaign['archive_copy'] ) : ?>
	<section class="gd-campaign-archive" aria-label="<?php esc_attr_e( 'Campaign status', 'greendawn' ); ?>" <?php greendawn_campaign_visibility( 'closed', $greendawn_phase ); ?>>
		<div class="gd-campaign-shell"><strong><?php echo esc_html( $greendawn_campaign['archive_title'] ); ?></strong><p><?php echo esc_html( $greendawn_campaign['archive_copy'] ); ?></p></div>
	</section>
<?php endif; ?>
<?php if ( $greendawn_campaign['guide_title'] || $greendawn_campaign['closed_guide'] || $greendawn_checks || $greendawn_campaign['panel_title'] ) : ?>
	<section id="campaign-guide" class="gd-campaign-guide gd-campaign-section" aria-label="<?php esc_attr_e( 'Campaign guide', 'greendawn' ); ?>">
		<div class="gd-campaign-shell <?php echo $greendawn_campaign['panel_title'] ? 'gd-campaign-guide__columns' : ''; ?>">
			<div>
				<p class="gd-campaign-kicker"><?php echo esc_html( get_the_title( $greendawn_campaign['id'] ) ); ?></p>
				<h2><?php greendawn_campaign_evergreen_text( $greendawn_campaign['guide_title'], $greendawn_campaign['closed_guide'], $greendawn_phase ); ?></h2>
				<p class="gd-campaign-lead"><?php greendawn_campaign_evergreen_text( $greendawn_campaign['guide_intro'], $greendawn_campaign['closed_summary'], $greendawn_phase ); ?></p>
				<?php if ( $greendawn_checks ) : ?>
					<ul class="gd-campaign-checklist" <?php greendawn_campaign_visibility( 'live late', $greendawn_phase ); ?>>
						<?php foreach ( $greendawn_checks as $greendawn_check ) : ?>
							<li><span aria-hidden="true">✓</span><div><strong><?php echo esc_html( $greendawn_check['title'] ); ?></strong><p><?php echo esc_html( $greendawn_check['copy'] ); ?></p></div></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( $greendawn_campaign['criteria_note'] || $greendawn_campaign['guidance_url'] ) : ?>
					<div class="gd-campaign-criteria" <?php greendawn_campaign_visibility( 'live late', $greendawn_phase ); ?>>
						<?php echo esc_html( $greendawn_campaign['criteria_note'] ); ?>
						<?php
						if ( $greendawn_campaign['guidance_url'] ) :
							?>
							<a href="<?php echo esc_url( $greendawn_campaign['guidance_url'] ); ?>"><?php echo esc_html( greendawn_campaign_copy( $greendawn_campaign['guidance_label'], __( 'Official GOV.UK guidance', 'greendawn' ) ) ); ?> <?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( $greendawn_campaign['panel_title'] ) : ?>
				<aside class="gd-campaign-panel">
					<?php
					if ( $greendawn_campaign['panel_kicker'] ) :
						?>
						<p class="gd-campaign-kicker"><?php echo esc_html( $greendawn_campaign['panel_kicker'] ); ?></p><?php endif; ?>
					<h3><?php echo esc_html( $greendawn_campaign['panel_title'] ); ?></h3>
					<p><?php greendawn_campaign_evergreen_text( $greendawn_campaign['panel_copy'], $greendawn_campaign['closed_panel'], $greendawn_phase ); ?></p>
					<?php greendawn_campaign_actions( $greendawn_campaign, $greendawn_campaign['panel_cta'] ); ?>
					<?php
					if ( $greendawn_campaign['panel_hint'] ) :
						?>
						<p class="gd-campaign-panel__hint"><?php echo esc_html( $greendawn_campaign['panel_hint'] ); ?></p><?php endif; ?>
					<?php
					if ( $greendawn_points ) :
						?>
						<ul class="gd-campaign-panel__points">
						<?php
						foreach ( $greendawn_points as $greendawn_point ) :
							?>
							<li><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?><?php echo esc_html( $greendawn_point['title'] ); ?></li><?php endforeach; ?>
					</ul><?php endif; ?>
				</aside>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>
<?php if ( $greendawn_steps ) : ?>
	<section class="gd-campaign-process gd-campaign-section" aria-label="<?php esc_attr_e( 'Installation process', 'greendawn' ); ?>" <?php greendawn_campaign_visibility( $greendawn_closed_steps ? 'live late closed upcoming' : 'live late', $greendawn_phase ); ?>>
		<div class="gd-campaign-shell">
			<div class="gd-campaign-section__header"><div><p class="gd-campaign-kicker"><?php echo esc_html( $greendawn_campaign['process_kicker'] ); ?></p><h2><?php greendawn_campaign_evergreen_text( $greendawn_campaign['process_title'], $greendawn_campaign['closed_process_title'], $greendawn_phase ); ?></h2></div><p class="gd-campaign-lead"><?php echo esc_html( $greendawn_campaign['process_intro'] ); ?></p></div>
			<ol class="gd-campaign-steps">
				<?php foreach ( array_values( $greendawn_steps ) as $greendawn_index => $greendawn_step ) : ?>
					<li <?php greendawn_campaign_visibility( $greendawn_step['closed_title'] || $greendawn_step['closed_copy'] ? 'live late closed upcoming' : 'live late', $greendawn_phase ); ?>>
						<p class="gd-campaign-step__number"><?php echo esc_html( sprintf( '%02d', $greendawn_index + 1 ) ); ?><span <?php greendawn_campaign_visibility( 'live late', $greendawn_phase ); ?>> / <?php echo esc_html( $greendawn_step['label'] ); ?></span></p>
						<h3><?php greendawn_campaign_evergreen_text( $greendawn_step['title'], $greendawn_step['closed_title'], $greendawn_phase ); ?></h3><p><?php greendawn_campaign_evergreen_text( $greendawn_step['copy'], $greendawn_step['closed_copy'], $greendawn_phase ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php
			if ( $greendawn_campaign['voucher_note'] ) :
				?>
				<p class="gd-campaign-voucher-note"><span aria-hidden="true">i</span><?php echo esc_html( $greendawn_campaign['voucher_note'] ); ?></p><?php endif; ?>
		</div>
	</section>
<?php endif; ?>
<?php if ( $greendawn_faqs ) : ?>
	<section class="gd-campaign-faq gd-campaign-section" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'greendawn' ); ?>" <?php greendawn_campaign_visibility( $greendawn_faq_phases, $greendawn_phase ); ?>>
		<div class="gd-campaign-shell gd-campaign-faq__columns">
			<div><p class="gd-campaign-kicker"><?php echo esc_html( $greendawn_campaign['faq_kicker'] ); ?></p>
			<?php
			if ( $greendawn_campaign['faq_title'] ) :
				?>
				<h2><?php echo esc_html( $greendawn_campaign['faq_title'] ); ?></h2><?php endif; ?><p class="gd-campaign-lead"><?php echo esc_html( $greendawn_campaign['faq_intro'] ); ?></p>
				<?php
				if ( $greendawn_campaign['installer_url'] ) :
					?>
					<a class="gd-campaign-textlink" href="<?php echo esc_url( $greendawn_campaign['installer_url'] ); ?>"><?php echo esc_html( $greendawn_campaign['installer_label'] ); ?><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a><?php endif; ?>
			</div>
			<div class="gd-campaign-faq__answers">
				<?php foreach ( $greendawn_faqs as $greendawn_faq ) : ?>
					<?php
					$greendawn_phases = greendawn_campaign_faq_phases( $greendawn_faq['visibility'] );
					?>
					<details <?php greendawn_campaign_visibility( $greendawn_phases, $greendawn_phase ); ?>><summary><?php echo esc_html( $greendawn_faq['question'] ); ?></summary><p><?php echo esc_html( $greendawn_faq['answer'] ); ?></p></details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
<?php if ( $greendawn_campaign['closing_title'] ) : ?>
	<section class="gd-campaign-closing" aria-label="<?php esc_attr_e( 'Discuss your project', 'greendawn' ); ?>"><div class="gd-campaign-shell"><div><p class="gd-campaign-kicker"><?php echo esc_html( $greendawn_campaign['closing_kicker'] ); ?></p><h2><?php echo esc_html( $greendawn_campaign['closing_title'] ); ?></h2><p><?php echo esc_html( $greendawn_campaign['closing_copy'] ); ?></p></div><?php greendawn_campaign_actions( $greendawn_campaign ); ?></div></section>
<?php endif; ?>
<?php if ( $greendawn_campaign['source_label'] || $greendawn_campaign['checked_date'] || $greendawn_campaign['source_note'] ) : ?>
	<div class="gd-campaign-source"><div class="gd-campaign-shell"><div>
	<?php
	if ( $greendawn_campaign['source_label'] && $greendawn_campaign['guidance_url'] ) :
		?>
		<a href="<?php echo esc_url( $greendawn_campaign['guidance_url'] ); ?>"><?php echo esc_html( $greendawn_campaign['source_label'] ); ?><?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?></a><?php endif; ?></div><p>
		<?php
		$greendawn_checked = \Greendawn_Core\Modules\Campaigns::boundary( $greendawn_campaign['checked_date'] );
		if ( $greendawn_checked ) {
			/* translators: %s: Date on which guidance was checked. */
			echo esc_html( sprintf( __( 'Guidance checked %s.', 'greendawn' ), wp_date( 'j F Y', $greendawn_checked, new DateTimeZone( 'Europe/London' ) ) ) ) . ' ';
		}
		echo esc_html( $greendawn_campaign['source_note'] );
		?>
	</p></div></div>
<?php endif; ?>
