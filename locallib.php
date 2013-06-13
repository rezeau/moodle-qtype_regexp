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
 * @copyright  Joseph REZEAU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function expand_regexp($myregexp) {
    global $regexporiginal;

    // JR 16 DEC 2011 add parentheses if necessary; still need to detect un-parenthesized pipe.
    $firstletter = substr($myregexp, 1);
    $lastletter = substr($myregexp, -1);
    if ( strstr($myregexp, '|') && $firstletter != '(' && $lastletter != ')') {
        $myregexp = '('.$myregexp.')';
    }

    $regexporiginal=$myregexp;

    $charlist = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // Change [a-c] to [abc] NOTE: ^ metacharacter is not processed inside [].
        $pattern = '/\\[\w-\w\\]/';     // Find [a-c] in $myregexp.
    while (preg_match($pattern, $myregexp, $matches, PREG_OFFSET_CAPTURE) ) {
        $result = $matches[0][0];
        $offset = $matches[0][1];
        $stringleft = substr($myregexp, 0, $offset +1);
            $stringright = substr($myregexp, $offset + strlen($result) -1);
        $c1 = $result[1];
        $c3 = $result[3];
        $rs = '';
        for ($c = strrpos($charlist, $c1); $c < strrpos($charlist, $c3) +1; $c++) {
            $rs.= $charlist[$c];
        }
        $myregexp = $stringleft.$rs.$stringright;

    }
    // Provisionally replace existing escaped [] before processing the change [abc] to (a|b|c) JR 11-9-2007.
    // See Oleg http://moodle.org/mod/forum/discuss.php?d=38542&parent=354095.
    while (strpos($myregexp, '\[')) {
        $c1 = strpos($myregexp, '\[');
        $c0 = $myregexp[$c1];
        $myregexp = substr($myregexp, 0, $c1  ) .'¬' .substr($myregexp, $c1 + 2);
    }
    while (strpos($myregexp, '\]')) {
        $c1 = strpos($myregexp, '\]');
        $c0 = $myregexp[$c1];
        $myregexp = substr($myregexp, 0, $c1  ) .'¤' .substr($myregexp, $c1 + 2);
    }

    // Change [abc] to (a|b|c).
    $pattern =  '/\[.*?\]/';     // Find [abc] in $myregexp.
    while (preg_match($pattern, $myregexp, $matches, PREG_OFFSET_CAPTURE) ) {
        $result = $matches[0][0];
        $offset = $matches[0][1];
        $stringleft = substr($myregexp, 0, $offset);
        $stringright = substr($myregexp, $offset + strlen($result));
        $rs = substr($result, 1, strlen($result) -2);
        $r = '';
        for ($i=0; $i < strlen($rs); $i++) {
            $r .= $rs[$i].'|';
        }
        $rs = '('.substr($r, 0, strlen($r)-1).')';
        $myregexp = $stringleft.$rs.$stringright;
    }

    // We can now safely restore the previously replaced escaped [].
    while (strpos($myregexp, '¬')) {
        $c1 = strpos($myregexp, '¬');
        $c0 = $myregexp[$c1];
        $myregexp = substr($myregexp, 0, $c1  ) .'\[' .substr($myregexp, $c1 + 2);
    }
    while (strpos($myregexp, '¤')) {
        $c1 = strpos($myregexp, '¤');
        $c0 = $myregexp[$c1];
        $myregexp = substr($myregexp, 0, $c1  ) .'\]' .substr($myregexp, $c1 + 2);
    }

    // Process ? in regexp (zero or one occurrence of preceding char).
    while (strpos($myregexp, '?')) {
        $c1 = strpos($myregexp, '?');
        $c0 = $myregexp[$c1 - 1];

        // If \? -> escaped ?, treat as literal char (replace with ¬ char temporarily).
        // This ¬ char chosen because non-alphanumeric & rarely used...
        if ($c0 == '\\') {
            $myregexp = substr($myregexp, 0, $c1 -1 ) .'¬' .substr($myregexp, $c1 + 1);
            continue;
        }
        // If )? -> meta ? action upon parens (), replace with ¤ char temporarily.
        // This ¤ char chosen because non-alphanumeric & rarely used...
        if ($c0 == ')') {
            $myregexp = substr( $myregexp, 0, $c1 -1 ) .'¤' .substr($myregexp, $c1 + 1);
            continue;
        }
        // If ? metacharacter acts upon an escaped char, put it in $c2.
        if ($myregexp[$c1 - 2] == '\\') {
            $c0 = '\\'.$c0;
        }
        $c2 = '('.$c0.'|)';
        $myregexp = str_replace($c0.'?', $c2, $myregexp);
    }
    // Teplaces possible temporary ¬ char with escaped question mark.
    if (strpos( $myregexp, '¬') != -1) {
        $myregexp = str_replace('¬', '\?', $myregexp);
        $regexporiginal = $myregexp;
    }
    // Replaces possible temporary ¤ char with escaped question mark.
    if (strpos( $myregexp, '¤') != -1) {
        $myregexp = str_replace('¤', ')?', $myregexp);
    }

    // Process ? metacharacter acting upon a set of parentheses \(.*?\)\?
    $myregexp = str_replace(')?', '|)', $myregexp);

    // Replace escaped characters with their escape code.
    while ($c = strpos($myregexp, '\\')) {
        $s1 = substr($myregexp, $c, 2);
        $s2 = $myregexp[$c + 1];
        $s2 = rawurlencode($s2);

        // Alaphanumeric chars can't be escaped; escape codes useful here are:
        // . = %2e    ; + = %2b ; * = %2a
        // Add any others as needed & modify below accordingly.
        switch ($s2) {
            case '.' : $s2 = '%2e';
            break;
            case '+' : $s2 = '%2b';
            break;
            case '*' : $s2 = '%2a';
            break;
        }
        $myregexp = str_replace($s1, $s2, $myregexp);
    }

    // Remove starting and trailing metacharacters; not used for generation but useful for testing regexp.
    if (strpos($myregexp, '^')) {
        $myregexp = substr($myregexp, 1);
    }
    if (strpos($myregexp, '$') == strlen($myregexp) -1) {
        $myregexp = substr( $myregexp, 0, strlen($myregexp) -1);
    }

    $mynewregexp = find_nested_ors($myregexp);     // Check $myregexp for nested parentheses.
    if ($mynewregexp != null) {
        $myregexp = $mynewregexp;
    }

    $result = find_ors($myregexp);     // Expand parenthesis contents.
    if ( is_array($result) ) {
        $results = implode('\n', $result);
    }
    return $result;    // Returns array of alternate strings.
}

