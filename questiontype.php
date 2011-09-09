<?php  // $Id: questiontype.php,v 1.1.4.7 2008/04/08 22:13:36 joseph_rezeau Exp $

///////////////////
/// REGEXP ///
///////////////////
// Jean-Michel Vedrine & Joseph Rezeau
// based on shortanswer/questiontype 
/// QUESTION TYPE CLASS //////////////////

///
/// This class contains some special features in order to make the
/// question type embeddable within a multianswer (cloze) question
///

	include("expandregexp.php"); // to generate alternate correct answers from reg expressions in answers
	class question_regexp_qtype extends default_questiontype {
	function name() {
		return 'regexp';
	}

	function get_question_options(&$question) {
		// Get additional information from database
		// and attach it to the question object
		if (!$question->options = get_record('question_regexp', 'question', $question->id)) {
			notify('Error: Missing question options!');
			return false;
		}

		if (!$question->options->answers = get_records('question_answers', 'question',
				$question->id, 'id ASC')) {
			notify('Error: Missing question answers!');
			return false;
		}
		return true;
	}

	function save_question_options($question) {
		if (!$oldanswers = get_records("question_answers", "question", $question->id, "id ASC")) {
			$oldanswers = array();
		}

		$answers = array();
		$maxfraction = -1;
		$i = 0;

		// Insert all the new answers
		foreach ($question->answer as $key => $dataanswer) {
			$i++;
            $result = '';
            $answer = '';
			if ($dataanswer != "") {
				if ($oldanswer = array_shift($oldanswers)) {  // Existing answer, so reuse it
					$answer = $oldanswer;
					$answer->answer   = trim($dataanswer);
					$answer->fraction = $question->fraction[$key];
					$answer->feedback = $question->feedback[$key];
					if (!update_record("question_answers", $answer)) {
						$result->error = "Could not update quiz answer! (id=$answer->id)";
						return $result;
					}
				} else {	// This is a completely new answer
					unset($answer);
					$answer->answer   = trim($dataanswer);
					$answer->question = $question->id;
					$answer->fraction = $question->fraction[$key];
					$answer->feedback = $question->feedback[$key];
					if (!$answer->id = insert_record("question_answers", $answer)) {
						$result->error = "Could not insert quiz answer!";
						return $result;
					}
				}
				$answers[] = $answer->id;
				if ( ($i == 1) && ($question->fraction[$key] == 1) ) {
					$maxfraction = 1;
				}
			}
		}
        // for instance the regexp has been authored as part of a CLOZE question, no usehint value has been provided
		if (!isset($question->usehint)) { 
			$question->usehint = 0;
		}

		if ($options = get_record("question_regexp", "question", $question->id)) {
			$options->answers = implode(",",$answers);
            $options->usehint = $question->usehint;
			if (!update_record("question_regexp", $options)) {
				$result->error = "Could not update quiz regexp options! (id=$options->id)";
				return $result;
			}
		} else {
			unset($options);
			$options->question = $question->id;
			$options->answers = implode(",",$answers);
            $options->usehint = $question->usehint;
			if (!insert_record("question_regexp", $options)) {
				$result->error = "Could not insert quiz regexp options!";
				return $result;
			}
		}

		// delete old answer records
		if (!empty($oldanswers)) {
			foreach($oldanswers as $oa) {
				delete_records('question_answers', 'id', $oa->id);
			}
		}
	}

	/**
	* Deletes question from the question-type specific tables
	*
	* @return boolean Success/Failure
	* @param object $question  The question being deleted
	*/
	function delete_question($questionid) {
		delete_records("question_regexp", "question", $questionid);
		return true;
	}

	function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
		global $CFG;
		global $closestcomplete;
        /// Use text services
        $textlib = textlib_get_instance();
        $formatoptions = '';
		// get *first* correct answer for this question
		// rewrite this function if more answers are needed
		$correctanswers = $this->get_correct_responses($question, $state);
		$readonly = empty($options->readonly) ? '' : 'readonly="readonly"';
		$formatoptions->noclean = true;
		$formatoptions->para = false;
		$nameprefix = $question->name_prefix;
		$isadaptive = ($cmoptions->optionflags & QUESTION_ADAPTIVE);
		$ispreview = ($state->attempt == 0); // workaround to detect if question is displayed to teacher in preview popup window
		/// Print question text and media

		$questiontext =  format_text($question->questiontext,
						 $question->questiontextformat,
						 $formatoptions, $cmoptions->course);
		$image = get_question_image($question, $cmoptions->course);
		/// Print input controls
		if (isset($state->responses[''])) {
			$r = $this->remove_blanks($state->responses['']); // $r = original full student response
			if ($closest = $this->find_closest(&$question, &$state, &$teststate, $isadaptive, $ispreview) ) {
				$response = $closest;
			} else {
				$response = $r;
			}
			$value = ' value="'.s($response, true).'" ';
		} else {
			$value = ' value="" ';
		}
		$inputname = ' name="'.$nameprefix.'" ';
		$r = stripslashes($r);
		$f = ''; // student's response with corrections to be displayed in feedback div
		$s = ''; // wrong part of student's response to be displayed in red crossed-out in feedback div
		$l = $textlib->strlen($r) - 1;
        $ishint = $textlib->substr($r, $l);
        if ($ishint == '¶') {
            $ishint = TRUE;
            $r = $textlib->substr($r, 0, $l);
        } else {
            $ishint = FALSE;
        }

		if ( ($ispreview || $isadaptive) && $r ) {
			$s = $textlib->substr($r, $textlib->strlen ($response ), 99);
			if ($ishint and $s) {
				$s = $textlib->substr($r, $textlib->strlen ($response) , 99);
				$response = $textlib->substr($response, 0, $textlib->strlen ($response));
			}
			if ($closest) {
				$f = '<span style="color:#0000FF;">'.$response.'</span><span style="text-decoration:line-through; color:#FF0000;">'.
                $s."</span><br />";
			} else {
				$f = '<span style="text-decoration:line-through; color:#FF0000;">'.stripslashes($response)."</span><br />";		
			}
		}
		$feedback = '';
        $class = '';
        $feedbackimg = '';

		if ($options->feedback) {
            $class = question_get_feedback_class(0);
            $feedbackimg = question_get_feedback_image(0);

            // hint has added to response one letter which makes response match one correct answer: submission is correct!
			if ($closestcomplete) { 
			// we must tell $state that everything is OK 
				$state->responses[''] = $closest;
				$state->last_graded->responses[''] = $closest;
                $state->last_graded->grade = $question->maxgrade - $state->last_graded->sumpenalty;
				$state->raw_grade = $question->maxgrade;
				$state->last_graded->raw_grade = $question->maxgrade;
			}

			foreach($question->options->answers as $answer) {
				if($this->test_response($question, $state, $answer)) {
                    $class = question_get_feedback_class($answer->fraction);
			        $feedbackimg = question_get_feedback_image($answer->fraction);
                    if ($answer->feedback) {
						$feedback = format_text($answer->feedback, true, $formatoptions, $cmoptions->course);
					}
					break;
				}
			}
		}
		$feedback = $f .$feedback;
		$correctanswer = '';
		if ($options->readonly && $options->correct_responses) {
			$delimiter = '';
			if ($correctanswers) {
                foreach ($correctanswers as $ca) {
					$correctanswer .= $delimiter.$ca;
					$delimiter = ', ';
				}
			}
		}
		$correctanswer = stripslashes($correctanswer);
		include("$CFG->dirroot/question/type/regexp/display.html");
	}
	
