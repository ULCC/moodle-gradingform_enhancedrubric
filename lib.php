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
 * Grading method controller for the Enhanced rubric plugin
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/grade/grading/form/lib.php');
require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/** enhancedrubric: Used to compare our gradeitem_type against. */
const ENHANCEDRUBRIC = 'enhancedrubric';

/**
 * This controller encapsulates the enhanced rubric grading logic
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_enhancedrubric_controller extends gradingform_controller {
    // Modes of displaying the rubric (used in gradingform_enhancedrubric_renderer)
    const DISPLAY_EDIT_FULL     = 1;
    /** Enhanced rubric display mode: Preview the enhanced rubric design with hidden fields */
    const DISPLAY_EDIT_FROZEN   = 2;
    /** Enhanced rubric display mode: Preview the enhanced rubric design (for person with manage permission) */
    const DISPLAY_PREVIEW       = 3;
    /** Enhanced rubric display mode: For evaluation, enabled (teacher grades a student) */
    const DISPLAY_EVAL          = 4;
    /** Enhanced rubric display mode: For evaluation, with hidden fields */
    const DISPLAY_EVAL_FROZEN   = 5;
    /** Enhanced rubric display mode: Teacher reviews filled enhanced rubric */
    const DISPLAY_REVIEW        = 6;
    /** Enhanced rubric display mode: Dispaly filled enhanced rubric (i.e. students see their grades) */
    const DISPLAY_VIEW          = 7;
    /** Enhanced rubric display mode: Preview the enhanced rubric (for people being graded) */
    const DISPLAY_PREVIEW_GRADED= 8;
    /** Enhanced rubric display mode: Display filled enhanced rubric history */
    const DISPLAY_HISTORY       = 9;

    /** Rubric sections used for marking workflow enabled, disabled or enabled then disabled later */
    /** Enhanced rubric initial section */
    const INITIAL               = 1;
    /** Enhanced rubric second section */
    const SECOND                = 2;
    /** Enhanced rubric final section */
    const FINAL                 = 3;
    /** Marking workflow not enabled */
    const NO_MWF = 0;
    /** Marking workflow not enabled */
    const MWF = 1;
    /** Marking workflow was enabled then disabled and grades were not released */
    const WAS_MWF_UNRELEASED = 2;
    /** Marking workflow was enabled then disabled and grades were released */
    const WAS_MWF_RELEASED = 3;
    /** Marking workflow enabled - all sections required */
    const MWF_SECTIONS = [self::INITIAL, self::SECOND, self::FINAL]; // [1, 2, 3].
    /** Marking workflow not enabled - only inital section required */
    const NO_MWF_SECTIONS = [self::INITIAL]; // [1].
    /** Marking workflow was enabled then disabled and grades were not released - use the intitial section */
    const WAS_MWF_UNRELEASED_SECTIONS = [self::INITIAL]; // [1].
    /** Marking workflow was enabled then disabled and grades were released - use the final section */
    const WAS_MWF_RELEASED_SECTIONS = [self::FINAL]; // [3].
    /** Sections required for each marking workflow option */
    const SECTIONS = [
        self::NO_MWF => self::NO_MWF_SECTIONS,
        self::MWF => self::MWF_SECTIONS,
        self::WAS_MWF_UNRELEASED => self::WAS_MWF_UNRELEASED_SECTIONS,
        self::WAS_MWF_RELEASED => self::WAS_MWF_RELEASED_SECTIONS
    ]; // [0 => [1], 1 => [1, 2, 3], 2 => [1], 3 => [3]].

    /**
     * Extends the module settings navigation with the enhanced rubric grading settings
     *
     * This function is called when the context for the page is an activity module with the
     * FEATURE_ADVANCED_GRADING, the user has the permission moodle/grade:managegradingforms
     * and there is an area with the active grading method set to 'enhancedrubric'.
     *
     * @param settings_navigation $settingsnav {@link settings_navigation}
     * @param navigation_node $node {@link navigation_node}
     */
    public function extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node=null) {
        $reportnode = navigation_node::create(get_string('viewallhistory', 'gradingform_enhancedrubric'),
                                              $this->get_all_history_url(),
                                              navigation_node::TYPE_CUSTOM, null);

        $reportnode->set_show_in_secondary_navigation(true);
        $reportnode->set_force_into_more_menu(true);
        $node->add_node($reportnode);
    }

    /**
     * Extends the module navigation
     *
     * This function is called when the context for the page is an activity module with the
     * FEATURE_ADVANCED_GRADING and there is an area with the active grading method set to the given plugin.
     *
     * @param global_navigation $navigation {@link global_navigation}
     * @param navigation_node $node {@link navigation_node}
     */
    public function extend_navigation(global_navigation $navigation, navigation_node $node=null) {
        if (has_capability('moodle/grade:managegradingforms', $this->get_context())) {
            // No need for preview if user can manage forms, he will have link to manage.php in settings instead.
            return;
        }
        if ($this->is_form_defined() && ($options = $this->get_options()) && !empty($options['alwaysshowdefinition'])) {
            $node->add(get_string('gradingof', 'gradingform_enhancedrubric', get_grading_manager($this->get_areaid())->get_area_title()),
                       new moodle_url('/grade/grading/form/'.$this->get_method_name().'/preview.php', array('areaid' => $this->get_areaid())),
                       navigation_node::TYPE_CUSTOM);
        }
    }

    /**
     * Saves the enhanced rubric definition into the database
     *
     * @see parent::update_definition()
     * @param stdClass $newdefinition enhanced rubric definition data as coming from gradingform_enhancedrubric_editrubric::get_data()
     * @param int|null $usermodified optional userid of the author of the definition, defaults to the current user
     */
    public function update_definition(stdClass $newdefinition, $usermodified = null) {
        $this->update_or_check_enhancedrubric($newdefinition, $usermodified, true);
        if (isset($newdefinition->enhancedrubric['regrade']) && $newdefinition->enhancedrubric['regrade']) {
            $this->mark_for_regrade();
        }
    }

    /**
     * Either saves the enhanced rubric definition into the database or check if it has been changed.
     * Returns the level of changes:
     * 0 - no changes
     * 1 - only texts or criteria sortorders are changed, students probably do not require re-grading
     * 2 - added levels but maximum score on enhanced rubric is the same, students still may not require re-grading
     * 3 - removed criteria or added levels or changed number of points, students require re-grading but may be re-graded automatically
     * 4 - removed levels - students require re-grading and not all students may be re-graded automatically
     * 5 - added criteria - all students require manual re-grading
     *
     * @param stdClass $newdefinition enhanced rubric definition data as coming from gradingform_enhancedrubric_editrubric::get_data()
     * @param int|null $usermodified optional userid of the author of the definition, defaults to the current user
     * @param boolean $doupdate if true actually updates DB, otherwise performs a check
     *
     */
    public function update_or_check_enhancedrubric(stdClass $newdefinition, $usermodified = null, $doupdate = false) {
        global $DB;

        // Firstly update the common definition data in the {grading_definition} table.
        if ($this->definition === false) {
            if (!$doupdate) {
                // If we create the new definition there is no such thing as re-grading anyway.
                return 5;
            }
            // If definition does not exist yet, create a blank one
            // (we need id to save files embedded in description).
            parent::update_definition(new stdClass(), $usermodified);
            parent::load_definition();
        }
        if (!isset($newdefinition->enhancedrubric['options'])) {
            $newdefinition->enhancedrubric['options'] = self::get_default_options();
        }
        $newdefinition->options = json_encode($newdefinition->enhancedrubric['options']);
        $editoroptions = self::description_form_field_options($this->get_context());
        $newdefinition = file_postupdate_standard_editor($newdefinition, 'description', $editoroptions, $this->get_context(),
            'grading', 'description', $this->definition->id);

        // Reload the definition from the database.
        $currentdefinition = $this->get_definition(true);
        $haschanges = array();

        // Update enhanced rubric data.
        if (empty($newdefinition->enhancedrubric['criteria'])) {
            $newcriteria = array();
        } else {
            $newcriteria = $newdefinition->enhancedrubric['criteria']; // New ones to be saved.
        }
        $currentcriteria = $currentdefinition->enhancedrubric_criteria;
        $criteriafields = array('sortorder', 'description', 'descriptionformat');
        $levelfields = array('score', 'min', 'definition', 'definitionformat');
        foreach ($newcriteria as $id => $criterion) {
            // Get list of submitted levels.
            $levelsdata = array();
            if (array_key_exists('levels', $criterion)) {
                $levelsdata = $criterion['levels'];
            }
            $criterionmaxscore = null;
            if (preg_match('/^NEWID\d+$/', $id)) {
                // Insert criterion into DB.
                $data = array('definitionid' => $this->definition->id, 'descriptionformat' => FORMAT_MOODLE); // TODO MDL-31235 format is not supported yet
                foreach ($criteriafields as $key) {
                    if (array_key_exists($key, $criterion)) {
                        $data[$key] = $criterion[$key];
                    }
                }
                if ($doupdate) {
                    $id = $DB->insert_record('enhancedrubric_criteria', $data);
                }
                $haschanges[5] = true;
            } else {
                // Update criterion in DB.
                $data = array();
                foreach ($criteriafields as $key) {
                    if (array_key_exists($key, $criterion) && $criterion[$key] != $currentcriteria[$id][$key]) {
                        $data[$key] = $criterion[$key];
                    }
                }
                if (!empty($data)) {
                    // Update only if something is changed.
                    $data['id'] = $id;
                    if ($doupdate) {
                        $DB->update_record('enhancedrubric_criteria', $data);
                    }
                    $haschanges[1] = true;
                }
                // Remove deleted levels from DB and calculate the maximum score for this criteria.
                foreach ($currentcriteria[$id]['levels'] as $levelid => $currentlevel) {
                    if ($criterionmaxscore === null || $criterionmaxscore < $currentlevel['score']) {
                        $criterionmaxscore = $currentlevel['score'];
                    }
                    if (!array_key_exists($levelid, $levelsdata)) {
                        if ($doupdate) {
                            $DB->delete_records('enhancedrubric_levels', array('id' => $levelid));
                        }
                        $haschanges[4] = true;
                    }
                }
            }
            foreach ($levelsdata as $levelid => $level) {
                if (isset($level['score'])) {
                    $level['score'] = unformat_float($level['score']);
                }

                $level['min'] = empty($level['min']) ? 0 : 1;

                if (preg_match('/^NEWID\d+$/', $levelid)) {
                    // insert level into DB
                    $data = array('criterionid' => $id, 'definitionformat' => FORMAT_MOODLE); // TODO MDL-31235 format is not supported yet
                    foreach ($levelfields as $key) {
                        if (array_key_exists($key, $level)) {
                            $data[$key] = $level[$key];
                        }
                    }
                    if ($doupdate) {
                        $levelid = $DB->insert_record('enhancedrubric_levels', $data);
                    }
                    if ($criterionmaxscore !== null && $criterionmaxscore >= $level['score']) {
                        // New level is added but the maximum score for this criteria did not change, re-grading may not be necessary.
                        $haschanges[2] = true;
                    } else {
                        $haschanges[3] = true;
                    }
                } else {
                    // Update level in DB.
                    $data = array();
                    foreach ($levelfields as $key) {
                        if (array_key_exists($key, $level) && $level[$key] != $currentcriteria[$id]['levels'][$levelid][$key]) {
                            $data[$key] = $level[$key];
                        }
                    }
                    if (!empty($data)) {
                        // Update only if something is changed.
                        $data['id'] = $levelid;
                        if ($doupdate) {
                            $DB->update_record('enhancedrubric_levels', $data);
                        }
                        if (isset($data['score'])) {
                            $haschanges[3] = true;
                        }
                        $haschanges[1] = true;
                    }
                }
            }
        }
        // Remove deleted criteria from DB.
        foreach (array_keys($currentcriteria) as $id) {
            if (!array_key_exists($id, $newcriteria)) {
                if ($doupdate) {
                    $DB->delete_records('enhancedrubric_criteria', array('id' => $id));
                    $DB->delete_records('enhancedrubric_levels', array('criterionid' => $id));
                }
                $haschanges[3] = true;
            }
        }
        foreach (array('status', 'description', 'descriptionformat', 'name', 'options') as $key) {
            if (isset($newdefinition->$key) && $newdefinition->$key != $this->definition->$key) {
                $haschanges[1] = true;
            }
        }
        if ($usermodified && $usermodified != $this->definition->usermodified) {
            $haschanges[1] = true;
        }
        if (!count($haschanges)) {
            return 0;
        }
        if ($doupdate) {
            parent::update_definition($newdefinition, $usermodified);
            $this->load_definition();
        }
        // Return the maximum level of changes.
        $changelevels = array_keys($haschanges);
        sort($changelevels);
        return array_pop($changelevels);
    }

    /**
     * Marks all instances filled with this enhanced rubric with the status INSTANCE_STATUS_NEEDUPDATE
     */
    public function mark_for_regrade() {
        global $DB;
        if ($this->has_active_instances()) {
            $conditions = array('definitionid'  => $this->definition->id,
                        'status'  => gradingform_instance::INSTANCE_STATUS_ACTIVE);
            $DB->set_field('grading_instances', 'status', gradingform_instance::INSTANCE_STATUS_NEEDUPDATE, $conditions);
        }
    }

    /**
     * Loads the enhanced rubric form definition if it exists
     *
     * There is a new array called 'enhancedrubric_criteria' appended to the list of parent's definition properties.
     */
    protected function load_definition() {
        global $DB;
        $sql = "SELECT gd.*,
                       rc.id AS rcid, rc.sortorder AS rcsortorder, rc.description AS rcdescription, rc.descriptionformat AS rcdescriptionformat,
                       rl.id AS rlid, rl.score AS rlscore, rl.min AS rlmin, rl.definition AS rldefinition, rl.definitionformat AS rldefinitionformat
                  FROM {grading_definitions} gd
             LEFT JOIN {enhancedrubric_criteria} rc ON (rc.definitionid = gd.id)
             LEFT JOIN {enhancedrubric_levels} rl ON (rl.criterionid = rc.id)
                 WHERE gd.areaid = :areaid AND gd.method = :method
              ORDER BY rc.sortorder,rl.score";
        $params = array('areaid' => $this->areaid, 'method' => $this->get_method_name());

        $rs = $DB->get_recordset_sql($sql, $params);
        $this->definition = false;
        foreach ($rs as $record) {
            // Pick the common definition data.
            if ($this->definition === false) {
                $this->definition = new stdClass();
                foreach (array('id', 'name', 'description', 'descriptionformat', 'status', 'copiedfromid',
                        'timecreated', 'usercreated', 'timemodified', 'usermodified', 'timecopied', 'options') as $fieldname) {
                    $this->definition->$fieldname = $record->$fieldname;
                }
                $this->definition->enhancedrubric_criteria = array();
            }
            // Pick the criterion data.
            if (!empty($record->rcid) && empty($this->definition->enhancedrubric_criteria[$record->rcid])) {
                foreach (array('id', 'sortorder', 'description', 'descriptionformat') as $fieldname) {
                    $this->definition->enhancedrubric_criteria[$record->rcid][$fieldname] = $record->{'rc'.$fieldname};
                }
                $this->definition->enhancedrubric_criteria[$record->rcid]['levels'] = array();
            }
            // Pick the level data.
            if (!empty($record->rlid)) {
                foreach (array('id', 'score', 'min', 'definition', 'definitionformat') as $fieldname) {
                    $value = $record->{'rl'.$fieldname};
                    if ($fieldname == 'score') {
                        $value = (float)$value; // To prevent display like 1.00000.
                    }
                    $this->definition->enhancedrubric_criteria[$record->rcid]['levels'][$record->rlid][$fieldname] = $value;
                }
            }
        }
        $rs->close();
        $options = $this->get_options();
        if (!$options['sortlevelsasc']) {
            foreach (array_keys($this->definition->enhancedrubric_criteria) as $rcid) {
                $this->definition->enhancedrubric_criteria[$rcid]['levels'] = array_reverse($this->definition->enhancedrubric_criteria[$rcid]['levels'], true);
            }
        }
    }

    /**
     * Returns the default options for the rubric display
     *
     * @return array
     */
    public static function get_default_options() {
        $options = array(
            'mintotal' => 0,
            'sortlevelsasc' => 1,
            'alwaysshowdefinition' => 1,
            'showdescriptionteacher' => 1,
            'showdescriptionstudent' => 1,
            'showscoreteacher' => 1,
            'showscorestudent' => 1,
            'enableremarks' => 1,
            'showremarksstudent' => 1,
            'showtotal' => 1,
            'showminimum' => 1,
            'showminimumgrader' => 1,
            'enableinternalcomments' => 1,
        );
        return $options;
    }

    /**
     * Gets the options of this enhanced rubric definition, fills the missing options with default values
     *
     *
     * @return array
     */
    public function get_options() {
        $options = self::get_default_options();
        if (!empty($this->definition->options)) {
            $thisoptions = json_decode($this->definition->options, true); // Assoc. array is expected.
            foreach ($thisoptions as $option => $value) {
                $options[$option] = $value;
            }
        }
        return $options;
    }

    /**
     * Converts the current definition into an object suitable for the editor form's set_data()
     *
     * @param boolean $addemptycriterion whether to add an empty criterion if the enhanced rubric is completely empty (just being created)
     * @return stdClass
     */
    public function get_definition_for_editing($addemptycriterion = false) {

        $definition = $this->get_definition();
        $properties = new stdClass();
        $properties->areaid = $this->areaid;
        if ($definition) {
            foreach (array('id', 'name', 'description', 'descriptionformat', 'status') as $key) {
                $properties->$key = $definition->$key;
            }
            $options = self::description_form_field_options($this->get_context());
            $properties = file_prepare_standard_editor($properties, 'description', $options, $this->get_context(),
                'grading', 'description', $definition->id);
        }
        $properties->enhancedrubric = array('criteria' => array(), 'options' => $this->get_options());
        if (!empty($definition->enhancedrubric_criteria)) {
            $properties->enhancedrubric['criteria'] = $definition->enhancedrubric_criteria;
        } else if (!$definition && $addemptycriterion) {
            $properties->enhancedrubric['criteria'] = array('addcriterion' => 1);
        }

        return $properties;
    }

    /**
     * Returns the form definition suitable for cloning into another area
     *
     * @see parent::get_definition_copy()
     * @param gradingform_controller $target the controller of the new copy
     * @return stdClass definition structure to pass to the target's {@link update_definition()}
     */
    public function get_definition_copy(gradingform_controller $target) {

        $new = parent::get_definition_copy($target);
        $old = $this->get_definition_for_editing();
        $new->description_editor = $old->description_editor;
        $new->enhancedrubric = array('criteria' => array(), 'options' => $old->enhancedrubric['options']);
        $newcritid = 1;
        $newlevid = 1;
        foreach ($old->enhancedrubric['criteria'] as $oldcritid => $oldcrit) {
            unset($oldcrit['id']);
            if (isset($oldcrit['levels'])) {
                foreach ($oldcrit['levels'] as $oldlevid => $oldlev) {
                    unset($oldlev['id']);
                    $oldcrit['levels']['NEWID'.$newlevid] = $oldlev;
                    unset($oldcrit['levels'][$oldlevid]);
                    $newlevid++;
                }
            } else {
                $oldcrit['levels'] = array();
            }
            $new->enhancedrubric['criteria']['NEWID'.$newcritid] = $oldcrit;
            $newcritid++;
        }

        return $new;
    }

    /**
     * Options for displaying the enhanced rubric description field in the form
     *
     * @param object $context
     * @return array options for the form description field
     */
    public static function description_form_field_options($context) {
        global $CFG;
        return array(
            'maxfiles' => -1,
            'maxbytes' => get_user_max_upload_file_size($context, $CFG->maxbytes),
            'context'  => $context,
        );
    }

    /**
     * Formats the definition description for display on page
     *
     * @return string
     */
    public function get_formatted_description() {
        if (!isset($this->definition->description)) {
            return '';
        }
        $context = $this->get_context();

        $options = self::description_form_field_options($this->get_context());
        $description = file_rewrite_pluginfile_urls($this->definition->description, 'pluginfile.php', $context->id,
            'grading', 'description', $this->definition->id, $options);

        $formatoptions = array(
            'noclean' => false,
            'trusted' => false,
            'filter' => true,
            'context' => $context
        );
        return format_text($description, $this->definition->descriptionformat, $formatoptions);
    }

    /**
     * Returns the enhanced rubric plugin renderer
     *
     * @param moodle_page $page the target page
     * @return gradingform_enhancedrubric_renderer
     */
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('gradingform_'. $this->get_method_name());
    }

    /**
     * Returns the HTML code displaying the preview of the grading form
     *
     * @param moodle_page $page the target page
     * @return string
     */
    public function render_preview(moodle_page $page) {

        if (!$this->is_form_defined()) {
            throw new coding_exception('It is the caller\'s responsibility to make sure that the form is actually defined');
        }

        $criteria = $this->definition->enhancedrubric_criteria;
        $options = $this->get_options();
        $enhancedrubric = '';
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $showdescription = true;
        } else {
            if (empty($options['alwaysshowdefinition']))  {
                // Ensure we don't display unless show enhanced rubric option enabled.
                return '';
            }
            $showdescription = $options['showdescriptionstudent'];
        }
        $output = $this->get_renderer($page);
        if ($showdescription) {
            $enhancedrubric .= $output->box($this->get_formatted_description(), 'gradingform_enhancedrubric-description');
        }
        if (has_capability('moodle/grade:managegradingforms', $page->context)) {
            $enhancedrubric .= $output->display_enhancedrubric(
                $criteria,
                $options,
                self::DISPLAY_PREVIEW,
                'enhancedrubric',
                gradingform_enhancedrubric_controller::INITIAL,
                null,
                null,
                has_capability('gradingform/enhancedrubric:viewhistory', $page->context),
                false,
                false,
                false,
                $this->get_areaid()
            );
        } else {
            $enhancedrubric .= $output->display_enhancedrubric($criteria, $options, self::DISPLAY_PREVIEW_GRADED, 'enhancedrubric');
        }

        return $enhancedrubric;
    }

    /**
     * Deletes the rubric definition and all the associated information
     */
    protected function delete_plugin_definition() {
        global $DB;

        // Get the list of instances.
        $instances = array_keys($DB->get_records('grading_instances', array('definitionid' => $this->definition->id), '', 'id'));
        // Delete all fillings.
        $DB->delete_records_list('enhancedrubric_fillings', 'instanceid', $instances);
        // Delete instances.
        $DB->delete_records_list('grading_instances', 'id', $instances);
        // Delete grade history.
        $DB->delete_records_list('enhancedrubric_history', 'instanceid', $instances);
        // Get the list of criteria records.
        $criteria = array_keys($DB->get_records('enhancedrubric_criteria', array('definitionid' => $this->definition->id), '', 'id'));
        // Delete levels.
        $DB->delete_records_list('enhancedrubric_levels', 'criterionid', $criteria);
        // Delete critera.
        $DB->delete_records_list('enhancedrubric_criteria', 'id', $criteria);
    }

    /**
     * If instanceid is specified and grading instance exists and it is created by this rater for
     * this item, this instance is returned.
     * If there exists a draft for this raterid+itemid, take this draft (this is the change from parent)
     * Otherwise new instance is created for the specified rater and itemid
     *
     * @param int $instanceid
     * @param int $raterid
     * @param int $itemid
     * @return gradingform_instance
     */
    public function get_or_create_instance($instanceid, $raterid, $itemid) {
        global $DB;
        if ($instanceid &&
                $instance = $DB->get_record('grading_instances', array('id'  => $instanceid, 'raterid' => $raterid, 'itemid' => $itemid), '*', IGNORE_MISSING)) {
            return $this->get_instance($instance);
        }
        if ($itemid && $raterid) {
            $params = array('definitionid' => $this->definition->id, 'raterid' => $raterid, 'itemid' => $itemid);
            if ($rs = $DB->get_records('grading_instances', $params, 'timemodified DESC', '*', 0, 1)) {
                $record = reset($rs);
                $currentinstance = $this->get_current_instance($raterid, $itemid);
                if ($record->status == gradingform_instance::INSTANCE_STATUS_INCOMPLETE &&
                        (!$currentinstance || $record->timemodified > $currentinstance->get_data('timemodified'))) {
                    $record->isrestored = true;
                    return $this->get_instance($record);
                }
            }
        }
        return $this->create_instance($raterid, $itemid);
    }

    /**
     * Returns html code to be included in student's feedback.
     *
     * @param moodle_page $page
     * @param int $itemid
     * @param array $gradinginfo result of function grade_get_grades
     * @param string $defaultcontent default string to be returned if no active grading is found
     * @param boolean $cangrade whether current user has capability to grade in this context
     * @return string
     */
    public function render_grade($page, $itemid, $gradinginfo, $defaultcontent, $cangrade) {
        $cangradesecond =
            has_capability('mod/assign:reviewgrades', $page->context) ||
            has_capability('mod/assign:releasegrades', $page->context) ||
            has_capability('mod/assign:managegrades', $page->context);
        $cangradefinal =
            has_capability('mod/assign:releasegrades', $page->context) ||
            has_capability('mod/assign:managegrades', $page->context);
        $canviewhistory = has_capability('gradingform/enhancedrubric:viewhistory', $page->context);
        return $this->get_renderer($page)->display_instances($this->get_active_instances($itemid), $defaultcontent, $cangrade, $canviewhistory, $cangradesecond, $cangradefinal);
    }

    // ///// full-text search support /////////////////////////////////////////////

    /**
     * Prepare the part of the search query to append to the FROM statement
     *
     * @param string $gdid the alias of grading_definitions.id column used by the caller
     * @return string
     */
    public static function sql_search_from_tables($gdid) {
        return " LEFT JOIN {enhancedrubric_criteria} rc ON (rc.definitionid = $gdid)
                 LEFT JOIN {enhancedrubric_levels} rl ON (rl.criterionid = rc.id)";
    }

    /**
     * Prepare the parts of the SQL WHERE statement to search for the given token
     *
     * The returned array cosists of the list of SQL comparions and the list of
     * respective parameters for the comparisons. The returned chunks will be joined
     * with other conditions using the OR operator.
     *
     * @param string $token token to search for
     * @return array
     */
    public static function sql_search_where($token) {
        global $DB;

        $subsql = array();
        $params = array();

        // Search in enhancedrubric criteria description.
        $subsql[] = $DB->sql_like('rc.description', '?', false, false);
        $params[] = '%'.$DB->sql_like_escape($token).'%';

        // Search in enhancedrubric levels definition.
        $subsql[] = $DB->sql_like('rl.definition', '?', false, false);
        $params[] = '%'.$DB->sql_like_escape($token).'%';

        return array($subsql, $params);
    }

    /**
     * Calculates and returns the possible minimum and maximum score (in points) for this enhanced rubric
     *
     * @return array
     */
    public function get_min_max_score() {
        if (!$this->is_form_available()) {
            return null;
        }
        $returnvalue = array('minscore' => 0, 'maxscore' => 0);
        foreach ($this->get_definition()->enhancedrubric_criteria as $id => $criterion) {
            $scores = array();
            foreach ($criterion['levels'] as $level) {
                $scores[] = $level['score'];
            }
            sort($scores);
            $returnvalue['minscore'] += $scores[0];
            $returnvalue['maxscore'] += $scores[count($scores)-1];
        }
        return $returnvalue;
    }

    /**
     * Calculates and returns the possible minimum total score (in points) for this enhanced rubric
     *
     * @return array|null
     */
    public function get_min_required_scores(): ?array {
        if (!$this->is_form_available()) {
            return null;
        }
        $returnvalue = [];
        foreach ($this->get_definition()->enhancedrubric_criteria as $id => $criterion) {
            foreach ($criterion['levels'] as $level) {
                if ($level['min']) {
                    $returnvalue[$id] = $level['score'];
                    continue 2;
                }
            }
        }
        return $returnvalue;
    }

    /**
     * @return array An array containing a single key/value pair with the 'enhancedrubric_criteria' external_multiple_structure.
     * @see gradingform_controller::get_external_definition_details()
     * @since Moodle 2.5
     */
    public static function get_external_definition_details() {
        $enhancedrubric_criteria = new external_multiple_structure(
            new external_single_structure(
                array(
                   'id'   => new external_value(PARAM_INT, 'criterion id', VALUE_OPTIONAL),
                   'sortorder' => new external_value(PARAM_INT, 'sortorder', VALUE_OPTIONAL),
                   'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                   'descriptionformat' => new external_format_value('description', VALUE_OPTIONAL),
                   'levels' => new external_multiple_structure(
                                   new external_single_structure(
                                       array(
                                        'id' => new external_value(PARAM_INT, 'level id', VALUE_OPTIONAL),
                                        'score' => new external_value(PARAM_FLOAT, 'score', VALUE_OPTIONAL),
                                        'definition' => new external_value(PARAM_RAW, 'definition', VALUE_OPTIONAL),
                                        'definitionformat' => new external_format_value('definition', VALUE_OPTIONAL)
                                       )
                                  ), 'levels', VALUE_OPTIONAL
                              )
                   )
              ), 'definition details', VALUE_OPTIONAL
        );
        return array('enhancedrubric_criteria' => $enhancedrubric_criteria);
    }

    /**
     * Returns an array that defines the structure of the enhanced rubric's filling. This function is used by
     * the web service function core_grading_external::get_gradingform_instances().
     *
     * @return array An array containing a single key/value pair with the 'criteria' external_multiple_structure
     * @see gradingform_controller::get_external_instance_filling_details()
     * @since Moodle 2.6
     */
    public static function get_external_instance_filling_details() {
        $criteria = new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'filling id'),
                    'criterionid' => new external_value(PARAM_INT, 'criterion id'),
                    'levelid' => new external_value(PARAM_INT, 'level id', VALUE_OPTIONAL),
                    'remark' => new external_value(PARAM_RAW, 'remark', VALUE_OPTIONAL),
                    'remarkformat' => new external_format_value('remark', VALUE_OPTIONAL)
                )
            ), 'filling', VALUE_OPTIONAL
        );
        return array ('criteria' => $criteria);
    }

    /**
     * If the cm is an assignment then this will return:
     * 1. A value to indicate if marking flow is:
     * a) Currently enabled
     * b) Currently disabled but had been previously enabled and grades were never released
     * c) Currently disabled but had been previously enabled and grades were released
     * 2. A boolean to indicate if grades have been released
     * 3. The current workflow status if enabled
     *
     * @param $itemid
     * @return array
     * @throws dml_exception
     */
    public function get_markingworkflow($itemid = null) {
        global $DB;

        $context = $this->get_context();
        [$context, $course, $cm] = get_context_info_array($context->id);
        $gradereleased = false;
        $workflowstatus = null;

        if ($cm && $cm->modname == 'assign') {
            $assign = new assign($context, $cm, $course);
            $markingworkflow = $assign->get_instance()->markingworkflow;

            if ($itemid) {
                $record = $DB->get_field('assign_grades', 'userid', ['id' => $itemid]);
                $flags = $assign->get_user_flags($record, false);

                if ($flags) {
                    $workflowstatus = $flags->workflowstate;
                    $gradereleased = $flags->workflowstate === ASSIGN_MARKING_WORKFLOW_STATE_RELEASED;

                    if (!$markingworkflow && $record && !empty($flags->workflowstate)) {
                        $markingworkflow = self::WAS_MWF_UNRELEASED;

                        if ($flags->workflowstate == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                            $markingworkflow = self::WAS_MWF_RELEASED;
                        }
                    }
                }
            }
        } else {
            $markingworkflow = self::NO_MWF;
        }

        return [$markingworkflow, $gradereleased, $workflowstatus];
    }

    /**
     * Returns URL of all grading history page.
     *
     * @param moodle_url $returnurl optional URL of a page where the user should be sent once they are finished with editing
     * @return moodle_url
     */
    public function get_all_history_url() {
        $params = array('areaid' => $this->areaid);

        return new moodle_url('/grade/grading/form/enhancedrubric/allusershistory.php', $params);
    }
}

