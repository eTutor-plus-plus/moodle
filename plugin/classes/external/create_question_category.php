<?php

namespace local_etutorsync\external;

use core_external\external_api;
use core_external\restricted_context_exception;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web Service to create a new question category.
 *
 * @package   local_etutorsync
 * @category  external
 */
class create_question_category extends external_api
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
                'course_category_id' => new external_value(PARAM_INT, 'The name of the course group this category should belong to.'),
                'parent_question_category_id' => new external_value(PARAM_INT, 'The identifier of the parent question category.', VALUE_DEFAULT, 0),
                'id' => new external_value(PARAM_INT, 'The task category identifier.'),
                'name' => new external_value(PARAM_RAW, 'The name of the task category.')
            ], 'The input data.')
        ]);
    }

    /**
     * External method execution
     *
     * @param array $data The data.
     * @return array The result.
     * @throws restricted_context_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @throws \coding_exception
     */
    public static function execute(array $data): array
    {
        // Validate parameters
        ['data' => $data] = self::validate_parameters(self::execute_parameters(), ['data' => $data]);

        // Perform security checks
        $cat_context = \context_coursecat::instance($data['course_category_id']);
        self::validate_context($cat_context);
        require_capability('moodle/question:managecategory', $cat_context);

        // Create the question category (https://www.examulator.com/er/output/tables/question_categories.html)
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        // If parent id is zero, then find default category for the current context
        $parent_id = $data['parent_question_category_id'];
        if (is_null($parent_id) || $parent_id <= 0) {
            $parent = $DB->get_record('question_categories', [
                'contextid' => $cat_context->id,
                'parent' => 0
            ]);
            if (is_null($parent))
                throw new \invalid_parameter_exception('Could not find top category for context');
            $parent_id = $parent->id;
        }

        // Insert
        $id = $DB->insert_record('question_categories', [
            'name' => $data['name'],
            'idnumber' => $data['id'],
            'contextid' => $cat_context->id,
            'info' => 'eTutor-generated category',
            'parent' => is_null($parent_id) ? 0 : $parent_id,
            'stamp' => 'eutor+' . date('ymdHis') . '+' . $data['id']
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
            'id' => new external_value(PARAM_INT, 'Id of the created question category')
        ]);
    }
}