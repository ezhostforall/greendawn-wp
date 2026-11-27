<?php
/**
 * Structured Guide, without a duplicate native post body or personal byline.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'greendawn-page greendawn-guide' ); ?>>
		<?php
		if ( post_password_required() ) {
			get_template_part( 'template-parts/components/page-hero', null, array( 'heading' => get_the_title() ) );
			echo '<div class="gd-shell gd-section">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native WordPress password form.
		} else {
			$greendawn_data     = greendawn_guide_template_data( get_the_ID() );
			$greendawn_topic    = get_term( $greendawn_data['primary_topic'] ?? 0, 'gdguide_topic' );
			$greendawn_sections = greendawn_reading_sections( $greendawn_data['sections'] ?? array() );
			get_template_part(
				'template-parts/components/page-hero',
				null,
				array(
					'variant'        => 'guide',
					'heading'        => get_the_title(),
					'intro'          => $greendawn_data['intro'] ?? '',
					'image_id'       => $greendawn_data['image'] ?? 0,
					'image_position' => $greendawn_data['image_position'] ?? '',
					'kicker'         => $greendawn_topic instanceof WP_Term ? $greendawn_topic->name : '',
					'metadata'       => $greendawn_data,
				)
			);
			?>
			<div class="gd-shell gd-section gd-reading-layout">
				<?php get_template_part( 'template-parts/components/reading-contents', null, array( 'sections' => $greendawn_sections ) ); ?>
				<div class="gd-reading-body">
					<?php
					get_template_part( 'template-parts/components/direct-answer', null, array( 'answer' => $greendawn_data['answer'] ?? '' ) );
					get_template_part( 'template-parts/components/reading-sections', null, array( 'sections' => $greendawn_sections ) );
					get_template_part(
						'template-parts/components/faq',
						null,
						array(
							'heading' => __( 'Additional questions', 'greendawn' ),
							'items'   => $greendawn_data['faqs'] ?? array(),
						)
					);
					get_template_part( 'template-parts/components/source-list', null, array( 'items' => $greendawn_data['sources'] ?? array() ) );
					?>
				</div>
			</div>
			<?php
			greendawn_reading_related( $greendawn_data['related'] ?? array() );
			greendawn_reading_cta( $greendawn_data );
			$greendawn_hub = greendawn_guides_hub_url();
			if ( $greendawn_hub ) {
				printf( '<nav class="gd-shell gd-reading-back" aria-label="%1$s"><a class="gd-page-text-link" href="%2$s">%3$s</a></nav>', esc_attr__( 'Guide navigation', 'greendawn' ), esc_url( $greendawn_hub ), esc_html__( 'Browse all guides', 'greendawn' ) );
			}
		}
		?>
	</article>
	<?php
endwhile;
get_footer();
