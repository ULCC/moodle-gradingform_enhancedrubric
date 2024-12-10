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
 * Handle workflow state updated.
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2022 Amanda Doughty, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_enhancedrubric;

use gradingform_enhancedrubric\event\workflow_state_updated_bulk;
use gradingform_enhancedrubric_instance;

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');

class observer {
    public static function workflow_state_updated_bulk(workflow_state_updated_bulk $event): void {
        $context = \context::instance_by_id($event->contextid);
        $userid = $event->relateduserid;
        $assign = new \assign($context, null, null);
        $grade = $assign->get_user_grade($userid, true);

        if ($grade->grade == -1) {
            return;
        }

        $gradingdisabled = $assign->grading_disabled($userid);
        $gradinginstance = self::get_grading_instance($userid, $grade, $gradingdisabled, $assign);

        if (!$gradinginstance instanceof gradingform_enhancedrubric_instance) {
            return;
        }

        if (!$gradingdisabled) {
            $grade->grade = $gradinginstance->get_grade();
            // Updates grade in assignment and gradebook.
            $assign->update_grade($grade);
        }
    }

    /**
     * ***** COPIED FROM mod/assign/locallib.php *****
     * Get an instance of a grading form if advanced grading is enabled.
     * This is specific to the assignment, marker and student.
     *
     * @param int $userid - The student userid
     * @param \stdClass|false $grade - The grade record
     * @param bool $gradingdisabled
     * @param \assign $assign
     * @return mixed gradingform_instance|null $gradinginstance
     */
    protected static function get_grading_instance($userid, $grade, $gradingdisabled, $assign) {
        global $CFG, $USER;

        $grademenu = make_grades_menu($assign->get_instance()->grade);
        $allowgradedecimals = $assign->get_instance()->grade > 0;

        $advancedgradingwarning = false;
        $gradingmanager = get_grading_manager($assign->get_context(), 'mod_assign', 'submissions');
        $gradinginstance = null;
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_available()) {
                $itemid = null;
                if ($grade) {
                    $itemid = $grade->id;
                }
//                if ($gradingdisabled && $itemid) {
                    $gradinginstance = $controller->get_current_instance($USER->id, $itemid);
//                } else if (!$gradingdisabled) {
////                    $instanceid = optional_param('advancedgradinginstanceid', 0, PARAM_INT);
//                    $instanceid = null;
//                    $gradinginstance = $controller->get_or_create_instance($instanceid,
//                                                                           $USER->id,
//                                                                           $itemid);
//                }
            } else {
                $advancedgradingwarning = $controller->form_unavailable_notification();
            }
        }
        if ($gradinginstance) {
            $gradinginstance->get_controller()->set_grade_range($grademenu, $allowgradedecimals);
        }
        return $gradinginstance;
    }
}
