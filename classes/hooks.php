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
 * Hooks into core code
 *
 * @package   gradingform_enhancedrubric
 * @copyright 2022 Amanda Doughty, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_enhancedrubric;

defined('MOODLE_INTERNAL') || die();

class hooks {
    /**
     * Add assign marking workflow settings to data.
     * @param \stdClass $formdata The data submitted
     */
    public static function extend_gradinginstance_get_grade($formdata) {
        if (isset($formdata->workflowstate)) {
            $formdata->advancedgrading['workflowstate'] = $formdata->workflowstate;
        } else {
            $formdata->advancedgrading['workflowstate'] = "";
        }
    }

    /**
     * Extends the settings navigation with the grading settings
     *
     * This function is called when the context for the page is an activity module with the
     * FEATURE_ADVANCED_GRADING and the user has the permission moodle/grade:managegradingforms.
     *
     * @param \settings_navigation $settingsnav {@link settings_navigation}
     * @param \navigation_node $modulenode {@link navigation_node}
     * @param gradingform_enhancedrubric_controller $controller
     */
    public static function extend_settings_navigation(
        \settings_navigation $settingsnav,
        \navigation_node $modulenode=null,
        \gradingform_controller $controller
    ) {
        if (!($controller instanceof \gradingform_enhancedrubric_controller)) {
            return;
        }

        $reportnode = \navigation_node::create(get_string('viewallhistory', 'gradingform_enhancedrubric'),
                                               $controller->get_all_history_url(),
                                              \navigation_node::TYPE_CUSTOM, null);

        $reportnode->set_show_in_secondary_navigation(true);
        $reportnode->set_force_into_more_menu(true);
        $modulenode->add_node($reportnode);
    }
}
