<?php
/**
 * Published team record; no login accounts, public profile or external embeds.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_member = get_post( absint( $args['post_id'] ?? 0 ) );
if ( ! $greendawn_member || 'team_member' !== $greendawn_member->post_type || 'publish' !== $greendawn_member->post_status || post_password_required( $greendawn_member ) ) {
	return;
}
$greendawn_member_name = get_the_title( $greendawn_member );
$greendawn_member_role = (string) get_post_meta( $greendawn_member->ID, 'greendawn_team_role', true );
$greendawn_member_bio  = (string) get_post_meta( $greendawn_member->ID, 'greendawn_team_biography', true );
$greendawn_member_link = esc_url( get_post_meta( $greendawn_member->ID, 'greendawn_team_linkedin', true ), array( 'https', 'http' ) );
$greendawn_member_meta = (string) get_post_meta( $greendawn_member->ID, 'greendawn_team_connections', true );
$greendawn_portrait    = wp_get_attachment_image(
	get_post_thumbnail_id( $greendawn_member ),
	'medium_large',
	false,
	array(
		'alt'     => $greendawn_member_name,
		'loading' => 'lazy',
		'sizes'   => '(max-width: 42rem) calc(100vw - 2rem), (max-width: 61rem) 45vw, 30vw',
	)
);
?>
<article class="gd-team-card">
	<?php
	if ( $greendawn_portrait ) :
		?>
		<figure><?php echo $greendawn_portrait; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped attachment markup from WordPress. ?></figure><?php endif; ?>
	<div class="gd-team-card__copy">
		<h3><?php echo esc_html( $greendawn_member_name ); ?></h3>
		<?php
		if ( '' !== $greendawn_member_role ) :
			?>
			<p class="gd-team-card__role"><?php echo esc_html( $greendawn_member_role ); ?></p><?php endif; ?>
		<?php
		if ( '' !== $greendawn_member_meta ) :
			?>
			<p class="gd-team-card__meta"><?php echo esc_html( $greendawn_member_meta ); ?></p><?php endif; ?>
		<?php echo wpautop( esc_html( $greendawn_member_bio ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text escaped before paragraph formatting. ?>
		<?php
		if ( '' !== $greendawn_member_link ) :
			?>
			<a class="gd-page-text-link" href="<?php echo esc_url( $greendawn_member_link ); ?>"><?php esc_html_e( 'View Profile on LinkedIn', 'greendawn' ); ?><span class="screen-reader-text"> — <?php echo esc_html( $greendawn_member_name ); ?></span><?php get_template_part( 'template-parts/home/arrow' ); ?></a><?php endif; ?>
	</div>
</article>