/**
 * Class to manage one enhanced rubric grading instance.
 *
 * Stores information and performs actions like update, copy, validate, submit, etc.
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2011 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_enhancedrubric_instance extends gradingform_instance {

    /** @var array stores the enhancedrubric, has two keys: 'criteria' and 'options' */
    protected $enhancedrubric;

    /** @var array stores the enhancedrubric, has two keys: 'criteria' and 'options' */
    protected $enhancedrubrichistory;

    /** @var array stores the sections to be displayed */
    protected $sections;

    /** @var bool stores the history status */
    protected $hasarchiveinstances;

    /** @var bool|mixed bool stores the grades released status */
    protected $gradereleased = false;

    /** @var mixed|null stores the current workflow status if marking workflow is enabled */
    protected $workflowstatus = null;

    /** @var mixed|null stores the state of marking workflow enable/disabled/was enabled */
    protected $markingworkflow = null;

    /**
     * Creates an instance
     *
     * @param gradingform_controller $controller
     * @param stdClass $data
     */
    public function __construct($controller, $data) {
        parent::__construct($controller, $data);

        [$this->markingworkflow, $this->gradereleased, $this->workflowstatus] = $controller->get_markingworkflow($data->itemid);
        $this->sections = gradingform_enhancedrubric_controller::SECTIONS[$this->markingworkflow];
    }

    /**
     * Deletes this (INCOMPLETE) instance from database.
     */
    public function cancel() {
        global $DB;
        parent::cancel();
        $DB->delete_records('enhancedrubric_fillings', array('instanceid' => $this->get_id()));
    }

    /**
     * Duplicates the instance before editing (optionally substitutes raterid and/or itemid with
     * the specified values)
     *
     * @param int $raterid value for raterid in the duplicate
     * @param int $itemid value for itemid in the duplicate
     * @return int id of the new instance
     */
    public function copy($raterid, $itemid) {
        global $DB;
        $instanceid = parent::copy($raterid, $itemid);
        $currentgrade = $this->get_enhancedrubric_filling();

        if ($currentgrade && isset($currentgrade['section'])) {
            foreach ($currentgrade['section'] as $section => $criteria) {
                foreach ($criteria['criteria'] as $criterionid => $record) {
                    $params = [
                        'instanceid' => $instanceid,
                        'criterionid' => $criterionid,
                        'levelid' => $record['levelid'],
                        'remark' => $record['remark'],
                        'remarkformat' => $record['remarkformat'],
                        'section' => $record['section']
                    ];
                    $DB->insert_record('enhancedrubric_fillings', $params);
                }
            }
        }

        return $instanceid;
    }

    /**
     * Determines whether the submitted form was empty.
     *
     * @param array $elementvalue value of element submitted from the form
     * @return boolean true if the form is empty
     */
    public function is_empty_form($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->enhancedrubric_criteria;

        foreach ($elementvalue['section'] as $elementvalueitem) {
            foreach ($criteria as $id => $criterion) {
                if (isset($elementvalueitem['criteria'][$id]['levelid'])
                    || !empty($elementvalueitem['criteria'][$id]['remark'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Removes the attempt from the gradingform_guide_fillings table
     * @param array $data the attempt data
     */
    public function clear_attempt($data) {
        global $DB;

        foreach ($data['section'] as $section => $criteria) {
            foreach ($criteria['criteria'] as $criterionid => $record) {
                $DB->delete_records('enhancedrubric_fillings',
                                    ['criterionid' => $criterionid, 'section' => $section,'instanceid' => $this->get_id()]);
            }
        }
    }

    /**
     * Validates that enhanced rubric is fully completed and contains valid grade on each criterion
     *
     * @param array $elementvalue value of element as came in form submit
     * @return boolean true if the form data is validated and contains no errors
     */
    public function validate_grading_element($elementvalue) {
        $criteria = $this->get_controller()->get_definition()->enhancedrubric_criteria;
        $context = $this->get_controller()->get_context();
        $cangradesecond =
            has_capability('mod/assign:reviewgrades', $context) ||
            has_capability('mod/assign:releasegrades', $context) ||
            has_capability('mod/assign:managegrades', $context);
        $cangradefinal =
            has_capability('mod/assign:releasegrades', $context) ||
            has_capability('mod/assign:managegrades', $context);

        foreach ($elementvalue['section'] as $section => $elementvalueitem) {
            if (!isset($elementvalueitem['criteria']) || !is_array($elementvalueitem['criteria']) || count($elementvalueitem['criteria']) < count($criteria)) {
                return false;
            }
            $secondempty = 0;
            $finalempty = 0;
            foreach ($criteria as $id => $criterion) {
                if (!isset($elementvalueitem['criteria'][$id]['levelid']) ||
                    // We allow null values so that the history includes all sections.
                    ($elementvalueitem['criteria'][$id]['levelid'] &&
                    !array_key_exists($elementvalueitem['criteria'][$id]['levelid'], $criterion['levels']))
                    ) {
                    switch ($section) {
                        case gradingform_enhancedrubric_controller::INITIAL:
                            return false;
                        case $section == gradingform_enhancedrubric_controller::SECOND:
                            $secondempty++;
                            break;
                        case $section == gradingform_enhancedrubric_controller::FINAL:
                            $finalempty++;
                            break;
                    }
                }
            }
            // If some criteria have been graded but not all of them have, then we return false.
            // This is to allow multiple markers to grade different sections at different times
            // and thereby allow some sections to remain ungraded when the form is saved. This prevents
            // partially graded sections from being submitted and also prevents clearing existing
            // graded sections.
            switch ($section) {
                case $cangradesecond && !$cangradefinal && $section == gradingform_enhancedrubric_controller::SECOND:
                    if ($secondempty > 0 && $secondempty < count($criteria)) {
                        return false;
                    }
                    break;
                // The second section cannot be empty if the third section is not empty.
                case $cangradefinal && $section == gradingform_enhancedrubric_controller::SECOND:
                    if ($finalempty < count($criteria) && $secondempty > 0 && $secondempty < count($criteria)) {
                        return false;
                    }
                    break;
                case $cangradefinal && $section == gradingform_enhancedrubric_controller::FINAL:
                    if ($finalempty > 0 && $finalempty < count($criteria)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Retrieves from DB and returns the data how this enhanced rubric was filled
     *
     * @param boolean $force whether to force DB query even if the data is cached
     * @return array
     */
    public function get_enhancedrubric_filling($force = false, $section = null) {
        global $DB;

        $params = ['instanceid' => $this->get_id()];
        if ($section) {
            $params['section'] = $section;
        }

        if ($this->enhancedrubric === null || $force) {
            $records = $DB->get_records('enhancedrubric_fillings', $params);
            $this->enhancedrubric = ['section' => []];

            // Structure required by gradingform_rubric\grades\grader\gradingpanel\external\fetch.
            foreach ($this->get_enhancedrubric_sections() as $section) {
                $this->enhancedrubric['section'][$section] = ['criteria' => []];
            }

            if ($records) {
                $this->enhancedrubric = ['section' => []];
                foreach ($records as $record) {
                    $this->enhancedrubric['section'][$record->section]['criteria'][$record->criterionid] = (array)$record;
                }
            }
        }

        return $this->enhancedrubric;
    }

    /**
     * Updates the instance with the data received from grading form. This function may be
     * called via AJAX when grading is not yet completed, so it does not change the
     * status of the instance.
     *
     * @param array $data
     */
    public function update($data) {
        global $DB;
        $currentgrades = $this->get_enhancedrubric_filling();
        parent::update($data);

        if (isset($data['workflowstate'])) {
            $this->workflowstatus = $data['workflowstate'];
            if ($data['workflowstate'] == ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                $this->gradereleased = true;
            }
        }

        foreach ($data['section'] as $section => $criteria) {
            $currentgrade = $currentgrades['section'][$section] ?? false;
            foreach ($criteria['criteria'] as $criterionid => $record) {
                    if (!$currentgrade || !array_key_exists($criterionid, $currentgrade['criteria'])) {
                        $newrecord = [
                            'instanceid' => $this->get_id(),
                            'criterionid' => $criterionid,
                            'levelid' => $record['levelid'] ?? null,
                            'remarkformat' => FORMAT_MOODLE,
                            'section' => $section,
                        ];
                        if (isset($record['remark'])) {
                            $newrecord['remark'] = $record['remark'];
                        }
                        $DB->insert_record('enhancedrubric_fillings', $newrecord);
                    } else {
                        $newrecord = ['id' => $currentgrade['criteria'][$criterionid]['id']];
                        foreach (['levelid', 'remark'/*, 'remarkformat' */] as $key) {
                            // TODO MDL-31235 format is not supported yet
                            if (isset($record[$key]) && $currentgrade['criteria'][$criterionid][$key] != $record[$key]) {
                                $newrecord[$key] = $record[$key];
                            }
                        }
                        if (count($newrecord) > 1) {
                            $DB->update_record('enhancedrubric_fillings', $newrecord);
                        }
                    }
            }
        }

        foreach ($data['section'] as $criteria) {
            foreach ($criteria['criteria'] as $criterionid => $record) {
                if (!array_key_exists($criterionid, $criteria['criteria'])) {
                    $DB->delete_records('enhancedrubric_fillings', ['id' => $record['id']]);
                }
            }
        }
        $this->get_enhancedrubric_filling();

        // Update internal comments.
        if (isset($data['internalcomment'])) {
            if (!$internalcomments = $DB->get_record('enhancedrubric_intcomments', ['instanceid' => $this->get_id()])) {
                $internalcomments = new stdClass();
                $internalcomments->instanceid = $this->get_id();
                $internalcomments->internalcomment = $data['internalcomment']['text'];
                $internalcomments->internalcommentformat = $data['internalcomment']['format'];
                $DB->insert_record('enhancedrubric_intcomments', $internalcomments);
            } else {
                $internalcomments->internalcomment = $data['internalcomment']['text'];
                $internalcomments->internalcommentformat = $data['internalcomment']['format'];
                $DB->update_record('enhancedrubric_intcomments', $internalcomments);
            }
        }
    }

    /**
     * Calculates the grade to be pushed to the gradebook
     *
     * @return float|int the valid grade from $this->get_controller()->get_grade_range()
     */
    public function get_grade() {
        global $DB;

        $graderange = array_keys($this->get_controller()->get_grade_range());
        if (empty($graderange)) {
            return -1;
        }

        // The grade is being saved so write the history.
        $definitionid = $this->get_data('definitionid');
        $instanceid = $this->get_data('id');
        $raterid = $this->get_data('raterid');
        $itemid = $this->get_data('itemid');

        $history = $DB->get_record(
            'enhancedrubric_history',
            ['definitionid' => $definitionid, 'instanceid' => $instanceid, 'raterid' => $raterid, 'itemid' => $itemid]
        );

        if ($history) {
            $history->workflowstatus = $this->workflowstatus ?: 'notmarked';
            $history->timemodified = time();
            $DB->update_record('enhancedrubric_history', $history);
        } else {
            $history = new stdClass();
            $history->definitionid = $this->get_data('definitionid');
            $history->instanceid = $this->get_data('id');
            $history->raterid = $this->get_data('raterid');
            $history->itemid = $this->get_data('itemid');
            $history->rawgrade = null;
            $history->status = $this->get_data('status');
            $history->workflowstatus = $this->workflowstatus ?: 'notmarked';
            $history->feedback = $this->get_data('feedback');
            $history->feedbackformat = $this->get_data('feedbackformat');
            $history->timemodified = $this->get_data('timemodified');
            $history->id = $DB->insert_record('enhancedrubric_history', $history);
        }

        $sections = $this->get_enhancedrubric_sections();
        // If there is more than one section then marking workflow must be enabled.
        // If the grade has not been released then use the intitial fillings to
        // calculate it.
        // If the grade has been released then use the last fillings completed
        // to calculate it.
        if (count($sections) == 1) {
            $section = array_pop($sections);
        } else if (!$this->gradereleased) {
            $section = gradingform_enhancedrubric_controller::INITIAL;
        } else {
            $section = $this->get_last_section_filled();
        }
        $grade = $this->get_grade_for_section($section);
        $allowdecimals = $this->get_controller()->get_allow_grade_decimals();

        // Update history record with calculated grade.
        $history->rawgrade = $grade;
        $DB->update_record('enhancedrubric_history', $history);

        return ($allowdecimals ? $grade : round($grade, 0));
    }

    /**
     * Calculates the what the grade would be after marking workflow is set to 'released'
     *
     * @param int $section section number (1, 2 or 3)
     * @param bool $force whether or not to force a DB query
     * @return float|int the valid grade from $this->get_controller()->get_grade_range()
     */
    public function get_grade_for_section(int $section, bool $force = true) {

        $minscores = $this->get_controller()->get_min_required_scores();
        $grade = $this->get_enhancedrubric_filling($force, $section);
        $graderange = array_keys($this->get_controller()->get_grade_range());

        if (empty($graderange)) {
            return -1;
        }

        sort($graderange);
        $mingrade = $graderange[0];
        $maxgrade = $graderange[count($graderange) - 1];
        $curscore = 0;
        $meetsallminscores = true;

        foreach ($grade['section'][$section]['criteria'] as $id => $record) {
            $score = $this->get_controller()->get_definition()->enhancedrubric_criteria[$id]['levels'][$record['levelid']]['score'];
            if ($score < $minscores[$id]) {
                $meetsallminscores = false;
            }
            $curscore += $score;
        }

        $options = $this->get_controller()->get_options();
        $mintotalscore = $options['mintotal'];

        if ($meetsallminscores && $curscore >= $mintotalscore) {
            return $maxgrade;
        } else {
            return $mingrade;
        }
    }

    /**
     * Gets the grade for the last section graded
     *
     * @return float|int the valid grade from $this->get_controller()->get_grade_range()
     */
    public function get_grade_for_last_section() {
        $sections = $this->get_enhancedrubric_sections();
        // If there is more than one section then marking workflow must be enabled.
        // If the grade has not been released then use the intitial fillings to
        // calculate it.
        // If the grade has been released then use the last fillings completed
        // to calculate it.
        if (count($sections) == 1) {
            $section = array_pop($sections);
        } else {
            $section = $this->get_last_section_filled();
        }

        $grade = $this->get_grade_for_section($section, false);
        $allowdecimals = $this->get_controller()->get_allow_grade_decimals();

        return ($allowdecimals ? $grade : round($grade, 0));
    }

    /**
     * Returns html for form element of type 'grading'.
     *
     * @param moodle_page $page
     * @param MoodleQuickForm_grading $gradingformelement
     * @return string
     */
    public function render_grading_element($page, $gradingformelement) {
        global $USER, $COURSE;

        $context = $this->get_controller()->get_context();
        $cangrade = has_capability('mod/assign:grade', $context);
        $cangradesecond =
            has_capability('mod/assign:reviewgrades', $context) ||
            has_capability('mod/assign:releasegrades', $context) ||
            has_capability('mod/assign:managegrades', $context);
        $cangradefinal =
            has_capability('mod/assign:releasegrades', $context) ||
            has_capability('mod/assign:managegrades', $context);
        $canviewhistory = has_capability('gradingform/enhancedrubric:viewhistory', $context);
        $canviewinternal = has_capability('gradingform/enhancedrubric:viewinternalfeedback', $context);
        $canaddinternal = has_capability('gradingform/enhancedrubric:addinternalfeedback', $context);

        if (!$gradingformelement->_flagFrozen) {
            $module = array('name'=>'gradingform_enhancedrubric', 'fullpath'=>'/grade/grading/form/enhancedrubric/js/enhancedrubric.js');
            $page->requires->js_init_call('M.gradingform_enhancedrubric.init', array(array('name' => $gradingformelement->getName())), true, $module);
            $mode = gradingform_enhancedrubric_controller::DISPLAY_EVAL;
        } else if ($gradingformelement->_persistantFreeze) {
            $mode = gradingform_enhancedrubric_controller::DISPLAY_EVAL_FROZEN;
        } else {
            $mode = gradingform_enhancedrubric_controller::DISPLAY_REVIEW;
        }
        $criteria = $this->get_controller()->get_definition()->enhancedrubric_criteria;
        $options = $this->get_controller()->get_options();
        $values = $gradingformelement->getValue();

        $html = '';
        if ($values === null) {
            $values = $this->get_enhancedrubric_filling();
        } else if (!$this->validate_grading_element($values)) {
            $html .= html_writer::tag('div', get_string('rubricnotcompleted', 'gradingform_enhancedrubric'), array('class' => 'gradingform_enhancedrubric-error'));
        }

        $currentinstance = $this->get_current_instance();
        if ($currentinstance && $currentinstance->get_status() == gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
            $html .= html_writer::div(get_string('needregrademessage', 'gradingform_enhancedrubric'), 'gradingform_enhancedrubric-regrade',
                                      array('role' => 'alert'));
        }
        $haschanges = false;
        $criteriahtml = '';
        if ($currentinstance) {
            $curfillings = $currentinstance->get_enhancedrubric_filling();

            foreach ($this->sections as $section) {
                if (isset($curfillings['section'], $curfillings['section'][$section]) && $curfillings) {
                    $value = $values['section'][$section];
                    $curfilling = $curfillings['section'][$section];

                    foreach ($curfilling['criteria'] as $criterionid => $curvalues) {
                        $value['criteria'][$criterionid]['savedlevelid'] = $curvalues['levelid'];
                        $newremark = null;
                        $newlevelid = null;
                        if (isset($value['criteria'][$criterionid]['remark'])) {
                            $newremark = $value['criteria'][$criterionid]['remark'];
                        }
                        if (isset($value['criteria'][$criterionid]['levelid'])) {
                            $newlevelid = $value['criteria'][$criterionid]['levelid'];
                        }
                        if ($newlevelid != $curvalues['levelid'] || $newremark != $curvalues['remark']) {
                            $haschanges = true;
                        }
                    }
                    $criteriahtml .= $this->get_controller()->get_renderer($page)->display_enhancedrubric(
                        $criteria,
                        $options,
                        $mode,
                        $gradingformelement->getName(),
                        $section,
                        $value,
                        $currentinstance,
                        $canviewhistory,
                        $cangrade,
                        $cangradesecond,
                        $cangradefinal
                    );
                } else {
                    $criteriahtml .= $this->get_controller()->get_renderer($page)->display_enhancedrubric(
                        $criteria,
                        $options,
                        $mode,
                        $gradingformelement->getName(),
                        $section,
                        null,
                        $currentinstance,
                        $canviewhistory,
                        $cangrade,
                        $cangradesecond,
                        $cangradefinal
                    );
                }
            }
        } else {
            foreach ($this->sections as $section) {
                if (isset($values['section'], $values['section'][$section]) && $values) {
                    $value = $values['section'][$section];
                    $criteriahtml .= $this->get_controller()->get_renderer($page)->display_enhancedrubric(
                        $criteria,
                        $options,
                        $mode,
                        $gradingformelement->getName(),
                        $section,
                        $value,
                        $this,
                        $canviewhistory,
                        $cangrade,
                        $cangradesecond,
                        $cangradefinal
                    );
                } else {
                    $criteriahtml .= $this->get_controller()->get_renderer($page)->display_enhancedrubric(
                        $criteria,
                        $options,
                        $mode,
                        $gradingformelement->getName(),
                        $section,
                        null,
                        $this,
                        $canviewhistory,
                        $cangrade,
                        $cangradesecond,
                        $cangradefinal
                    );
                }
            }
        }

        if ($this->get_data('isrestored') && $haschanges) {
            $html .= html_writer::tag('div', get_string('restoredfromdraft', 'gradingform_enhancedrubric'), array('class' => 'gradingform_enhancedrubric-restored'));
        }
        if (!empty($options['showdescriptionteacher'])) {
            $html .= html_writer::tag('div', $this->get_controller()->get_formatted_description(), array('class' => 'gradingform_enhancedrubric-description'));
        }

        $html .= $criteriahtml;

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $COURSE->maxbytes,
            'trust' => false,
            'context' => $context,
            'noclean' => true,
            'id' => 'id_internalcomment'
        ];
        $internalcomments = $this->get_internal_comments($editoroptions, $context, $currentinstance);
        // Add internal comments editor.
        $html .= $this->get_controller()->get_renderer($page)->render_internal_comments(
            $mode,
            $options,
            $canaddinternal,
            $canviewinternal,
            $internalcomments,
            $editoroptions
        );

        return $html;
    }

    /**
     * Retrieves from DB and returns the data how this grading form was graded
     *
     * @param boolean $force whether to force DB query even if the data is cached
     * @return array
     */
    public function get_enhancedrubric_history($force = false) {
        global $DB;

        $params = ['itemid' => $this->get_data('itemid'), 'instanceid' => $this->get_id()];

        if ($this->enhancedrubrichistory === null || $force) {
            $records = $DB->get_records('enhancedrubric_history', $params);
            $this->enhancedrubrichistory = [];
            foreach ($records as $record) {
                $this->enhancedrubrichistory[$record->id] = (array)$record;
            }
        }
        return $this->enhancedrubrichistory;
    }

    /**
     * Checks if there is any history saved
     * @return bool
     * @throws dml_exception
     */
    public function has_history() {
        global $DB;

        if ($this->hasarchiveinstances === null) {
            $params = ['itemid' => $this->get_data('itemid')];
            $total = $DB->count_records('enhancedrubric_history', $params);
            if ($total > 1) {
                $this->hasarchiveinstances = true;
            }
        }

        return $this->hasarchiveinstances;
    }

    /**
     * Returns the array of sections used
     *
     * @return array
     */
    public function get_enhancedrubric_sections() {
        return $this->sections;
    }

    /**
     * Returns the marking workflow status if mwf enabled
     *
     * @return mixed|null
     */
    public function get_enhancedrubric_workflowstatus() {
        return $this->workflowstatus;
    }

    /**
     * Sets the marking workflow status if mwf enabled
     *
     * @param int $workflowstatus
     */
    public function set_enhancedrubric_workflowstatus($workflowstatus) {
        $this->workflowstatus = $workflowstatus;
    }

    /**
     * Get the last section that has fillings.
     *
     * @return int $section
     */
    public function get_last_section_filled(): int {
        global $DB;

        $sql = "SELECT MAX(section) AS section 
                      FROM {enhancedrubric_fillings}
                     WHERE instanceid = :instanceid
                       AND levelid IS NOT NULL
            ";
        $params = ['instanceid' => $this->get_id()];

        if ($record = $DB->get_records_sql($sql, $params)) {
            return (int)array_pop($record)->section;
        }

        return 0;
    }

    public function get_internal_comments($editoroptions, $context, $currentinstance) {
        global $DB;

        if ($currentinstance) {
            if (!$internalcomments = $DB->get_record('enhancedrubric_intcomments', ['instanceid' => $currentinstance->get_id()])) {

                $internalcomments = new stdClass();
                $internalcomments->instanceid = $currentinstance->get_id();
            }
        } else {
            $internalcomments = new stdClass();
        }

        if (!empty($internalcomments->id)) {
            $editoroptions['subdirs'] = file_area_contains_subdirs(
                $context,
                'gradingform_enhancedrubric',
                'internalcomment',
                $internalcomments->id
            );
            return file_prepare_standard_editor(
                $internalcomments,
                'internalcomment',
                $editoroptions,
                $context,
                'gradingform_enhancedrubric',
                'internalcomment',
                $internalcomments->id
            );
        }

        $editoroptions['subdirs'] = false;
        return file_prepare_standard_editor(
            $internalcomments,
            'internalcomment',
            $editoroptions,
            $context,
            'gradingform_enhancedrubric',
            'internalcomment',
            null
        );
    }
}
