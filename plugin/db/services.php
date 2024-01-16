<?php
/**
 * Web service function declarations for the local_etutorsync plugin.
 *
 * @package   local_etutorsync
 */

$functions = [
	'local_etutorsync_create_course_category' => [
		'classname' => 'local_etutorsync\external\create_course_category',
		'description' => 'Create a course category.',
		'type' => 'write',
		'ajax' => false,
		'services' => [],
	],
	// 'local_etutorsync_create_question' => [
	//     'classname' => 'local_etutorsync\external\create_question',
	//     'description' => 'Create a CodeRunner question',
	//     'type' => 'write',
	// 	'ajax' => false,
	//     'services' => [],
	// ],
];