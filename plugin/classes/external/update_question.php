<?php

namespace local_etutorsync\external;

defined('MOODLE_INTERNAL') || die;

require_once ("{$CFG->libdir}/externallib.php");
use stdClass;

/**
 * Web Service to update an existing question.
 *
 * @package   local_etutorsync
 * @category  external
 */
class update_question extends \external_api
{
    /**
     * Returns description of method parameters
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters
    {
        return new \external_function_parameters([
            'data' => new \external_single_structure([
                'category_id' => new \external_value(PARAM_INT, 'The name of the category this question should belong to.'),
                'id' => new \external_value(PARAM_INT, 'The identifier of the Question.'),
                'name' => new \external_value(PARAM_RAW, 'The task name.'),
                'questiontext' => new \external_value(PARAM_RAW, 'The description of the Question.'),
                'points' => new \external_value(PARAM_RAW, 'The Maximum points achiveable'),
                'coderunnertype' => new \external_value(PARAM_RAW, 'The tasktype of the question'),
                'course_category_id' => new \external_value(PARAM_INT, 'The id of the category.'),
                'templateparams' => new \external_value(PARAM_RAW, 'The template information to link the question to the task through coderunner'),
                'oldMoodleId' => new \external_value(PARAM_INT, 'The old moodleID of the previous existing question'),
                'examTask' => new \external_value(PARAM_RAW, 'Whether this is an exam question, influences the submission mode used (true/false).'),
                'tag' => new \external_value(PARAM_RAW, 'The tag to create for the question.', VALUE_OPTIONAL)
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
        require_capability('moodle/question:editall', $cat_context);

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
        $coderunner_options->validateonsave = 0;
        $DB->insert_record('question_coderunner_options', $coderunner_options);

        // Insert test cases for precheck
        $testcase = new stdClass();
        $testcase->questionid = $question->id;
        $testcase->useasexample = 0;
        $testcase->testcode = $data['examTask'] == 'true' ? 'RUN' : 'DIAGNOSE';
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
        $oldquestionversion = $DB->get_record('question_versions', array('questionid' => $data['oldMoodleId']));
        if (is_object($oldquestionversion)) {
            // creates a new version of the question with the created questionid
            $questionversion = new stdClass();
            $questionversion->questionbankentryid = $oldquestionversion->questionbankentryid;
            $questionversion->questionid = $question->id;
            $questionversion->version = $oldquestionversion->version + 1;
            $questionversion->status = 'ready';
            $DB->insert_record('question_versions', $questionversion);
        }

        // update tag
        //$DB->delete_records('tag_instance', ['itemid' => $data['oldMoodleId'], 'itemtype' => 'question', 'contextid' => $cat_context->id, 'component' => 'core_question']);
        if (!is_null($data['tag']) && strlen($data['tag']) > 0) {
            $tagId = 0;

            // find tag
            $tag = $DB->get_record('tag', ['name' => $data['tag']]);
            if (!is_object($tag) || $tag === false) {
                $tagColl = new stdClass();
                $tagColl->sortorder = 0;
                $collId = $DB->insert_record('tag_coll', $tagColl);

                $tag = new stdClass();
                $tag->name = $data['tag'];
                $tag->rawname = $data['tag'];
                $tag->timemodified = time();
                $tag->userid = $USER->id;
                $tag->tagcollid = $collId;
                $tagId = $DB->insert_record('tag', $tag);
            } else {
                $tagId = $tag->id;
            }

            // add tag to question
            $tagInstance = new stdClass();
            $tagInstance->tagid = $tagId;
            $tagInstance->component = 'core_question';
            $tagInstance->itemtype = 'question';
            $tagInstance->itemid = $question->id;
            $tagInstance->contextid = $cat_context->id;
            $tagInstance->tiuserid = 0;
            $tagInstance->ordering = 0;
            $tagInstance->timecreated = time();
            $tagInstance->timemodified = time();
            $DB->insert_record('tag_instance', $tagInstance);
        }

        $transaction->allow_commit();

        return ['questionid' => $question->id]; // Return the created question ID
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_single_structure
     */
    public static function execute_returns(): \external_single_structure
    {
        return new \external_single_structure([
            'questionid' => new \external_value(PARAM_INT, 'Question ID')
        ]);
    }
}