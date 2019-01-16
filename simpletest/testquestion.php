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
 * Unit tests for the REGEXP question definition class.
 *
 * @package    qtype_regexp
 * @copyright  2008 Joseph Rézeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/regexp/question.php');
require_once($CFG->dirroot . '/question/engine/simpletest/helpers.php');


/**
 * Unit tests for the REGEXP question definition class.
 *
 * @copyright  2008 Joseph Rézeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_regexp_question_maker extends test_question_maker {
    /**
     * Makes a REGEXP question with (first) correct answer "it's blue, white and red"
     * partially correct answer must match "(it('s| is) |they are )?blue, white, red"
     * @return qtype_regexp_question
     */
    public static function make_a_regexp_question() {
        question_bank::load_question_definition_classes('regexp');
        $pm = new qtype_regexp_question();
        self::initialise_a_question($pm);
        $pm->name = 'Regular expression short answer question';
        $pm->questiontext = 'French flag colors : __________';
        $pm->generalfeedback = 'Generalfeedback: ';
        $pm->regexpoptions = new regexp_options();
        $pm->answers = array(
            13 => new question_answer(13, "it's blue, white and red", 1.0, 'ok', FORMAT_HTML),
            14 => new question_answer(14, "(it('s| is) |they are )?blue, white, red", 0.8, 'yes', FORMAT_HTML),
            15 => new question_answer(15, '--.*blue.*', 0.0, 'Missing blue!', FORMAT_HTML),
            15 => new question_answer(15, '.*', 0.0, 'No, no, no! Try again', FORMAT_HTML),
        );
        $pm->qtype = question_bank::get_qtype('regexp');
        return $pm;
    }

}


/**
 * Unit tests for the REGEXP question definition class.
 *
 * @copyright  2011 Joseph REZEAU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_regexp_question_test extends UnitTestCase {
    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_compare_string_with_wildcard() {
        // Test case sensitive literal matches.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Frog', 'Frog', false));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Frog', 'frog', false));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '   Frog   ', 'Frog', false));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Frogs', 'Frog', false));

        // Test case insensitive literal matches.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Frog', 'frog', true));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '   FROG   ', 'Frog', true));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Frogs', 'Frog', true));

        // Test case sensitive wildcard matches.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Frog', 'F.*og', false));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Fog', 'F*og', false));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '   Fat dog   ', 'F.*og', false));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Frogs', 'F.*og', false));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Fg', 'F.*og', false));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'frog', 'F.*og', false));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                '   fat dog   ', 'F.*og', false));

        // Test case insensitive wildcard matches.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Frog', 'F.*og', true));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Fog', 'F.*og', true));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '   Fat dog   ', 'F.*og', true));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Frogs', 'F.*og', true));
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                'Fg', 'F.*og', true));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'frog', 'F.*og', true));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '   fat dog   ', 'F.*og', true));

        // Test match using regexp special chars.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '   *   ', '\*', false));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                '*', '\*', false));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'Frog*toad', 'Frog\*toad', false));
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'a', '[a-z]', false));
        // See http://moodle.org/mod/forum/discuss.php?d=120557.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                'ITÁLIE', 'Itálie', true));

        // Test match using 'missing words'.
        // Detect missing word 'blue' :: True.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                "orange and black", '--.*blue.*', false));
        // Detect missing word 'blue' :: False.
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                "orange and blue", '--.*blue.*', false));

        // Test match using 'missing words'
        // Detect missing words 'blue' OR 'red' OR 'white' :: True.
        $this->assertTrue(qtype_regexp_question::compare_string_with_wildcard(
                "orange and black", '--.*(&&blue&&red&&white).*' , false));
        // Detect missing words 'blue' OR 'red' OR 'white' :: False.
        $this->assertFalse(qtype_regexp_question::compare_string_with_wildcard(
                "orange blue white black red", '--.*(&&blue&&red&&white).*' , false));
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_is_complete_response() {
        $question = test_regexp_question_maker::make_a_regexp_question();

        $this->assertFalse($question->is_complete_response(array()));
        $this->assertFalse($question->is_complete_response(array('answer' => '')));
        $this->assertTrue($question->is_complete_response(array('answer' => '0')));
        $this->assertTrue($question->is_complete_response(array('answer' => '0.0')));
        $this->assertTrue($question->is_complete_response(array('answer' => 'x')));
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_is_gradable_response() {
        $question = test_regexp_question_maker::make_a_regexp_question();

        $this->assertFalse($question->is_gradable_response(array()));
        $this->assertFalse($question->is_gradable_response(array('answer' => '')));
        $this->assertTrue($question->is_gradable_response(array('answer' => '0')));
        $this->assertTrue($question->is_gradable_response(array('answer' => '0.0')));
        $this->assertTrue($question->is_gradable_response(array('answer' => 'x')));
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_grading() {
        $question = test_regexp_question_maker::make_a_regexp_question();

        $this->assertEqual(array(0, question_state::$gradedwrong),
                $question->grade_response(array('answer' => 'x')));
        $this->assertEqual(array(1, question_state::$gradedright),
                $question->grade_response(array('answer' => "it's blue, white and red")));
        $this->assertEqual(array(0.8, question_state::$gradedpartial),
                $question->grade_response(array('answer' => 'blue, white, red')));
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_get_correct_response() {
        $question = test_regexp_question_maker::make_a_regexp_question();

        $this->assertEqual(array('answer' => "it's blue, white and red"),
                $question->get_correct_response());
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_get_question_summary() {
        $question = test_regexp_question_maker::make_a_regexp_question();
        $qsummary = $question->get_question_summary();
        $this->assertEqual('French flag colors : __________', $qsummary);
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_summarise_response() {
        $question = test_regexp_question_maker::make_a_regexp_question();
        $summary = $question->summarise_response(array('answer' => 'dog'));
        $this->assertEqual('dog', $summary);
    }

    /**
     * Unit tests for the REGEXP question definition class.
     */
    public function test_classify_response() {
        $sa = test_regexp_question_maker::make_a_regexp_question();
        $sa->start_attempt(new question_attempt_step(), 1);

        $this->assertEqual(array(
                new question_classified_response(13, "it's blue, white and red", 1.0)),
                $sa->classify_response(array('answer' => "it's blue, white and red")));
        $this->assertEqual(array(
                new question_classified_response(14, 'they are blue, white, red', 0.8)),
                $sa->classify_response(array('answer' => 'they are blue, white, red')));
        $this->assertEqual(array(
                new question_classified_response(14, 'it is blue, white, red', 0.8)),
                $sa->classify_response(array('answer' => 'it is blue, white, red')));
        $this->assertEqual(array(
                new question_classified_response(14, 'blue, white, red', 0.8)),
                $sa->classify_response(array('answer' => 'blue, white, red')));
        $this->assertEqual(array(
                new question_classified_response(15, 'red and white', 0.0)),
                $sa->classify_response(array('answer' => 'red and white')));
        $this->assertEqual(array(
                new question_classified_response(15, 'black', 0.0)),
                $sa->classify_response(array('answer' => 'black')));
        $this->assertEqual(array(
                question_classified_response::no_response()),
                $sa->classify_response(array('answer' => '')));
    }
}
