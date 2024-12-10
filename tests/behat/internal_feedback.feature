@synergylearning @gradingform @gradingform_enhancedrubric @javascript
Feature: Rubrics can include internal comments for markers
  In order to give and recieve feedback for marking
  As a teacher
  I need to add and/or view internal comments

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
      | teacher2 | Teacher   | 2        |
      | student1 | Student   | 1        |
      | student2 | Student   | 2        |
      | student3 | Student   | 3        |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | teacher        |
      | teacher2 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
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

  Scenario: Users with associated permissions can add/view internal comments
    # User with capability gradingform/enhancedrubric:addinternalfeedback can edit and view internal feedback for graders
    Given I log in as "teacher2"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    Then "Internal comments" "field" should exist
    And the "Internal comments" "field" should be enabled
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very poor                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 1 | Very poor                  | 1 |
    And I set the field "Internal comments" to "<p>A comment about grading.</p>"
    And I press "Save changes"
    And I log out

    # User with capability gradingform/enhancedrubric:viewinternalfeedback can view but cannot edit internal feedback for graders
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then "A comment about grading." "text" should exist in the "#enhancedrubric-advancedgrading-internalcomment" "css_element"

  Scenario: User without capability gradingform/enhancedrubric:viewinternalfeedback cannot view internal feedback for graders
    Given the following "permission overrides" exist:
      | capability | permission | role | contextlevel | reference |
      | gradingform/enhancedrubric:viewinternalfeedback | Prohibit | teacher | Course | C1 |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then "Internal comments" "field" should not exist

  Scenario: Internal feedback field is not shown when setting is disabled
    Given I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I go to "Test assignment 1 name" advanced grading definition page
    And I click on "Enable internal rubric comments" "checkbox"
    And I press "Save"
    When I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then "Internal comments" "field" should not exist
