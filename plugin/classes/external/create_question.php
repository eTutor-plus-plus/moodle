<?php

namespace local_etutorsync\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use stdClass;

/**
 * Web Service to create a new question.
 *
 * @package   local_etutorsync
 * @category  external
 */
class create_question extends external_api
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
                'category_id' => new external_value(PARAM_INT, 'The name of the category this question should belong to.'),
                'id' => new external_value(PARAM_INT, 'The identifier of the Question.'),
                'name' => new external_value(PARAM_RAW, 'The task name.'),
                'questiontext' => new external_value(PARAM_RAW, 'The description of the Question.'),
                'points' => new external_value(PARAM_RAW, 'The Maximum points achiveable'),
                'coderunnertype' => new external_value(PARAM_RAW, 'The tasktype of the question'),
                'course_category_id' => new external_value(PARAM_INT, 'The id of the category.'),
                'templateparams' => new external_value(PARAM_RAW, 'The template information to link the question to the task through coderunner'),
            ], 'The input data.')
        ]);
    }

    /**
     * External method execution.
     * 
     * @param array $data The data.
     * @return array The result.
     */
    public static function execute(array $data): array
    {
        global $USER, $DB;

        // Validate parameters
        ['data' => $data] = self::validate_parameters(self::execute_parameters(), ['data' => $data]);

        // Perform security checks
        $cat_context = \context_coursecat::instance($data['course_category_id']);
        self::validate_context($cat_context);
        require_capability('moodle/question:add', $cat_context);

        //start transaction
        $transaction = $DB->start_delegated_transaction();

        // Prepare the question base data
        $question = new stdClass();
        $question->category = $data['category_id'];
        $question->name = $data['name'];
        $question->questiontext = $data['questiontext'];
        $question->questiontextformat = FORMAT_HTML;
        $question->generalfeedback = '';
        $question->generalfeedbackformat = FORMAT_HTML;
        $question->defaultmark = $data['points'];
        $question->penalty = 0.0;
        $question->qtype = 'coderunner';
        $question->length = 1;
        $question->timecreated = time();
        $question->timemodified = $question->timecreated;
        $question->stamp = make_unique_id_code();
        $question->createdby = $USER->id;
        $question->modifiedby = $USER->id;
        $question->id = $DB->insert_record('question', $question);

        // Prepare specific data for CodeRunner questions
        $coderunner_options = new stdClass();
        $coderunner_options->questionid = $question->id;
        $coderunner_options->coderunnertype = $data['coderunnertype']; // e.g., 'python3'
        $coderunner_options->prototypetype = 0;
        $coderunner_options->allornothing = 1;
        $coderunner_options->penaltyregime = '0';
        $coderunner_options->precheck = 3;
        $coderunner_options->hidecheck = 1;
        $coderunner_options->answerboxlines = 10;
        $coderunner_options->useace = 1; // Use Ace editor for syntax highlighting
        $coderunner_options->templateparams = $data['templateparams'];
        $coderunner_options->templateparamsvald = $data['templateparams'];
        $coderunner_options->hoisttemplateparams = 1;
        $coderunner_options->displayfeedback = 1;
        $DB->insert_record('question_coderunner_options', $coderunner_options);

        // Insert test cases for precheck
        $testcase = new stdClass();
        $testcase->questionid = $question->id;
        $testcase->useasexample = 0;
        $testcase->testcode = 'DIAGNOSE';
        $testcase->testtype = 1;
        $testcase->hiderestiffail = 0;
        $testcase->display = 'SHOW';
        $testcase->mark = $data['points'];
        $DB->insert_record('question_coderunner_tests', $testcase);

        // Insert test cases for check
        $testcase = new stdClass();
        $testcase->questionid = $question->id;
        $testcase->useasexample = 0;
        $testcase->testcode = 'SUBMIT';
        $testcase->testtype = 0;
        $testcase->hiderestiffail = 0;
        $testcase->display = 'HIDE';
        $testcase->mark = $data['points'];
        $DB->insert_record('question_coderunner_tests', $testcase);

        // a connection table from question to version where the id is added
        $questionbank = new stdClass();
        $questionbank->questioncategoryid = $data['category_id'];
        $questionbank->idnumber = $question->id;
        $questionbank->questionbankid = $DB->insert_record('question_bank_entries', $questionbank);

        // adds the question to questionbankentry which refers to the category
        $questionversion = new stdClass();
        $questionversion->questionbankentryid = $questionbank->questionbankid;
        $questionversion->questionid = $question->id;
        $DB->insert_record('question_versions', $questionversion);

        $transaction->allow_commit();

        return ['questionid' => $question->id]; // Return the created question ID
    }

    /**
     * Returns description of method result value.
     *
     * @return \core_external\external_description
     */
    public static function execute_returns(): external_single_structure
    {
        return new external_single_structure([
            'questionid' => new external_value(PARAM_INT, 'Id of the created question')
        ]);
    }
}