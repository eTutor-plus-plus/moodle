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
        'capabilities' => 'moodle/question:managecategory'
	],
    'local_etutorsync_update_question_category' => [
        'classname' => 'local_etutorsync\external\update_question_category',
        'description' => 'Update a question category.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/question:managecategory'
    ],
    'local_etutorsync_create_question' => [
        'classname'   => 'local_etutorsync\external\create_question',
        'description' => 'Saves a new coderunner question.',
        'type'        => 'write',
        'ajax'        => false,
        'capabilities' => 'moodle/question:add'
    ],
    'local_etutorsync_deprecate_old_question' => [
        'classname'   => 'local_etutorsync\external\deprecate_old_question',
        'description' => 'Marks old Questions.',
        'type'        => 'write',
        'ajax'        => false,
        'capabilities' => 'moodle/question:editall'
    ],
    'local_etutorsync_update_question' => [
        'classname'   => 'local_etutorsync\external\update_question',
        'description' => 'Updates existing Question.',
        'type'        => 'write',
        'ajax'        => false,
        'capabilities' => 'moodle/question:editall'
    ]
];