// Find individual $nestedors expressions in $myregexp.
// Return false.
function is_nested_ors ($mystring) {
    $orsstart = 0; $orsend = 0; $isnested = false; $parens = 0; $result = '';
    for ($i = 0; $i < strlen($mystring); $i++) {
        switch ($mystring[$i]) {
            case '(':
                $parens++;
                if ($parens == 1) {
                    $orsstart = $i;
                }
                if ($parens == 2) {
                    $isnested = true;
                }
                break;
            case ')':
                $parens--;
                if ($parens == 0) {
                    if ($isnested == true) {
                        $orsend = $i + 1;
                        $i = strlen($mystring);
                        break;
                    }
                }
                break;
        }
    }

    if ($isnested == true) {
        $result = substr( $mystring, $orsstart, $orsend - $orsstart);
        return $result;
    }

    return false;
}

// Find nested parentheses.
function is_parents ($myregexp) {
    $finalresult = null;
    $pattern = '/[^(|)]*\\(([^(|)]*\\|[^(|)]*)+\\)[^(|)]*/';
    if (preg_match_all($pattern, $myregexp, $matches, PREG_OFFSET_CAPTURE)) {
        $matches = $matches[0];
        for ($i=0; $i<count($matches); $i++) {
            $thisresult = $matches[$i][0];
            $leftchar = $thisresult[0];
            $rightchar = $thisresult[strlen($thisresult) -1];
            $outerchars = $leftchar .$rightchar;
            if ($outerchars !== '()') {
                $finalresult = $thisresult;
                break;
            }
        }
    }

    return $finalresult;
}

