<?php
/**
 * Campaign landing page.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();
$greendawn_campaign = greendawn_campaign_context()['current'];
if ( $greendawn_campaign ) :
	$greendawn_phase = $greendawn_campaign['phase'];
	$greendawn_open  = in_array( $greendawn_phase, array( 'live', 'late' ), true );
	$greendawn_id    = $greendawn_campaign['id'];
	$greendawn_hero  = absint( $greendawn_campaign['hero_image'] );
	if ( ! wp_attachment_is_image( $greendawn_hero ) || 'trash' === get_post_status( $greendawn_hero ) ) {
		$greendawn_hero = get_post_thumbnail_id( $greendawn_id );
	}
	?>
	<article class="gd-campaign-landing" data-gd-campaign-timeline="<?php echo esc_attr( greendawn_campaign_timeline( $greendawn_campaign ) ); ?>">
		<section class="gd-campaign-hero" aria-labelledby="gd-campaign-title">
			<?php if ( $greendawn_hero ) : ?>
				<?php
				echo wp_get_attachment_image(
					$greendawn_hero,
					'full',
					false,
					array(
						'class'         => 'gd-campaign-hero__image',
						'alt'           => '',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes the image attributes.
				?>
			<?php elseif ( 'workplace-charging-grant' === get_post_field( 'post_name', $greendawn_id ) ) : ?>
				<img class="gd-campaign-hero__image" src="<?php echo esc_url( get_theme_file_uri( '/assets/campaigns/workplace-charging.webp' ) ); ?>" alt="" width="326" height="760" fetchpriority="high">
			<?php endif; ?>
			<div class="gd-campaign-shell">
				<div class="gd-campaign-hero__copy">
					<p class="gd-campaign-kicker"><?php echo esc_html( $greendawn_campaign['kicker'] ); ?></p>
					<h1 id="gd-campaign-title">
						<?php echo esc_html( greendawn_campaign_copy( $greendawn_campaign['headline'], get_the_title( $greendawn_id ) ) ); ?>
						<?php
						if ( $greendawn_campaign['accent'] ) :
							?>
							<em><?php echo esc_html( $greendawn_campaign['accent'] ); ?></em><?php endif; ?>
					</h1>
					<p data-gd-campaign-variant="live late" <?php echo $greendawn_open ? '' : 'hidden'; ?>><?php greendawn_campaign_variant_text( $greendawn_campaign['intro'], $greendawn_campaign['late_intro'], $greendawn_phase ); ?></p>
					<p data-gd-campaign-variant="closed upcoming" <?php echo $greendawn_open ? 'hidden' : ''; ?>><?php echo esc_html( greendawn_campaign_copy( $greendawn_campaign['closed_intro'], __( 'Talk to us about your project.', 'greendawn' ) ) ); ?></p>
					<div class="gd-campaign-actions">
						<?php greendawn_campaign_actions( $greendawn_campaign ); ?>
						<?php if ( $greendawn_campaign['guide_title'] || $greendawn_campaign['closed_guide'] || $greendawn_campaign['eligibility'] || $greendawn_campaign['panel_title'] ) : ?>
						<a class="gd-campaign-textlink" href="#campaign-guide">
							<?php echo esc_html( greendawn_campaign_copy( $greendawn_campaign['guide_link'], __( 'Read the guide', 'greendawn' ) ) ); ?>
							<?php get_template_part( 'template-parts/home/arrow', null, array( 'diagonal' => true ) ); ?>
						</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<section class="gd-campaign-dates" aria-label="<?php esc_attr_e( 'Key campaign dates', 'greendawn' ); ?>" data-gd-campaign-variant="live late" <?php echo $greendawn_open ? '' : 'hidden'; ?>>
			<div class="gd-campaign-shell">
				<?php if ( $greendawn_campaign['lateAt'] && $greendawn_campaign['planning_label'] ) : ?>
					<div><p><?php echo esc_html( $greendawn_campaign['planning_label'] ); ?></p>
						<strong><?php echo esc_html( wp_date( 'j F Y', $greendawn_campaign['lateAt'] - 1, new DateTimeZone( 'Europe/London' ) ) ); ?></strong>
						<p><?php greendawn_campaign_variant_text( $greendawn_campaign['planning_hint'], $greendawn_campaign['late_hint'], $greendawn_phase ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( $greendawn_campaign['endsAt'] ) : ?>
					<div><p><?php echo esc_html( $greendawn_campaign['expiry_label'] ); ?></p>
						<strong><?php echo esc_html( wp_date( 'j F Y', $greendawn_campaign['endsAt'] - 1, new DateTimeZone( 'Europe/London' ) ) ); ?></strong>
						<p><?php echo esc_html( $greendawn_campaign['expiry_hint'] ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php get_template_part( 'template-parts/campaign/content', null, $greendawn_campaign ); ?>
	</article>
<?php elseif ( have_posts() ) : ?>
	<?php the_post(); ?>
	<?php the_content(); ?>
<?php endif; ?>
<?php get_footer(); ?>
