<?php
/**
 * Web service function declarations for the local_etutorsync plugin.
 *
 * @package   local_etutorsync
 */
$functions = [
	'local_etutorsync_create_question_category' => [
		'classname' => 'local_etutorsync\external\create_question_category',
		'description' => 'Create a question category.',
		'type' => 'write',
		'ajax' => false,
		'services' => []
	]
];