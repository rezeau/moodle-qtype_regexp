@qtype @qtype_regexp
Feature: Preview a Regexp question with hints
  As a teacher
  In order to check my Regexp questions with hints will work for students
  I need to preview them

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype  | name       | template |
      | Test questions   | regexp | regexp-001 | frenchflagletterhint |
      | Test questions   | regexp | regexp-002 | frenchflagwordhint |

  @javascript @_switch_window
  Scenario: Preview a Regexp question with letter hints
    When I am on the "regexp-001" "core_question > preview" page logged in as teacher
    And I should see "What are the colours of the French flag?"
    # Set behaviour options to adaptive mode in order to view the hints
    And I set the following fields to these values:
      | behaviour | adaptive |
    And I press "Start again with these options"
    Then "Buy next letter" "button" should be visible
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's"
    And I press "Check"
    Then I should see "Incorrect"
    And I should see "Missing blue!"
    And I click on "Buy next letter" "button"
    And I should see "Added letter:"
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's blue, white and red"
    And I click on "Check" "button"
    And I click on "Submit and finish" "button"
    Then I should see "The best answer."
    #And I should see "General feedback: OK"
    And I should see "The best correct answer is:"
    And I should see "it's blue, white and red"
    And I click on "Show/Hide alternate answers" "link"
    And I should see "The other accepted answers are:"
    And I should see "80%"
    And I should see "it's blue, white, red"
    And I should see "it is blue, white, red"
    And I should see "they are blue, white, red"
    And I should see "blue, white, red"

  @javascript @_switch_window
  Scenario: Preview a Regexp question with word hints and a penalty of 0.2
    When I am on the "regexp-002" "core_question > preview" page logged in as teacher
    And I should see "What are the colours of the French flag?"
    # Set behaviour options to adaptive mode in order to view the hints
    And I set the following fields to these values:
      | behaviour | adaptive |
    And I press "Start again with these options"
    #Then "Buy next word" "button" should exist
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's white"
    And I press "Check"
    Then I should see "Incorrect"
    And I should see "Misplaced words"
    And I should see "Missing blue!" 
    And I should see "Marks for this submission: 0.00/1.00. This submission attracted a penalty of 0.20"
    And I click on "Buy next word" "button"
    And I should see "Added word: blue, This Help cost you a penalty of: 0.20. Total penalties so far: 0.40."
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's blue, wh"
    And I click on "Check" "button"
    And I click on "Buy next word" "button"
    And I should see "Completed word: white This Help cost you a penalty of: 0.20. Total penalties so far: 0.80."
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's blue, white and red"
    And I click on "Check" "button"
    And I click on "Submit and finish" "button"
    Then I should see "The best answer."
    #And I should see "General feedback: OK"
    And I should see "The best correct answer is:"
    And I should see "it's blue, white and red"
    And I click on "Show/Hide alternate answers" "link"
    And I should see "The other accepted answers are:"
    And I should see "Correct"
    And I should see "Marks for this submission: 1.00/1.00. Accounting for previous tries, this gives 0.20/1.00."

  @javascript @_switch_window
  Scenario: Preview a Regexp question with word hints and no penalties
    When I am on the "regexp-002" "core_question > preview" page logged in as teacher
    And I should see "What are the colours of the French flag?"
    # Set behaviour options to adaptive mode in order to view the hints
    And I set the following fields to these values:
      | behaviour | adaptivenopenalty |
    And I press "Start again with these options"
    #Then "Get next word" "button" should exist
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's white"
    And I press "Check"
    Then I should see "Incorrect"
    And I should see "Misplaced words"
    And I should see "Missing blue!"
    And I should see "Marks for this submission: 0.00/1.00."
    And I click on "Get next word" "button"
    And I should see "Added word: blue,"
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's blue, wh"
    And I click on "Check" "button"
    And I click on "Get next word" "button"
    And I should see "Completed word: white"
    And I set the field with xpath "//div[@class='answer']//input[contains(@id, '1_answer')]" to "it's blue, white and red"
    And I click on "Check" "button"
    And I click on "Submit and finish" "button"
    Then I should see "The best answer."
    And I should see "The best correct answer is:"
    And I should see "it's blue, white and red"
    And I click on "Show/Hide alternate answers" "link"
    And I should see "The other accepted answers are:"
    And I should see "Correct"
    And I should see "Marks for this submission: 1.00/1.00."
