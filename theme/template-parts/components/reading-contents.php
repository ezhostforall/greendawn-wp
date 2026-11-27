<?php
/**
 * One input section list supplies desktop navigation and mobile native disclosure.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
$greendawn_sections = greendawn_reading_sections( is_array( $args['sections'] ?? null ) ? $args['sections'] : array(), $args['prefix'] ?? 'guide' );
if ( ! $greendawn_sections ) {
	return;
}
$greendawn_label = greendawn_component_text( $args['label'] ?? __( 'On this page', 'greendawn' ) );
?>
<div class="gd-reading-contents" data-gd-reading-contents>
	<?php foreach ( array( 'desktop', 'mobile' ) as $greendawn_mode ) : ?>
		<?php if ( 'mobile' === $greendawn_mode ) : ?>
			<details class="gd-reading-contents__mobile"><summary><?php echo esc_html( $greendawn_label ); ?></summary>
		<?php else : ?>
			<div class="gd-reading-contents__desktop"><p class="gd-eyebrow"><?php echo esc_html( $greendawn_label ); ?></p>
		<?php endif; ?>
		<nav aria-label="<?php echo esc_attr( $greendawn_label ); ?>">
			<?php foreach ( $greendawn_sections as $greendawn_section ) : ?>
				<a href="#<?php echo esc_attr( $greendawn_section['id'] ); ?>"><?php echo esc_html( $greendawn_section['nav_label'] ? $greendawn_section['nav_label'] : $greendawn_section['heading'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php echo 'mobile' === $greendawn_mode ? '</details>' : '</div>'; ?>
	<?php endforeach; ?>
</div>
