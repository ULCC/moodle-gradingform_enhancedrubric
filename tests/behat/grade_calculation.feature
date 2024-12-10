@synergylearning @gradingform @gradingform_enhancedrubric @javascript
Feature: Converting rubric score to grades
  In order to use and refine rubrics to grade students
  As a teacher
  I need to be able to use different grade settings

  Scenario Outline: Convert rubric scores to grades.
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
    And the following "scales" exist:
      | name         | scale                                     |
      | Test scale 1 | Disappointing, Good, Very good, Excellent |
    And the following "activities" exist:
      | activity   | name              | intro | course | idnumber    | grade   | advancedgradingmethod_submissions |
      | assign     | Test assignment 1 | Test  | C1     | assign1     | <grade> | enhancedrubric                    |
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
    # Grading a student.
    When I navigate to "Assignment" in current page administration
    And I go to "Student 1" "Test assignment 1" activity advanced grading page
    And I grade by filling the enhanced rubric with:
      | Criterion A | <gradeA> |  | 1 |
      | Criterion B | <gradeB> |  | 1 |
      | Criterion C | <gradeC> |  | 1 |
      | Criterion D | <gradeD> |  | 1 |
    And I save the advanced grading form
    # Checking that the user grade is correct.
    Then I should see "<studentgrade>" in the "student1@example.com" "table_row"
    And I log out

    Examples:
      | grade        | gradeA | gradeB | gradeC | gradeD | studentgrade   |
      | 100          | 1      | 1      | 1      |2       | 0              |
      | 100          | 3      | 3      | 3      |1       | 0              |
      | 100          | 2      | 2      | 1      |2       | 100            |
      | Test scale 1 | 1      | 1      | 1      |2       | Disappointing  |
      | Test scale 1 | 3      | 3      | 3      |1       | Disappointing  |
      | Test scale 1 | 2      | 2      | 1      |2       | Excellent      |
