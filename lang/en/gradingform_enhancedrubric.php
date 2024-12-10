<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file for plugin gradingform_enhancedrubric
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actualtotal'] = 'Points earned: {$a}';
$string['addcriterion'] = 'Add criterion';
$string['additionalfeedback'] = 'Additional feedback';
$string['alwaysshowdefinition'] = 'Allow users to preview rubric (otherwise it will only be displayed after grading)';
$string['backtoediting'] = 'Back to editing';
$string['backtohistory'] = 'Back to rubric grading history report';
$string['confirmdeletecriterion'] = 'Are you sure you want to delete this criterion?';
$string['confirmdeletelevel'] = 'Are you sure you want to delete this level?';
$string['copyinitial'] = 'Copy initial grading details';
$string['copysecond'] = 'Copy second grading details';
$string['criteria'] = 'Criteria';
$string['criterion'] = 'Section {$a->section} Criterion {$a->criterion}';
$string['criterionaddlevel'] = 'Add level';
$string['criteriondelete'] = 'Delete criterion';
$string['criterionduplicate'] = 'Duplicate criterion';
$string['criterionempty'] = 'Click to edit criterion';
$string['criterionmovedown'] = 'Move down';
$string['criterionmoveup'] = 'Move up';
$string['criterionremark'] = 'Remark for criterion {$a->description}: {$a->remark}';
$string['definerubric'] = 'Define rubric';
$string['definition'] = 'Level';
$string['description'] = 'Description';
$string['feedback'] = 'Feedback';
$string['grade'] = 'Assignment grade';
$string['gradelabel'] = 'Assignment grade: ';
$string['grader'] = 'Grader';
$string['graderlabel'] = 'Grader: ';
$string['graderid'] = 'Grader ID';
$string['graderfirstname'] = 'Grader firstname';
$string['graderlastname'] = 'Grader surname';
$string['enableinternalcomments'] = 'Enable internal rubric comments';
$string['enableremarks'] = 'Allow grader to add text remarks for each criterion';
$string['enhancedrubric'] = 'Enhanced rubric';
$string['enhancedrubrichistory'] = 'Rubric grading report';
$string['enhancedrubric:addinternalfeedback'] = 'Add internal rubric feedback';
$string['enhancedrubric:viewhistory'] = 'View grading history';
$string['enhancedrubric:viewinternalfeedback'] = 'View internal rubric feedback';
$string['err_mintwolevels'] = 'Each criterion must have at least two levels';
$string['err_multipleminimum'] = 'Criterion can only have one minimum requirement';
$string['err_nocriteria'] = 'Rubric must contain at least one criterion';
$string['err_nodefinition'] = 'Level definition can not be empty';
$string['err_nodescription'] = 'Criterion description can not be empty';
$string['err_nominimum'] = 'Criterion must contain one minimum requirement';
$string['err_novariations'] = 'Criterion levels cannot all be worth the same number of points';
$string['err_scoreformat'] = 'Number of points for each level must be a valid number';
$string['err_totalscore'] = 'Maximum number of points possible when graded by the rubric must be more than zero';
$string['feedbacklabel'] = 'Feedback comments:  ';
$string['graderfirstname'] = 'Grader first name';
$string['gradersurname'] = 'Grader surname';
$string['gradingof'] = '{$a} grading';
$string['internalcomment'] = 'Internal comments:';
$string['internalcomments'] = 'Internal comments';
$string['learner'] = 'Learner';
$string['learnerfirstname'] = 'Learner first name';
$string['learnerid'] = 'User database ID';
$string['learnerlastname'] = 'Learner surname';
$string['level'] = 'Level {$a->definition}, {$a->score} points.';
$string['leveldelete'] = 'Delete level {$a}';
$string['leveldefinition'] = 'Level {$a} definition';
$string['levelempty'] = 'Click to edit level';
$string['levelsgroup'] = 'Levels group';
$string['markingstage'] = 'Marking stage';
$string['markingstagelabel'] = 'Marking stage: ';
$string['mincheckboxforlevel'] = 'Minimum level required';
$string['minpostfix'] = 'minimum';
$string['mintotal'] = 'Minimum total points required: ';
$string['mintotalrequired'] = 'Total points required: {$a}';
$string['name'] = 'Name';
$string['needregrademessage'] = 'The rubric definition was changed after this student had been graded. The student can not see this rubric until you check the rubric and update the grade.';
$string['notset'] = 'Not set';
$string['pluginname'] = 'Enhanced rubric';
$string['pointsvalue'] = '{$a} points';
$string['previewrubric'] = 'Preview rubric';
$string['privacy:metadata:criterionid'] = 'An identifier for a specific criterion being graded.';
$string['privacy:metadata:enhancedrubric_history'] = 'Stores information about the user\'s grade created by the rubric.';
$string['privacy:metadata:feedback'] = 'The feedback received for the grade.';
$string['privacy:metadata:fillingssummary'] = 'Stores information about the user\'s grade created by the rubric.';
$string['privacy:metadata:instanceid'] = 'An identifier relating to a grade in an activity.';
$string['privacy:metadata:levelid'] = 'The level obtained in the rubric.';
$string['privacy:metadata:raterid'] = 'An identifier relating the a grader in an instance.';
$string['privacy:metadata:rawgrade'] = 'The raw grade obtained for the activity';
$string['privacy:metadata:remark'] = 'Remarks related to the rubric criterion being assessed.';
$string['privacy:metadata:section'] = 'An identifier for the marking workflow section rubric.';
$string['privacy:metadata:timemodified'] = 'The time the grade was saved';
$string['privacy:metadata:workflowstatus'] = 'The workflow status at the time the grade was saved.';
$string['remark'] = 'Feedback';
$string['regrademessage1'] = 'You are about to save changes to a rubric that has already been used for grading. Please indicate if existing grades need to be reviewed. If you set this then the rubric will be hidden from students until their item is regraded.';
$string['regrademessage5'] = 'You are about to save significant changes to a rubric that has already been used for grading. The gradebook value will be unchanged, but the rubric will be hidden from students until their item is regraded.';
$string['regradeoption0'] = 'Do not mark for regrade';
$string['regradeoption1'] = 'Mark for regrade';
$string['restoredfromdraft'] = 'NOTE: The last attempt to grade this person was not saved properly so draft grades have been restored. If you want to cancel these changes use the \'Cancel\' button below.';
$string['rubric'] = 'Rubric';
$string['rubricmapping'] = 'Score to grade mapping rules';
$string['rubricmappingexplained'] = 'The minimum possible score for this rubric is <b>{$a->minscore} points</b>. The maximum score is <b>{$a->maxscore} points</b>.

