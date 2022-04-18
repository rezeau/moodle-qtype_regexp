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
 * Test helpers for the regexp question type.
 *
 * @package    qtype_regexp
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Test helper class for the regexp question type.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_regexp_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array('frenchflag');
    }

    /**
     * Makes a REGEXP question with (first) correct answer "it's blue, white and red",
     * partially correct answer must match "(it('s| is) |they are )?blue, white, red".
     * This question also has a '.*' match anything answer.
     * @return qtype_regexp_question
     */
    public function make_regexp_question_frenchflag() {
        question_bank::load_question_definition_classes('regexp');
        $rx = new qtype_regexp_question();
        test_question_maker::initialise_a_question($rx);
        $rx->name = 'Regular expression short answer question';
        $rx->questiontext = 'French flag colors : __________';
        $rx->generalfeedback = 'Generalfeedback: ';
        $rx->usecase = false;
        $rx->answers = array(
            13 => new question_answer(13, "it's blue, white and red", 1.0, 'The best answer.', FORMAT_HTML),
            14 => new question_answer(14, "(it('s| is) |they are )?blue, white, red", 0.8, 'An acceptable answer.', FORMAT_HTML),
            15 => new question_answer(15, '--.*blue.*', 0.0, 'Missing blue!', FORMAT_HTML),
            16 => new question_answer(16, '.*', 0.0, 'No, no, no! Try again', FORMAT_HTML),
        );
        $rx->qtype = question_bank::get_qtype('regexp');
        return $rx;
    }

    /**
     * Gets the question data for a regexp question with correct answer "it's blue, white and red",
     * partially correct answer must match "(it('s| is) |they are )?blue, white, red".
     * This question also has a '.*' match anything answer.
     * @return stdClass
     */
    public function get_regexp_question_data_frenchflag() {
        $qdata = new stdClass();
        test_question_maker::initialise_question_data($qdata);

        $qdata->qtype = 'regexp';
        $qdata->name = 'Regular expression short answer question';
        $qdata->questiontext = 'French flag colors : __________';
        $qdata->generalfeedback = 'Generalfeedback';

        $qdata->options = new stdClass();
        $qdata->options->usecase = 0;
        $qdata->options->answers = array(
            13 => new question_answer(13, "it's blue, white and red", 1.0, 'The best answer.', FORMAT_HTML),
            14 => new question_answer(14, "(it('s| is) |they are )?blue, white, red", 0.8, 'An acceptable answer.', FORMAT_HTML),
            15 => new question_answer(15, '--.*blue.*', 0.0, 'Missing blue!', FORMAT_HTML),
            16 => new question_answer(16, '.*', 0.0, 'No, no, no! Try again.', FORMAT_HTML),
        );

        return $qdata;
    }

    /**
     * Gets the question form data for a regexp question with correct answer "it's blue, white and red",
     * partially correct answer must match "(it('s| is) |they are )?blue, white, red".
     * This question also has a '.*' match anything answer.
     * @return stdClass
     */
    public function get_regexp_question_form_data_frenchflag() {
        $form = new stdClass();

        $form->name = 'Regular expression short answer question';
        $form->questiontext = array('French flag colors : __________', 'format' => FORMAT_HTML);
        $form->defaultmark = 1.0;
        $form->generalfeedback = array('text' => 'Generalfeedback: OK.', 'format' => FORMAT_HTML);
        $form->usecase = false;
        $form->answer = array("it's blue, white and red", "(it('s| is) |they are )?blue, white, red", '--.*blue.*', '.*');
        $form->fraction = array('1.0', '0.8', '0.0', '0.0');
        $form->feedback = array(
            array('text' => 'The best answer.', 'format' => FORMAT_HTML),
            array('text' => 'An acceptable answer.', 'format' => FORMAT_HTML),
            array('text' => 'Missing blue!', 'format' => FORMAT_HTML),
            array('text' => 'No, no, no! Try again.', 'format' => FORMAT_HTML),
        );

        return $form;
    }
}
