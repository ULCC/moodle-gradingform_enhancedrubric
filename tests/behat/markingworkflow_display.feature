@synergylearning @gradingform @gradingform_enhancedrubric
Feature: Rubrics sections can be edited by users with the correct capabilities
  In order to use and refine rubrics to grade students
  As a teacher
  I need to edit section rubrics

  @javascript
  Scenario: I can edit section rubric
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
    And I am on "Course 1" course homepage
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

    # Viewing rubric as teacher.
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then "input[type='radio']" "css_element" should exist in the "#enhancedrubric-advancedgrading1" "css_element"
    And I press "Feedback comments - Second grading"
    And "input[type='radio']" "css_element" should not exist in the "#enhancedrubric-advancedgrading2" "css_element"
    And "Copy initial grading details" "button" should not exist in the "#enhancedrubric-advancedgrading2" "css_element"
    And I press "Feedback comments - Final grading"
    And "input[type='radio']" "css_element" should not exist in the "#enhancedrubric-advancedgrading3" "css_element"
    And "Copy initial grading details" "button" should not exist in the "#enhancedrubric-advancedgrading3" "css_element"
    And "Copy second grading details" "button" should not exist in the "#enhancedrubric-advancedgrading3" "css_element"
    And I am on "Course 1" course homepage
    And I log out

    # Viewing rubric as editing teacher.
    When I log in as "teacher2"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    Then "input[type='radio']" "css_element" should exist in the "#enhancedrubric-advancedgrading1" "css_element"
    And I press "Feedback comments - Second grading"
    And "input[type='radio']" "css_element" should exist in the "#enhancedrubric-advancedgrading2" "css_element"
    And "Copy initial grading details" "button" should exist in the "#enhancedrubric-advancedgrading2" "css_element"
    And "input[type='radio']" "css_element" should exist in the "#enhancedrubric-advancedgrading3" "css_element"
    And "Copy initial grading details" "button" should exist in the "#enhancedrubric-advancedgrading3" "css_element"
    And "Copy second grading details" "button" should exist in the "#enhancedrubric-advancedgrading3" "css_element"
