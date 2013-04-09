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
    // JR we suppose hints are not really needed in REGEXP question type, so set start nb at zero
    const DEFAULT_NUM_HINTS = 0;
    protected function definition_inner($mform) {
        global $CFG, $OUTPUT, $SESSION;
        
        require_once($CFG->dirroot.'/question/type/regexp/locallib.php');
        
        $this->showalternate = false;
        if ("" != optional_param('showalternate', '', PARAM_RAW)) {
            $this->showalternate = true;
            $this->questionid = optional_param('id', '', PARAM_NOTAGS);
            $this->usecase = optional_param('usecase', '', PARAM_NOTAGS);
            $this->studentshowalternate = optional_param('studentshowalternate', '', PARAM_NOTAGS);
            $this->fraction = optional_param_array('fraction', '', PARAM_RAW);        
            $this->currentanswers = optional_param_array('answer', '', PARAM_NOTAGS);
            //$this->feedback = optional_param('feedback', '', PARAM_NOTAGS);
            // no longer works in moodle 2.2 and later see http://moodle.org/mod/forum/discuss.php?d=197118
            // so use data_submitted() instead
            $feedback = data_submitted()->feedback;
            // we only need to get the feedback text, for validation purposes when showalternate is requested
            foreach($feedback as $key => $fb) {
                $this->feedback[$key]['text'] = clean_param($fb['text'], PARAM_NOTAGS); 
            }
        }

        // hint mode :: None / Letter / Word
        $menu = array(get_string('none'), get_string('letter', 'qtype_regexp'), get_string('word', 'qtype_regexp'));
        $mform->addElement('select', 'usehint', get_string('usehint', 'qtype_regexp'), $menu);
        $mform->addHelpButton('usehint', 'usehint', 'qtype_regexp');

        // use case :: yes / no
        $menu = array(get_string('caseno', 'qtype_regexp'), get_string('caseyes', 'qtype_regexp'));
        $mform->addElement('select', 'usecase', get_string('casesensitive', 'qtype_regexp'), $menu);

        // display all correct alternate answers to student on review page :: yes / no 
        $menu = array(get_string('no'), get_string('yes'));
        $mform->addElement('select', 'studentshowalternate', get_string('studentshowalternate', 'qtype_regexp'), $menu);
        $mform->addHelpButton('studentshowalternate', 'studentshowalternate', 'qtype_regexp');

        //$mform->closeHeaderBefore('answersinstruct');
        $mform->addElement('static', 'answersinstruct', 'Note.-', get_string('filloutoneanswer', 'qtype_regexp'));

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_shortanswer', '{no}'),
            question_bank::fraction_options(), $minoptions = 3, $addoptions =1); 
        $mform->addElement('header', 'showhidealternate', get_string('showhidealternate', 'qtype_regexp'));
        $mform->addHelpButton('showhidealternate', 'showhidealternate', 'qtype_regexp');
        
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'showalternate', get_string('showalternate', 'qtype_regexp'));
        $mform->registerNoSubmitButton('showalternate');
        
        if ($this->showalternate) {
            $qu = new stdClass();
            $qu->id = $this->questionid;
            $qu->answers = array();
            $i = 0;
            $this->fraction[0] = 1;
            $data = array();
            foreach($this->currentanswers as $key => $answer) {
                $qu->answers[$i] = new stdClass();
                $qu->answers[$i]->answer = $answer;
                $qu->answers[$i]->fraction = $this->fraction[$i];
                // for sending $data to validation
                $data['answer'][$i] = $answer;
                $data['fraction'][$i] = $this->fraction[$i];
                $data['feedback'][$i] = $this->feedback[$i];
                $i++;
            }

            $moodle_val = $this->validation($data, '');
            if ((is_array($moodle_val) && count($moodle_val)!==0)) {
                // non-empty array means errors
                foreach ($moodle_val as $element=>$msg) {
                    $mform->setElementError($element, $msg);
                }
                // set to false in order to set hidealternate button to disabled
                $this->showalternate = false;
            } else {
                // we need to unset SESSION in case Answers have been edited since last call to get_alternateanswers() 
                if (isset($SESSION->qtype_regexp_question->alternateanswers[$this->questionid])) {
                   unset($SESSION->qtype_regexp_question->alternateanswers[$this->questionid]);
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
        }

        $disabled = '';
        if ($this->showalternate) {
            $disabled = '';
        } else {
            $disabled = 'disabled';
        }
        $buttonarray[] = $mform->createElement('submit', 'hidealternate', get_string('hidealternate', 'qtype_regexp'), $disabled);
        $mform->registerNoSubmitButton('hidealternate');

        $mform->addGroup($buttonarray, '', '', array(' '), false);
        
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
                'numhints', 'addhint', 1, get_string('addahint', 'qtype_regexp'));
        
        $mform->setAdvanced('tags');
    }

    protected function data_preprocessing($question) {
        global $CFG, $PAGE, $SESSION;

        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        if (!empty($question->options)) {
            $question->usecase = $question->options->usecase;
            $question->usehint = $question->options->usehint;
            $question->studentshowalternate = $question->options->studentshowalternate;
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
        $data['fraction'][0] = 1;
        $grades = $data['fraction'];
        $answercount = 0;
        $illegalmetacharacters = ". ^ $ * + { } \\";

        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== ''){
                $answercount++;
                $parenserror = '';
                $metacharserror = '';

                // we do not check parenthesis and square brackets in Answer 1 (correct answer)
                if ($key > 0) {
                    $parenserror = check_permutations($trimmedanswer);
                    if ($parenserror) {
                        $errors["answer[$key]"] = $parenserror.'<br />';
                    }
                    $markedline = '';
                    for ($i=0;$i<strlen($trimmedanswer);$i++) {
                        $markedline .= ' ';
                    }
                    $parenserror = check_my_parens($trimmedanswer, $markedline);
                    if ($parenserror) {
                        if (empty($errors["answer[$key]"])) {
                            $errors["answer[$key]"] = get_string("regexperrorparen", "qtype_regexp").'<br />';
                        } else {
                            $errors["answer[$key]"] .= get_string("regexperrorparen", "qtype_regexp").'<br />';
                        }
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
    // pending fix of bug 
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
