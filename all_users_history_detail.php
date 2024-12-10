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
 * Fillings history enhanced rubric page
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2022 Amanda Doughty, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

$instanceid = required_param('instanceid', PARAM_INT);
$areaid = required_param('areaid', PARAM_INT);
$itemid = required_param('itemid', PARAM_INT);
$raterid = required_param('raterid', PARAM_INT);

$manager = get_grading_manager($areaid);
[$context, $course, $cm] = get_context_info_array($manager->get_context()->id);
$assignment = new assign($context, $cm, $course);
$commentfeedbackplugin = $assignment->get_feedback_plugin_by_type('comments');
$commentsenabled = $commentfeedbackplugin->is_enabled() && $commentfeedbackplugin->is_visible();
// Does this assignment use a scale?
$scaleoptions = null;
if ($assignment->get_instance()->grade < 0) {
    if ($scale = $DB->get_record('scale', array('id'=>-($assignment->get_instance()->grade)))) {
        $scaleoptions = make_menu_from_list($scale->scale);
    }
}

require_login($course, true, $cm);
require_capability('gradingform/enhancedrubric:viewhistory', $context);

$title = get_string('enhancedrubrichistory', 'gradingform_enhancedrubric');
$pageurl = new moodle_url(
    '/grade/grading/form/enhancedrubric/history_detail.php',
    [
        'instanceid' => $instanceid,
        'areaid' => $areaid,
        'itemid' => $itemid,
        'raterid' => $raterid
    ]
);

$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$controller = $manager->get_controller('enhancedrubric');
[$context, $course, $cm] = get_context_info_array($manager->get_context()->id);
$assignment = new assign($context, $cm, $course);
$grademenu = make_grades_menu($assignment->get_instance()->grade);
$allowgradedecimals = $assignment->get_instance()->grade > 0;
$controller->set_grade_range($grademenu, $allowgradedecimals);
$instance = $controller->get_or_create_instance($instanceid, $raterid, $itemid);
[$markingworkflow, $gradereleased, $workflowstatus] = $controller->get_markingworkflow($itemid);
$table = new \gradingform_enhancedrubric\all_users_rubrics_table(
    $areaid,
    null,
    null,
    $itemid,
    $markingworkflow,
    $context,
    $commentsenabled,
    $scaleoptions,
    null,
    $instance->get_controller()
);
$table->baseurl = $pageurl;

if ($table->is_downloading()) {
    $table->download();
    exit();
}

$cangrade = has_capability('mod/assign:grade', $context);
$cangradesecond =
    has_capability('mod/assign:reviewgrades', $context) ||
    has_capability('mod/assign:releasegrades', $context) ||
    has_capability('mod/assign:managegrades', $context);
$cangradefinal =
    has_capability('mod/assign:releasegrades', $context) ||
    has_capability('mod/assign:managegrades', $context);
$canviewhistory = has_capability('gradingform/enhancedrubric:viewhistory', $context);

$renderer = $PAGE->get_renderer('gradingform_enhancedrubric');

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $renderer->display_instance_history($instance, $cangrade, $cangradesecond, $cangradefinal);
echo $table->download_buttons();
echo $OUTPUT->footer();
