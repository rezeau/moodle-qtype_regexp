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
 * Unit tests for the regexp question type class.
 *
 * @package    qtype_regexp
 * @copyright  2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/regexp/questiontype.php');


/**
 * Unit tests for the regexp question type class.
 *
 * @copyright  2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_regexp_test extends UnitTestCase {

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public static $includecoverage = array(
        'question/type/questiontypebase.php',
        'question/type/regexp/questiontype.php',
    );

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected $qtype;

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function setUp() {
        $this->qtype = new qtype_regexp();
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function tearDown() {
        $this->qtype = null;
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected function get_test_question_data() {
        $q = new stdClass();
        $q->id = 1;
        $q->options->answers[1] = (object) array('answer' => 'frog', 'fraction' => 1);
        $q->options->answers[2] = (object) array('answer' => '*', 'fraction' => 0.1);

        return $q;
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function test_name() {
        $this->assertEqual($this->qtype->name(), 'regexp');
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function test_can_analyse_responses() {
        $this->assertTrue($this->qtype->can_analyse_responses());
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function test_get_random_guess_score() {
        $q = $this->get_test_question_data();
        $this->assertEqual(0.1, $this->qtype->get_random_guess_score($q));
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function test_get_possible_responses() {
        $q = $this->get_test_question_data();

        $this->assertEqual(array(
            $q->id => array(
                1 => new question_possible_response('frog', 1),
                2 => new question_possible_response('*', 0.1),
                null => question_possible_response::no_response()),
        ), $this->qtype->get_possible_responses($q));
    }
}
