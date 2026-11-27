<?php
/**
 * Template Name: Greendawn Guides
 * Template Post Type: page
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'greendawn-page greendawn-guides' ); ?>>
		<?php
		if ( post_password_required() ) {
			get_template_part( 'template-parts/components/page-hero', null, array( 'heading' => get_the_title() ) );
			echo '<div class="gd-shell gd-section">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native WordPress password form.
		} else {
			$greendawn_data   = greendawn_reading_page_data( get_the_ID() );
			$greendawn_state  = greendawn_guides_state();
			$greendawn_result = class_exists( '\Greendawn_Core\Modules\Guide_Discovery' ) ? \Greendawn_Core\Modules\Guide_Discovery::search( $greendawn_state['search'], $greendawn_state['topic'], $greendawn_state['page'] ) : null;
			$greendawn_topics = class_exists( '\Greendawn_Core\Modules\Guide_Discovery' ) ? \Greendawn_Core\Modules\Guide_Discovery::topics( $greendawn_data['topics'] ?? array() ) : array();
			get_template_part(
				'template-parts/components/page-hero',
				null,
				array(
					'heading'  => $greendawn_data['heading'] ?? get_the_title(),
					'intro'    => $greendawn_data['intro'] ?? '',
					'image_id' => $greendawn_data['image'] ?? 0,
				)
			);
			?>
			<section class="gd-shell gd-section gd-guides-browse" aria-label="<?php esc_attr_e( 'Browse guides', 'greendawn' ); ?>">
				<?php
				get_template_part(
					'template-parts/components/guide-filters',
					null,
					array(
						'url'    => get_permalink(),
						'label'  => $greendawn_data['search_label'] ?? __( 'Search guides', 'greendawn' ),
						'query'  => $greendawn_state['search'],
						'topic'  => $greendawn_state['topic'],
						'topics' => $greendawn_topics,
					)
				);
				if ( '' === $greendawn_state['search'] && '' === $greendawn_state['topic'] && 1 === $greendawn_state['page'] ) {
					$greendawn_feature = get_post( $greendawn_data['featured'] ?? 0 );
					$greendawn_card    = $greendawn_feature instanceof WP_Post && 'gdguide' === $greendawn_feature->post_type ? greendawn_article_card_data( $greendawn_feature ) : array();
					$greendawn_prompt  = ! empty( $greendawn_data['enquiry_heading'] );
					if ( $greendawn_card || $greendawn_prompt ) :
						?>
						<div class="gd-guides-feature<?php echo $greendawn_card && $greendawn_prompt ? ' gd-guides-feature--split' : ''; ?>">
							<?php
							if ( $greendawn_card ) {
								get_template_part( 'template-parts/components/article-card', null, array_merge( $greendawn_card, array( 'variant' => 'guide-featured' ) ) );
							}
							?>
							<?php if ( $greendawn_prompt ) : ?>
								<aside class="gd-guides-prompt">
									<h2><?php echo esc_html( $greendawn_data['enquiry_heading'] ); ?></h2>
									<?php echo greendawn_page_prose( $greendawn_data['enquiry_copy'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted prose. ?>
									<?php $greendawn_action = greendawn_component_cta( array( 'action' => 'enquiry' ), 'services' ); ?>
									<a class="gd-home-button" href="<?php echo esc_url( $greendawn_action['url'] ); ?>"<?php echo $greendawn_action['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed escaped launcher attributes. ?>><?php echo esc_html( ! empty( $greendawn_data['cta_label'] ) ? $greendawn_data['cta_label'] : __( 'Discuss your site', 'greendawn' ) ); ?></a>
								</aside>
							<?php endif; ?>
						</div>
						<?php
					endif;
				}
				?>
				<div class="gd-guides-results-heading"><h2><?php esc_html_e( 'Explore the guides', 'greendawn' ); ?></h2><p role="status"><?php echo esc_html( sprintf( /* translators: %s: number of matching Guides. */ _n( '%s guide', '%s guides', $greendawn_result ? $greendawn_result->found_posts : 0, 'greendawn' ), number_format_i18n( $greendawn_result ? $greendawn_result->found_posts : 0 ) ) ); ?></p></div>
				<?php if ( $greendawn_result && $greendawn_result->posts ) : ?>
					<div class="gd-related-grid gd-guides-results">
						<?php
						foreach ( $greendawn_result->posts as $greendawn_post ) {
							get_template_part( 'template-parts/components/article-card', null, greendawn_article_card_data( $greendawn_post ) );
						}
						?>
					</div>
					<?php else : ?>
					<div class="gd-guides-empty"><h3><?php echo esc_html( $greendawn_data['empty_heading'] ?? __( 'No guides found', 'greendawn' ) ); ?></h3><?php echo greendawn_page_prose( $greendawn_data['empty_copy'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Restricted prose. ?></div>
				<?php endif; ?>
				<?php if ( $greendawn_result && $greendawn_result->max_num_pages > 1 ) : ?>
					<nav class="gd-guides-pagination" aria-label="<?php esc_attr_e( 'Guide results pages', 'greendawn' ); ?>">
						<?php
						$greendawn_query_args = array_filter(
							array(
								'guide_search' => rawurlencode( $greendawn_state['search'] ),
								'guide_topic'  => $greendawn_state['topic'],
							),
							static fn( $value ) => '' !== $value
						);
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes pagination links.
						echo paginate_links(
							array(
								'base'     => add_query_arg( 'guide_page', '%#%', get_permalink() ),
								'format'   => '',
								'current'  => $greendawn_state['page'],
								'total'    => $greendawn_result->max_num_pages,
								'add_args' => $greendawn_query_args,
							)
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress escapes pagination links.
						?>
					</nav>
				<?php endif; ?>
			</section>
			<?php
			if ( ! empty( $greendawn_data['trust_heading'] ) || ! empty( $greendawn_data['trust_copy'] ) ) {
				get_template_part(
					'template-parts/components/media-copy',
					null,
					array(
						'heading' => $greendawn_data['trust_heading'],
						'copy'    => $greendawn_data['trust_copy'],
					)
				);
			}
			greendawn_reading_cta( $greendawn_data );
		}
		?>
	</article>
	<?php
endwhile;
get_footer();
