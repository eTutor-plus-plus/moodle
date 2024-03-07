<?php

/**
 * CLI script for configuring Moodle for eTutor dev environment.
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

$usage = "Configures Moodle for eTutor dev environment.

Usage:
    # php configure-moodle.php [--help|-h]

Options:
    -h --help                   Print this help.
";

list($options, $unrecognised) = cli_get_params(['help' => false], ['h' => 'help']);

if ($unrecognised) {
	$unrecognised = implode(PHP_EOL . '  ', $unrecognised);
	cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
	cli_writeln($usage);
	exit(2);
}

// Enable REST protocol
$class = \core_plugin_manager::resolve_plugininfo_class('webservice');
$class::enable_plugin('rest', true);
cli_writeln('Enabled REST protocol.');

// Create REST role
$roleId = create_role('Web Service', 'web_service', 'Web Service for eTutor Task Administration', 'none');
cli_writeln('Created REST role with ID ' . $roleId);
set_role_contextlevels($roleId, [10]);
cli_writeln('Set role context levels');
assign_capability('webservice/rest:use', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/category:manage', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/category:viewhiddencategories', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/question:managecategory', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/question:add', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/question:editall', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/question:moveall', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/question:tagall', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('moodle/question:viewall', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('qbank/customfields:changelockedcustomfields', CAP_ALLOW, $roleId, context_system::instance(), true);
assign_capability('qbank/customfields:viewhiddencustomfields', CAP_ALLOW, $roleId, context_system::instance(), true);
cli_writeln('Assigned capabilities to role');

// Create REST user
$userData = new stdClass();
$userData->username = 'etutor_sync';
$userData->firstname = 'Sync';
$userData->lastname = 'eTutor';
$userData->email = 'etutor@dke.uni-linz.ac.at';
$userData->maildisplay = 0;
$userData->confirmed = 1;
$userData->auth = 'manual';
$userData->mnethostid = $CFG->mnet_localhost_id;
$userData->password = hash_internal_user_password('some-random-password-with-special!CharsAndNumbers123');
$userId = user_create_user($userData, true, false);
cli_writeln('Created REST user with ID ' . $userId);

// Assign user to role
role_assign($roleId, $userId, context_system::instance());
cli_writeln('Assigned user to role');

// Add external service
$webservicemanager = new webservice();
$service = new stdClass();
$service->name = 'eTutor Sync';
$service->shortname = 'etutor_sync';
$service->enabled = 1;
$service->restrictedusers = 1;
$serviceId = $webservicemanager->add_external_service($service);
cli_writeln('Added external service with ID ' . $serviceId);
$webservicemanager->add_external_function_to_service('core_course_create_categories', $serviceId);
$webservicemanager->add_external_function_to_service('core_course_get_categories', $serviceId);
$webservicemanager->add_external_function_to_service('core_course_update_categories', $serviceId);
$webservicemanager->add_external_function_to_service('local_etutorsync_create_question_category', $serviceId);
$webservicemanager->add_external_function_to_service('local_etutorsync_update_question_category', $serviceId);
$webservicemanager->add_external_function_to_service('local_etutorsync_create_question', $serviceId);
$webservicemanager->add_external_function_to_service('local_etutorsync_deprecate_old_question', $serviceId);
$webservicemanager->add_external_function_to_service('local_etutorsync_update_question', $serviceId);
cli_writeln('Added functions to external service');
$serviceuser = new stdClass();
$serviceuser->externalserviceid = $serviceId;
$serviceuser->userid = $userId;
$webservicemanager->add_ws_authorised_user($serviceuser);
cli_writeln('Added user to external service');

// Create custom question fields
use core_customfield\api;
use core_customfield\category_controller;
use core_customfield\field_controller;

$category = category_controller::create(0, (object) ['name' => 'eTutor', 'component' => 'qbank_customfields', 'area' => 'question', 'itemid' => 0]);
api::save_category($category);

$field = field_controller::create(0, (object) ['name' => 'Task-ID', 'shortname' => 'etutor_task_id', 'description' => 'The eTutor task id.', 'type' => 'text', 'categoryid' => $category->get('id')], $category);
api::save_field_configuration($field, (object) ['configdata' => ['locked' => 1, 'visibility' => 1]]);

$field = field_controller::create(0, (object) ['name' => 'Last Modification', 'shortname' => 'etutor_last_modification', 'description' => 'The timestamp the task was last modified in the eTutor system.', 'type' => 'date', 'categoryid' => $category->get('id')], $category);
api::save_field_configuration($field, (object) ['configdata' => ['locked' => 1, 'visibility' => 1, 'includetime' => 1]]);

cli_writeln('Created eTutor question field category with ID ' . $category->get('id'));

// Enable multi-language content
$class = core_plugin_manager::resolve_plugininfo_class('filter');
$class::enable_plugin('multilang', 1);
cli_writeln('Enabled multi-language content.');