<?php

namespace local_etutorsync\external;

defined('MOODLE_INTERNAL') || die;

require_once ("{$CFG->libdir}/externallib.php");

/**
 * Web Service to update an existing question category.
 *
 * @package   local_etutorsync
 * @category  external
 */
class update_question_category extends \external_api
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
                'id' => new \external_value(PARAM_INT, 'The question category identifier to update (moodle-id, not etutor-id).'),
                'course_category_id' => new \external_value(PARAM_INT, 'The id of the course group this category belongs to. Cannot be updated, required for security check.'),
                'parent_question_category_id' => new \external_value(PARAM_INT, 'The identifier of the parent question category.', VALUE_DEFAULT, 0),
                'name' => new \external_value(PARAM_RAW, 'The name of the task category.')
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

        // Update the question category (https://www.examulator.com/er/output/tables/question_categories.html)
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        // Load category
        $category = $DB->get_record('question_categories', ['id' => $data['id']]);

        // If parent id is zero, then find default category for the current context
        $parent_id = $data['parent_question_category_id'];
        if ($category->parent != $parent_id) {
            if (is_null($parent_id) || $parent_id <= 0) {
                $parent = $DB->get_record('question_categories', [
                    'contextid' => $cat_context->id,
                    'parent' => 0
                ]);
                if (is_null($parent) || $parent == false)
                    throw new \invalid_parameter_exception('Could not find top category for context');
                $parent_id = $parent->id;
            }
        }

        // Update
        $DB->update_record('question_categories', [
            'id' => $category->id,
            'name' => $data['name'],
            'parent' => $parent_id
        ]);
        $transaction->allow_commit();

        // Log the update of this category.
        $event = \core\event\question_category_updated::create_from_question_category_instance($category);
        $event->trigger();

        // Return a value as described in the returns function.
        return ['id' => $category->id];
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_single_structure
     */
    public static function execute_returns(): \external_single_structure
    {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Id of the updated question category')
        ]);
    }
}