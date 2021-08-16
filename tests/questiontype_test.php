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
 * @copyright 2021 Joseph Rézeau <joseph@rezeau.org>
 * @copyright based on work by 2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/regexp/questiontype.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/regexp/edit_regexp_form.php');


/**
 * Unit tests for the regexp question type class.
 *
 * @copyright 2021 Joseph Rézeau <joseph@rezeau.org>
 * @copyright based on work by 2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_regexp_test extends advanced_testcase {

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
    protected function setUp(): void {
        $this->qtype = new qtype_regexp();
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected function tearDown(): void {
        $this->qtype = null;
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected function get_test_question_data() {
        return test_question_maker::get_question_data('regexp');
    }

    /**
     *  explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'regexp');
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
    public function test_get_possible_responses() {
        $q = test_question_maker::get_question_data('regexp');

        $this->assertEquals(array(
            $q->id => array(
                13 => new question_possible_response("it's blue, white and red", 1),
                14 => new question_possible_response("(it('s| is) |they are )?blue, white, red", 0.8),
                15 => new question_possible_response('--.*blue.*', 0.0),
                16 => new question_possible_response('.*', 0.0),
                null => question_possible_response::no_response()),
        ), $this->qtype->get_possible_responses($q));
    }

    public function test_question_saving_frenchflag() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $questiondata = test_question_maker::get_question_data('regexp');
        $formdata = test_question_maker::get_question_form_data('regexp');

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category(array());

        $formdata->category = "{$cat->id},{$cat->contextid}";
        qtype_regexp_edit_form::mock_submit((array)$formdata);

        $form = qtype_regexp_test_helper::get_question_editing_form($cat, $questiondata);

        $this->assertTrue($form->is_validated());

        $fromform = $form->get_data();

        $returnedfromsave = $this->qtype->save_question($questiondata, $fromform);
        $actualquestionsdata = question_load_questions(array($returnedfromsave->id));
        $actualquestiondata = end($actualquestionsdata);

        foreach ($questiondata as $property => $value) {
            if (!in_array($property, array('id', 'version', 'timemodified', 'timecreated', 'options'))) {
                $this->assertEquals($value, $actualquestiondata->$property);
            }
        }

        foreach ($questiondata->options as $optionname => $value) {
            if ($optionname != 'answers') {
                $this->assertEquals($value, $actualquestiondata->options->$optionname);
            }
        }
    }

}
