<?php
/** Disposable component composition; this is not a public Guide template. */
if ( 'local' !== wp_get_environment_type() || 'http://localhost:8080' !== home_url() ) { return; }
$greendawn_fixture = get_option( 'greendawn_reading_fixture', array() );
$greendawn_empty = is_page( 'reading-components-empty' );
$greendawn_legacy = is_page( 'reading-components-legacy' );
$greendawn_guide = greendawn_guide_view( $greendawn_fixture['guide'] ?? 0 );
$greendawn_sections = greendawn_reading_sections( $greendawn_guide['sections'] ?? array() );
$greendawn_card = $greendawn_guide ? greendawn_article_card_data( get_post( $greendawn_guide['id'] ) ) : array();
get_header();
the_post();
?>
<article class="greendawn-page">
<?php
get_template_part( 'template-parts/components/page-hero', null, array_merge(
	array( 'heading' => 'Shared component review', 'intro' => 'A disposable component fixture.', 'image_id' => $greendawn_empty ? 0 : $greendawn_fixture['image'] ),
	$greendawn_legacy ? array() : array( 'variant' => 'guide', 'kicker' => 'Practical guidance', 'image_position' => 'upper', 'metadata' => $greendawn_empty ? array() : $greendawn_guide, 'cta' => array( 'action' => 'enquiry', 'url' => home_url( '/contact/' ), 'label' => 'Discuss this project', 'entry_point' => 'hero', 'intent' => 'ev-charging' ) )
) );
?>
<div class="gd-shell gd-section">
<?php if ( $greendawn_legacy ) : ?>
	<?php get_template_part( 'template-parts/components/feature-grid', null, array( 'heading' => 'Existing numbered features', 'items' => array( array( 'title' => 'First feature', 'copy' => 'Existing numbered presentation.' ) ) ) ); ?>
	<?php get_template_part( 'template-parts/components/articles', null, array( 'heading' => 'Existing articles', 'posts' => array( get_post( $greendawn_fixture['post'] ) ) ) ); ?>
<?php elseif ( $greendawn_empty ) : ?>
	<p>Empty optional components are omitted.</p>
	<?php foreach ( array( 'direct-answer', 'reading-contents', 'reading-sections', 'source-list', 'coverage-list', 'editorial-meta' ) as $greendawn_part ) { get_template_part( 'template-parts/components/' . $greendawn_part, null, array() ); } ?>
<?php else : ?>
	<?php get_template_part( 'template-parts/components/guide-filters', null, array( 'url' => get_permalink(), 'query' => 'load & supply', 'topic' => 'power', 'topics' => array( array( 'slug' => 'power', 'name' => 'Site power', 'count' => 1 ), array( 'slug' => 'planning', 'name' => 'Planning', 'count' => 2 ) ) ) ); ?>
	<div class="gd-section">
		<h2>Featured guidance</h2>
		<?php get_template_part( 'template-parts/components/article-card', null, array_merge( $greendawn_card, array( 'variant' => 'guide-featured' ) ) ); ?>
	</div>
	<div class="gd-reading-layout">
		<?php get_template_part( 'template-parts/components/reading-contents', null, array( 'sections' => $greendawn_sections ) ); ?>
		<div class="gd-reading-body">
			<?php get_template_part( 'template-parts/components/direct-answer', null, array( 'answer' => $greendawn_guide['answer'] ?? '' ) ); ?>
			<?php get_template_part( 'template-parts/components/reading-sections', null, array( 'sections' => $greendawn_sections ) ); ?>
			<?php get_template_part( 'template-parts/components/source-list', null, array( 'items' => $greendawn_guide['sources'] ?? array() ) ); ?>
			<?php get_template_part( 'template-parts/components/faq', null, array( 'heading' => 'More questions', 'items' => $greendawn_guide['faqs'] ?? array() ) ); ?>
		</div>
	</div>
	<h2>Coverage</h2>
	<?php get_template_part( 'template-parts/components/coverage-list', null, array( 'items' => array( array( 'name' => 'Birmingham' ), array( 'name' => 'Shropshire' ) ) ) ); ?>
	<?php get_template_part( 'template-parts/components/feature-grid', null, array( 'variant' => 'icons', 'heading' => 'Services', 'items' => array( array( 'title' => 'Workplace charging', 'copy' => 'Plan around how the site operates.', 'icon' => 'bolt' ), array( 'title' => 'Fleet charging', 'copy' => 'Consider dwell times and vehicle demand.', 'icon' => 'fleet' ), array( 'title' => 'Property', 'copy' => 'Coordinate with your landlord.', 'icon' => 'property' ) ) ) ); ?>
<?php endif; ?>
</div>
<?php
get_template_part( 'template-parts/components/media-copy', null, array_merge( array( 'heading' => 'Published project evidence', 'copy' => 'A concise supported project extract.', 'image_id' => $greendawn_empty ? 0 : $greendawn_fixture['image'] ), $greendawn_legacy ? array() : array( 'tone' => 'dark', 'kicker' => 'Project experience', 'caption' => 'Fixture image, without a project location claim.', 'link' => array( 'label' => 'View the published source', 'url' => home_url( '/gallery/' ) ) ) ) );
if ( ! $greendawn_legacy && ! $greendawn_empty ) {
	get_template_part( 'template-parts/components/articles', null, array( 'layout' => 'grid', 'heading' => 'Related guides', 'posts' => array( get_post( $greendawn_fixture['guide'] ), get_post( $greendawn_fixture['guide_two'] ), get_post( $greendawn_fixture['guide_three'] ), get_post( $greendawn_fixture['draft'] ), get_post( $greendawn_fixture['protected'] ) ) ) );
}
get_template_part( 'template-parts/components/cta-band', null, array_merge( array( 'heading' => 'Plan your next step', 'copy' => 'Use the existing enquiry journey.', 'label' => 'Talk to the team', 'url' => home_url( '/contact/' ) ), $greendawn_legacy ? array() : array( 'action' => 'enquiry', 'entry_point' => 'final-cta', 'intent' => 'ev-charging' ) ) );
?>
</article>
<?php get_footer(); ?>
