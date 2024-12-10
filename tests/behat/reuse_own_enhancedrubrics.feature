@synergylearning @gradingform @gradingform_enhancedrubric
Feature: Reuse my rubrics in other activities
  In order to save time creating duplicated grading forms
  As a teacher
  I need to reuse rubrics that I created previously

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity   | name                      | intro                           | course | section | idnumber |
      | assign     | Test assignment 1 name    | Test assignment 1 description   | C1     | 1       | assign1  |
      | assign     | Test assignment 2 name    | Test assignment 2 description   | C1     | 1       | assign1  |
    And I am on the "Test assignment 1 name" "assign activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | Grading method | Enhanced rubric |
    And I press "Save and return to course"
    And I go to "Test assignment 1 name" advanced grading definition page
    And I set the following fields to these values:
      | Name | Assignment 1 rubric |
      | Description | Assignment 1 description |
    And I define the following enhanced rubric:
      | Criterion A | Level 11 | 0 | 0 | Level 12 | 1 | 1 | Level 13 | 2 | 0 | Level 14  | 3 | 0 |
      | Criterion B | Level 21 | 0 | 0 | Level 22 | 1 | 1 | Level 23 | 2 | 0 | Level 24  | 3 | 0 |
      | Criterion C | Level 31 | 0 | 0 | Level 32 | 1 | 1 | Level 33 | 2 | 0 | Level 34  | 3 | 0 |
      | Criterion D | Level 41 | 0 | 0 | Level 42 | 1 | 0 | Level 43 | 2 | 1 | Level 44  | 3 | 0 |
    And I press "Save rubric and make it ready"
    And I am on the "Test assignment 2 name" "assign activity editing" page
    And I set the following fields to these values:
      | Grading method | Enhanced rubric |
    And I press "Save and return to course"
    And I set "Test assignment 2 name" activity to use "Assignment 1 rubric" grading form
    Then I should see "Ready for use"
    And I should see "Criterion A"
    And I should see "Criterion B"
    And I should see "Criterion C"
    And I should see "Criterion D"
    And I am on "Course 1" course homepage
    And I go to "Test assignment 1 name" advanced grading definition page
    And I should see "Criterion A"
    And I should see "Criterion B"
    And I should see "Criterion C"
    And I should see "Criterion D"

  @javascript
  Scenario: A teacher can reuse one of his/her previously created rubrics, with Javascript enabled

  Scenario: A teacher can reuse one of his/her previously created rubrics, with Javascript disabled