// Find ((a|b)c).
function find_nested_ors ($myregexp) {
    // Find next nested parentheses in $myregexp.
    while ($nestedors = is_nested_ors ($myregexp)) {
        $nestedorsoriginal = $nestedors;

        // Find what?
        while ($myparent = is_parents ($nestedors)) {
            $leftchar = $nestedors[strpos($nestedors, $myparent) - 1];
            $rightchar = $nestedors[strpos($nestedors, $myparent) + strlen($myparent)];
            $outerchars = $leftchar.$rightchar;
            switch ($outerchars) {
                case '||':
                case '()':
                    $leftpar = '';
                    $rightpar = '';
                    break;
                case '((':
                case '))':
                case '(|':
                case '|(':
                case ')|':
                case '|)':
                    $leftpar = '('; $rightpar = ')';
                    break;
                default:
                    break;
            }
            $t1 = find_ors ($myparent);
            $t = implode('|', $t1);
            $myresult = $leftpar.$t.$rightpar;
            $nestedors = str_replace( $myparent, $myresult, $nestedors);

        }
        // Detect sequence of ((*|*)|(*|*)) within parentheses or |) or (| and remove all INSIDE parentheses.
        $pattern = '/(\\(|\\|)\\([^(|)]*\\|[^(|)]*\\)(\\|\\([^(|)]*\\|[^(|)]*\\))*(\\)|\\|)/';
        while (preg_match($pattern, $nestedors, $matches, PREG_OFFSET_CAPTURE)) {
            $plainors = $matches[0][0];
            $leftchar = $plainors[0];
            $rightchar = $plainors[strlen($plainors) -1];
            // Remove leading & trailing chars.
            $plainors2 = substr($plainors, 1, strlen($plainors) -2);
            $plainors2 = str_replace(  '(',  '', $plainors2);
            $plainors2 = str_replace(  ')',  '', $plainors2);
            $plainors2 = $leftchar .$plainors2 .$rightchar;
            $nestedors = str_replace(  $plainors,  $plainors2, $nestedors);
            if (is_parents($nestedors)) {
                $myregexp = str_replace( $nestedorsoriginal, $nestedors, $myregexp);
                continue;
            }
        }

        // Any sequence of (|)(|) in $nestedors? process them all.
        $pattern = '/(\\([^(]*?\\|*?\\)){2,99}/';
        while (preg_match($pattern, $nestedors, $matches, PREG_OFFSET_CAPTURE)) {
            $parensseq = $matches[0][0];
            $myresult = find_ors ($parensseq);
            $myresult = implode('|', $myresult);
            $nestedors = str_replace( $parensseq, $myresult, $nestedors);
        }
        // Test if we have reached the singleOrs stage.
        if (is_parents ($nestedors) != null) {
            $myregexp = str_replace( $nestedorsoriginal, $nestedors, $myregexp);
            continue;
        }
        // No parents left in $nestedors, ...
        // Find all single (*|*|*|*) and remove parentheses.
        $patternsingleors = '/\\([^()]*\\)/';
        $patternsingleorstotal = '/^\\([^()]*\\)$/';

        while ($p = preg_match($patternsingleors, $nestedors, $matches, PREG_OFFSET_CAPTURE)) {
            $r = preg_match($patternsingleorstotal, $nestedors, $matches, PREG_OFFSET_CAPTURE);
            if ($r) {
                if ($matches[0][0] == $nestedors) {
                    break;
                } // We have reached top of $nestedors: keep ( )!
            }
            $r = preg_match($patternsingleors, $nestedors, $matches, PREG_OFFSET_CAPTURE);
            $singleparens = $matches[0][0];
            $myresult = substr($singleparens, 1, strlen($singleparens)-2);
            $nestedors = str_replace( $singleparens, $myresult, $nestedors);
            if (is_parents ($nestedors) != null) {
                $myregexp = str_replace( $nestedorsoriginal, $nestedors, $myregexp);
                continue;
            }

        }
        $myregexp = str_replace( $nestedorsoriginal, $nestedors, $myregexp);

    } // End while ($nestedors = is_nested_ors ($myregexp)).
    return $myregexp;
}

function find_ors ($mystring) {
    global $regexporiginal;

    // Add extra space between consecutive parentheses (that extra space will be removed later on).
    $pattern = '/\\(.*?\\|.*?\\)/';
    while (strpos($mystring, ')(')) {
        $mystring = str_replace( ')(', ')µ(', $mystring);
    }
    if (strpos($mystring, ')(')) {
        $mystring = str_replace( ')(', ')£(', $mystring);
    }
    // In $mystring, find the parts outside of parentheses ($plainparts).
    $plainparts = preg_split($pattern, $mystring);
    if ($plainparts) {
        $plainparts = index_plain_parts ($mystring, $plainparts);
    }
    $a = preg_match_all($pattern, $mystring, $matches, PREG_OFFSET_CAPTURE);
    if (!$a) {
        $regexporiginal = stripslashes($regexporiginal);
        return $regexporiginal;
    }
    $plainors = index_ors($mystring, $matches);
    // Send $list of $plainparts and $plainors to expand_ors () function.
    return(expand_ors ($plainparts, $plainors));
}

