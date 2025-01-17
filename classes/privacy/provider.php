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
 * Privacy class for requesting user data.
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2018 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_enhancedrubric\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;

/**
 * Privacy class for requesting user data.
 *
 * @copyright  2018 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_grading\privacy\gradingform_provider_v2 {

    /**
     * Returns meta data about this system.
     *
     * @param  collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table('enhancedrubric_fillings', [
            'instanceid' => 'privacy:metadata:instanceid',
            'criterionid' => 'privacy:metadata:criterionid',
            'levelid' => 'privacy:metadata:levelid',
            'remark' => 'privacy:metadata:remark',
            'section' => 'privacy:metadata:section',
        ], 'privacy:metadata:fillingssummary');
        $collection->add_database_table('enhancedrubric_history', [
            'instanceid' => 'privacy:metadata:instanceid',
            'raterid' => 'privacy:metadata:raterid',
            'rawgrade' => 'privacy:metadata:rawgrade',
            'workflowstatus' => 'privacy:metadata:workflowstatus',
            'feedback' => 'privacy:metadata:feedback',
            'timemodified' => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:enhancedrubric_history');
        return $collection;
    }

    /**
     * Export user data relating to an instance ID.
     *
     * @param  \context $context Context to use with the export writer.
     * @param  int $instanceid The instance ID to export data for.
     * @param  array $subcontext The directory to export this data to.
     */
    public static function export_gradingform_instance_data(\context $context, int $instanceid, array $subcontext) {
        global $DB;
        // Get records from the provided params.
        $params = ['instanceid' => $instanceid];
        $primarykey = $DB->sql_concat('rf.section', "'. '", 'rc.description');
        $sql = "SELECT $primarykey, rc.description, rl.definition, rl.score, rf.remark
                  FROM {enhancedrubric_fillings} rf
                  JOIN {enhancedrubric_criteria} rc ON rc.id = rf.criterionid
                  JOIN {enhancedrubric_levels} rl ON rf.levelid = rl.id
                 WHERE rf.instanceid = :instanceid";
        $records = $DB->get_records_sql($sql, $params);
        if ($records) {
            $subcontext = array_merge($subcontext, [get_string('enhancedrubric', 'gradingform_enhancedrubric'), $instanceid]);
            \core_privacy\local\request\writer::with_context($context)->export_data($subcontext, (object) $records);
        }
        $sql = "SELECT eh.id, eh.instanceid, eh.raterid, eh.rawgrade, eh.workflowstatus, eh.feedback
                  FROM {enhancedrubric_history} eh
                 WHERE eh.instanceid = :instanceid";
        $records = $DB->get_records_sql($sql, $params);
        if ($records) {
            $subcontext = array_merge($subcontext, [get_string('enhancedrubrichistory', 'gradingform_enhancedrubric'), $instanceid]);
            \core_privacy\local\request\writer::with_context($context)->export_data($subcontext, (object) $records);
        }
    }

    /**
     * Deletes all user data related to the provided instance IDs.
     *
     * @param  array  $instanceids The instance IDs to delete information from.
     */
    public static function delete_gradingform_for_instances(array $instanceids) {
        global $DB;
        $DB->delete_records_list('enhancedrubric_fillings', 'instanceid', $instanceids);
        $DB->delete_records_list('enhancedrubric_history', 'instanceid', $instanceids);
    }
}
