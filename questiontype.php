<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Serve question type files
 *
 * @since      2.0
 * @package    qtype
 * @subpackage regexp
 * @copyright  Jean-Michel Vedrine  & Joseph Rézeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @package questionbank
 * @subpackage questiontypes
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/regexp/question.php');

/**
 * The regexp question type.
 *
 *
 *
 */
class qtype_regexp extends question_type {
    public function extra_question_fields() {
        return array('qtype_regexp', 'usehint', 'usecase', 'studentshowalternate');
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
    }

    public function save_question_options ($question) {
        global $DB, $SESSION, $CFG;
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        $result = new stdClass;

        $context = $question->context;

        $oldanswers = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');

        $answers = array();

        // Insert all the new answers.
        foreach ($question->answer as $key => $answerdata) {
            // Check for, and ignore, completely blank answer from the form.
            if (trim($answerdata) == '' && $question->fraction[$key] == 0 &&
                    html_is_blank($question->feedback[$key]['text'])) {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
            // JR august 2012 remove any superfluous blanks in expressions before saving.
            $answer->answer = remove_blanks($answerdata);
            // Set grade for Answer 1 to 1 (100%).
            if ($key === 0) {
                $question->fraction[$key] = 1;
            }
            $answer->fraction = $question->fraction[$key];
            $answer->feedback = $this->import_or_save_files($question->feedback[$key],
                    $context, 'question', 'answerfeedback', $answer->id);
            $answer->feedbackformat = $question->feedback[$key]['format'];
            $DB->update_record('question_answers', $answer);

            $answers[] = $answer->id;
        }

        $question->answers = implode(',', $answers);
        $parentresult = parent::save_question_options($question);
        if ($parentresult !== null) {
            // Parent function returns null if all is OK.
            return $parentresult;
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }
        $this->save_hints($question);

        // JR dec 2011 unset alternateanswers and alternatecorrectanswers after question has been edited, just in case.
        $qid = $question->id;
        if (isset($SESSION->qtype_regexp_question->alternateanswers[$qid])) {
            unset($SESSION->qtype_regexp_question->alternateanswers[$qid]);
        }
        if (isset($SESSION->qtype_regexp_question->alternatecorrectanswers[$qid])) {
            unset($SESSION->qtype_regexp_question->alternatecorrectanswers[$qid]);
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->usecase = $questiondata->options->usecase;
        $question->usehint = $questiondata->options->usehint;
        $question->studentshowalternate = $questiondata->options->studentshowalternate;
        $this->initialise_question_answers($question, $questiondata);
        $qid = $question->id;
    }

    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return $answer->fraction;
            }
        }
        return 0;
    }

    public function get_possible_responses ($questiondata) {
        $responses = array();

        foreach ($questiondata->options->answers as $aid => $answer) {
            $responses[$aid] = new question_possible_response($answer->answer, $answer->fraction);
        }
        $responses[null] = question_possible_response::no_response();
        return array($questiondata->id => $responses);
    }

    /**
     * Provide export functionality for xml format
     * @param question object the question object
     * @param format object the format object so that helper methods can be used
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return string the data to append to the output buffer or false if error
     */
    // IMPORT/EXPORT FUNCTIONS.

    /*
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */

    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */

    public function export_to_xml ($question, qformat_xml $format, $extra=null) {
        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }
        // Omit table name (question).
        array_shift($extraquestionfields);
        $expout='';
        foreach ($extraquestionfields as $field) {
            $exportedvalue = $question->options->$field;
            if (!empty($exportedvalue) && htmlspecialchars($exportedvalue) != $exportedvalue) {
                $exportedvalue = '<![CDATA[' . $exportedvalue . ']]>';
            }
            $expout .= "    <$field>{$exportedvalue}</$field>\n";
        }
        foreach ($question->options->answers as $answer) {
            $percent = 100 * $answer->fraction;
            $expout .= "    <answer fraction=\"$percent\">\n";
            $expout .= $format->writetext($answer->answer, 3, false);
            $expout .= "      <feedback format=\"html\">\n";
            $expout .= $format->writetext($answer->feedback, 4, false);
            $expout .= "      </feedback>\n";
            $expout .= "    </answer>\n";
        }
        return $expout;
    }

    /**
     * Provide import functionality for xml format
     * @param data mixed the segment of data containing the question
     * @param question object question object processed (so far) by standard import code
     * @param format object the format object so that helper methods can be used (in particular error())
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return object question object suitable for save_options() call or false if cannot handle
     **/

    public function import_from_xml ($data, $question, qformat_xml $format, $extra=null) {
        // Check question is for us.
        $qtype = $data['@']['type'];
        if ($qtype=='regexp') {
            $qo = $format->import_headers( $data );

            // Header parts particular to regexp.
            $qo->qtype = "regexp";
            $qo->usehint = 0;

            // Get usehint.
            $qo->usehint = $format->getpath($data, array('#', 'usehint', 0, '#'), $qo->usehint );
            // Get usecase.
            $qo->usecase = $format->getpath($data, array('#', 'usecase', 0, '#'), $qo->usecase );
            // Get studentshowalternate.
            $qo->studentshowalternate = new stdClass;
            $qo->studentshowalternate = $format->getpath($data, array('#', 'studentshowalternate', 0, '#'),
                            $qo->studentshowalternate );

            // Run through the answers.
            $answers = $data['#']['answer'];
            $a_count = 0;
            foreach ($answers as $answer) {
                $ans = $format->import_answer($answer);
                $qo->answer[$a_count] = $ans->answer['text'];
                $qo->fraction[$a_count] = $ans->fraction;
                $qo->feedback[$a_count] = $ans->feedback;
                ++$a_count;
            }
            return $qo;
        } else {
            return false;
        }
    }
}