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

use core_user\fields;
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
class all_users_rubrics_table extends \table_sql {
    /** @var string */
    protected $downloadparamname = 'download';

    /** @var array the criteria ids */
    protected $criteria;

    /** @var integer the grading area id */
    protected $areaid;

    /** @var integer the grade item id */
    protected $itemid;

    /** @var string|null the marking workflow status */
    protected $markingworkflow;

    /** @var integer the grade item id */
    protected $context;

    /** @var bool the grade item id */
    protected $commentsenabled;

    /** @var array the criteria ids */
    protected $scaleoptions;

    /** @var integer the grading instance id */
    protected $instanceid;

    /** @var gradingform_enhancedrubric_controller controller */
    protected $controller;

    /**
     * Sets up the table
     * @param int $areaid
     * @param int|null $definitionid
     * @param int|null $assignid
     * @param int|null $itemid
     * @param string|null $markingworkflow
     * @param \context $context
     * @param bool $commentsenabled
     * @param array|null $scaleoptions
     * @param int|null $instanceid
     * @param gradingform_enhancedrubric_controller|null $controller
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct(
        int $areaid,
        ?int $definitionid,
        ?int $assignid,
        ?int $itemid,
        ?string $markingworkflow,
        \context $context,
        bool $commentsenabled,
        ?array $scaleoptions,
        ?int $instanceid = null,
        ?gradingform_enhancedrubric_controller $controller = null
    ) {
        global $DB;

        parent::__construct('gradingform-enhancedrubric-history');
        $this->attributes['class'] = 'gradingform-enhancedrubric-history';
        $this->areaid = $areaid;
        $this->definitionid = $definitionid;
        $this->assignid = $assignid;
        $this->instanceid = $instanceid;
        $this->markingworkflow = $markingworkflow;
        $this->context = $context;
        $this->commentsenabled = $commentsenabled;
        $this->scaleoptions = $scaleoptions;
        $this->itemid = $itemid;
        $this->controller = $controller;

        $sql = "SELECT ec.id as criterionid
                      FROM {enhancedrubric_criteria} ec
                      JOIN {grading_definitions} gd
                        ON gd.id = ec.definitionid
                       AND gd.areaid = :areaid";
        $params = ['areaid' => $this->areaid, 'itemid' => $this->itemid];
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
                'grader_lastname' => get_string('graderlastname', 'gradingform_enhancedrubric'),
                'timemodified' => get_string('updatedate', 'gradingform_enhancedrubric'),
                'grade' => get_string('grade', 'gradingform_enhancedrubric'),
                'workflowstatus' => get_string('markingstage', 'gradingform_enhancedrubric'),
                'actions' => \html_writer::span(get_string('actions'), 'sr-only'),
            ];
        } else {
            $columnsheaders = [
                'learner_id' => get_string('learnerid', 'gradingform_enhancedrubric'),
                'learner_firstname' => get_string('learnerfirstname', 'gradingform_enhancedrubric'),
                'learner_lastname' => get_string('learnerlastname', 'gradingform_enhancedrubric'),
            ];

            $userfields = fields::get_identity_fields(null);

            foreach ($userfields as $userfield) {
                $columnsheaders += [$userfield => get_string($userfield, 'core')];
            }

            if ($this->markingworkflow) {
                $columnsheaders += [
                    'workflowstatus' => get_string('markingstage', 'gradingform_enhancedrubric')
                ];
            }

            $columnsheaders += [
                'timemodified' => get_string('updatedate', 'gradingform_enhancedrubric'),
                'total_score' => get_string('score', 'gradingform_enhancedrubric'),
                'grade' => get_string('grade', 'gradingform_enhancedrubric'),
            ];

            $i = 0;

            foreach (gradingform_enhancedrubric_controller::SECTIONS[$this->markingworkflow] as $section) {
                $nameprefix = !$this->markingworkflow ? '' : get_string('sectionheader' . $section, 'gradingform_enhancedrubric');
                $identifier = !$this->markingworkflow ? 'sectionheader' : 'sectionheader' . $section;
                $critprefix = get_string($identifier, 'gradingform_enhancedrubric');
                $columnsheaders += ["grader_id$section" => $nameprefix . get_string('graderid', 'gradingform_enhancedrubric')];
                $columnsheaders += ["grader_firstname$section" => $nameprefix . get_string('graderfirstname', 'gradingform_enhancedrubric')];
                $columnsheaders += ["grader_lastname$section" => $nameprefix . get_string('graderlastname', 'gradingform_enhancedrubric')];
                foreach ($this->criteria as $ignored) {
                    $columnsheaders += ["description$i" => $critprefix . get_string('criteria', 'gradingform_enhancedrubric')];
                    $columnsheaders += ["definition$i" => $critprefix . get_string('definition', 'gradingform_enhancedrubric')];
                    $columnsheaders += ["score$i" => $critprefix . get_string('score', 'gradingform_enhancedrubric')];
                    $columnsheaders += ["remark$i" => $critprefix . get_string('remark', 'gradingform_enhancedrubric')];
                    $i++;
                }
            }

            if ($this->commentsenabled) {
                $columnsheaders += [
                    'commenttext' => get_string('feedback', 'gradingform_enhancedrubric'),
                ];
            }

            $columnsheaders += [
                'internalcomment' => get_string('internalcomments', 'gradingform_enhancedrubric'),
            ];
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
    public function col_grader_fullname(\stdClass $row): string {
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
    public function col_learner_fullname(\stdClass $row): string {
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
     * @return null|string
     */
    public function col_timemodified(\stdClass $row): ?string {
        return $row->timemodified ? userdate($row->timemodified) : null;
    }

