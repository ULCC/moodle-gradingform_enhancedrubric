@synergylearning @gradingform @gradingform_enhancedrubric @javascript
Feature: Rubrics can be created and edited with marking workflow enabled
  In order to use and refine rubrics to grade students
  As a teacher
  I need to edit previously used rubrics with marking workflow enabled

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student1@example.com |
      | student3 | Student   | 3        | student1@example.com |
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
      | advancedgradingmethod_submissions   | enhancedrubric           |
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
      | Criterion A | Level 11 | 0 | 0 | Level 12 | 1 | 1 | Level 13 | 2 | 0 | Level 14 | 3 | 0 |
      | Criterion B | Level 21 | 0 | 0 | Level 22 | 1 | 1 | Level 23 | 2 | 0 | Level 24 | 3 | 0 |
      | Criterion C | Level 31 | 0 | 0 | Level 32 | 1 | 1 | Level 33 | 2 | 0 | Level 34 | 3 | 0 |
      | Criterion D | Level 41 | 0 | 0 | Level 42 | 1 | 0 | Level 43 | 2 | 1 | Level 44 | 3 | 0 |
    And I press "Save rubric and make it ready"
    And I log out

  Scenario: I can use rubrics to grade and edit them later updating students grades
    # Grading a student - student1 all grading stages completed when grades released.
    # Initial rubric used for grade.
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then I should see "Feedback comments - Initial grading"
    And I should see "Feedback comments - Second grading"
    And I should see "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 3 | Very good                  | 1 |
    # Teacher role does not have permission to edit settings.
    And I press "Save changes"
    And I should see "Total points required: 7"
    And I should see "Points earned: 10 / 12"
    And I click on "View all submissions" "link"
    # Checking that the user grade is correct.
    Then I should see "Competent" in the "Student 1" "table_row"
    And I log out

    # Initial rubric used for grade before release.
    When I log in as "teacher2"
    And I am on "Course 1" course homepage
    # Second rubric filled.
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 2 |
      | Criterion B | 2 | Mmmm, you can do it better | 2 |
      | Criterion C | 2 | Mmmm, you can do it better | 2 |
      | Criterion D | 1 | Very poor                  | 2 |
    And I save the advanced grading form
    # Initial rubric still used for grade before release.
    Then I should see "Competent" in the "Student 1" "table_row"

    # Updating the user grade.
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    Then I should see "Total points required: 7"
    And I should see "Points earned: 8 / 12"

    # Final rubric filled.
    When I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Bad, I changed my mind     | 3 |
      | Criterion B | 1 | Mmmm, you can do it better | 3 |
      | Criterion C | 2 | Mmmm, you can do it better | 3 |
      | Criterion D | 2 | Very poor                  | 3 |
    And I complete the advanced grading form with these values:
      | Feedback comments | In general... work harder... |
    # Initial rubric still used for grade before release.
    Then I should see "Competent" in the "Student 1" "table_row"

    # Release the grade.
    When I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I set the field "Marking workflow state" to "Released"
    And I save the advanced grading form
    Then I should see "Released" in the "Student 1" "table_row"
    And I should see "Not yet competent" in the "Student 1" "table_row"
    And I log out

    # Viewing it as a student.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "Not yet competent" in the ".feedback" "css_element"
    And I should see "Rubric test description" in the ".feedback" "css_element"
    And I should see "In general... work harder..."
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "3"
    And I should see "Total points required: 7"
    And I should see "Points earned: 6 / 12"
    And I log out

    # Editing a enhanced rubric definition without regrading students.
    When I am on the "Course 1" course page logged in as teacher2
    And I go to "Test assignment 1 name" advanced grading definition page
    And "Save as draft" "button" should not exist
    And I click on "Move down" "button" in the "Criterion A" "table_row"
    And I replace "Level 11" enhanced rubric level with "Level 11 edited" in "Criterion A" criterion
    And I press "Save"
    Then I should see "You are about to save changes to a rubric that has already been used for grading."
    And I set the field "menuenhancedrubricregrade" to "Do not mark for regrade"
    And I press "Continue"
    And I log out

    # Check that the student still sees the grade.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "0" in the ".feedback" "css_element"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And I log out

    # Editing a enhanced rubric with significant changes.
    When I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I go to "Test assignment 1 name" advanced grading definition page
    And I click on "Move down" "button" in the "Criterion B" "table_row"
    And I replace "1" enhanced rubric level with "11" in "Criterion A" criterion
    And I press "Save"
    Then I should see "You are about to save significant changes to a rubric that has already been used for grading. The gradebook value will be unchanged, but the rubric will be hidden from students until their item is regraded."
    And I press "Continue"
    And I log out

    # Check that the student doesn't see the grade.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "0" in the ".feedback" "css_element"
    And I should not see "Rubric test description" in the "Grade" "table_row"
    And I log out

    # Regrade student.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as teacher2
    When I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then I should see "The rubric definition was changed after this student had been graded. The student can not see this rubric until you check the rubric and update the grade."
    And I save the advanced grading form
    And I log out

    # Check that the student sees the grade again.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "0" in the ".feedback" "css_element"
    And the level with "11" points is selected for the enhanced rubric criterion "Criterion A" in "3"

  Scenario: Second grading stage is used when Final stage is not completed and grades are released.
    # Grading a student - student2 Initial and Second stages completed when grades released.
    # Second rubric used for grade.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I go to "Student 2" "Test assignment 1 name" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very poor                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 1 | Very poor                  | 1 |
    And I press "Save changes"
    And I log out
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    # Second rubric filled.
    And I go to "Student 2" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 2 |
      | Criterion B | 2 | Mmmm, you can do it better | 2 |
      | Criterion C | 2 | Mmmm, you can do it better | 2 |
      | Criterion D | 3 | Very good                  | 2 |
    And I save the advanced grading form
    # Release the grade.
    And I go to "Student 2" "Test assignment 1 name" activity advanced grading page
    And I set the field "Marking workflow state" to "Released"
    And I save the advanced grading form
    And I log out

    # Viewing it as a student.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student2
    Then I should see "Competent" in the ".feedback" "css_element"
    And I should see "Rubric test description" in the ".feedback" "css_element"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion D" in "3"
    And I should see "Total points required: 7"
    And I should see "Points earned: 10 / 12"
    And I log out

  Scenario: Initial grading stage is used when Second and Final stages are not completed and grades are released.
    # Grading a student - student3 Initial stages completed when grades released.
    # Initial rubric used for grade.
    Given I log in as "teacher2"
    And I am on "Course 1" course homepage
    And I go to "Student 3" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Initial grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Very poor                  | 1 |
      | Criterion B | 2 | Very poor                  | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 3 | Very good                  | 1 |
    And I press "Save changes"
    # Release the grade.
    And I go to "Student 3" "Test assignment 1 name" activity advanced grading page
    And I set the field "Marking workflow state" to "Released"
    And I press "Save changes"

    # Viewing it as a teacher.
    When I am on the "Test assignment 1 name" "assign activity" page
    And I follow "View all submissions"
    Then I should see "Competent" in the "Student 3" "table_row"
    And I log out

    # Viewing it as a student.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student3
    Then I should see "Competent" in the ".feedback" "css_element"
    And I should see "Rubric test description" in the ".feedback" "css_element"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion D" in "3"
    And I should see "Total points required: 7"
    And I should see "Points earned: 9 / 12"
    And I log out

  Scenario: Correct grading stage is used when grades are released in bulk.
    # Initial rubric filled.
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I go to "Student 2" "Test assignment 1 name" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Very poor                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 1 | Very poor                  | 1 |
    And I press "Save changes"
    And I log out
    And I log in as "teacher2"
    And I am on "Course 1" course homepage
    # Second rubric filled.
    And I go to "Student 2" "Test assignment 1 name" activity advanced grading page
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 2 |
      | Criterion B | 2 | Mmmm, you can do it better | 2 |
      | Criterion C | 2 | Mmmm, you can do it better | 2 |
      | Criterion D | 2 | Mmmm, you can do it better | 2 |
    # Final rubric filled.
    And I press "Feedback comments - Second grading"
    And I press "Feedback comments - Final grading"
    And I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 3 |
      | Criterion B | 2 | Mmmm, you can do it better | 3 |
      | Criterion C | 2 | Mmmm, you can do it better | 3 |
      | Criterion D | 3 | Very good                  | 3 |
    And I save the advanced grading form
    # Release the grades in bulk.
    And I follow "Test assignment 1 name"
    And I follow "View all submissions"
    Then I should see "Not yet competent" in the "Student 2" "table_row"

    When I set the field "selectall" to "1"
    And I set the field "operation" to "Set marking workflow state"
    And I click on "Go" "button" confirming the dialogue
    And I set the field "Marking workflow state" to "Released"
    And I set the field "Notify student" to "No"
    And I press "Save changes"
    And I follow "Test assignment 1 name"
    And I follow "View all submissions"
    Then I should see "Competent" in the "Student 2" "table_row"
    And I log out

    # Viewing it as a student.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student2
    Then I should see "Competent" in the ".feedback" "css_element"
    And I should see "Rubric test description" in the ".feedback" "css_element"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion A" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion B" in "3"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "3"
    And the level with "3" points is selected for the enhanced rubric criterion "Criterion D" in "3"
    And I should see "Total points required: 7"
    And I should see "Points earned: 10 / 12"
    And I log out
