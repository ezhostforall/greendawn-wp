<?php
/**
 * Approved Workplace campaign landing-page content, checked 19 November 2026.
 *
 * @package Greendawn_Core
 */

defined( 'ABSPATH' ) || exit;

return array(
	'guide_link'           => 'Check your eligibility',
	'guide_title'          => 'Could your workplace qualify?',
	'guide_intro'          => 'Start with your site and who will use the chargers. These are the key points to discuss with our team.',
	'closed_guide'         => 'Planning your workplace charging?',
	'closed_summary'       => 'Tell us about your site and we’ll discuss a practical installation plan. If you hold a grant voucher, check its expiry date.',
	'metrics'              => array(
		array(
			'prefix' => 'Up to',
			'value'  => '£500',
			'copy'   => 'Grant funding per charging socket.',
		),
		array(
			'prefix' => 'Up to',
			'value'  => '75%',
			'copy'   => 'Of eligible purchase and installation costs, including VAT.',
		),
		array(
			'prefix' => 'Maximum',
			'value'  => '40',
			'copy'   => 'Sockets per applicant, across all sites.',
		),
	),
	'eligibility'          => array(
		array(
			'title' => 'Your organisation',
			'copy'  => 'An eligible business, charity, public sector organisation or small accommodation business.',
		),
		array(
			'title' => 'Dedicated parking',
			'copy'  => 'Existing off-road spaces designated to your organisation and linked to your workplace.',
		),
		array(
			'title' => 'Permission to install',
			'copy'  => 'You own the site, or have the necessary landlord and landowner consent.',
		),
		array(
			'title' => 'The right charging use',
			'copy'  => 'Staff or fleet charging for businesses. Broader use is permitted for charities, public authorities and small accommodation businesses.',
		),
	),
	'criteria_note'        => 'Full scheme criteria apply, including prior grants, subsidy limits and mandatory installation rules.',
	'guidance_label'       => 'Read the official eligibility guidance',
	'panel_kicker'         => 'Your next step',
	'panel_copy'           => 'Tell us where you need charging and what you’re planning. We’ll discuss eligibility, electrical capacity and a realistic timeline.',
	'panel_cta'            => 'Discuss my eligibility',
	'panel_hint'           => 'Not sure how many chargers you need? We can work through that together.',
	'panel_points'         => array( array( 'title' => 'Site capacity' ), array( 'title' => 'Charger selection' ), array( 'title' => 'Installation planning & aftercare' ) ),
	'process_kicker'       => 'A clear route to installation',
	'process_title'        => 'From first plans to charging at work.',
	'process_intro'        => 'Greendawn helps you work through the practical decisions, so your charging fits your site and the way you’ll use it.',
	'closed_process_title' => 'Plan your next charging installation.',
	'process_steps'        => array(
		array(
			'label'        => 'PLAN',
			'title'        => 'Discuss your site.',
			'copy'         => 'Work through charger numbers, electrical capacity and layout. Arrange a site survey before applying.',
			'closed_title' => 'Discuss your site.',
			'closed_copy'  => 'Work through charger numbers, electrical capacity and layout. Agree the right approach for your workplace.',
		),
		array(
			'label'        => 'APPLY',
			'title'        => 'Get your voucher.',
			'copy'         => 'Your organisation applies to OZEV. Receive voucher approval before installation begins.',
			'closed_title' => 'Agree your plan.',
			'closed_copy'  => 'Choose equipment and agree a practical installation timeline. If you hold a voucher, check its expiry before committing.',
		),
		array(
			'label'        => 'INSTALL',
			'title'        => 'Install and claim.',
			'copy'         => 'An OZEV-authorised installer fits eligible equipment, deducts the grant from your invoice and submits the claim.',
			'closed_title' => 'Install and care.',
			'closed_copy'  => 'Coordinate installation and plan for ongoing care. Existing voucher holders should confirm claim requirements with their installer.',
		),
	),
	'voucher_note'         => 'Installation and the grant claim must be completed within 180 days of your voucher being issued. Check your voucher’s expiry date when planning.',
	'faq_kicker'           => 'Before you get started',
	'faq_title'            => 'A few useful answers.',
	'faq_intro'            => 'Have a question about your own site? Bring it to the first conversation.',
	'faqs'                 => array(
		array(
			'question'   => 'Can we apply if we lease our premises?',
			'answer'     => 'Yes, provided the site qualifies and you have the necessary landlord and landowner permission.',
			'visibility' => 'active',
		),
		array(
			'question'   => 'Already have a grant voucher?',
			'answer'     => 'Tell us when it was issued and when it expires. Installation and the installer’s claim must be completed within the voucher’s 180-day validity period.',
			'visibility' => 'always',
		),
		array(
			'question'   => 'Should we apply before installation?',
			'answer'     => 'Yes. Voucher approval must be in place before installation begins. Use an OZEV-authorised installer and eligible commercial charging equipment.',
			'visibility' => 'active',
		),
		array(
			'question'   => 'Is {{planning_short}} the official grant deadline?',
			'answer'     => 'No. It is Greendawn’s planning target for agreeing your EV charging timeline. The scheme closes on {{expiry}}. Your voucher has its own expiry date.',
			'visibility' => 'active',
		),
	),
	'installer_url'        => 'https://www.gov.uk/guidance/workplace-charging-scheme-guidance-for-installers',
	'installer_label'      => 'Official installation guidance',
	'closing_kicker'       => 'Let’s work out your next step',
	'closing_title'        => 'Make a plan for your workplace.',
	'closing_copy'         => 'Share your site location, who will use the chargers and your estimated charger count. We’ll take it from there.',
	'source_label'         => 'Based on official Workplace Charging Scheme guidance',
	'checked_date'         => '20261119',
	'source_note'          => 'Funding is subject to eligibility and scheme conditions.',
	'archive_title'        => 'The Workplace Charging Scheme closed on {{expiry}}.',
	'archive_copy'         => 'This campaign is now archived. We can still help plan your workplace charging installation. If you hold a voucher, check its expiry date.',
);
