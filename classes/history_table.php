<?php
// This file is part of the tool_certificate plugin for Moodle - http://moodle.org/
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
 * Class history_table
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2022 Amanda Doughty, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradingform_enhancedrubric;

use gradingform_enhancedrubric_controller;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class history_table
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2022 Amanda Doughty, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class history_table extends \table_sql {
    /** @var string */
    protected $downloadparamname = 'download';

    /** @var integer the grading instance id */
    protected $instanceid;

    /** @var integer the grading area id */
    protected $areaid;

    /** @var integer the grade item id */
    protected $itemid;

    /** @var array the criteria ids */
    protected $criteria;

    /** @var string|null the marking workflow status */
    protected $markingworkflow;

    /** @var gradingform_enhancedrubric_controller controller */
    protected $controller;

    /**
     * Sets up the table
     * @param int $areaid
     * @param int $itemid
     * @param string|null $markingworkflow
     * @param int|null $instanceid
     * @param gradingform_enhancedrubric_controller|null $controller
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct(
        int $areaid,
        ?int $itemid,
        ?string $markingworkflow,
        ?int $instanceid = null,
        ?gradingform_enhancedrubric_controller $controller = null
    ) {
        global $DB;

        parent::__construct('gradingform-enhancedrubric-history');
        $this->attributes['class'] = 'gradingform-enhancedrubric-history';
        $this->areaid = $areaid;
        $this->instanceid = $instanceid;
        $this->markingworkflow = $markingworkflow;
        $this->itemid = $itemid;
        $this->controller = $controller;

        $sql = "SELECT ec.id as criterionid
                      FROM {enhancedrubric_criteria} ec
                      JOIN {grading_definitions} gd
                        ON gd.id = ec.definitionid
                       AND gd.areaid = :areaid";
        $params = ['areaid' => $this->areaid];
        $this->criteria = $DB->get_records_sql($sql, $params);

        $filename = format_string('gradingform-enhancedrubric-history');
        $this->is_downloading(optional_param($this->downloadparamname, 0, PARAM_ALPHA),
        $filename, get_string('enhancedrubrichistory', 'gradingform_enhancedrubric'));

        if (!$this->is_downloading()) {
            $columnsheaders = [];

            if (!$this->itemid) {
                $columnsheaders += [
                    'learner_firstname' => get_string('learnerfirstname', 'gradingform_enhancedrubric'),
                    'learner_lastname' => get_string('learnerlastname', 'gradingform_enhancedrubric'),
                ];
            }

            $columnsheaders += [
                'grader_firstname' => get_string('graderfirstname', 'gradingform_enhancedrubric'),
                'grader_lastname' => get_string('gradersurname', 'gradingform_enhancedrubric'),
                'timemodified' => get_string('updatedate', 'gradingform_enhancedrubric'),
                'rawgrade' => get_string('grade', 'gradingform_enhancedrubric'),
                'workflowstatus' => get_string('markingstage', 'gradingform_enhancedrubric'),
                'actions' => \html_writer::span(get_string('actions'), 'sr-only'),
            ];
        } else {
            $columnsheaders = [
                'learner_firstname' => get_string('learnerfirstname', 'gradingform_enhancedrubric'),
                'learner_lastname' => get_string('learnerlastname', 'gradingform_enhancedrubric'),
            ];

            $userfields = \core_user\fields::get_identity_fields(null);

            foreach ($userfields as $userfield) {
                $columnsheaders += [$userfield => get_string($userfield, 'core')];
            }

            $columnsheaders += [
                'grader_firstname' => get_string('graderfirstname', 'gradingform_enhancedrubric'),
                'grader_lastname' => get_string('gradersurname', 'gradingform_enhancedrubric'),
                'timemodified' => get_string('updatedate', 'gradingform_enhancedrubric'),
                'rawgrade' => get_string('grade', 'gradingform_enhancedrubric'),
            ];

            $i = 0;

            foreach (\gradingform_enhancedrubric_controller::SECTIONS[$this->markingworkflow] as $section) {
                $prefix = get_string('sectionheader' . $section, 'gradingform_enhancedrubric');
                foreach ($this->criteria as $ignored) {
                    $columnsheaders += ["description$i" => $prefix . ' ' . get_string('criteria', 'gradingform_enhancedrubric')];
                    $columnsheaders += ["definition$i" => $prefix . ' ' . get_string('definition', 'gradingform_enhancedrubric')];
                    $columnsheaders += ["score$i" => $prefix . ' ' . get_string('score', 'gradingform_enhancedrubric')];
                    $columnsheaders += ["remark$i" => $prefix . ' ' . get_string('remark', 'gradingform_enhancedrubric')];
                    $i++;
                }
            }
        }

        $this->define_columns(array_keys($columnsheaders));
        $this->define_headers(array_values($columnsheaders));
        $this->collapsible(false);
        $this->sortable(true, 'timemodified', SORT_DESC);
        $this->no_sorting('actions');
        $this->pagesize = 10;
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
        $this->column_class('actions', 'text-right');
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_fullname($row): string {
        return fullname($row);
    }

    /**
     * Generate the grader fullname column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_grader_fullname($row): string {
        $user = new \stdClass();
        foreach ($row as $property => $value) {
            $property = str_replace('grader_', '', $property);
            $user->$property = $value;
        }
        return fullname($user);
    }

    /**
     * Generate the learner fullname column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_learner_fullname($row): string {
        $user = new \stdClass();
        foreach ($row as $property => $value) {
            $property = str_replace('learner_', '', $property);
            $user->$property = $value;
        }
        return fullname($user);
    }

    /**
     * Generate the timemodified column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_timemodified($row): string {
        return userdate($row->timemodified);
    }

    /**
     * Generate the rawgrade column.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_rawgrade($row): ?string {
        if (!$gradinginstance = $this->controller->get_or_create_instance($row->instanceid, $row->userid, $this->itemid)) {
            return '-';
        }
        $grade = (int)$gradinginstance->get_grade_for_last_section();

        if ($grade === -1) {
            return '-';
        }

        if (!empty($this->scaleoptions)) {
            // This is a scale - we need to convert any grades to indexes in the scale.
            return $this->scaleoptions[$grade] ?? null;
        }

        return unformat_float($grade);
    }

    /**
     * Generate the workflowstatus column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_workflowstatus($row): string {
        if ($row->workflowstatus) {
            return get_string('markingworkflowstate' . $row->workflowstatus, 'mod_assign');
        }
        return $row->workflowstatus;
    }

    /**
     * Generate the actions column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_actions($row): string {
        global $OUTPUT;

        // View fillings history.
        $url = new \moodle_url('/grade/grading/form/enhancedrubric/history_detail.php');
        $url->params([
            'areaid' => $this->areaid,
            'instanceid' => $row->instanceid,
            'itemid' => $this->itemid,
            'raterid' => $row->raterid,
            'sesskey' => sesskey()
        ]);
        $pix = new \pix_icon('i/search', get_string('viewdetail', 'gradingform_enhancedrubric'));
        return $OUTPUT->action_link($url, $pix);
    }

    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     * @uses \tool_certificate\certificate
     */
    public function query_db($pagesize, $useinitialsbar = false) {
        global $DB;

        $params = ['itemid' => $this->itemid];
        $total = $DB->count_records('enhancedrubric_history', $params);
        $userfields1 = \core_user\fields::for_name()->get_sql('u1', false, 'grader_', '', false)->selects;
        $userfields2 = \core_user\fields::for_name()->get_sql('u2', false, 'learner_', '', false)->selects;
        $useridfields = \core_user\fields::for_identity(null)->get_sql('u2', false, '', '', false)->selects;
        $fillingsjoins = [];
        $fillingsfields = [];
        $i = 0;

        foreach (\gradingform_enhancedrubric_controller::SECTIONS[$this->markingworkflow] as $section) {
            foreach ($this->criteria as $criterion) {
                $fillingsfields[] = "q$i.description as description$i, q$i.definition as definition$i, q$i.score as score$i, q$i.remark as remark$i, q$i.section as section$i";
                $fillingsjoins[] = "LEFT JOIN (SELECT efh.id, efh.instanceid, efh.criterionid, ec.description, el.definition, el.score, efh.remark, efh.section
                                             FROM {enhancedrubric_fillings} efh
                                             JOIN {enhancedrubric_criteria} ec
                                               ON ec.id = efh.criterionid
                                             JOIN {enhancedrubric_levels} el
                                               ON el.id = efh.levelid) q$i
                                               ON eh.instanceid = q$i.instanceid
                                              AND q$i.criterionid = $criterion->criterionid
                                              AND q$i.section = $section";
                $i++;
            }
        }

        $fillingsfields = implode(",", $fillingsfields);
        $fillingsjoins = implode("\n", $fillingsjoins);
        $where = $this->itemid ? ' AND eh.itemid = ' . $this->itemid : '';
        $where .= $this->instanceid ? ' AND eh.instanceid = ' . $this->instanceid : '';

        $sql = "SELECT eh.id as eh_id,
                       q0.id,
                       eh.timemodified,
                       eh.workflowstatus,
                       eh.raterid,
                       eh.instanceid,
                       eh.itemid,
                       u1.id as userid,
                       u1.firstname as grader_firstname,
                       u1.lastname as grader_lastname, " .
                       $userfields1 . ",
                       u2.id as learner_id,
                       u2.firstname as learner_firstname,
                       u2.lastname as learner_lastname, " .
                       $userfields2 . "," . $useridfields . ",
                       eh.timemodified,
                       eh.rawgrade, " .
                       $fillingsfields . "
                  FROM {enhancedrubric_history} eh
                  JOIN {assign_grades} ag
                    ON ag.id = eh.itemid
                  JOIN {user} u1
                    ON (u1.id = eh.raterid)
                  JOIN {user} u2
                    ON (u2.id = ag.userid)
                  $fillingsjoins
                  WHERE 1 = 1
                 $where
              ORDER BY {$this->get_sql_sort()}";

        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $params, $this->get_page_start(),
                $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $params);
        }

        $this->pagesize($pagesize, $total);
    }

    /**
     * Download the data.
     *
     */
    public function download() {
        global $DB;

        \core\session\manager::write_close();
        $params = ['itemid' => $this->itemid];
        $total = $DB->count_records('enhancedrubric_history', $params);
        $this->out($total, false);
        exit;
    }
}