function expand_ors ($plainparts, $plainors) {
    /* This function expands a chunk of words containing a single set of parenthesized alternatives
    of the type: <(aaa|bbb)> OR <ccc (aaa|bbb)> OR <ccc (aaa|bbb) ddd> etc.
    into a LIST of possible alternatives,
    e.g. <ccc (aaa|bbb|)> -> <ccc aaa>, <ccc bbb>, <ccc>.
    */

    $expandedors = array();
    $expandedors[] = '';
    $slen = count($expandedors);
    $expandedors[$slen-1] = '';
    // Condition isset($plainparts[0]) added 14 SEP 2011.
    if (isset($plainparts[0]) && $plainparts[0] == 0) { // If chunk begins with $plainparts.
        $expandedors[$slen-1] = $plainparts[1];
        array_splice($plainparts, 0, 2);
    }
    while ((count($plainparts) !=0) || (count($plainors) !=0)) { // Go through sentence $plainparts.
        $l = count($expandedors);
        for ($k=0; $k<$l; $k++) {
            for ($m=0; $m < count($plainors[1]); $m++) {
                $expandedors[] = '';
                $slen = count($expandedors) -1;
                $expandedors[$slen] = $expandedors[0].$plainors[1][$m];
                if (count($plainparts)) {
                    if ($plainparts[1]) {
                        $expandedors[$slen] .=$plainparts[1];
                    }
                }
                $expandedors[$slen] = rawurldecode($expandedors[$slen]);
            }
            array_splice($expandedors, 0, 1);    // Remove current "model" sentence from Sentences.
        }
        array_splice($plainors, 0, 2); // Remove current $plainors.
        array_splice($plainparts, 0, 2); // Remove current $plainparts.
    }
    // Eliminate all extra µ signs which have been placed to replace consecutive parentheses by )µ(.
    $n = count ($expandedors);
    for ($i = 0; $i < $n; $i++) {
        if (is_int(strpos($expandedors[$i], 'µ') ) ) { // Corrects strpos for 1st char of a string found!
            $expandedors[$i] = str_replace('µ', '', $expandedors[$i]);
        }
    }
    return ($expandedors);
}

function index_plain_parts($mystring, $plainparts) {
    $indexedplainparts = array();
    if (is_array($plainparts) ) {
        foreach ($plainparts as $parts) {
            if ($parts) {
                $index = strpos($mystring, $parts);
                $indexedplainparts[] = $index;
                $indexedplainparts[] = $parts;
            }
        }
    }
    return ($indexedplainparts);
}

function index_ors($mystring, $plainors) {
    $indexedplainors = array();
    foreach ($plainors as $ors) {
        foreach ($ors as $or) {
            $indexedplainors[] = $or[1];
            $o = substr($or[0], 1, strlen($or[0]) -2);
            $o = explode('|', $o);
            $indexedplainors[] = $o;
        }
    }
    return ($indexedplainors);
}

// Functions adapted from Hot Potatoes.
function check_beginning( $guess, $answer, $ignorecase) {
    $outstring = '';
    if ($ignorecase) {
        $guessoriginal = $guess;
        $guess = strtoupper($guess);
        $answer = strtoupper($answer);
    }

    $i1 = textlib::strlen($guess);
    $i2 = textlib::strlen($answer);

    for ($i=0; ( $i< $i1 && $i< $i2); $i++) {
        if (strlen($answer) < $i ) {
            break;
        }
        if (textlib::substr($guess, $i, 1) == textlib::substr($answer, $i , 1)) {
            $outstring .= textlib::substr($guess, $i, 1);
        } else {
            break;
        }
    }

    if ($ignorecase) {
        $outstring = textlib::substr($guessoriginal, 0, textlib::strlen($outstring));
    }
    return $outstring;
}

