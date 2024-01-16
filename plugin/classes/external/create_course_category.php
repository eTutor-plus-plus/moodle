<?php

namespace local_etutorsync\external;

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

/**
 * Web Service to create a new course category.
 *
 * @package   local_etutorsync
 * @category  external
 */
class create_course_category extends \core_external\external_api
{
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters
    {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The organizational unit identifier.'),
                'name' => new external_value(PARAM_RAW, 'The name of the organizational unit.')
            ], 'The input data.')
        ]);
    }

    /**
     * External method execution
     *
     * @param object $data The data.
     * @return array
     */
    public static function execute(object $data): array
    {
        // Validate all of the parameters
        ['data' => $data] = self::validate_parameters(self::execute_parameters(), ['data' => $data]);

        // Perform security checks, for example:
        $system_context = \context_system::instance();
        self::validate_context($system_context);
        require_capability('moodle/category:manage', $system_context);

        // Create the course category
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $id = $DB->insert_record('course_categories', [ // see https://www.examulator.com/er/output/tables/course_categories.html
            'name' => $data['name'],
            'idnumber' => $data['id']
        ]);
        $transaction->allow_commit();

        // Return a value as described in the returns function.
        return ['id' => $id];
    }

    /**
     * Returns description of method result value.
     *
     * @return \core_external\external_description
     */
    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Id of the created course category')
        ]);
    }
}