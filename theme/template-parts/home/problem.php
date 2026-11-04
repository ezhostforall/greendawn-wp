<?php
/**
 * The coordination problem and Greendawn's approach.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;
?>
<section id="problem" class="gd-journey-section gd-problem" aria-labelledby="gd-problem-title">
	<div class="gd-home-shell gd-problem__layout">
		<div class="gd-problem__intro">
			<p class="gd-eyebrow gd-journey-eyebrow"><?php echo esc_html( greendawn_home_field( 'problem_eyebrow' ) ); ?></p>
			<h2 id="gd-problem-title"><?php echo esc_html( greendawn_home_field( 'problem_title' ) ); ?></h2>
			<p><?php echo esc_html( greendawn_home_field( 'problem_intro' ) ); ?></p>
			<p><?php echo esc_html( greendawn_home_field( 'problem_summary' ) ); ?></p>
		</div>

		<div class="gd-problem__model">
			<p class="gd-problem__label"><?php echo esc_html( greendawn_home_field( 'problem_model_label' ) ); ?></p>
			<ul class="gd-problem__fragments" role="list">
				<?php foreach ( array( 'requirements', 'power', 'groundworks', 'equipment', 'software', 'support' ) as $greendawn_fragment ) : ?>
					<li><?php echo esc_html( greendawn_home_field( 'problem_fragment_' . $greendawn_fragment ) ); ?></li>
				<?php endforeach; ?>
			</ul>
			<div class="gd-problem__connector" aria-hidden="true"><span></span></div>
			<div class="gd-problem__answer">
				<p class="gd-problem__answer-label"><?php echo esc_html( greendawn_home_field( 'problem_answer_label' ) ); ?></p>
				<strong><?php echo esc_html( greendawn_home_field( 'problem_answer_title' ) ); ?></strong>
				<p><?php echo esc_html( greendawn_home_field( 'problem_answer_copy' ) ); ?></p>
			</div>
		</div>
	</div>
</section>