function get_closest( $guess, $answers, $ignorecase, $ishint) {
    $closest[0] = ''; // Closest answer to be displayed as input field value.
    $closest[1] = ''; // Closest answer to be displayed in feedback line.
    $closest[2] = ''; // Hint state :: plus (added 1 letter), minus (removed extra chars & added 1 letter),
                      // Complete (correct response achieved), nil (beginning of sentence).
    $closest[3] = ''; // Student's guess (rest of).
    $closest[4] = ''; // Added letter or word (according to Help mode).
    $closesta = '';
    $l = textlib::strlen($guess);
    $ignorebegin = '';
    if ($ishint) {
        $closest[2] = 'nil';
    }
    $rightbits = array();
    foreach ($answers as $answer) {
        $rightbits[0][] = $answer;
        $rightbits[1][] = check_beginning($guess, $answer, $ignorecase, $ishint);
    }
    $s = count($rightbits);
    $longest = 0;
    if ($s) {
        $a = $rightbits[0];
        $s = count($a);
        for ($i=0; $i<$s; $i++) {
            $a = $rightbits[0][$i];
            $g = $rightbits[1][$i];
            if (textlib::strlen($g) > $longest) {
                $longest = textlib::strlen($g);
                $closesta = $g;
                if ($ishint) {
                    $closest[2] = 'plus';
                    $closesta_hint = $closesta;
                    $closesta_hint .= textlib::substr($a, $longest, 1);
                    $lenguess = textlib::strlen($guess);
                    $lenclosesta_hint = textlib::strlen($closesta_hint) - 1;
                    if ($lenguess > $lenclosesta_hint) {
                        $closest[2] = 'minus';
                    }
                    if (textlib::substr($a, $longest, 1) == ' ') { // If hint letter is a space, add next one.
                        $closesta_hint .= textlib::substr($a, $longest + 1, 1);
                    }
                    // Word help ADDED JR 18 DEC 2011.
                    if ($ishint > 1) {
                        if (preg_match('/\s.*/', $a, $matches, PREG_OFFSET_CAPTURE, strlen($g) + 1) ) {
                            $closesta_hint = substr($a, 0, $matches[0][1]);
                        } else {
                            $closesta_hint = $a;
                        }
                    }

                    // JR 13 OCT 2012 to fix potential html format tags inside correct answer.
                    $aa = preg_replace("/\//", "\/", $a);

                    if ( preg_match('/^'.$aa.'$/'.$ignorecase, $closesta_hint)) {
                        $closest[2] = 'complete'; // Hint gives a complete correct answer.
                        $state = new stdClass(); // Instantiate $state explicitely for PHP 5.3 compliance.
                        $state->raw_grade = 0;
                        break;
                    }
                    if ($ignorecase) {
                        $ignorebegin = !preg_match('/'.$g.'/', $a);
                    }
                }
            }
        }
    }
    $closest[0] = $closesta;
    // Student clicked the help button with an empty answer.
    if ($closest[0] == '' && $ishint) {
    	$closest[2] = 'plus';
    	$answer = $answers[0];
    	switch ($ishint) {
    		case 1: // Add letter.
    			$closesta_hint = $answer[0];
    			break;
            case 2: // Add word.
            	$words = explode(' ', $answer);
                $closesta_hint = $words[0];
                break;
    	}
    }

    // Type of hint state.
    switch ($closest[2]) {
        case 'plus':
            $closest[0] = $closesta_hint;
            $closest[1] = $guess;
            if ($ignorebegin) {
                $closest[1] = '';
            }
            $closest[4] = str_replace($guess, "", $closesta_hint);
            break;
        case 'minus':
            $closest[0] = $closesta_hint;
            $closest[1] = $closesta;
            break;
        case 'complete':
            $closest[0] = $a;
            $closest[1] = $a;
            break;
        default:
            $closest[0] = $closesta;
            $closest[1] = $closest[0];
    }
    // Search for correct *words* in student's guess, after closest answer has been found
    // and even if closest answer is null JR 26 FEB 2012.
    if ($closest[2] != 'complete') {
        $nbanswers = count ($answers);
        $lenclosesta = strlen($closest[0]);
        $minus = 0;
        if ($closest[2] == 'minus') {
            $minus = 1;
        }
        $restofanswer = substr($guess, $lenclosesta - $minus);
        // 24 APRIL 2013 thanks to Jeff F.
        // this does not work in case expected answer is e.g. "two twenty thirty" and student guess is "twenty two thirty"
        // so for the moment, let's replace this loop with simply setting $restofanswers to $rightbits[0][0].
        $restofanswers = $rightbits[0][0];
        if ($restofanswer) {
            $wordsinrestofanswer = preg_split('/ /', $restofanswer);
            $i = 0;
            unset($array1, $array1);
            $array1 = preg_split("/[\s,]+/", $restofanswer);
            $array2 = preg_split("/[\s,]+/", $restofanswers);
            $misplacedwords = array_intersect($array1, $array2);
            $wrongwords = array_diff($array1, $array2);
            foreach ($wrongwords as $key => $value) {
                $wrongwords[$key] = '<span class="wrongword">'.$value.'</span>';
            }
            unset ($result);
            $result =  $misplacedwords + $wrongwords;
            ksort($result);
            $result = implode (" | ", $result);
            $guess = '<span class="misplacedword">'.$result.'</span>';
            $closest[3] = $guess;
            unset ($result);
        }
    }
    return $closest;
}
// End of functions adapted from Hot Potatoes.

