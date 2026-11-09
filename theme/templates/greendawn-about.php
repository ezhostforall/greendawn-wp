<?php
/**
 * Template Name: Greendawn About Us
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$greendawn_teams = greendawn_page_records( greendawn_about_field( 'team_members', array() ), 'team_member' );
	$greendawn_posts = greendawn_page_records( greendawn_about_field( 'posts', array() ), 'post' );
	$greendawn_story = (string) greendawn_about_field( 'story' );
	$greendawn_image = wp_get_attachment_image(
		absint( greendawn_about_field( 'story_image', 0 ) ),
		'large',
		false,
		array(
			'loading' => 'lazy',
			'sizes'   => '(max-width: 61rem) calc(100vw - 2rem), 45vw',
		)
	);
	?>
	<article <?php post_class( 'greendawn-page greendawn-about' ); ?>>
		<?php
		get_template_part(
			'template-parts/components/page-hero',
			null,
			array(
				'heading'  => greendawn_about_field( 'heading', get_the_title() ),
				'accent'   => greendawn_about_field( 'accent' ),
				'intro'    => greendawn_about_field( 'intro' ),
				'image_id' => greendawn_about_field( 'hero_image', get_post_thumbnail_id() ),
			)
		);
		?>
		<?php if ( '' !== $greendawn_story || $greendawn_image ) : ?>
			<section class="gd-section gd-story" aria-labelledby="about-story-title">
				<div class="gd-shell gd-media-copy<?php echo $greendawn_image ? '' : ' gd-media-copy--text'; ?>">
					<?php
					if ( $greendawn_image ) :
						?>
						<figure><?php echo $greendawn_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped attachment markup from WordPress. ?></figure><?php endif; ?>
					<div class="gd-reading-copy">
						<h2 id="about-story-title"><?php echo esc_html( greendawn_about_field( 'story_heading' ) ); ?></h2>
						<?php echo wpautop( esc_html( $greendawn_story ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
		<?php
		$greendawn_steps = array();
		foreach ( array( 'consult', 'design', 'install', 'manage' ) as $greendawn_stage ) {
			$greendawn_steps[] = array(
				'title' => greendawn_about_field( $greendawn_stage . '_title' ),
				'copy'  => greendawn_about_field( $greendawn_stage . '_copy' ),
			);
		}
		if ( '' !== greendawn_about_field( 'process_title' ) ) {
			get_template_part(
				'template-parts/components/process',
				null,
				array(
					'instance_id' => 'about-process',
					'heading'     => greendawn_about_field( 'process_title' ),
					'steps'       => $greendawn_steps,
					'surface'     => 'navy',
				)
			);

		}
		if ( '' !== greendawn_about_field( 'cta_heading' ) ) {
			get_template_part(
				'template-parts/components/cta-band',
				null,
				array(
					'instance_id' => 'about-cta',
					'heading'     => greendawn_about_field( 'cta_heading' ),
					'copy'        => greendawn_about_field( 'cta_copy' ),
					'label'       => greendawn_about_field( 'cta_label' ),
					'url'         => greendawn_about_field( 'cta_url' ),
				)
			);
		}
		?>
		<?php if ( $greendawn_teams ) : ?>
			<section class="gd-section gd-team" aria-labelledby="about-team-title">
				<div class="gd-shell">
					<h2 id="about-team-title"><?php echo esc_html( greendawn_about_field( 'team_heading' ) ); ?></h2>
					<div class="gd-team-grid">
						<?php foreach ( $greendawn_teams as $greendawn_member ) : ?>
							<?php get_template_part( 'template-parts/components/team-card', null, array( 'post_id' => $greendawn_member->ID ) ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
		<?php
		if ( '' !== greendawn_about_field( 'form_heading' ) ) {
			get_template_part(
				'template-parts/components/form-panel',
				null,
				array(
					'instance_id' => 'about-form',
					'heading'     => greendawn_about_field( 'form_heading' ),
					'form_id'     => greendawn_about_field( 'form', 0 ),
				)
			);
		}
		?>
		<?php if ( $greendawn_posts ) : ?>
			<section class="gd-section gd-page-insights" data-gd-articles aria-labelledby="about-insights-title">
				<div class="gd-shell">
					<h2 id="about-insights-title"><?php echo esc_html( greendawn_about_field( 'posts_heading' ) ); ?></h2>
					<div class="gd-article-controls"><button type="button" data-gd-track-direction="-1" aria-controls="about-article-track" aria-label="<?php esc_attr_e( 'Previous articles', 'greendawn' ); ?>" hidden>←</button><button type="button" data-gd-track-direction="1" aria-controls="about-article-track" aria-label="<?php esc_attr_e( 'Next articles', 'greendawn' ); ?>" hidden>→</button></div>
					<div id="about-article-track" class="gd-article-track" role="region" aria-label="<?php esc_attr_e( 'Related articles — scroll for more', 'greendawn' ); ?>" tabindex="0">
						<?php foreach ( $greendawn_posts as $greendawn_article ) : ?>
							<?php
							get_template_part(
								'template-parts/components/article-card',
								null,
								array(
									'instance_id' => 'about-article-' . $greendawn_article->ID,
									'title'       => get_the_title( $greendawn_article ),
									'url'         => get_permalink( $greendawn_article ),
									'image_id'    => get_post_thumbnail_id( $greendawn_article ),
									'show_label'  => false,
								)
							);
							?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;
get_footer();
