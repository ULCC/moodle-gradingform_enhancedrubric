@synergylearning @gradingform @gradingform_enhancedrubric
Feature: Rubrics can be created and edited
  In order to use and refine rubrics to grade students
  As a teacher
  I need to edit previously used rubrics

  @javascript
  Scenario: I can use rubrics to grade and edit them later updating students grades
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "activity" exists:
      | activity                            | assign                   |
      | course                              | C1                       |
      | name                                | Test assignment 1 name   |
      | assignsubmission_onlinetext_enabled | 1                        |
      | assignfeedback_comments_enabled     | 1                        |
      | advancedgradingmethod_submissions   | enhancedrubric           |
      | grade                               | Default competence scale |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I go to "Test assignment 1 name" advanced grading definition page
    # Defining a enhanced rubric.
    And I set the following fields to these values:
      | Name                          | Assignment 1 rubric     |
      | Description                   | Rubric test description |
      | Minimum total points required | 7                       |
    And I define the following enhanced rubric:
      | TMP Criterion 1 | TMP Level 11 | 11 | 0 | TMP Level 12 | 12 | 1 |
      | TMP Criterion 2 | TMP Level 21 | 21 | 0 | TMP Level 22 | 22 | 1 |
      | TMP Criterion 3 | TMP Level 31 | 31 | 0 | TMP Level 32 | 32 | 1 |
      | TMP Criterion 4 | TMP Level 41 | 41 | 0 | TMP Level 42 | 42 | 1 |
    # Checking that only the last ones are saved.
    And I define the following enhanced rubric:
      | Criterion A | Level 11 | 0 | 1 | Level 12 | 1 | 0 | Level 13 | 2 | 0 | Level 14  | 3 | 0 |
      | Criterion B | Level 21 | 0 | 0 | Level 22 | 1 | 1 | Level 23 | 2 | 0 | Level 24  | 3 | 0 |
      | Criterion C | Level 31 | 0 | 0 | Level 32 | 1 | 1 | Level 33 | 2 | 0 | Level 34  | 3 | 0 |
      | Criterion D | Level 41 | 0 | 0 | Level 42 | 1 | 0 | Level 43 | 2 | 1 | Level 44  | 3 | 0 |
    And I press "Save as draft"
    And I go to "Test assignment 1 name" advanced grading definition page
    And I click on "Move down" "button" in the "Criterion A" "table_row"
    And I press "Save rubric and make it ready"
    Then I should see "Ready for use"

    When I go to "Test assignment 1 name" advanced grading definition page
    And I replace enhanced rubric minimum with "Level 12" in "Criterion A" criterion
    And I press "Save"
    And I go to "Test assignment 1 name" advanced grading definition page
    And I press "Save"
    #Then "Level 12" minimum should not be checked - step to add

    # Grading a student.
    When I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | 2 | Very good | 1 |
    And I press "Save changes"
    # Checking that it complains if you don't select a level for each criterion.
    Then I should see "Please choose something for each criterion"

    # Met minimum requirement for each criteria and scored more than minimum total.
    When I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 3 | Very good                  | 1 |
    And I complete the advanced grading form with these values:
      | Feedback comments | In general... work harder... |
    # Checking that the user grade is correct.
    Then I should see "Competent" in the "Student 1" "table_row"

    # Updating the user grade.
    When I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then I should see "Total points required: 7"
    And I should see "Points earned: 10 / 12"

    # Did not meet minimum requirement for one criteria and scored more than minimum total.
    When I grade by filling the enhanced rubric with:
      | Criterion A | 3 | Very good                  | 1 |
      | Criterion B | 2 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 1 | Very poor                  | 1 |
    And I save the advanced grading form
    # Checking that the user grade is correct.
    Then I should see "Not yet competent" in the "Student 1" "table_row"

    # Updating the user grade.
    When I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then I should see "Total points required: 7"
    And I should see "Points earned: 8 / 12"

    # Met minimum requirement for each criteria but scored less than minimum total.
    When I grade by filling the enhanced rubric with:
      | Criterion A | 1 | Bad, I changed my mind     | 1 |
      | Criterion B | 1 | Mmmm, you can do it better | 1 |
      | Criterion C | 2 | Mmmm, you can do it better | 1 |
      | Criterion D | 2 | Very poor                  | 1 |
    And I save the advanced grading form
    Then I should see "Not yet competent" in the "Student 1" "table_row"
    And I log out

    # Viewing it as a student.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "0" in the ".feedback" "css_element"
    And I should see "Rubric test description" in the ".feedback" "css_element"
    And I should see "In general... work harder..."
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion B" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion C" in "1"
    And the level with "2" points is selected for the enhanced rubric criterion "Criterion D" in "1"
    And I should see "Total points required: 7"
    And I should see "Points earned: 6 / 12"
    And I log out

    # Editing a enhanced rubric definition without regrading students.
    When I am on the "Course 1" course page logged in as teacher1
    And I go to "Test assignment 1 name" advanced grading definition page
    Then "Save as draft" "button" should not exist

    When I click on "Move up" "button" in the "Criterion A" "table_row"
    And I replace "Level 11" enhanced rubric level with "Level 11 edited" in "Criterion A" criterion
    And I press "Save"
    Then I should see "You are about to save changes to a rubric that has already been used for grading."
    And I set the field "menuenhancedrubricregrade" to "Do not mark for regrade"
    And I press "Continue"
    And I log out

    # Check that the student still sees the grade.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "0" in the ".feedback" "css_element"
    And the level with "1" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And I log out

    # Editing a enhanced rubric with significant changes.
    When I log in as "teacher1"
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
    When I am on the "Test assignment 1 name" "assign activity" page logged in as teacher1
    And I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1 name" activity advanced grading page
    Then I should see "The rubric definition was changed after this student had been graded. The student can not see this rubric until you check the rubric and update the grade."
    And I save the advanced grading form
    And I log out

    # Check that the student sees the grade again.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should see "0" in the ".feedback" "css_element"
    And the level with "11" points is selected for the enhanced rubric criterion "Criterion A" in "1"
    And I log out

    # Hide all enhanced rubric info for students
    When I am on the "Course 1" course page logged in as teacher1
    And I go to "Test assignment 1 name" advanced grading definition page
    And I set the field "Allow users to preview rubric (otherwise it will only be displayed after grading)" to ""
    And I set the field "Display rubric description during evaluation" to ""
    And I set the field "Display rubric description to those being graded" to ""
    And I set the field "Display points for each level during evaluation" to ""
    And I set the field "Display points for each level to those being graded" to ""
    And I set the field "Display total points for each level to those being graded" to ""
    And I set the field "Display minimum points for each level to those grading" to ""
    And I set the field "Display minimum points for each level to those being graded" to ""
    And I press "Save"
    And I set the field "menuenhancedrubricregrade" to "Do not mark for regrade"
    And I press "Continue"
    And I log out

    # Students should not see anything.
    And I am on the "Test assignment 1 name" "assign activity" page logged in as student1
    Then I should not see "Criterion A" in the ".submissionstatustable" "css_element"
    And I should not see "Criterion B" in the ".submissionstatustable" "css_element"
    And I should not see "Criterion C" in the ".submissionstatustable" "css_element"
    And I should not see "Criterion D" in the ".submissionstatustable" "css_element"
    And I should not see "Rubric test description" in the ".feedback" "css_element"
    And I should not see "Total points required:"
    And I should not see "Points earned:"
    And I log out

    # Graders should not see totals.
    When I am on the "Test assignment 1 name" "assign activity" page logged in as teacher1
    Then I should not see "Total points required:"
    And I should not see "Points earned:"
