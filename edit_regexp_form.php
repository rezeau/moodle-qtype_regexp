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

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the editing form for the regexp question type.
 *
 * @copyright  2011 Joseph REZEAU
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questionbank
 * @subpackage questiontypes
 * @subpackage regexp
 */

/**
 * regexp editing form definition.
 */
class qtype_regexp_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */

    public function __construct($submiturl, $question, $category, $contexts, $formeditable = true) {

        if ("" != optional_param('answer', '', PARAM_RAW)) {
            // new Answer field added OR error detected by validation
            $this->currentanswers = optional_param('answer', '', PARAM_NOTAGS);
            $this->usecase = optional_param('usecase', '', PARAM_NOTAGS);
            $this->fraction = optional_param('fraction', '', PARAM_RAW);
        } elseif (isset($question->id)) {
            // question data is set
            $this->questionid = $question->id;
            $this->currentanswers = $question->options->answers;
            $this->usecase = $question->options->usecase;
        } else {
            // starting a new question edit; nothing is set yet
            $this->currentanswers = '';
        }
        parent::__construct($submiturl, $question, $category, $contexts, $formeditable);
    }

    protected function definition_inner($mform) {
        global $CFG, $OUTPUT, $SESSION;
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        $mform->removeElement('generalfeedback'); //general feedback has no meaning in the REGEXP question type, only specific feedback
        $menu = array(get_string('none'), get_string('letter', 'qtype_regexp'), get_string('word', 'qtype_regexp'));
        $mform->addElement('select', 'usehint', get_string('usehint', 'qtype_regexp'), $menu);
        $mform->addHelpButton('usehint', 'usehint', 'qtype_regexp');
        $menu = array(get_string('caseno', 'qtype_regexp'), get_string('caseyes', 'qtype_regexp'));
        $mform->addElement('select', 'usecase', get_string('casesensitive', 'qtype_regexp'), $menu);
        $mform->addElement('static', 'answersinstruct', '', get_string('filloutoneanswer', 'qtype_regexp'));
        $mform->closeHeaderBefore('answersinstruct');
        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_shortanswer', '{no}'),
            question_bank::fraction_options(), $minoptions = 3, $addoptions =1);

        // TODO format nicely a div for alternate answers
        $mform->addElement('header', 'showhidealternateheader',
                get_string('showhidealternate', 'qtype_regexp'));
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'showalternate', get_string('showalternate', 'qtype_regexp'));
        if (!isset ($SESSION->qtype_regexp->showalternate)) {
            $SESSION->qtype_regexp->showalternate = false;
        }
        if ($SESSION->qtype_regexp->showalternate) {
            $disabled = '';
        } else {
            $disabled = 'disabled';
        }
        $buttonarray[] = $mform->createElement('submit', 'hidealternate', get_string('hidealternate', 'qtype_regexp'), $disabled);

        $mform->addGroup($buttonarray, '', '', array(' '), false);
        //we are using a hook in questiontype to resdisplay the form and it expects a parameter
        //wizard, which we won't actually use but we need to pass it to avoid an error message.
        $mform->addElement('hidden', 'wizard', '');

        if ($SESSION->qtype_regexp->showalternate) {
            $qu->id = $this->questionid;
            $qu->answers = array();
            $i = 0;
            foreach($this->currentanswers as $answer) {
                $qu->answers[$i]->answer = $answer->answer;
                $qu->answers[$i]->fraction = $answer->fraction;
                $i++;
            }
            $alternateanswers = get_alternateanswers($qu);
            $mform->addElement('html', '<div class="alternateanswers">');
            $alternatelist = '';
            foreach($alternateanswers as $key => $alternateanswer) {
                $mform->addElement('static', 'alternateanswer', get_string('answer').' '.$key.' ('.$alternateanswer['fraction'].')',
                    '<span class="regexp">'.$alternateanswer['regexp'].'</span>' );
                $list = '';
                foreach($alternateanswer['answers'] as $alternate) {
                    $list.= '<li>'.$alternate.'</li>';
                }
                $mform->addElement('static', 'alternateanswer', '', '<ul class="square">'.$list.'</ul>');
            }
            $mform->addElement('html', '</div>');
        }

        $mform->addElement('header', 'multitriesheader',
                get_string('settingsformultipletries', 'qtype_regexp'));
        $withclearwrong = false;
        $withshownumpartscorrect = false;

        $penalties = array(
            1.00,
            0.50,
            0.33,
            0.25,
            0.20,
            0.10,
            0.05,
            0.00
        );
        if (!empty($this->question->penalty) && !in_array($this->question->penalty, $penalties)) {
            $penalties[] = $this->question->penalty;
            sort($penalties);
        }
        $penaltyoptions = array();
        foreach ($penalties as $penalty) {
            $penaltyoptions["$penalty"] = (100 * $penalty) . '%';
        }
        $mform->addElement('select', 'penalty',
                get_string('penaltyforeachincorrecttry', 'qtype_regexp'), $penaltyoptions);
        $mform->addRule('penalty', null, 'required', null, 'client');
        $mform->addHelpButton('penalty', 'penaltyforeachincorrecttry', 'qtype_regexp');
        $mform->setDefault('penalty', 0.1);

        if (isset($this->question->hints)) {
            $counthints = count($this->question->hints);
        } else {
            $counthints = 0;
        }

        if ($this->question->formoptions->repeatelements) {
            $repeatsatstart = max(self::DEFAULT_NUM_HINTS, $counthints);
        } else {
            $repeatsatstart = $counthints;
        }

        list($repeated, $repeatedoptions) = $this->get_hint_fields(
                $withclearwrong, $withshownumpartscorrect);
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
                'numhints', 'addhint', 1, get_string('addanotherhint', 'question'));
    }

    protected function data_preprocessing($question) {
        global $CFG, $PAGE, $SESSION;
        $SESSION->qtype_regexp->showalternate = false;
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        if (!empty($question->options)) {
            $question->usecase = $question->options->usecase;
            $question->usehint = $question->options->usehint;
        } else {
            $key = 0;
            $default_values['fraction['.$key.']'] = 1;
            $question = (object)((array)$question + $default_values);
        }
        // disable the score dropdown list for Answer 1 to make sure it remains at 100%
        // grade for Answer 1 will need to be automatically set to 1 in questiontype.php,  save_question_options($question)
        $i=1;
        foreach ($this->_form->_elements as $element) {
            if ($element->_attributes['name'] == 'fraction[0]') {
                break;
            }
            $i++;
        }
        $this->_form->_elements[$i]->_attributes['disabled'] = 'disabled';

        return $question;
    }

    public function validation($data, $files) {
        global $CFG;
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        $errors = parent::validation($data, $files);
        $answers = $data['answer'];
        $grades = $data['fraction'];
        $answercount = 0;
        $illegalmetacharacters = ". ^ $ * + ? { } \\";
        $data['fraction'][0] = 1;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== ''){
                $answercount++;
                $parenserror = '';
                $metacharserror = '';
                //$errors["answer[$key]"] = null;
                // we do not check parenthesis and square brackets in Answer 1 (correct answer)
                if ($key > 0) {
                    $markedline = '';
                    for ($i=0;$i<strlen($trimmedanswer);$i++) {
                        $markedline .= ' ';
                    }
                    $parenserror = check_my_parens($trimmedanswer, $markedline);
                    if ($parenserror) {
                        $errors["answer[$key]"] = get_string("regexperrorparen", "qtype_regexp").'<br />';
                        $markedline = $parenserror;
                    }
                    // we do not test unescaped metacharacters in Answers expressions for incorrect responses (grade = None)
                    if ($data['fraction'][$key] > 0) {
                        $metacharserror = check_unescaped_metachars($trimmedanswer, $markedline);
                        if ($metacharserror) {
                            $errormessage = get_string("illegalcharacters", "qtype_regexp", $illegalmetacharacters);
                            if (empty($errors["answer[$key]"])) {
                              $errors["answer[$key]"] = $errormessage;
                            } else {
                                $errors["answer[$key]"] .= $errormessage;
                            }
                        }
                    }
                    if ($metacharserror || $parenserror) {
                        $answerstringchunks = splitstring ($trimmedanswer);
                        $nbchunks = count($answerstringchunks);
                        $errors["answer[$key]"] .= '<pre><div class="displayvalidationerrors">';
                        if ($metacharserror) {
                            $illegalcharschunks = splitstring ($metacharserror);
                            for ($i=0;$i<$nbchunks;$i++) {
                              $errors["answer[$key]"] .= '<br />'.$answerstringchunks[$i].'<br />'.$illegalcharschunks[$i];
                            }
                        } elseif ($parenserror) {
                            $illegalcharschunks = splitstring ($parenserror);
                            for ($i=0;$i<$nbchunks;$i++) {
                                $errors["answer[$key]"] .= '<br />'.$answerstringchunks[$i].'<br />'.$illegalcharschunks[$i];
                            }
                        }
                        $errors["answer[$key]"] .= '</div></pre>';
                    }
                }
            } else if ($data['fraction'][$key] != 0 || !html_is_blank($data['feedback'][$key]['text'])) {
                if ($key === 0){
                    $errors['answer[0]'] = get_string('answer1mustbegiven', 'qtype_regexp');
                } else {
                   $errors["answer[$key]"] = get_string('answermustbegiven', 'qtype_regexp');
                }
                $answercount++;
            }
        }

        return $errors;
    }

    // JR moved here to get rid of pesky automatically added blank Answer field upon re-editing question
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
        $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions,
                $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $countanswers = count($this->question->options->$answersoption);
        } else {
            $countanswers = 0;
        }
        if ($this->question->formoptions->repeatelements) {
            if ($countanswers) {
                $repeatsatstart = $countanswers;
            } else {
                $repeatsatstart = $minoptions;
            }
        } else {
            $repeatsatstart = $countanswers;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
                'noanswers', 'addanswers', $addoptions,
                get_string('addmoreanswers', 'qtype_regexp'));
    }

    public function qtype() {
        return 'regexp';
    }
}
