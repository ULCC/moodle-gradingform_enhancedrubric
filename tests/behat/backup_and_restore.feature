@synergylearning @gradingform @gradingform_enhancedrubric @javascript
Feature: Test duplicating an assignment using enhanced rubric
  As a teacher
  In order re-use my courses containing enhanced rubric marking
  I need to be able to backup and restore them

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
      | markingworkflow                     | 1                 |
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
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Very poor                  | 1 |
      | Criterion B | 0 | Very poor                  | 1 |
      | Criterion C | 0 | Very poor                  | 1 |
      | Criterion D | 0 | Very poor                  | 1 |
    And I press "Save changes"
    And I press "Feedback comments - Initial grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Ok                         | 1 |
      | Criterion B | 1 | Ok                         | 1 |
      | Criterion C | 1 | Ok                         | 1 |
      | Criterion D | 1 | Ok                         | 1 |
    And I press "Feedback comments - Initial grading"
    And I press "Feedback comments - Second grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Mmmm, you can do it better | 2 |
      | Criterion B | 1 | Ok                         | 2 |
      | Criterion C | 1 | Ok                         | 2 |
      | Criterion D | 1 | Ok                         | 2 |
    And I press "Feedback comments - Second grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Mmmm, you can do it better | 3 |
      | Criterion B | 1 | Ok                         | 3 |
      | Criterion C | 2 | Mmmm, you can do it better | 3 |
      | Criterion D | 1 | Ok                         | 3 |
    And I press "Save changes"
    And I press "Feedback comments - Initial grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Mmmm, you can do it better | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Mmmm, you can do it better | 1 |
    And I press "Feedback comments - Initial grading"
    And I press "Feedback comments - Second grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Mmmm, you can do it better | 2 |
      | Criterion B | 3 | Very good                  | 2 |
      | Criterion C | 2 | Mmmm, you can do it better | 2 |
      | Criterion D | 2 | Mmmm, you can do it better | 2 |
    And I press "Feedback comments - Second grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Mmmm, you can do it better | 3 |
      | Criterion B | 3 | Very good                  | 3 |
      | Criterion C | 2 | Mmmm, you can do it better | 3 |
      | Criterion D | 3 | Very good                  | 3 |
    And I set the field "Marking workflow state" to "Released"
    And I press "Save changes"
    And I log out
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I am on "Course 2" course homepage
    And I go to "Student 1" "Test assignment 1" activity advanced grading page

    # Checking that the user grade is correct.
    And I follow "View rubric grading history"
    Then the following should exist in the "gradingform-enhancedrubric-history" table:
      | Grader first name | Grader surname | Updated date            | Assignment grade | Marking stage |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 100              | Released      |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 0                |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 100              |               |
      | Teacher           | 1              | ##today##%A, %d %B %Y## | 0                |               |

    When I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r0')]" "xpath_element"
    Then I should see "Points earned: 10 / 12"
    And I should see "100.00" in the "#enhancedrubric-grade" "css_element"
    And I should see "Released" in the "#enhancedrubric-status" "css_element"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "2"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion B" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion D" in "3"