    /**
     * Generate the grade column.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grade($row): ?string {
        if (!$gradinginstance = $this->controller->get_current_instance($row->learner_id, $row->itemid)) {
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
     * Generate the score based on whether grades have been released or not.
     *
     * @param \stdClass $row
     * @return null|int
     */
    public function col_total_score(\stdClass $row): ?int {
        return $row->total_score ? round($row->total_score, 2) : null;
    }

    /**
     * Generate the workflowstatus column.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_workflowstatus($row): ?string {
        if ($row->workflowstatus) {
            return get_string('markingworkflowstate' . $row->workflowstatus, 'mod_assign');
        }
        return $row->workflowstatus;
    }

    /**
     * Generate the internal comment column.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_internalcomment(\stdClass $row): ?string {
        if ($row->internalcomment) {
            return html_entity_decode($this->format_text($row->internalcomment));
        }
        return null;
    }

    /**
     * Generate the feedback column.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_commenttext(\stdClass $row): ?string {
        if ($row->commenttext) {
            return html_entity_decode($this->format_text($row->commenttext));
        }
        return null;
    }

    /**
     * Generate the grader firstname column for section 1.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grader_firstname1(\stdClass $row): ?string {
        if ($this->section_graded($row, 1)) {
            return $row->grader_firstname1;
        }
        return null;
    }

    /**
     * Generate the grader lastname column for section 1.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grader_lastname1(\stdClass $row): ?string {
        if ($this->section_graded($row, 1)) {
            return $row->grader_lastname1;
        }
        return null;
    }

    /**
     * Generate the grader firstname column for section 2.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grader_firstname2(\stdClass $row): ?string {
        if ($this->section_graded($row, 2)) {
            return $row->grader_firstname2;
        }
        return null;
    }

    /**
     * Generate the grader lastname column for section 2.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grader_lastname2(\stdClass $row): ?string {
        if ($this->section_graded($row, 2)) {
            return $row->grader_lastname2;
        }
        return null;
    }

    /**
     * Generate the grader firstname column for section 3.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grader_firstname3(\stdClass $row): ?string {
        if ($this->section_graded($row, 3)) {
            return $row->grader_firstname3;
        }
        return null;
    }

    /**
     * Generate the grader lastname column for section 3.
     *
     * @param \stdClass $row
     * @return null|string
     */
    public function col_grader_lastname3(\stdClass $row): ?string {
        if ($this->section_graded($row, 3)) {
            return $row->grader_lastname3;
        }
        return null;
    }