// remove extra blank spaces from student's response
	function remove_blanks($text) {
		$pattern = "/  /"; // finds 2 successive spaces (note: \s does not work with French 'à' character! 
		while($w = preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE) ) {
			$text = substr($text, 0, $matches[0][1]) .substr($text ,$matches[0][1] + 1);
		}
		return $text;
	}
	
	// ULPGC ecastro
	// this function is used by the Item analysis module!
	function check_response(&$question, &$state) {
	//JR uncomment return false if you want real student answers displayed
	//return false;
		$answers = &$question->options->answers;
		$testedstate = clone($state);
		$teststate   = clone($state);
		$i = 0;
		foreach($answers as $aid => $answer) {
			$teststate->responses[''] = $answer->answer;
			if($this->compare_responses($question, $testedstate, $teststate) ) {
				return $aid;
			}
		}
		return false;
	}

	function compare_responses(&$question, &$state, &$teststate) {
        /// Use text services
        $textlib = textlib_get_instance();

		if (isset($state->responses[''])) {
			$response0 = $this->remove_blanks( stripslashes( $state->responses[''] ) );
		} else {
			$response0 = '';
		}
		if (!$response0) {
			return false;
		}
        $answers = $question->options->answers;
        $answers = current($answers);
        $firstcorrectanswer = $answers->answer;
        $r = $this->remove_blanks($state->responses['']); // $r = original full student response
		if ($r && $question->options->usehint) {
			$c = $r[strlen($r)-1];
			$d = ord($c);
				if ($d == 9) { // hint button added \t char (code 9) at end of student response
                $r = substr($r,0,strlen($r)-1) .'¶';
				$state->responses[''] = $r;
			}
		}
		if (isset($teststate->responses[''])) {
			$response1 = trim($teststate->responses['']);
		} else {
			$response1 = '';
		}
// testing for presence of final /i code meaning ignore case
		$ignorecase = "";
		if ( substr($response1,strlen($response1) - 2, 2) == '/i') {
			$response1 = substr($response1,0,strlen($response1) - 2);
			$ignorecase = 'i';
		}

/// testing for presence of (right or wrong) elements in student's answer

        // no need to do any further matching
        if ($response1 == $response0) {
        	return true;
		}
		if ( (preg_match('/^'.$response1.'$/'.$ignorecase, $response0)) ) {
			return true;
		}
// testing for absence of needed (right) elements in student's answer, through initial '--' coding
		if (substr($response1,0,2) == '--') {
			$response1 = substr($response1,2);
            // this is a NOT (a AND b AND c etc.) request
            if (preg_match('/^.*\&\&.*$/', $response1)) {
                $pattern = '/&&[^(|)]*/';
                $missingstrings = preg_match_all($pattern,$response1, $matches, PREG_OFFSET_CAPTURE);
                $strmissingstrings = $matches[0][0][0];
                $strmissingstrings = $textlib->substr($strmissingstrings, 2);
                $openparenpos = $matches[0][0][1] -1;
                $closeparenpos = $openparenpos + $textlib->strlen($strmissingstrings) + 4;
                $start = $textlib->substr($response1 , 0, $openparenpos);
                $finish = $textlib->substr($response1 , $closeparenpos);
                $missingstrings = explode ('&&', $strmissingstrings);
                foreach ($missingstrings as $missingstring) {
                    $missingstring = $start.$missingstring.$finish;
                    if (preg_match('/'.$missingstring.'/'.$ignorecase, $response0) == 0 ) {
                        return true;
                    }
                }
            } else {  // this is a NOT (a OR b OR c etc.) request                			
                if (preg_match('/^'.$response1.'$/'.$ignorecase, $response0)  == 0) {
    				return true;
    			}									
            }
        }
		return false;
	}

	// function to find whether student's response matches at least the beginning of one of the correct answers
	function find_closest(&$question, &$state, &$teststate, $isadaptive, $ispreview) {
		global $CFG;
		global $closestcomplete;
		$closestcomplete = false;
        /// Use text services
        $textlib = textlib_get_instance();

		if (isset($state->responses[''])) {
			$response0 = $this->remove_blanks(stripslashes($state->responses['']));
		} else {
			return null;
		}
		if ( (!$isadaptive) && (!$ispreview) ) {
			return null; // no need to generate alternate answers because no hint will be needed in non-adaptive mode
		}

		 // generate alternative answers for answers with score > 0%
		 // this means that TEACHER MUST write answers with a > 0% grade as regexp generating alternative answers
		$correctanswers = array();
		$i = 0;
        $firstcorrectanswer = '';
		foreach ($question->options->answers as $answer) {
			if ($i == 0) {
				$i++;
				$firstcorrectanswer = $answer->answer;
				$correctanswer['answer'] = $answer->answer;
				$correctanswer['fraction'] = $answer->fraction;
				$correctanswers[] = $correctanswer;
			} else if ($answer->fraction != 0) {   
    			$correctanswer['answer'] = $answer->answer;
    			$correctanswer['fraction'] = $answer->fraction;
    			$correctanswers[] = $correctanswer;
			}
		}
		$alternateanswers = array();
		$alternateanswersic = array();
		$i=0;
		foreach ($correctanswers as $thecorrectanswer) {
			$i++;
			if ($i == 1) { 
				$alternateanswers[] = $firstcorrectanswer;
				continue;
			}
			$correctanswer = $thecorrectanswer['answer'];
			$fraction = $thecorrectanswer['fraction']*100;
            $fraction = $fraction."%"; //JR 05-10-2007
			if ( substr($correctanswer,strlen($correctanswer) - 2, 2) == '/i') { // ignore case
				$correctanswer = substr($correctanswer, 0, strlen($correctanswer) - 2);
				$r = expand_regexp($correctanswer); // go to expand_regexp function to generate alternative answers
				if ($r) { // if error in regular expression, expand_regexp will return nothing
					if (is_array($r)) {
						$alternateanswersic[] = "$fraction <strong>$correctanswer</strong> (ignore case)"; 
                        // CAUTION: the <strong> tag is detected in get_closest function!
						$alternateanswersic = array_merge($alternateanswersic, $r); // ignorecase alternateanswers
					} else {
							$alternateanswersic[] = "$fraction <strong>$r</strong>"; 
							$alternateanswersic[] = "$r"; 
					}
				}
			} else { // do not ignorecase
				$r = expand_regexp($correctanswer);
				if ($r) { // if error in regular expression, expand_regexp will return nothing
					if (is_array($r)) {
						$alternateanswers[] = "$fraction <strong>$correctanswer</strong>";				
						$alternateanswers = array_merge($alternateanswers, $r); // normal alternateanswers
					} else {
							$alternateanswers[] = "$fraction <strong>$r</strong>"; 
							$alternateanswers[] = "$r"; 
					}
				}
			}
		}

// print display button for teacher only
		if (($ispreview) && ((sizeof($alternateanswersic) != 0) || (sizeof($alternateanswers) != 0))) {
			$show = get_string("showalternate", "qtype_regexp");
			echo("<input type=\"button\" value=\"$show\" onclick=\"showdiv('allanswers',this)\" />");

// print alernate answers
			echo('<div id="allanswers" style="margin-bottom:0px; margin-top:0px; display:none;"><hr />');	
				foreach ($alternateanswers as $answer) {
					echo("$answer<br />");
				}	
				foreach ($alternateanswersic as $answer) {
					echo("$answer<br />");
				}
			echo("<hr /></div>");
		}
        $findhint = $textlib->substr($response0, strlen($response0)-1);

// if student response is null (nothing typed in) then no need to go get closest correct answer
		if (!$response0) {
            return false;
		}
        if ($response0 == $findhint) {
            $closest = $textlib->substr($firstcorrectanswer, 0, 1);
            return $closest;
        }


// find closest answer matching student response

		if (is_array ($alternateanswers) ) {
			$closestanswer = get_closest( $response0, $alternateanswers, false);
		}
		if (is_array ($alternateanswersic) ) {
			$closestansweric = get_closest( $response0, $alternateanswersic, true);
		}
		if ($closestanswer[1] == true) {
			$closestcomplete = true;
			return $closestanswer[0];	
		} elseif ($closestansweric[1] == true) {
			$closestcomplete = true;		
			return $closestansweric[0];	
		} 

		if (strlen($closestanswer[0]) > strlen($closestansweric[0]) ) { // find longest closest answer from ignore case and don't ignore case
			$closest = $closestanswer[0];
		} else {
			$closest = $closestansweric[0];
		}

// if nothing correct, give first character of firstcorrectanswer to student (if option usehint for this question)
		if ($closest == '' && $question->options->usehint) {
            $closest = $firstcorrectanswer;
            $closest = $textlib->substr($firstcorrectanswer, 0, 1);
		}
		return $closest;
	}

	function test_response(&$question, &$state, &$answer) {
		$teststate   = clone($state);
		$teststate->responses[''] = trim($answer->answer);
			if($this->compare_responses($question, $state, $teststate)) {
				return true;
			}
		return false;
	}
	
    function get_actual_response($question, $state) {
       // change length to truncate responses here if you want
       $lmax = 255;
       if (!empty($state->responses)) {
              $responses[] = (strlen($state->responses['']) > $lmax) ?
               substr($state->responses[''], 0, $lmax).'...' : $state->responses[''];
       } else {
           $responses[] = '';
       }
       return $responses;
    }
    /**
    * Return a summary of the student response
    *
    * This function returns a short string of no more than a given length that
    * summarizes the student's response in the given $state. This is used for
    * example in the response history table
    * @return string         The summary of the student response
    * @param object $question 
    * @param object $state   The state whose responses are to be summarized
    * @param int $length     The maximum length of the returned string
    */
    function response_summary($question, $state, $length=255) {
        // This should almost certainly be overridden
        return substr(implode(',', $this->get_actual_response($question, $state)), 0, $length);
    }

	/// BACKUP FUNCTIONS ////////////////////////////

	/*
	 * Backup the data in the question
	 *
	 * This is used in question/backuplib.php
	 */
	function backup($bf,$preferences,$question,$level=6) {

		$status = true;

		$regexps = get_records("question_regexp","question",$question,"id");
		//If there are regexps
		if ($regexps) {
			//Iterate over each regexp
			foreach ($regexps as $regexp) {
				$status = fwrite ($bf,start_tag("REGEXP",$level,true));
				//Print regexp contents
				fwrite ($bf,full_tag("ANSWERS",$level+1,false,$regexp->answers));
                fwrite ($bf,full_tag("USEHINT",$level+1,false,$regexp->usehint));
				$status = fwrite ($bf,end_tag("REGEXP",$level,true));
			}
			//Now print question_answers
			$status = question_backup_answers($bf,$preferences,$question);
		}
		return $status;
	}

