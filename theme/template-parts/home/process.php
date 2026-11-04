<?php
/**
 * Delivery process using the same stage labels as the hero.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_stages = greendawn_home_stages();
?>
<section id="process" class="gd-journey-section gd-process" aria-labelledby="gd-process-title">
	<div class="gd-home-shell">
		<div class="gd-process__intro">
			<div>
				<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( implode( ' · ', $greendawn_stages ) ); ?></p>
				<h2 id="gd-process-title"><?php echo esc_html( greendawn_home_field( 'process_title' ) ); ?></h2>
			</div>
			<p><?php echo esc_html( greendawn_home_field( 'process_intro' ) ); ?></p>
		</div>
		<ol class="gd-process__steps" role="list">
			<?php $greendawn_stage_number = 0; ?>
			<?php foreach ( $greendawn_stages as $greendawn_stage => $greendawn_label ) : ?>
				<li>
					<span aria-hidden="true"><?php echo esc_html( sprintf( '%02d', ++$greendawn_stage_number ) ); ?></span>
					<h3><?php echo esc_html( $greendawn_label ); ?></h3>
					<p><?php echo esc_html( greendawn_home_field( $greendawn_stage . '_description' ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
