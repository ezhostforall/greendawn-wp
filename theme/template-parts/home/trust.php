<?php
/**
 * Greendawn homepage credentials and approved client marks.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="gd-trust gd-home-shell" aria-label="<?php esc_attr_e( 'Commercial EV charging delivery', 'greendawn' ); ?>">
	<div class="gd-trust__intro">
		<p class="gd-eyebrow"><?php esc_html_e( 'Commercial delivery', 'greendawn' ); ?></p>
		<p><?php greendawn_the_field( 'greendawn_home_trust_intro', 'text', false, __( 'Experience across dealership, fleet, workplace and destination sites.', 'greendawn' ) ); ?></p>
	</div>

	<div class="gd-trust__credentials" aria-label="<?php esc_attr_e( 'Greendawn delivery footprint', 'greendawn' ); ?>">
		<span>
			<strong><?php esc_html_e( 'GB', 'greendawn' ); ?></strong>
			<small><?php esc_html_e( 'England, Scotland and Wales', 'greendawn' ); ?></small>
		</span>
		<span>
			<strong><?php esc_html_e( 'AC–DC', 'greendawn' ); ?></strong>
			<small><?php esc_html_e( 'Equipment selected around the requirement', 'greendawn' ); ?></small>
		</span>
	</div>

	<ul class="gd-trust__clients" aria-label="<?php esc_attr_e( 'Selected Greendawn project experience', 'greendawn' ); ?>">
		<li class="gd-trust-client">
			<img class="gd-trust-client__logo gd-trust-client__logo--johnsons" src="<?php echo esc_url( get_theme_file_uri( '/assets/home/johnsons-cars.png' ) ); ?>" alt="<?php esc_attr_e( 'Johnsons Cars', 'greendawn' ); ?>" width="195" height="51" loading="lazy" decoding="async">
		</li>
		<li class="gd-trust-client">
			<img class="gd-trust-client__logo gd-trust-client__logo--salvation-army" src="<?php echo esc_url( get_theme_file_uri( '/assets/home/salvation-army.svg' ) ); ?>" alt="<?php esc_attr_e( 'The Salvation Army', 'greendawn' ); ?>" width="159" height="187" loading="lazy" decoding="async">
		</li>
	</ul>
</section>