/// RESTORE FUNCTIONS /////////////////

	/*
	 * Restores the data in the question
	 *
	 * This is used in question/restorelib.php
	 */
	function restore($old_question_id,$new_question_id,$info,$restore) {

		$status = true;
        $regexp = '';

		//Get the regexps array
		$regexps = $info['#']['REGEXP'];

		//Iterate over regexps
		for($i = 0; $i < sizeof($regexps); $i++) {
			$sho_info = $regexps[$i];

			//Now, build the question_regexp record structure
			$regexp->question = $new_question_id;
			$regexp->answers = backup_todb($sho_info['#']['ANSWERS']['0']['#']);
            $regexp->usehint = backup_todb($sho_info['#']['USEHINT']['0']['#']);

			//We have to recode the answers field (a list of answers id)
			//Extracts answer id from sequence
			$answers_field = "";
			$in_first = true;
			$tok = strtok($regexp->answers,",");
			while ($tok) {
				//Get the answer from backup_ids
				$answer = backup_getid($restore->backup_unique_code,"question_answers",$tok);
				if ($answer) {
					if ($in_first) {
						$answers_field .= $answer->new_id;
						$in_first = false;
					} else {
						$answers_field .= ",".$answer->new_id;
					}
				}
				//check for next
				$tok = strtok(",");
			}
			//We have the answers field recoded to its new ids
			$regexp->answers = $answers_field;

			//The structure is equal to the db, so insert the question_regexp
			$newid = insert_record ("question_regexp",$regexp);

			//Do some output
			if (($i+1) % 50 == 0) {
				if (!defined('RESTORE_SILENTLY')) {
					echo ".";
					if (($i+1) % 1000 == 0) {
						echo "<br />";
					}
				}
				backup_flush(300);
			}

			if (!$newid) {
				$status = false;
			}
		}

		return $status;
	}
 
 
 /**
    * Provide export functionality for xml format
    * @param question object the question object
    * @param format object the format object so that helper methods can be used 
    * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
    * @return string the data to append to the output buffer or false if error
    */
    function export_to_xml( $question, $format, $extra=null ) {
        $expout = "    <usehint>{$question->options->usehint}</usehint>\n ";
        foreach($question->options->answers as $answer) {
            $percent = 100 * $answer->fraction;
            $expout .= "    <answer fraction=\"$percent\">\n";
            $expout .= $format->writetext( $answer->answer,3,false );
            $expout .= "      <feedback>\n";
            $expout .= $format->writetext( $answer->feedback,4,false );
            $expout .= "      </feedback>\n";
            $expout .= "    </answer>\n";
        }
        return $expout;
    }

    function import_from_xml($data, $question, $format, $extra=null) {
        if (!array_key_exists('@', $data)) {
            return false;
        }
        if (!array_key_exists('type', $data['@'])) {
            return false;
        }
        if ($data['@']['type'] == 'regexp') {

            // get common parts
            $question = $format->import_headers($data);

            // header parts particular to regexp
            $question->qtype = 'regexp';

            // get usehint
            $question->usehint = $data['#']['usehint'][0]['#'];

            // run through the answers
            $answers = $data['#']['answer'];  
            $a_count = 0;
            foreach ($answers as $answer) {
                $ans = $format->import_answer( $answer );
                $question->answer[$a_count] = $ans->answer;
                $question->fraction[$a_count] = $ans->fraction;
                $question->feedback[$a_count] = $ans->feedback;
                ++$a_count;
            }
            return $question;
        }
            return false;
     }
}
//// END OF CLASS ////

//////////////////////////////////////////////////////////////////////////
//// INITIATION - Without this line the question type is not in use... ///
//////////////////////////////////////////////////////////////////////////
// Register this question type with the system.
question_register_questiontype(new question_regexp_qtype()); 
?>