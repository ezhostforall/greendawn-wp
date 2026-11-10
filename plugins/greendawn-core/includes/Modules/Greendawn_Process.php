<?php
/**
 * Approved process defaults and the existing homepage field-key adapter.
 *
 * @package Greendawn_Core
 */

namespace Greendawn_Core\Modules;

defined( 'ABSPATH' ) || exit;

/**
 * One default definition; saved copy remains owned by each existing Page.
 */
final class Greendawn_Process {

	/**
	 * Supplies the approved language for new process uses.
	 *
	 * @return array<string,string>
	 */
	public static function defaults(): array {
		return array(
			'process_title' => __( 'A clear route from requirement to operation.', 'greendawn' ),
			'process_intro' => __( 'You do not need a finished specification to start. Each stage establishes the decisions and information needed for the next.', 'greendawn' ),
			'consult_title' => __( 'Consult', 'greendawn' ),
			'consult_copy'  => __( 'Tell us about your vehicles, users, sites and objectives. We establish the requirement and recommend the next step.', 'greendawn' ),
			'design_title'  => __( 'Design', 'greendawn' ),
			'design_copy'   => __( 'Assess power, layout and delivery constraints. Where a technical survey is needed, agree its scope before developing the design and proposal.', 'greendawn' ),
			'install_title' => __( 'Install', 'greendawn' ),
			'install_copy'  => __( 'Coordinate the agreed electrical and civil works, equipment, testing and commissioning. Plan access and handover with your team.', 'greendawn' ),
			'manage_title'  => __( 'Manage', 'greendawn' ),
			'manage_copy'   => __( 'Put the agreed software, monitoring, maintenance and fault-support arrangements in place, with clear ongoing responsibilities.', 'greendawn' ),
		);
	}

	/**
	 * Maps approved defaults to legacy homepage keys without migrating saved metadata.
	 *
	 * @return array<string,string>
	 */
	public static function homepage_defaults(): array {
		$defaults = self::defaults();
		$legacy   = array(
			'process_title' => $defaults['process_title'],
			'process_intro' => $defaults['process_intro'],
		);
		foreach ( array( 'consult', 'design', 'install', 'manage' ) as $stage ) {
			$legacy[ $stage . '_label' ]       = $defaults[ $stage . '_title' ];
			$legacy[ $stage . '_description' ] = $defaults[ $stage . '_copy' ];
		}
		return $legacy;
	}
}
