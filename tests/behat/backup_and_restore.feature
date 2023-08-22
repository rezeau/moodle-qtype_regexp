@qtype @qtype_shortanswer
Feature: Test duplicating a quiz containing a Regexp question
  As a teacher
  In order re-use my courses containing Regexp questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name            | template |
      | Test questions   | regexp | regularshortanswer-001 | frenchflag |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | regularshortanswer-001 | 1 |

  @javascript
  Scenario: Backup and restore a course containing a Regexp question
    #Needs to set browser window height to maximum to avoid error!
    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I am on the "Course 2" "core_question > course question bank" page
    And I choose "Edit question" action for "regularshortanswer-001" in the question bank
    And I pause
    Then the following fields match these values:
      | Question name        | regularshortanswer-001                            |
      #| Question text        | French flag colors: __________                    |
      | General feedback     | OK.                                               |
      | Default mark         | 1                                                 |
      | Case sensitivity     | No, case is unimportant                           |
      | id_answer_0          | it's blue, white and red                          |
      | id_fraction_0        | 100%                                              |
      | id_feedback_0        | That is a very good answer.                       |
      | id_answer_1          | --.*blue.*                                        |
      | id_fraction_1        | None                                              |
      | id_feedback_1        | Missing blue!                                     |
      #| id_answer_2          | *                                                 |
      #| id_fraction_2        | None                                              |
      #| id_feedback_2        | That is a bad answer.                             |
    