// Function to find whether student's response matches at least the beginning of one of the correct answers.

function find_closest($question, $currentanswer, $correct_response=false, $hintadded = false) {
    global $CFG;
    // JR dec 2011 moved get alternate answers to new function.
    $alternateanswers = get_alternateanswers($question);
    $alternatecorrectanswers = array();
    // JR jan 2012 changed contents of alternateanswers.
    if (isset($question->id)) {
        $qid = $question->id;
        if (!isset ($SESSION->qtype_regexp_question->alternatecorrectanswers[$qid])) {
            foreach ($alternateanswers as $key => $alternateanswer) {
                foreach ($alternateanswer['answers'] as $alternate) {
                    $alternatecorrectanswers[] = $alternate;
                }
            }
            $SESSION->qtype_regexp_question->alternatecorrectanswers[$qid] = $alternatecorrectanswers;
        }
    }
    // Testing ignorecase.
    $ignorecase = 'i';
    if ($question->usecase) {
        $ignorecase = '';
    };
    // Only use ishint value if hint button has been clicked.
    $ishint = $question->usehint * $hintadded;

    // Find closest answer matching student response.
    if (!isset($currentanswer) && !$correct_response) {
        return null;
    }
    if ($correct_response) {
        return $alternatecorrectanswers;
    }
    $closest = get_closest( $currentanswer, $alternatecorrectanswers, $ignorecase, $ishint);
    if ($closest[2] == 'complete') {
        return $closest;
    }
    // Give first character of firstcorrectanswer to student (if option usehint for this question).
    // TODO JR maybe not?
    /*if ($closest[0] == '' && ($question->usehint == true) && $closest[2] == 'nil' ) {
        $closest[0] = $textlib->substr($firstcorrectanswer, 0, 1);
    }*/
    return $closest;
}

// Remove extra blank spaces from student's response.
function remove_blanks($text) {
    // Finds 2 successive spaces (note: \s does not work with French 'à' character!
    $pattern = "/  /";
    while ($w = preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE) ) {
        $text = substr($text, 0, $matches[0][1]) .substr($text, $matches[0][1] + 1);
    }
    return $text;
}

// Check that parentheses and square brackets are balanced, including nested ones.
function check_my_parens($myregexp, $markedline) {
    $parens = array();
    $sqbrackets = array();

    // Walk the $myregexp string to find parentheses and square brackets.
    for ($i = 0; $i<strlen($myregexp); $i++) {
        $escaped = false;
        if ($i > 0 && $myregexp[$i - 1] == "\\") {
            $escaped = true;
        }
        if (!$escaped) {
            switch ($myregexp[$i]) {
                case '(': $parens[$i] = 'open';
                break;
                case ')': $parens[$i] = 'close';
                break;
                case '[': $sqbrackets[$i] = 'open';
                break;
                case ']': $sqbrackets[$i] = 'close';
                break;
                default:
                break;
            }
        }
    }
    // Check for parentheses.
    $tags['open'] = '(';
    $tags['close'] = ')';
    $markedline2 = check_balanced($parens, $tags, $markedline);

    // Check for square brackets.
    $tags['open'] = '[';
    $tags['close'] = ']';
    $markedline2 = check_balanced($sqbrackets, $tags, $markedline2);
    if ($markedline2 != $markedline) {
        return $markedline2;
    } else {
        return;
    }
}

