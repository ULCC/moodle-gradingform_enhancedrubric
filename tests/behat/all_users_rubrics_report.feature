@synergylearning @gradingform @gradingform_enhancedrubric @javascript
Feature: Rubrics grades are displayed for all users
  In order to use and refine rubrics to grade students
  As a teacher
  I need to be able to see the history report for all users

  Background:
    Given the following "users" exist:
      | username | firstname     | lastname | idnumber |
      | teacher1 | Teacher       | 1        | T1       |
      | teacher2 | Initialgrader | 1        | T2       |
      | teacher3 | Secondgrader  | 1        | T3       |
      | teacher4 | Finalgrader   | 1        | T4       |
      | teacher5 | Teacher       | 1        | T5       |
      | student1 | Student       | 1        | S1       |
      | student2 | Student       | 2        | S2       |
      | student3 | Student       | 3        | S3       |
      | student4 | Student       | 4        | S4       |
      | student5 | Student       | 5        | S5       |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "roles" exist:
      | shortname     | name           | archetype |
      | initialgrader | Initial grader | teacher   |
      | secondgrader  | Second grader  | teacher   |
      | finalgrader   | Final grader   | teacher   |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | initialgrader  |
      | teacher3 | C1     | secondgrader   |
      | teacher4 | C1     | finalgrader    |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | student5 | C1     | student        |
    And I log in as "admin"
    And I set the following system permissions of "Initial grader" role:
      | capability               | permission |
      | mod/assign:grade         | Allow      |
      | mod/assign:reviewgrades  | Prevent    |
      | mod/assign:managegrades  | Prevent    |
      | mod/assign:releasegrades | Prevent    |
    And I set the following system permissions of "Second grader" role:
      | capability               | permission |
      | mod/assign:grade         | Allow      |
      | mod/assign:reviewgrades  | Allow      |
      | mod/assign:managegrades  | Prevent    |
      | mod/assign:releasegrades | Prevent    |
    And I set the following system permissions of "Final grader" role:
      | capability               | permission |
      | mod/assign:grade         | Allow      |
      | mod/assign:reviewgrades  | Allow      |
      | mod/assign:managegrades  | Allow      |
      | mod/assign:releasegrades | Prevent    |
    And I log out
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
      | Criterion A | Level 11 | 0 | 0 | Level 12 | 1 | 1 | Level 13 | 2 | 0 | Level 14 | 3 | 0 |
      | Criterion B | Level 21 | 0 | 0 | Level 22 | 1 | 1 | Level 23 | 2 | 0 | Level 24 | 3 | 0 |
      | Criterion C | Level 31 | 0 | 0 | Level 32 | 1 | 1 | Level 33 | 2 | 0 | Level 34 | 3 | 0 |
      | Criterion D | Level 41 | 0 | 0 | Level 42 | 1 | 0 | Level 43 | 2 | 1 | Level 44 | 3 | 0 |
    And I press "Save rubric and make it ready"
    And I log out
    # Student 1 - graded by Initial grader, Second grader, Final grader.
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Very poor | 1 |
      | Criterion B | 0 | Very poor | 1 |
      | Criterion C | 0 | Very poor | 1 |
      | Criterion D | 0 | Very poor | 1 |
    And I press "Save changes"
    And I log out
    And I log in as "teacher3"
    And I am on "Course 1" course homepage
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Ok | 2 |
      | Criterion B | 2 | Ok | 2 |
      | Criterion C | 2 | Ok | 2 |
      | Criterion D | 2 | Ok | 2 |
    And I press "Save changes"
    And I log out
    And I log in as "teacher4"
    And I am on "Course 1" course homepage
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Mmmm, you can do it better | 3 |
      | Criterion B | 2 | Ok                         | 3 |
      | Criterion C | 1 | Mmmm, you can do it better | 3 |
      | Criterion D | 2 | Ok                         | 3 |
    And I press "Save changes"
    # Student 2 - graded by Initial grader, Second grader, Final grader.
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I go to "Student 2" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Very poor | 1 |
      | Criterion B | 0 | Very poor | 1 |
      | Criterion C | 0 | Very poor | 1 |
      | Criterion D | 0 | Very poor | 1 |
    And I press "Save changes"
    And I log out
    And I log in as "teacher3"
    And I am on "Course 1" course homepage
    And I go to "Student 2" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Ok | 2 |
      | Criterion B | 2 | Ok | 2 |
      | Criterion C | 2 | Ok | 2 |
      | Criterion D | 2 | Ok | 2 |
    And I press "Save changes"
    And I log out
    And I log in as "teacher4"
    And I am on "Course 1" course homepage
    And I go to "Student 2" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Mmmm, you can do it better | 3 |
      | Criterion B | 2 | Ok                         | 3 |
      | Criterion C | 1 | Mmmm, you can do it better | 3 |
      | Criterion D | 2 | Ok                         | 3 |
    And I press "Save changes"
    And I log out

  Scenario: View only the latest grades for users
    Given I am on the "Course 1" course page logged in as teacher1
    And I go to "Test assignment 1" advanced grading page
    And I press "View all rubric grading history"
    Then the following should exist in the "gradingform-enhancedrubric-history" table:
      | Learner first name | Learner surname | Grader first name | Grader surname | Updated date            | Assignment grade | Marking stage |
      | Student            | 1               | Finalgrader       | 1              | ##today##%A, %d %B %Y## | 0                | Not marked    |
      | Student            | 2               |                   |                |                         | 0                | Not marked    |
      | Student            | 3               |                   |                |                         | 0                | Not marked    |
      | Student            | 4               |                   |                |                         | 0                | Not marked    |
      | Student            | 5               |                   |                |                         | 0                | Not marked    |
    And the following should not exist in the "gradingform-enhancedrubric-history" table:
      | Grader first name | Grader surname | Updated date            | Assignment grade | Marking stage |
      | Initialgrader     | 1              | ##today##%A, %d %B %Y## | 0                | Not marked    |
      | Secondgrader      | 1              | ##today##%A, %d %B %Y## | 100              | Not marked    |

    When I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r0')]" "xpath_element"
    Then I should see "Points earned: 6 / 12"
    And I should see "0" in the "#enhancedrubric-grade" "css_element"
    And I should see "Not marked" in the "#enhancedrubric-status" "css_element"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion D" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "2"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "3"

    # Student 1 - graded by Second grader, Second grader, Final grader.
    Given I log in as "teacher3"
    And I am on "Course 1" course homepage
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    And I press "Feedback comments - Second grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Ok        | 1 |
      | Criterion B | 0 | Very poor | 1 |
      | Criterion C | 0 | Very poor | 1 |
      | Criterion D | 0 | Very poor | 1 |
    And I press "Save changes"
    And I log out
    When I am on the "Course 1" course page logged in as teacher1
    And I go to "Test assignment 1" advanced grading page
    And I press "View all rubric grading history"
    And I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r0')]" "xpath_element"
    Then I should see "Points earned: 6 / 12"
    And I should see "0" in the "#enhancedrubric-grade" "css_element"
    And I should see "Not marked" in the "#enhancedrubric-status" "css_element"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion D" in "1"

    # Student 1 - graded by Second grader, Final grader, Final grader.
    Given I log in as "teacher4"
    And I am on "Course 1" course homepage
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 0 | Mmmm, you can do it better | 2 |
      | Criterion B | 2 | Ok                         | 2 |
      | Criterion C | 2 | Ok                         | 2 |
      | Criterion D | 2 | Ok                         | 2 |
    And I press "Save changes"
    And I log out
    When I am on the "Course 1" course page logged in as teacher1
    And I go to "Test assignment 1" advanced grading page
    And I press "View all rubric grading history"
    And I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r0')]" "xpath_element"
    Then I should see "Points earned: 6 / 12"
    And I should see "0" in the "#enhancedrubric-grade" "css_element"
    And I should see "Not marked" in the "#enhancedrubric-status" "css_element"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion A" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "2"

    # Student 1 - graded by Final grader, Final grader, Final grader.
    And I am on "Course 1" course homepage
    # Initial rubric filled.
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Ok | 1 |
      | Criterion B | 2 | Ok | 1 |
      | Criterion C | 2 | Ok | 1 |
      | Criterion D | 2 | Ok | 1 |
    # Second rubric filled.
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Initial grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Ok                         | 2 |
      | Criterion B | 2 | Ok                         | 2 |
      | Criterion C | 2 | Ok                         | 2 |
      | Criterion D | 0 | Mmmm, you can do it better | 2 |
    # Final rubric filled.
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 3 |
      | Criterion B | 3 | Very good                  | 3 |
      | Criterion C | 2 | Mmmm, you can do it better | 3 |
      | Criterion D | 3 | Very good                  | 3 |
    And I press "Save changes"
    And I log out

    When I am on the "Course 1" course page logged in as teacher1
    And I go to "Test assignment 1" advanced grading page
    And I press "View all rubric grading history"
    And I click on "View rubric detail history" "link" in the "//tr[contains(@id, 'r0')]" "xpath_element"
    Then I should see "Points earned: 11 / 12"
    And I should see "100" in the "#enhancedrubric-grade" "css_element"
    And I should see "Not marked" in the "#enhancedrubric-status" "css_element"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "2"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "2"
    And the level with "0" points is selected for the enhanced rubric criterion "Criterion D" in "2"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion D" in "3"