    /**
     * Generate the actions column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_actions(\stdClass $row): string {
        global $OUTPUT;

        if (!$row->itemid) {
            return '';
        }
        
        // View fillings history.
        $url = new \moodle_url('/grade/grading/form/enhancedrubric/all_users_history_detail.php');
        $url->params([
            'areaid' => $this->areaid,
            'instanceid' => $row->instanceid,
            'itemid' => $row->itemid,
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
     */
    public function query_db($pagesize, $useinitialsbar = false) {
        global $DB;

        $sql = "SELECT COUNT(DISTINCT itemid) 
                FROM {enhancedrubric_history} 
                WHERE definitionid = :definitionid";
        $params = ['definitionid' => $this->definitionid];
        $total = $DB->count_records_sql($sql, $params);

        $params = ['assignid' => $this->assignid];
        $userfields1 = fields::for_name()->get_sql('grader_section3', false, 'grader_', '', false)->selects;
        $userfields2 = fields::for_name()->get_sql('learner', false, 'learner_', '', false)->selects;
        $useridfields = fields::for_identity(null)->get_sql('learner', false, '', '', false)->selects;

        [$usql, $uparams] = get_enrolled_sql($this->context, 'mod/assign:submit');
        $params += $uparams;

        $raterjoins = $this->rater_by_permission_subqueries();
        $raterjoins = implode("\n", $raterjoins);

        [$fillingsfields, $fillingsjoins, $fillingssum] = $this->filling_subqueries();
        $fillingsfields = implode(",", $fillingsfields);
        $fillingsjoins = implode("\n", $fillingsjoins);

        $where = $this->itemid ? ' AND eh.itemid = ' . $this->itemid : '';

        $sql = "SELECT learner.id as learner_id,
                       fillings0.id as efhid,
                       learner.firstname as learner_firstname,
                       learner.lastname as learner_lastname, " .
                       $userfields2 . "," . $useridfields . ",
                       ehlatest.workflowstatus as workflowstatus, 
                       ehlatest.raterid,
                       grader_section3.idnumber as userid,
                       grader_section3.firstname as grader_firstname,
                       grader_section3.lastname as grader_lastname, " .
                       $userfields1 . ",
                       ehlatest.timemodified,
                       ehlatest.instanceid,
                       ehlatest.itemid,
                       ag.grade,
                       af.commenttext,
                       ehc.internalcomment,
                       ehc.internalcommentformat," .
                       $fillingssum  . "," .
                       $fillingsfields . "
                  FROM {user} learner
                  JOIN ($usql) je ON je.id = learner.id
             LEFT JOIN {assign_grades} ag ON learner.id = ag.userid AND ag.assignment = :assignid
             LEFT JOIN {assignfeedback_comments} af ON af.grade = ag.id
             LEFT JOIN (SELECT eh.*
                          FROM {enhancedrubric_history} eh
                          JOIN (SELECT max(id) AS id
                                  FROM {enhancedrubric_history}
                              GROUP BY itemid
                                ) meh ON meh.id = eh.id
                        ) ehlatest ON ag.id = ehlatest.itemid
             $raterjoins
             LEFT JOIN {enhancedrubric_intcomments} ehc ON ehc.instanceid = ehlatest.instanceid
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
     * Get the SQL for latest instances of each section
     *
     * @param bool $bypermission if true then get the ratings given by users with initial, second, final grading permissions
     * @return array
     */
    protected function filling_subqueries($bypermission = false): array {
        $sections = gradingform_enhancedrubric_controller::SECTIONS[$this->markingworkflow];
        $fillingsjoins = [];
        $fillingsfields = [];
        $fillingssum = [];
        $i = 0;
        foreach ($sections as $section) {
            $jointable = $bypermission ? "eh_section$section" : 'ehlatest';
            $fillingssum[$section] = [];
            $criteriafields = [];
            $gradingfields = "
                grader_section$section.idnumber as grader_id$section,
                grader_section$section.firstname as grader_firstname$section,
                grader_section$section.lastname as grader_lastname$section,";
            foreach ($this->criteria as $criterion) {
                $fillingssum[$section][] = "fillings$i.score";
                $criteriafields[] = "
                fillings$i.description as description$i,
                fillings$i.definition as definition$i,
                fillings$i.score as score$i,
                fillings$i.remark as remark$i,
                fillings$i.section as section$i";
                $fillingsjoins[] = "LEFT JOIN (SELECT ef.id, 
                                                      ef.instanceid,
                                                      ef.criterionid,
                                                      ec.description,
                                                      el.definition,
                                                      el.score,
                                                      ef.remark,
                                                      ef.section
                                                FROM {enhancedrubric_fillings} ef
                                                JOIN {enhancedrubric_criteria} ec ON ec.id = ef.criterionid
                                                JOIN {enhancedrubric_levels} el ON el.id = ef.levelid) fillings$i
                                           ON $jointable.instanceid = fillings$i.instanceid
                                          AND fillings$i.criterionid = $criterion->criterionid
                                          AND fillings$i.section = $section";
                $i++;
            }

            $fillingsfields[] = $gradingfields . implode(',', $criteriafields);
        }

        // If there is more than one section then marking workflow must be enabled.
        // If the grade has not been released then use the intitial fillings to
        // calculate it.
        // If the grade has been released then use the last fillings completed
        // to calculate it.
        if (count($sections) === 1) {
            $sectionforgrade = array_pop($sections);
            $fillingssum = '('.implode('+', $fillingssum[$sectionforgrade]).') AS total_score';
        } else {
            $initialsectionforgrade = gradingform_enhancedrubric_controller::INITIAL;
            $initialfillingssum = '(' . implode('+', $fillingssum[$initialsectionforgrade]) . ') AS initial_score';

            $fillingssums = [];
            foreach ($sections as $sectionforgrade) {
                $fillingssums[] = '(' . implode('+', $fillingssum[$sectionforgrade]) . ')';
            }
            $fillingssums = array_reverse($fillingssums);
            $fillingssum = $initialfillingssum . ', COALESCE(' . implode(',', $fillingssums) . ') AS total_score';
        }

        return [$fillingsfields, $fillingsjoins, $fillingssum];
    }

