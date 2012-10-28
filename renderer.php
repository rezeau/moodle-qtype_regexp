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
 * Regexp question renderer class.
 *
 * @package    qtype
 * @subpackage regexp
 * @copyright  2011 Joseph REZEAU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// important: this file must be utf8-encoded

defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for regexp questions.
 *
 * @copyright  2011 Joseph REZEAU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_regexp_renderer extends qtype_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $CFG, $currentanswerwithhint;
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        $question = $qa->get_question();
        $inputname = $qa->get_qt_field_name('answer');
        $ispreview = !isset($options->attempt);
        $currentanswer = remove_blanks ($qa->get_last_qt_var('answer') );
        $response = $qa->get_last_qt_data();
        $laststep = $qa->get_reverse_step_iterator();
        $hintadded = false;
        foreach ($qa->get_reverse_step_iterator() as $step) {
            $hintadded = $step->has_behaviour_var('_helps') === true;
                break;
        }
        $closest = find_closest($question, $currentanswer, $correct_response=false, $hintadded);
        $question->closest = $closest;
        $currentanswer = $closest[0];

        // showing / hiding regexp generated alternative sentences (for teacher only)
        // changed from javascript to print_collapsible_region OCT 2012
 
        if ($ispreview) {
            $alternateanswers = get_alternateanswers($question);
            $response = $question->get_correct_response();
            $correctanswer = $response['answer'];
            print_collapsible_region_start('', 'showhidealternate', get_string('showhidealternate', 'qtype_regexp'),
                'core_question_preview_showhidealternate_collapsed', true);
            echo html_writer::start_tag('div class="notifytiny"', array('id' => 'alternateanswers'));
            echo '<hr />';
            if ($question->usecase) {
                $case = get_string('caseyes', 'qtype_regexp');
            } else {
                $case = get_string('caseno', 'qtype_regexp');
            }
            echo get_string('casesensitive', 'qtype_regexp').' : <b>'.$case.'</b><hr />';

            foreach($alternateanswers as $key => $alternateanswer) {
                echo get_string('answer').' '.$key.' ('.$alternateanswer['fraction'].') ','<span class="regexp">'.$alternateanswer['regexp'].'</span>';
                $list = '';
                foreach($alternateanswer['answers'] as $alternate) {
                    $alternate = str_replace(" ", "&nbsp;", $alternate);
                    $list.= '<li>'.$alternate.'</li>';
                }
                echo '<ul class="square">'.$list.'</ul>';
            }
            echo("<hr />");
            echo html_writer::end_tag('div');
            print_collapsible_region_end();
        }

        $inputattributes = array(
            'type' => 'text',
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => 80,
        );

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
        }

        $feedbackimg = '';
        if ($options->correctness) {
            $answer = $question->get_matching_answer(array('answer' => $currentanswer));
            if ($answer) {
                $fraction = $answer->fraction;
            } else {
                $fraction = 0;
            }
            $inputattributes['class'] = $this->feedback_class($fraction);
            $feedbackimg = $this->feedback_image($fraction);
        }
        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;
        if (preg_match('/_____+/', $questiontext, $matches)) {
            $placeholder = $matches[0];
            $inputattributes['size'] = round(strlen($placeholder) * 1.1);
        }

        $input = html_writer::empty_tag('input', $inputattributes) . $feedbackimg;

        if ($placeholder) {
            $questiontext = substr_replace($questiontext, $input,
                    strpos($questiontext, $placeholder), strlen($placeholder));
        }

        $result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        if (!$placeholder) {
            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            $result .= get_string('answer', 'qtype_shortanswer',
                    html_writer::tag('div', $input, array('class' => 'answer')));
            $result .= html_writer::end_tag('div');
        }

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error(array('answer' => $currentanswer)),
                    array('class' => 'validationerror'));
        }

        return $result;
    }

    public function feedback(question_attempt $qa, question_display_options $options) {
        $result = '';
        $hint = null;
        if ($options->feedback) {
            $result .= html_writer::nonempty_tag('div', $this->specific_feedback($qa/* , $options */),
                    array('class' => 'specificfeedback'));
            $hint = $qa->get_applicable_hint();
        }

        if ($options->numpartscorrect) {
            $result .= html_writer::nonempty_tag('div', $this->num_parts_correct($qa),
                    array('class' => 'numpartscorrect'));
        }

        if ($hint) {
            $result .= $this->hint($qa, $hint);
        }

        if ($options->generalfeedback) {
            $result .= html_writer::nonempty_tag('div', $this->general_feedback($qa),
                    array('class' => 'generalfeedback'));
        }

        if ($options->rightanswer) {
            $display_correct_answers = $this->correct_response($qa);
            $result .= html_writer::nonempty_tag('div', $display_correct_answers,
                    array('class' => 'rightanswer'));
        }

        return $result;
    }

    public function specific_feedback(question_attempt $qa/* , question_display_options $options */) {
        $question = $qa->get_question();
        $currentanswer = remove_blanks($qa->get_last_qt_var('answer') );
        $ispreview = false;
        $completemessage = '';
        $closestcomplete = false;
        foreach ($qa->get_reverse_step_iterator() as $step) {
            $hintadded = $step->has_behaviour_var('_helps') === true;
            break;
        }
        $closest = $question->closest;
        if ($hintadded) { // hint added one letter or hint added letter and answer is complete
            $answer = $question->get_matching_answer(array('answer' => $closest[0]));
            // help has added letter OR word and answer is complete
            $isstateimprovable = $qa->get_behaviour()->is_state_improvable($qa->get_state());
            if ($closest[2] == 'complete' && $isstateimprovable) {
                $closestcomplete = true;
                $class = '"correctness correct"';
                $completemessage = '<div class='.$class.'>'.get_string("clicktosubmit", "qtype_regexp").'</div>';
            }
        } else {
            $answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
        }
        if ($closest[3]) {
            $closest[3] = '['.$closest[3].']'; // rest of submitted answer, in red
        }
        $f = ''; // student's response with corrections to be displayed in feedback div
            $f = '<span style="color:#0000FF;">'.$closest[1].'<strong>'.$closest[4].'</strong></span> '.$closest[3]; // color blue for correct words/letters
        if ($answer && $answer->feedback || $closestcomplete == true) {
            return $question->format_text($f.$answer->feedback.$completemessage, $answer->feedbackformat,
                $qa, 'question', 'answerfeedback', $answer->id);
        } else {
            return $f;
        }
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $display_responses = '';
        $alternateanswers = get_alternateanswers($question);
        $bestcorrectanswer = $alternateanswers[1]['answers'][0]; 

        if (count($alternateanswers) == 1 ) { // no alternative answers besides the only "correct" answer
            $display_responses .= get_string('correctansweris', 'qtype_regexp', $bestcorrectanswer);
        } else {
            $display_responses .= get_string('bestcorrectansweris', 'qtype_regexp', $bestcorrectanswer).'<br />';
        }
        // teacher can always view alternate answers; student can only view if question is set to studentshowalternate
        $canview = question_has_capability_on($question, 'view');
        if ($question->studentshowalternate || $canview) {
            $display_responses .= print_collapsible_region_start('expandalternateanswers', 'id'.$question->id, get_string('showhidealternate', 'qtype_regexp'),
                            'showhidealternate', true, true);
            foreach ($alternateanswers as $key => $alternateanswer) {
               if ($key == 1) { // first (correct) Answer
                   if (count($alternateanswers) > 1) {
                       $display_responses .= get_string('correctanswersare', 'qtype_regexp').'<br />';
                   }
                } else {
                   $fraction = $alternateanswer['fraction'];
                   $display_responses .= "<strong>$fraction</strong><br />";
                   foreach($alternateanswer['answers'] as $alternate) {
                       $display_responses .=  $alternate.'<br />';
                    }
                }
            }
            $display_responses .= print_collapsible_region_end(true);
        }     
        return $display_responses;
    }
}
