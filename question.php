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
 * Regexp question definition class.
 *
 * @package    qtype
 * @subpackage regexp
 * @copyright  2011 Joseph REZEAU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a regexp question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//class qtype_regexp_question extends question_graded_by_strategy
class qtype_regexp_question extends question_graded_by_strategy
        implements question_response_answer_comparer {

    /** @var boolean whether answers should be graded case-sensitively. */
    public $usecase;
    /** @var boolean whether student can ask for help (next correct letter will be added). */
    public $usehint;
    /** @var array of question_answer. */
    public $answers = array();

    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function get_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function summarise_response_withhelp(array $response) {
        global $CFG;
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        if (isset($response['answer'])) {
            $answer = $response['answer'];
            $closest = $this->closest;
            return $answer.' => '.$closest[0];
        } else {
            return null;
        }
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
                ($response['answer'] || $response['answer'] === '0');
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_regexp');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function get_answers() {
        return $this->answers;
    }

    public function compare_response_with_answer(array $response, question_answer $answer) {
        global $CFG, $currentanswerwithhint;
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        $response['answer'] = remove_blanks ($response['answer']);
        if ($currentanswerwithhint) {
            $response['answer'] = $currentanswerwithhint;
        }
        if ($response == $this->get_correct_response()) {
            return true;
        }
        // do NOT match student response against Answer 1 : if it matches, already matched by get_correct_response() above
        // and Answer 1 may contain metacharacters that do not follow correct regex syntax
        // get id of Answer 1
        foreach ($this->answers as $key=>$value) {
            break;
        }
        // if this is Answer 1 then return; do not try to match
        if ($key == $answer->id) {
            return;
        }
        return self::compare_string_with_wildcard(
                $response['answer'], $answer->answer, $answer->fraction, !$this->usecase);
    }
    public static function compare_string_with_wildcard($string, $pattern, $grade, $ignorecase) {
        if (substr($pattern,0,2) != '--') {
            // answers with a positive grade must be anchored for strict match
            // incorrect answers are not strictly matched
            if ($grade > 0) {
               $regexp = '/^' . $pattern . '$/';
            } else {
                $regexp = '/' . $pattern. '/';
            }
            $regexp .= 'u'; // for potential utf-8 characters
            // Make the match insensitive if requested to.
            if ($ignorecase) {
                $regexp .= 'i';
            }
            if (preg_match($regexp, trim($string))) {
                return true;
            }
        }
        // testing for absence of needed (right) elements in student's answer, through initial -- coding
        if (substr($pattern,0,2) == '--') {
            if ($ignorecase) {
                $ignorecase = 'i';
            }
            
            $response1 = substr($pattern,2);
            $response0 = $string;
            // testing for absence of more than one needed word
            if (preg_match('/^.*\&\&.*$/', $response1)) {
                $pattern = '/&&[^(|)]*/';
                $missingstrings = preg_match_all($pattern,$response1, $matches, PREG_OFFSET_CAPTURE);
                $strmissingstrings = $matches[0][0][0];
                $strmissingstrings = substr($strmissingstrings, 2);
                $openparenpos = $matches[0][0][1] -1;
                $closeparenpos = $openparenpos + strlen($strmissingstrings) + 4;
                $start = substr($response1 , 0, $openparenpos);
                $finish = substr($response1 , $closeparenpos);
                $missingstrings = explode ('&&', $strmissingstrings);
                foreach ($missingstrings as $missingstring) {
                    $missingstring = $start.$missingstring.$finish;
                    if (preg_match('/'.$missingstring.'/'.$ignorecase, $response0) == 0 ) {
                        return true;
                    }
                }
            } else {  // this is *not* a NOT (a OR b OR c etc.) request
                if (preg_match('/^'.$response1.'$/'.$ignorecase, $response0)  == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function check_file_access($qa, $options, $component, $filearea,
        $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {

            $answer = $qa->get_question()->get_matching_answer(array('answer' => $currentanswer));
            $answerid = reset($args); // itemid is answer id.
            return $options->feedback && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        GLOBAL $CFG;
        // check that regexpadaptivewithhelp behaviour has been installed
        // if not installed, then the regexp questions will follow the "standard" behaviours
        // and Help button will not be available
        // NOTE: from 2.2 you cannot install regexp if corresponding behaviours have not been installed first
        // see plugin->dependencies in version.php file
        // only use the regexpadaptivewithhelp behaviour is question uses hint
        if ($this->usehint) {
            if ($preferredbehaviour == 'adaptive' && file_exists($CFG->dirroot.'/question/behaviour/regexpadaptivewithhelp/')) {
                return question_engine::make_behaviour('regexpadaptivewithhelp', $qa, $preferredbehaviour);
            }
            if ($preferredbehaviour == 'adaptivenopenalty' && file_exists($CFG->dirroot.'/question/behaviour/regexpadaptivewithhelpnopenalty/')) {
                return question_engine::make_behaviour('regexpadaptivewithhelpnopenalty', $qa, $preferredbehaviour);
            }
        }
        return question_engine::make_archetypal_behaviour($preferredbehaviour, $qa);
    }
}
