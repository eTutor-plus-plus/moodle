<?php

namespace local_etutorsync\external;
use core_external\external_api;
use core_external\restricted_context_exception;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use dml_exception;
use dml_transaction_exception;
use invalid_parameter_exception;
use moodle_exception;
use required_capability_exception;
use stdClass;

require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/editlib.php');
class deprecate_old_question extends external_api {

    public static function execute_parameters() : external_function_parameters{
    {
        return new external_function_parameters([
            'data' => new external_single_structure([
                'question_id' => new external_value(PARAM_INT, 'The id of the question which should be marked deprecated.'),
                'course_category_id' => new external_value(PARAM_INT, 'The id of the category.'),
                'title_extension' => new external_value(PARAM_RAW, 'The title name extension.'),
            ], 'The input data.')
        ]);
    }

    }

    public static function execute(array $data) : array
    {
        global $USER, $DB;
    
        // Decode the question data from JSON
        ['data' => $data] = self::validate_parameters(self::execute_parameters(), ['data' => $data]);   

        $cat_context = \context_coursecat::instance($data['course_category_id']);
        self::validate_context($cat_context);
        require_capability('moodle/question:editall', $cat_context);

        //start transaction
        $transaction = $DB->start_delegated_transaction();


        //updates the Test of the Question to mark it as deprecated
        $question = $DB->get_record('question', array('id' => $data['question_id']), '*', MUST_EXIST);
        $question->name = $data['title_extension'] . $question->name;
        $DB->update_record('question', $question);

        
        $transaction->allow_commit();

        return ['questionid' => $question->id]; // Return updated Question Id

    }

    public static function execute_returns():external_single_structure {
        return new external_single_structure(
            array(
                'questionid' => new external_value(PARAM_INT, 'Question ID')
            )
        );
    }
}