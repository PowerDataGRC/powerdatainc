<?php
/**
 * Altitude Pro child theme.
 *
 * @package Altitude Pro
 * @author  StudioPress
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/altitude/
 */

/**
 * Genesis responsive menus settings. (Requires Genesis 3.0+.)
 */
return [
	'script' => [
		'menuClasses' => [
			'combine' => [
				'.nav-primary',
				'.nav-secondary',
			],
			'others'  => [],
		],
	],
	'extras' => [
		'media_query_width' => '800px',
	],
];