If the minium level is not achieved for any of the criteria then the minimum score is applied. If the minimum total score is not
achieved then the minimum score is applied. 

If the minimum level is achieved for all criteria, and the minimum total is achieved then the maximum score is applied. 

If a scale is used for grading, the score will be either the lowest or highest scale.';
$string['rubricnotcompleted'] = 'Please choose something for each criterion';
$string['rubricoptions'] = 'Rubric options';
$string['rubricstatus'] = 'Current rubric status';
$string['score'] = 'Score';
$string['save'] = 'Save';
$string['saverubric'] = 'Save rubric and make it ready';
$string['saverubricdraft'] = 'Save as draft';
$string['score'] = 'Score';
$string['scoreinputforlevel'] = 'Score input for level {$a}';
$string['scorepostfix'] = '{$a}points';
$string['sectionheader'] = 'Grading ';
$string['section1'] = 'Feedback comments - Initial grading';
$string['sectionheader1'] = 'Initial grading ';
$string['section2'] = 'Feedback comments - Second grading';
$string['sectionheader2'] = 'Second grading ';
$string['section3'] = 'Feedback comments - Final grading';
$string['sectionheader3'] = 'Final grading ';
$string['showdescriptionstudent'] = 'Display rubric description to those being graded';
$string['showdescriptionteacher'] = 'Display rubric description during evaluation';
$string['showremarksstudent'] = 'Show remarks to those being graded';
$string['showscorestudent'] = 'Display points for each level to those being graded';
$string['showscoreteacher'] = 'Display points for each level during evaluation';
$string['showtotal'] = 'Display total points for each level to those being graded';
$string['showminimum'] = 'Display minimum points for each level to those being graded';
$string['showminimumgrader'] = 'Display minimum points for each level to those grading';
$string['sortlevelsasc'] = 'Sort order for levels:';
$string['sortlevelsasc0'] = 'Descending by number of points';
$string['sortlevelsasc1'] = 'Ascending by number of points';
$string['updatedate'] = 'Updated date';
$string['updatedatelabel'] = 'Update date:';
$string['viewallhistory'] = 'View all rubric grading history';
$string['viewdetail'] = 'View rubric detail history';
$string['viewhistory'] = 'View rubric grading history';
$string['zerolevelsabsent'] = 'Warning: The minimum possible score for this rubric is not 0; this can result in unexpected grades for the activity. To avoid this, each criterion should have a level with 0 points.<br>
This warning may be ignored if a scale is used for grading, and the minimum levels in the rubric correspond to the minimum value of the scale.';