function check_balanced ($bracketstype, $tags, $markedline) {
    $open = array();
    foreach ($bracketstype as $key => $value) {
        switch ($value) {
            case 'open':
                $open[] = $key;
                break;
            case 'close':
                if ($open) {
                    $index = array_pop ($open);
                    $bracketstype[$index] = null;
                    $bracketstype[$key] = null;
                }
            break;
        }
    }
    foreach ($bracketstype as $key => $value) {
        if ($value) {
            if ($value == 'open') {
                $mark = $tags['open'];
            }
            if ($value == 'close') {
                $mark = $tags['close'];
            }
            $markedline[$key] = $mark;
        }
    }
    return $markedline;
}

function check_unescaped_metachars ($myregexp, $markedline) {
    // Joseph Rezeau 02 SEPTEMBER 2011
    // function to detect un-escaped metacharacters.

    /* Full list of metacharacters used in regular expressions syntax.
    ALL these characters can be used as metacharacters in INCORRECT Answers (grade = None)
        . ^ $ * ( ) [ ] + ? | { } \

    Characters which can NOT be used as metacharacters in an accepted Answer (grade > 0)
        and MUST be escaped if used for their LITERAL value: . ^ $ * + { } \

    Characters which CAN be used as metacharacters in an accepted Answer (grade > 0)
        and must be escaped IF used for their LITERAL value: use of those characters
        must lead to alternative CORRECT answers
        ( ) [ ] | ?
    */
    $markedline2 = $markedline;
    // All metacharacters must be escaped.

    // Check for unescaped metacharacters, except for backslash itself.
    $unescaped_regex = '/(?<!\\\\)[\.\^\$\*\+\{\}]/';
    // 1 (?<!\\\\) NO backslash preceding (this is a negative lookahead assertion)
    // 2 [\.\^\$\*\+\{\}] list of metacharacters which can NOT be used in context of accepted Answer (grade > 0).

    $unescaped_metachars = preg_match_all($unescaped_regex, $myregexp, $matches, PREG_OFFSET_CAPTURE);
    if ($unescaped_metachars) {
        foreach ($matches as $v1) {
            // In marked line, replace blank spaces with the unescaped metacharacter.
            foreach ($v1 as $v2) {
                $markedline2[$v2[1]] = $v2[0];
            }
        }
    }

    // Now check for unescaped backslashes.
    $unescaped_regex = '/(^|[^\\\])\\\[^\.|\*|\(|\\[\]\{\}\/)\+\?\^\|\$\.]/';
    // 1 (^|[^\\\]) = beginning of sentence OR no backslash.
    // 2 \\\ = followed by backslash.
    // 3 [^\.|\*|\(|\\[\]\{\}\/)\+\?\^\|\$\.] = NOT followed by a metacharacter.

    $unescaped_metachars = preg_match_all($unescaped_regex, $myregexp, $matches, PREG_OFFSET_CAPTURE);
    if ($unescaped_metachars) {
        $foundbackslash = substr($matches[0][0][0], 1, 3);
        // We must skip a valid escaped backslash.
        if ($foundbackslash != "\\\\") {
            foreach ($matches as $v1) {
                // In marked line, replace blank spaces with the unescaped backslash \.
                foreach ($v1 as $v2) {
                    $markedline2[$v2[1] + 1] = '\\';
                }
            }
        }
    }
    if ($markedline2 != $markedline) {
        return $markedline2;
    } else {
        return;
    }
}

// When displaying unescaped_metachars or unbalanced brackets, too long strings need to be cut up into chunks.
// Change $maxlen if necessary (e.g. to fit smaller width screens).
function splitstring ($longstring, $maxlen=75) {
    $len = textlib::strlen($longstring);
    $stringchunks = array();
    if ($len < $maxlen) {
        $stringchunks [] = $longstring;
    } else {
        for ($i=0; $i<$len; $i += $maxlen) {
            $stringchunks [] = textlib::substr($longstring, $i, $maxlen);
        }
    }
    return $stringchunks;
}

