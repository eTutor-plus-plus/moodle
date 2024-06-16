<?php

namespace local_etutorsync\external;

defined('MOODLE_INTERNAL') || die;

require_once ("{$CFG->libdir}/externallib.php");

use \core\event\question_category_created;
use \dml_exception;
use \dml_transaction_exception;
use \invalid_parameter_exception;
use \moodle_exception;
use \required_capability_exception;
use \stdClass;

/**
 * Web Service to create a new question category.
 *
 * @package   local_etutorsync
 * @category  external
 */
class create_question_category extends \external_api
{
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters
    {
        return new \external_function_parameters([
            'data' => new \external_single_structure([
                'course_category_id' => new \external_value(PARAM_INT, 'The name of the course group this category should belong to.'),
                'parent_question_category_id' => new \external_value(PARAM_INT, 'The identifier of the parent question category.', VALUE_DEFAULT, 0),
                'id' => new \external_value(PARAM_RAW, 'The task category identifier.'),
                'name' => new \external_value(PARAM_RAW, 'The name of the task category.')
            ], 'The input data.')
        ]);
    }

    /**
     * External method execution
     *
     * @param array $data The data.
     * @return array The result.
     * @throws \restricted_context_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @throws \moodle_exception
     */
    public static function execute(array $data): array
    {
        // Validate parameters
        ['data' => $data] = self::validate_parameters(self::execute_parameters(), ['data' => $data]);

        // Perform security checks
        $cat_context = \context_coursecat::instance($data['course_category_id'], MUST_EXIST);
        if ($cat_context == false)
            throw new invalid_parameter_exception('Could not find context. At least on course must exist in category for sync to work.');
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
                'parent' => 0,
                'name' => 'top'
            ]);
            if (is_null($parent) || $parent == false)
                throw new invalid_parameter_exception('Could not find top category for context');
            $parent_id = $parent->id;
        } else { // Validate parent id belongs to the same context
            if (!($DB->get_field('question_categories', 'contextid', ['id' => $parent_id]) == $cat_context->id)) {
                throw new moodle_exception('cannotinsertquestioncatecontext', 'question', '',
                    ['cat' => $data, 'ctx' => $cat_context->id]);
            }
        }

        // Set idnumber to null, if it already exists, to prevent errors
        $idnumber = $data['id'];
        if ($DB->record_exists('question_categories',
            ['idnumber' => $idnumber, 'contextid' => $cat_context->id])) {
            $idnumber = null;
        }

        // Insert
        $id = $DB->insert_record('question_categories', [
            'name' => $data['name'],
            'idnumber' => $data['id'],
            'contextid' => $cat_context->id,
            'info' => 'eTutor-generated category',
            'parent' => is_null($parent_id) ? 0 : $parent_id,
            'stamp' => make_unique_id_code()
        ]);
        $transaction->allow_commit();

        // Log the creation of this category
        $category = new stdClass();
        $category->id = $id;
        $category->contextid = $cat_context->id;
        $event = question_category_created::create_from_question_category_instance($category);
        $event->trigger();

        // Return a value as described in the returns function.
        return ['id' => $id];
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_single_structure
     */
    public static function execute_returns(): \external_single_structure
    {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Id of the created question category')
        ]);
    }
}