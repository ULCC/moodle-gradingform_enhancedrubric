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
 * History enhanced rubric page
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2022 Amanda Doughty, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$download = optional_param('download', null, PARAM_ALPHA);
$instanceid = required_param('instanceid', PARAM_INT);
$areaid = required_param('areaid', PARAM_INT);
$itemid = required_param('itemid', PARAM_INT);

$manager = get_grading_manager($areaid);
$controller = $manager->get_controller('enhancedrubric');
[$context, $course, $cm] = get_context_info_array($manager->get_context()->id);
$assignment = new assign($context, $cm, $course);
$grademenu = make_grades_menu($assignment->get_instance()->grade);
$allowgradedecimals = $assignment->get_instance()->grade > 0;
$controller->set_grade_range($grademenu, $allowgradedecimals);

require_login($course, true, $cm);
require_capability('gradingform/enhancedrubric:viewhistory', $context);

$title = get_string('enhancedrubrichistory', 'gradingform_enhancedrubric');
$pageurl = new moodle_url('/grade/grading/form/enhancedrubric/history.php', ['instanceid' => $instanceid, 'areaid' => $areaid, 'itemid' => $itemid]);
$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);

[$markingworkflow, $gradereleased, $workflowstatus] = $controller->get_markingworkflow($itemid);
$table = new \gradingform_enhancedrubric\history_table($areaid, $itemid, $markingworkflow, null, $controller);
$table->baseurl = $pageurl;

if ($table->is_downloading()) {
    $table->download();
    exit();
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$table->out(10, false);
echo $OUTPUT->single_button($controller->get_all_history_url(), get_string('viewallhistory', 'gradingform_enhancedrubric'));
echo $OUTPUT->footer();