function get_alternateanswers($question) {
    global $CFG, $SESSION;
    $qid = '';

    if (isset($question->id)) {
        $qid = $question->id;
        if (isset ($SESSION->qtype_regexp_question->alternateanswers[$qid])) {
            return $SESSION->qtype_regexp_question->alternateanswers[$qid];
        }
    }
    $alternateanswers = array();
    $i = 1;
    foreach ($question->answers as $answer) {
        if ($answer->fraction != 0) {
            // This is Answer 1 :: do not process as regular expression.
            if ($i == 1) {
                $alternateanswers[$i]['fraction'] = ($answer->fraction*100).'%';
                $alternateanswers[$i]['regexp'] = $answer->answer;
                $alternateanswers[$i]['answers'][] = $answer->answer;
            } else {
                // JR added permutations OCT 2012.
                $answer->answer = has_permutations($answer->answer);
                // End permutations.
                $r = expand_regexp($answer->answer);
                if ($r) {
                    $alternateanswers[$i]['fraction'] = ($answer->fraction*100).'%';
                    $alternateanswers[$i]['regexp'] = $answer->answer;
                    if (is_array($r)) {
                        $alternateanswers[$i]['answers'] = $r; // Normal alternateanswers (expanded).
                    } else {
                        $alternateanswers[$i]['answers'][] = $r; // Regexp was not expanded.
                    }
                }
            }
        }
        $i++;
    }
    // Store alternate answers in SESSION for caching effect DEC 2011.
    $SESSION->qtype_regexp_question->alternateanswers[$qid] = $alternateanswers;
    $SESSION->qtype_regexp_question->alternatecorrectanswers[$qid] = '';
    return $alternateanswers;
}

// JR added OCT 2012.
function check_permutations($ans) {
    $p = preg_match_all("/\[\[(.*)\]\]/U", $ans, $matches);
    if ($p==0) {
        return;
    }
    if ($p>2) {
        return get_string("regexperrortoomanypermutations", "qtype_regexp");
    }
    $nbpermuted = count($matches[1]);
    for ($i=0; $i<$nbpermuted; $i++) {
        $ans = $matches[1][$i];
        $p = preg_match_all("/(.*)_(.*)_.*/U", $ans, $matches_p);
        if ($p==0) {
            return get_string("regexperrornopermutations", "qtype_regexp");
        }
        $p = preg_match_all("/_/", $ans, $matches_p);
        $n = count($matches_p[0]);
        if ($odd = $n%2) {
            return get_string("regexperroroddunderscores", "qtype_regexp").' '.$n;
        }
    }
}

function has_permutations($ans) {
    require_once('combinatorics.php');
    $combinatorics = new Math_Combinatorics;
    $staticparts = array();
    $p = preg_match_all("/\[\[(.*)\]\]/U", $ans, $matches);
    if ($p==0) {
        return $ans;
    }
    $nbpermuted = count($matches[1]);
    $p = preg_match_all("/(.*)\[\[(.*)\]\](.*)/", $ans, $nonpermuted);
    if ($nbpermuted > 1) {
        $p = preg_match_all("/(.*)\[\[(.*)\]\](.*)/", $nonpermuted[1][0], $nonpermuted2);
        $beginning2 = $nonpermuted2[1][0];
        $staticparts[0] = $nonpermuted2[1][0];
        $staticparts[1] = $nonpermuted2[3][0];
        $staticparts[2] = $nonpermuted[3][0];
    } else {
        $staticparts[0] = $nonpermuted[1][0];
        $staticparts[1] = $nonpermuted[3][0];
    }
    $nbstaticparts = count($staticparts);
    $res = array();

    for ($i=0; $i<$nbpermuted; $i++) {
        $res[$i] = '(';
        $ans = $matches[1][$i];
        $p = preg_match_all("/(.*)_(.*)_.*/U", $ans, $matches_p);
        $permutations = $combinatorics->permutations($matches_p[2]);
        $nb = count($matches_p[2]);
        $p = preg_match_all("/_.*_(.*)/", $ans, $matches_r);
        $rightelement = '';
        if ($p) {
            $rightelement = $matches_r[1][0];
        }
        foreach ($permutations as $permutation) {
            for ($j=0; $j<$nb; $j++) {
                $res[$i] .=  $matches_p[1][$j] .$permutation[$j];
            }
            $res[$i] .= $rightelement.'|';
        }
        $res[$i] = rtrim($res[$i], '|');
        $res[$i] .=')';
    }
    $result = '';
    for ($i=0; $i<$nbstaticparts-1; $i++) {
        $result .= $staticparts[$i].$res[$i];
    }
    $result .= $staticparts[$i];
    return $result;
}
