@synergylearning @gradingform @gradingform_enhancedrubric @javascript
Feature: Viewing rubric history
  In order to use and refine rubrics to grade students
  As a teacher
  I need to be able to see the grade history

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activity" exists:
      | activity                            | assign            |
      | course                              | C1                |
      | name                                | Test assignment 1 |
      | assignsubmission_onlinetext_enabled | 1                 |
      | assignfeedback_comments_enabled     | 1                 |
      | idnumber                            | assign1           |
      | advancedgradingmethod_submissions   | enhancedrubric    |
      | markingworkflow                     | 0                 |
    And I log in as "teacher1"
    And I change window size to "large"
    And I am on "Course 1" course homepage with editing mode on
    And I go to "Test assignment 1" advanced grading definition page
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

  Scenario: View history.
    # Grading a student.
    When I navigate to "Assignment" in current page administration
    And I go to "Student 2" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Very poor                  | 1 |
      | Criterion B | 0 | Very poor                  | 1 |
      | Criterion C | 0 | Very poor                  | 1 |
      | Criterion D | 0 | Very poor                  | 1 |
    And I press "Save changes"
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 1 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 1 | Very poor                  | 1 |
    And I press "Save changes"
    # Checking that the user grade is correct.
    And I follow "View rubric grading history"
    Then the following should exist in the "gradingform-enhancedrubric-history" table:
      | Grader first name | Grader surname | Updated date            | Assignment grade | Marking stage |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 100              |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 0                |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 100              |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 0                |               |

    When I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r3')]" "xpath_element"
    Then I should see "Points earned: 8 / 12"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "1"

  Scenario: View history with group assignments.
    Given the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | G1       |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
    And I am on the "Test assignment 1" "assign activity" page
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I set the field "Students submit in groups" to "Yes"
    And I press "Save and display"
    # Grading a student.
    When I navigate to "Assignment" in current page administration
    And I go to "Student 2" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Very poor                  | 1 |
      | Criterion B | 0 | Very poor                  | 1 |
      | Criterion C | 0 | Very poor                  | 1 |
      | Criterion D | 0 | Very poor                  | 1 |
    And I press "Save changes"
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 1 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I press "Save changes"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 3 | Mmmm, you can do it better | 1 |
      | Criterion D | 1 | Very poor                  | 1 |
    And I press "Save changes"
    # Checking that the user grade is correct.
    And I follow "View rubric grading history"
    Then the following should exist in the "gradingform-enhancedrubric-history" table:
      | Grader first name | Grader surname | Updated date            | Assignment grade | Marking stage |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 100              |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 0                |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 100              |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 0                |               |

    When I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r3')]" "xpath_element"
    Then I should see "Points earned: 8 / 12"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "1"