    /**
     * Generate the joins required to get raters with initial, second, final grading permissions.
     *
     * @return array
     */
    protected function rater_by_permission_subqueries(): array {
        global $DB;

        // Get users with each capability used in enhanced rubric marking.
        [$usql, $uparams] = get_enrolled_sql($this->context, 'mod/assign:grade');
        $cangrade = array_keys($DB->get_records_sql($usql, $uparams));
        [$usql, $uparams] = get_enrolled_sql($this->context, 'mod/assign:reviewgrades');
        $canreview = array_keys($DB->get_records_sql($usql, $uparams));
        [$usql, $uparams] = get_enrolled_sql($this->context, 'mod/assign:releasegrades');
        $canrelease = array_keys($DB->get_records_sql($usql, $uparams));
        [$usql, $uparams] = get_enrolled_sql($this->context, 'mod/assign:managegrades');
        $canmanage = array_keys($DB->get_records_sql($usql, $uparams));

        $initialraters = array_diff($cangrade, $canreview, $canrelease, $canmanage);
        $secondraters = array_diff(array_intersect($cangrade, $canreview), $canrelease, $canmanage);
        $finalraters = array_diff(array_intersect($cangrade, $canreview, $canmanage), $canrelease);
        $raters = [1 => $initialraters, 2 => $secondraters, 3 => $finalraters];

        foreach ($raters as $section => $ratertype) {
            if ($ratertype) {
                $raters[$section] = "LEFT JOIN (SELECT eh.*
                                                     FROM {enhancedrubric_history} eh
                                                     JOIN (SELECT COALESCE(eh_latestwithcaps.id, eh_latest.id) AS id
                                                             FROM (SELECT itemid, MAX(id) AS id
                                                                     FROM {enhancedrubric_history}
                                                                 GROUP BY itemid) eh_latest 
                                                                LEFT JOIN (SELECT itemid,
                                                                                  MAX(id) AS id
                                                                     FROM {enhancedrubric_history}
                                                                    WHERE raterid IN (".implode(',', $ratertype).")
                                                                 GROUP BY itemid) eh_latestwithcaps ON eh_latest.itemid = eh_latestwithcaps.itemid
                                                           ) meh ON meh.id = eh.id
                                                   ) eh_section$section ON ag.id = eh_section$section.itemid
                                     LEFT JOIN {user} grader_section$section ON grader_section$section.id = eh_section$section.raterid";
            } else {
                $raters[$section] = "LEFT JOIN (SELECT eh.*
                                                  FROM {enhancedrubric_history} eh
                                                  JOIN (SELECT max(id) AS id
                                                          FROM {enhancedrubric_history}
                                                      GROUP BY itemid
                                                        ) meh ON meh.id = eh.id
                                                ) eh_section$section ON ag.id = eh_section$section.itemid
                                     LEFT JOIN {user} grader_section$section ON grader_section$section.id = eh_section$section.raterid";
            }
        }

        return $raters;
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

    /**
     * Is there a grading for this section?
     *
     * @param \stdClass $row
     * @param int $section
     * @return bool
     */
    protected function section_graded(\stdClass $row, int $section): bool {
        $firstalias = ($section - 1) * count($this->criteria);
        $lastalias = $firstalias + count($this->criteria) - 1;
        $aliasnums = range($firstalias, $lastalias);
        foreach ($aliasnums as $aliasnum) {
            $alias = "score$aliasnum";
            if ($row->$alias) {
                return true;
            }
        }
        return false;
    }
}
