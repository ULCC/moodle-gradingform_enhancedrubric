@synergylearning @gradingform @gradingform_enhancedrubric
Feature: Rubrics can be copied from one section to another
  In order to use and refine rubrics to grade students
  As a teacher
  I need to copy previous section rubrics

  @javascript
  Scenario: I can use rubrics to copy grades
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | teacher        |
      | teacher2 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activity" exists:
      | activity                            | assign                   |
      | course                              | C1                       |
      | name                                | Test assignment 1 name   |
      | assignsubmission_onlinetext_enabled | 1                        |
      | assignfeedback_comments_enabled     | 1                        |
      | advancedgradingmethod_submissions   |enhancedrubric            |
      | grade                               | Default competence scale |
      | markingworkflow                     | 1                        |
    And I log in as "teacher2"
    And I am on "Course 1" course homepage with editing mode on
    And I go to "Test assignment 1 name" advanced grading definition page
    # Defining a enhanced rubric.
    And I set the following fields to these values:
      | Name                          | Assignment 1 rubric     |
      | Description                   | Rubric test description |
      | Minimum total points required | 7                       |
    And I define the following enhanced rubric:
      | Criterion A | Level 11 | 0 | 0 | Level 12 | 1 | 1 | Level 13 | 2 | 0 | Level 14  | 3 | 0 |
      | Criterion B | Level 21 | 0 | 0 | Level 22 | 1 | 1 | Level 23 | 2 | 0 | Level 24  | 3 | 0 |
      | Criterion C | Level 31 | 0 | 0 | Level 32 | 1 | 1 | Level 33 | 2 | 0 | Level 34  | 3 | 0 |
      | Criterion D | Level 41 | 0 | 0 | Level 42 | 1 | 0 | Level 43 | 2 | 1 | Level 44  | 3 | 0 |
    And I press "Save rubric and make it ready"
    And I log out

    # Grading a student.
    When I log in as "teacher2"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Very bad | 1 |
      | Criterion B | 0 | Very bad | 1 |
      | Criterion C | 0 | Very bad | 1 |
      | Criterion D | 0 | Very bad | 1 |
    And I press "Save changes"
    # Initial rubric copied to second.
    And I press "Feedback comments - Final grading"
    And I press "Feedback comments - Second grading"
    And I click on "Copy initial grading details" "button" in the "#enhancedrubric-advancedgrading2" "css_element"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good | 2 |
    And I press "Save changes"
    Then the level with "3" points is selected for the enhanced rubric criterion "Criterion A" in "2"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion B" in "2"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion C" in "2"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion D" in "2"
    And the feedback "Very good" exists for the enhanced rubric criterion "Criterion A" in "2"
    And the feedback "Very bad" exists for the enhanced rubric criterion "Criterion B" in "2"
    And the feedback "Very bad" exists for the enhanced rubric criterion "Criterion C" in "2"
    And the feedback "Very bad" exists for the enhanced rubric criterion "Criterion D" in "2"

    # Final rubric filled.
    When I click on "Copy initial grading details" "button" in the "#enhancedrubric-advancedgrading3" "css_element"
    And I press "Save changes"
    Then the level with "0" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion D" in "3"
    # We need to wait for the toast to clear as it obscures the button.
    And I wait "1" seconds

    When I click on "Copy second grading details" "button" in the "#enhancedrubric-advancedgrading3" "css_element"
    And I grade by filling the enhanced rubric with:
      | Criterion B | 2 | Very good | 3 |
      | Criterion C | 2 | Very good | 3 |
      | Criterion D | 3 | Very good | 3 |
    And I press "Save changes"
    Then the level with "3" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion D" in "3"
