<?php
/**
 * Homepage adapter for the shared process; field keys and motion hooks stay stable.
 *
 * @package Greendawn
 */

defined( 'ABSPATH' ) || exit;

$greendawn_stages = greendawn_home_stages();
$greendawn_steps  = array();
foreach ( $greendawn_stages as $greendawn_stage => $greendawn_label ) {
	$greendawn_steps[] = array(
		'title' => $greendawn_label,
		'copy'  => greendawn_home_field( $greendawn_stage . '_description' ),
	);
}
get_template_part(
	'template-parts/components/process',
	null,
	array(
		'instance_id' => 'process',
		'title_id'    => 'gd-process-title',
		'eyebrow'     => implode( ' · ', $greendawn_stages ),
		'heading'     => greendawn_home_field( 'process_title' ),
		'intro'       => greendawn_home_field( 'process_intro' ),
		'phone_intro' => ! empty( $args['mobile_refresh'] ) ? __( 'One team coordinates power, groundworks, chargers, software and support. An outline of what your site needs is enough to start.', 'greendawn' ) : '',
		'phone_title' => ! empty( $args['mobile_refresh'] ) ? __( 'How your EV charging project works', 'greendawn' ) : '',
		'steps'       => $greendawn_steps,
	)
);
