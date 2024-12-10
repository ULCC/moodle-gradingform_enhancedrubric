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
 * Contains renderer used for displaying enhanced rubric
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2011 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Grading method plugin renderer
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2011 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_enhancedrubric_renderer extends plugin_renderer_base {

    /**
     * This function returns html code for displaying criterion. Depending on $mode it may be the
     * code to edit enhanced rubric, to preview the enhanced rubric, to evaluate somebody or to review the evaluation.
     *
     * This function may be called from display_enhacedrubric() to display the whole enhanced rubric, or it can be
     * called by itself to return a template used by JavaScript to add new empty criteria to the
     * enhanced rubric being designed.
     * In this case it will use macros like {NAME}, {LEVELS}, {CRITERION-id}, etc.
     *
     * When overriding this function it is very important to remember that all elements of html
     * form (in edit or evaluate mode) must have the name $elementname.
     *
     * Also JavaScript relies on the class names of elements and when developer changes them
     * script might stop working.
     *
     * @param int $mode rubric display mode, see {@link gradingform_enhancedrubric_controller}
     * @param array $options display options for this rubric, defaults are: {@link gradingform_enhancedrubric_controller::get_default_options()}
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param array|null $criterion criterion data
     * @param string $levelsstr evaluated templates for this criterion levels
     * @param array|null $value (only in view mode) teacher's feedback on this criterion
     * @param int $section section number (With marking workflow enabled there are 3 sections containing the rubric)
     * @return string
     */
    public function criterion_template($mode, $options, $elementname = '{NAME}', $criterion = null, $levelsstr = '{LEVELS}', $value = null, $section = 1) {
        // TODO MDL-31235 description format, remark format
        if (!is_array($criterion) || !array_key_exists('id', $criterion)) {
            $criterion = array('id' => '{CRITERION-id}', 'description' => '{CRITERION-description}', 'sortorder' => '{CRITERION-sortorder}', 'class' => '{CRITERION-class}');
        } else {
            foreach (array('sortorder', 'description', 'class') as $key) {
                // Set missing array elements to empty strings to avoid warnings.
                if (!array_key_exists($key, $criterion)) {
                    $criterion[$key] = '';
                }
            }
        }

        $sectionstr = '';
        $sectionarr = '';

        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
            // There may be one than one copy of the rubric to grade or display if marking workflow is enabled.
            // We need to be able to distinguish between copies.
            $sectionstr = '-section-{SECTION}';
            $sectionarr = '[section][{SECTION}]';
        }

        $criteriontemplate = html_writer::start_tag('tr', array('class' => 'criterion'. $criterion['class'], 'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}'));

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $criteriontemplate .= html_writer::start_tag('td', array('class' => 'controls'));
            foreach (array('moveup', 'delete', 'movedown', 'duplicate') as $key) {
                $value = get_string('criterion'.$key, 'gradingform_enhancedrubric');
                $button = html_writer::empty_tag('input', array('type' => 'submit', 'name' => '{NAME}[criteria][{CRITERION-id}]['.$key.']',
                    'id' => '{NAME}-criteria-{CRITERION-id}-'.$key, 'value' => $value));
                $criteriontemplate .= html_writer::tag('div', $button, array('class' => $key));
            }
            $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                                        'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]',
                                                                        'value' => $criterion['sortorder']));
            $criteriontemplate .= html_writer::end_tag('td'); // .controls

            // Criterion description text area.
            $descriptiontextareaparams = array(
                'name' => '{NAME}[criteria][{CRITERION-id}][description]',
                'id' => '{NAME}-criteria-{CRITERION-id}-description',
                'aria-label' => get_string('criterion', 'gradingform_enhancedrubric', ''),
                'cols' => '10', 'rows' => '5'
            );
            $description = html_writer::tag('textarea', s($criterion['description']), $descriptiontextareaparams);
        } else {
            if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]', 'value' => $criterion['sortorder']));
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][description]', 'value' => $criterion['description']));
            }
            $description = s($criterion['description']);
        }
        $descriptionclass = 'description';
        if (isset($criterion['error_description'])) {
            $descriptionclass .= ' error';
        }

        // Description cell params.
        $descriptiontdparams = array(
            'class' => $descriptionclass,
            'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-description-cell'
        );
        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
            // Set description's cell as tab-focusable.
            $descriptiontdparams['tabindex'] = '0';
            // Set label for the criterion cell.
            $descriptiontdparams['aria-label'] = get_string('criterion', 'gradingform_enhancedrubric', ['section' => $section, 'criterion' => s($criterion['description'])]);
        }

        // Description cell.
        $criteriontemplate .= html_writer::tag('td', $description, $descriptiontdparams);

        // Levels table.
        $levelsrowparams = array('id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels');
        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $levelsrowparams['role'] = 'radiogroup';
        }
        $levelsrow = html_writer::tag('tr', $levelsstr, $levelsrowparams);

        $levelstableparams = array(
            'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels-table',
            'aria-label' => get_string('levelsgroup', 'gradingform_enhancedrubric')
        );
        $levelsstrtable = html_writer::tag('table', $levelsrow, $levelstableparams);
        $levelsclass = 'levels';
        if (isset($criterion['error_levels'])) {
            $levelsclass .= ' error';
        }
        $criteriontemplate .= html_writer::tag('td', $levelsstrtable, array('class' => $levelsclass));
        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('criterionaddlevel', 'gradingform_enhancedrubric');
            $button = html_writer::empty_tag('input', array('type' => 'submit', 'name' => '{NAME}' . $sectionarr . '[criteria][{CRITERION-id}][levels][addlevel]',
                'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels-addlevel', 'value' => $value, 'class' => 'btn btn-secondary'));
            $criteriontemplate .= html_writer::tag('td', $button, array('class' => 'addlevel'));
        }
        $displayremark = ($options['enableremarks'] && ($mode != gradingform_enhancedrubric_controller::DISPLAY_VIEW || $options['showremarksstudent']));
        if ($displayremark) {
            $currentremark = $value['remark'] ?? '';

            // Label for criterion remark.
            $remarkinfo = new stdClass();
            $remarkinfo->description = s($criterion['description']);
            $remarkinfo->remark = $currentremark;
            $remarklabeltext = get_string('criterionremark', 'gradingform_enhancedrubric', $remarkinfo);

            if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL) {
                // HTML parameters for remarks text area.
                $remarkparams = array(
                    'name' => '{NAME}' . $sectionarr . '[criteria][{CRITERION-id}][remark]',
                    'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-remark',
                    'cols' => '10', 'rows' => '3',
                    'aria-label' => $remarklabeltext
                );
                $input = html_writer::tag('textarea', s($currentremark), $remarkparams);
                $criteriontemplate .= html_writer::tag('td', $input, array('class' => 'remark'));
            } else if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL_FROZEN) {
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}' . $sectionarr . '[criteria][{CRITERION-id}][remark]', 'value' => $currentremark));
            } else if (
                $mode == gradingform_enhancedrubric_controller::DISPLAY_REVIEW ||
                $mode == gradingform_enhancedrubric_controller::DISPLAY_VIEW ||
                $mode == gradingform_enhancedrubric_controller::DISPLAY_HISTORY
            ) {
                // HTML parameters for remarks cell.
                $remarkparams = array(
                    'class' => 'remark',
                    'tabindex' => '0',
                    'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-remark',
                    'aria-label' => $remarklabeltext
                );
                $criteriontemplate .= html_writer::tag('td', s($currentremark), $remarkparams);
            }
        }
        $criteriontemplate .= html_writer::end_tag('tr'); // .criterion

        $criteriontemplate = str_replace('{NAME}', $elementname, $criteriontemplate);
        $criteriontemplate = str_replace('{CRITERION-id}', $criterion['id'], $criteriontemplate);
        $criteriontemplate = str_replace('{SECTION}', $section, $criteriontemplate);
        return $criteriontemplate;
    }

    /**
     * This function returns html code for displaying one level of one criterion. Depending on $mode
     * it may be the code to edit rubric, to preview the rubric, to evaluate somebody or to review the evaluation.
     *
     * This function may be called from display_enhancedrubric() to display the whole rubric, or it can be
     * called by itself to return a template used by JavaScript to add new empty level to the
     * criterion during the design of rubric.
     * In this case it will use macros like {NAME}, {CRITERION-id}, {LEVEL-id}, etc.
     *
     * When overriding this function it is very important to remember that all elements of html
     * form (in edit or evaluate mode) must have the name $elementname.
     *
     * Also JavaScript relies on the class names of elements and when developer changes them
     * script might stop working.
     *
     * @param int $mode rubric display mode see {@link gradingform_enhancedrubric_controller}
     * @param array $options display options for this rubric, defaults are: {@link gradingform_enhancedrubric_controller::get_default_options()}
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param string|int $criterionid either id of the nesting criterion or a macro for template
     * @param array|null $level level data, also in view mode it might also have property $level['checked'] whether this level is checked
     * @return string
     */
    public function level_template($mode, $options, $elementname = '{NAME}', $section = 1, $criterionid = '{CRITERION-id}', $level = null) {
        // TODO MDL-31235 definition format
        if (!isset($level['id'])) {
            $level = array('id' => '{LEVEL-id}', 'definition' => '{LEVEL-definition}', 'score' => '{LEVEL-score}', 'min' => '{LEVEL-min}', 'class' => '{LEVEL-class}', 'checked' => false);
        } else {
            foreach (array('score', 'min', 'definition', 'class', 'checked', 'index') as $key) {
                // Set missing array elements to empty strings to avoid warnings.
                if (!array_key_exists($key, $level)) {
                    $level[$key] = '';
                }
            }
        }

        $sectionstr = '';
        $sectionarr = '';

        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
            // There may be one than one copy of the rubric to grade or display if marking workflow is enabled.
            // we need to be able to distinguish between copies.
            $sectionstr = '-section-{SECTION}';
            $sectionarr = '[section][{SECTION}]';
        }

        // Get level index.
        $levelindex = $level['index'] ?? '{LEVEL-index}';

        // Template for one level within one criterion
        $tdattributes = array(
            'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels-{LEVEL-id}',
            'class' => 'level' . $level['class']
        );
        if (isset($level['tdwidth'])) {
            $tdattributes['width'] = round($level['tdwidth']).'%';
        }

        $leveltemplate = html_writer::start_tag('div', array('class' => 'level-wrapper'));
        $minparams = array(
            'type' => 'checkbox',
            'id' => '{NAME}' . $sectionarr . '[criteria][{CRITERION-id}][levels][{LEVEL-id}][min]',
            'name' => '{NAME}' . $sectionarr . '[criteria][{CRITERION-id}][levels][{LEVEL-id}][min]',
            'aria-label' => get_string('mincheckboxforlevel', 'gradingform_enhancedrubric', $levelindex),
        );

        if ($level['min'] == "on" || $level['min'] == 1) {
            $minparams['checked'] = 'checked';
        }

        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $minparams['disabled'] = 'disabled';
        }

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $definitionparams = array(
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][definition]',
                'aria-label' => get_string('leveldefinition', 'gradingform_enhancedrubric', $levelindex),
                'cols' => '10', 'rows' => '4'
            );
            $definition = html_writer::tag('textarea', s($level['definition']), $definitionparams);

            $scoreparams = array(
                'type' => 'text',
                'id' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                'aria-label' => get_string('scoreinputforlevel', 'gradingform_enhancedrubric', $levelindex),
                'size' => '3',
                'value' => $level['score']
            );
            $score = html_writer::empty_tag('input', $scoreparams);
        } else {
            if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
                $leveltemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][definition]', 'value' => $level['definition']));
                $leveltemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]', 'value' => $level['score']));
                if ($level['min']) {
                    $leveltemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][min]', 'value' => $level['min']));
                }
            }
            $definition = s($level['definition']);
            $score = $level['score'];
        }

        $min = html_writer::empty_tag('input', $minparams);

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL) {
            $levelradioparams = array(
                'type' => 'radio',
                'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                'name' => '{NAME}' . $sectionarr . '[criteria][{CRITERION-id}][levelid]',
                'value' => $level['id']
            );
            if ($level['checked']) {
                $levelradioparams['checked'] = 'checked';
            }
            $input = html_writer::empty_tag('input', $levelradioparams);
            $leveltemplate .= html_writer::div($input, 'radio');
        }
        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL_FROZEN) {
            $leveltemplate .= html_writer::empty_tag('input',
                 array(
                     'type' => 'hidden',
                     'name' => '{NAME}'.$sectionarr.'[criteria][{CRITERION-id}][levelid]',
                     'value' => $level['checked'] ? $level['id'] : null
                 )
            );
        }
        $score = html_writer::tag('span', $score, array('id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels-{LEVEL-id}-score', 'class' => 'scorevalue'));
        $definitionclass = 'definition';
        if (isset($level['error_definition'])) {
            $definitionclass .= ' error';
        }

        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {

            $tdattributes['tabindex'] = '0';
            $levelinfo = new stdClass();
            $levelinfo->definition = s($level['definition']);
            $levelinfo->score = $level['score'];
            $levelinfo->min = $level['min'];
            $tdattributes['aria-label'] = get_string('level', 'gradingform_enhancedrubric', $levelinfo);

            if ($mode != gradingform_enhancedrubric_controller::DISPLAY_PREVIEW &&
                $mode != gradingform_enhancedrubric_controller::DISPLAY_PREVIEW_GRADED) {
                // Add role of radio button to level cell if not in edit and preview mode.
                $tdattributes['role'] = 'radio';
                if ($level['checked']) {
                    $tdattributes['aria-checked'] = 'true';
                } else {
                    $tdattributes['aria-checked'] = 'false';
                }
            }
        }

        $leveltemplateparams = array(
            'id' => '{NAME}' . $sectionstr . '-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition-container'
        );
        $leveltemplate .= html_writer::div($definition, $definitionclass, $leveltemplateparams);
        $displayscore = true;
        if (!$options['showscoreteacher'] &&
            in_array($mode, array(gradingform_enhancedrubric_controller::DISPLAY_EVAL, gradingform_enhancedrubric_controller::DISPLAY_EVAL_FROZEN, gradingform_enhancedrubric_controller::DISPLAY_REVIEW, gradingform_enhancedrubric_controller::DISPLAY_HISTORY))
        ) {
            $displayscore = false;
        }
        if (!$options['showscorestudent'] && in_array($mode, array(gradingform_enhancedrubric_controller::DISPLAY_VIEW, gradingform_enhancedrubric_controller::DISPLAY_PREVIEW_GRADED))) {
            $displayscore = false;
        }
        if ($displayscore) {
            $scoreclass = 'score';
            if (isset($level['error_score'])) {
                $scoreclass .= ' error';
            }
            $leveltemplate .= html_writer::tag('div', get_string('scorepostfix', 'gradingform_enhancedrubric', $score), array('class' => $scoreclass));
            $minclass = 'min';
            if (isset($level['error_minimum'])) {
                $minclass .= ' error';
            }
            $leveltemplate .= html_writer::tag('div', $min . get_string('minpostfix', 'gradingform_enhancedrubric'), array('class' => $minclass));
        }
        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('leveldelete', 'gradingform_enhancedrubric', $levelindex);
            $buttonparams = array(
                'type' => 'submit',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][delete]',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-delete',
                'value' => $value
            );
            $button = html_writer::empty_tag('input', $buttonparams);
            $leveltemplate .= html_writer::tag('div', $button, array('class' => 'delete'));
        }
        $leveltemplate .= html_writer::end_tag('div'); // .level-wrapper

        $leveltemplate = html_writer::tag('td', $leveltemplate, $tdattributes); // The .level cell.

        $leveltemplate = str_replace('{NAME}', $elementname, $leveltemplate);
        $leveltemplate = str_replace('{SECTION}', $section, $leveltemplate);
        $leveltemplate = str_replace('{CRITERION-id}', $criterionid, $leveltemplate);
        $leveltemplate = str_replace('{LEVEL-id}', $level['id'], $leveltemplate);
        return $leveltemplate;
    }

    /**
     * This function returns html code for displaying rubric template (content before and after
     * criteria list). Depending on $mode it may be the code to edit rubric, to preview the rubric,
     * to evaluate somebody or to review the evaluation.
     *
     * This function is called from display_enhancedrubric() to display the whole rubric.
     *
     * When overriding this function it is very important to remember that all elements of html
     * form (in edit or evaluate mode) must have the name $elementname.
     *
     * Also JavaScript relies on the class names of elements and when developer changes them
     * script might stop working.
     *
     * @param int $mode rubric display mode see {@link gradingform_enhancedrubric_controller}
     * @param array $options display options for this rubric, defaults are: {@link gradingform_enhancedrubric_controller::get_default_options()}
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param string $criteriastr evaluated templates for this rubric's criteria
     * @return string
     */
    protected function enhancedrubric_template($mode, $options, $elementname, $criteriastr, $actualtotal, $section = null, $collapse = '') {
        $classsuffix = ''; // CSS suffix for class of the main div. Depends on the mode.
        switch ($mode) {
            case gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL:
                $classsuffix = ' editor editable'; break;
            case gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN:
                $classsuffix = ' editor frozen';  break;
            case gradingform_enhancedrubric_controller::DISPLAY_PREVIEW:
            case gradingform_enhancedrubric_controller::DISPLAY_PREVIEW_GRADED:
                $classsuffix = ' editor preview';  break;
            case gradingform_enhancedrubric_controller::DISPLAY_EVAL:
                $classsuffix = ' evaluate editable'; break;
            case gradingform_enhancedrubric_controller::DISPLAY_EVAL_FROZEN:
                $classsuffix = ' evaluate frozen';  break;
            case gradingform_enhancedrubric_controller::DISPLAY_REVIEW:
            case gradingform_enhancedrubric_controller::DISPLAY_HISTORY:
                $classsuffix = ' review';  break;
            case gradingform_enhancedrubric_controller::DISPLAY_VIEW:
                $classsuffix = ' view';  break;
        }

        $rubrictemplate = html_writer::start_tag('div',
             ['id' => 'enhancedrubric-{NAME}' . $section, 'class' => 'w-100 clearfix gradingform_enhancedrubric'.$classsuffix.$collapse]
        );

        // Rubric table.
        $rubrictableparams = array(
            'class' => 'criteria',
            'id' => '{NAME}' . $section . '-criteria',
            'aria-label' => get_string('rubric', 'gradingform_enhancedrubric'));
        $rubrictable = html_writer::tag('table', $criteriastr, $rubrictableparams);
        $rubrictemplate .= $rubrictable;

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('addcriterion', 'gradingform_enhancedrubric');
            $criteriainputparams = array(
                'type' => 'submit',
                'name' => '{NAME}[criteria][addcriterion]',
                'id' => '{NAME}-criteria-addcriterion',
                'value' => $value
            );
            $input = html_writer::empty_tag('input', $criteriainputparams);
            $rubrictemplate .= html_writer::tag('div', $input, array('class' => 'addcriterion btn btn-secondary'));
        }

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL ||
            $mode == gradingform_enhancedrubric_controller::DISPLAY_HISTORY ||
            $mode == gradingform_enhancedrubric_controller::DISPLAY_REVIEW
        ) {
            $totalstrs = [];

            if ($options['showminimumgrader']) {
                $mintotal = $options['mintotal'] ?? 0;
                $totalstrs[] = get_string('mintotalrequired', 'gradingform_enhancedrubric', $mintotal );
                $totalstrs[] = get_string('actualtotal', 'gradingform_enhancedrubric', $actualtotal );
            }

            $rubrictemplate .= html_writer::tag('div', implode(' ', $totalstrs), ['class' => 'mdl-right']);
        }

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_VIEW) {
            $totalstrs = [];

            if ($options['showminimum']) {
                $mintotal = $options['mintotal'] ?? 0;
                $totalstrs[] = get_string('mintotalrequired', 'gradingform_enhancedrubric', $mintotal );
            }

            if ($options['showtotal']) {
                $totalstrs[] = get_string('actualtotal', 'gradingform_enhancedrubric', $actualtotal );
            }

            $rubrictemplate .= html_writer::tag('div', implode(' ', $totalstrs), ['class' => 'mdl-right']);
        }

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL) {
            if ($section == gradingform_enhancedrubric_controller::SECOND || $section == gradingform_enhancedrubric_controller::FINAL) {
                $value = get_string('copyinitial', 'gradingform_enhancedrubric');
                $copyinputparams = array(
                    'type' => 'submit',
                    'name' => '{NAME}[criteria][copyinitial]',
                    'id' => '{NAME}-criteria-copyinitial-' . $section,
                    'data-copyfrom' => 1,
                    'data-copyto' => $section,
                    'value' => $value
                );
                $input = html_writer::empty_tag('input', $copyinputparams);
                $rubrictemplate .= html_writer::tag('div', $input, array('class' => 'copy copyinitial mdl-align mb-3 mt-3'));
            }

            if ($section == gradingform_enhancedrubric_controller::FINAL) {
                $value = get_string('copysecond', 'gradingform_enhancedrubric');
                $copyinputparams = array(
                    'type' => 'submit',
                    'name' => '{NAME}[criteria][copysecond]',
                    'id' => '{NAME}-criteria-copysecond-' . $section,
                    'data-copyfrom' => 2,
                    'data-copyto' => $section,
                    'value' => $value
                );
                $input = html_writer::empty_tag('input', $copyinputparams);
                $rubrictemplate .= html_writer::tag('div', $input, array('class' => 'copy copysecond mdl-align mb-3'));
            }
        }

        $rubrictemplate .= $this->enhancedrubric_edit_options($mode, $options);
        $rubrictemplate .= html_writer::end_tag('div');

        return str_replace('{NAME}', $elementname, $rubrictemplate);
    }

    /**
     * Generates html template to view/edit the rubric options. Expression {NAME} is used in
     * template for the form element name
     *
     * @param int $mode rubric display mode see {@link gradingform_enhancedrubric_controller}
     * @param array $options display options for this rubric, defaults are: {@link gradingform_enhancedrubric_controller::get_default_options()}
     * @return string|null
     */
    protected function enhancedrubric_edit_options($mode, $options) {
        if ($mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL
                && $mode != gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN
                && $mode != gradingform_enhancedrubric_controller::DISPLAY_PREVIEW) {
            // Options are displayed only for people who can manage.
            return;
        }
        $html = html_writer::start_tag('div', array('class' => 'options'));
        $html .= html_writer::tag('div', get_string('rubricoptions', 'gradingform_enhancedrubric'), array('class' => 'optionsheading'));
        $attrs = array('type' => 'hidden', 'name' => '{NAME}[options][optionsset]', 'value' => 1);
        foreach ($options as $option => $value) {
            $html .= html_writer::start_tag('div', array('class' => 'option '.$option));
            $attrs = array('name' => '{NAME}[options]['.$option.']', 'id' => '{NAME}-options-'.$option);
            switch ($option) {
                case 'sortlevelsasc':
                    // Display option as dropdown.
                    $html .= html_writer::label(get_string($option, 'gradingform_enhancedrubric'), $attrs['id'], false);
                    $value = (int)(!!$value); // Make sure $value is either 0 or 1.
                    if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
                        $selectoptions = array(0 => get_string($option.'0', 'gradingform_enhancedrubric'), 1 => get_string($option.'1', 'gradingform_enhancedrubric'));
                        $valuestr = html_writer::select($selectoptions, $attrs['name'], $value, false, array('id' => $attrs['id']));
                        $html .= html_writer::tag('span', $valuestr, array('class' => 'value'));
                    } else {
                        $html .= html_writer::tag('span', get_string($option.$value, 'gradingform_enhancedrubric'), array('class' => 'value'));
                        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
                            $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                        }
                    }
                    break;
                case 'mintotal':
                    // Display option as text field.
                    $html .= html_writer::tag('label', get_string($option, 'gradingform_enhancedrubric'), array('for' => $attrs['id']));
                    if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL) {
                        $html .= html_writer::empty_tag('input', $attrs + array('type' => 'text', 'value' => $value));
                    } else {
                        $html .= html_writer::tag('span', $value, array('class' => 'value'));
                        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
                            $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                        }
                    }
                    break;
                default:
                    if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN && $value) {
                        // Id should be different than the actual input added later.
                        $attrs['id'] .= '_hidden';
                        $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                    }
                    // Display option as checkbox
                    $attrs['type'] = 'checkbox';
                    $attrs['value'] = 1;
                    if ($value) {
                        $attrs['checked'] = 'checked';
                    }
                    if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN || $mode == gradingform_enhancedrubric_controller::DISPLAY_PREVIEW) {
                        $attrs['disabled'] = 'disabled';
                        unset($attrs['name']);
                        // Id should be different then the actual input added later.
                        $attrs['id'] .= '_disabled';
                    }
                    $html .= html_writer::empty_tag('input', $attrs);
                    $html .= html_writer::tag('label', get_string($option, 'gradingform_enhancedrubric'), array('for' => $attrs['id']));
                    break;
            }
            if (get_string_manager()->string_exists($option.'_help', 'gradingform_enhancedrubric')) {
                $html .= $this->help_icon($option, 'gradingform_enhancedrubric');
            }
            $html .= html_writer::end_tag('div'); // .option.
        }
        $html .= html_writer::end_tag('div'); // .options.
        return $html;
    }

    /**
     * This function returns html code for displaying rubric. Depending on $mode it may be the code
     * to edit rubric, to preview the rubric, to evaluate somebody or to review the evaluation.
     *
     * It is very unlikely that this function needs to be overriden by theme. It does not produce
     * any html code, it just prepares data about rubric design and evaluation, adds the CSS
     * class to elements and calls the functions level_template, criterion_template and
     * enhancedrubric_template
     *
     * @param array $criteria data about the rubric design
     * @param array $options display options for this rubric, defaults are: {@link gradingform_enhancedrubric_controller::get_default_options()}
     * @param int $mode rubric display mode, see {@link gradingform_enhancedrubric_controller}
     * @param string $elementname the name of the form element (in editor mode) or the prefix for div ids (in view mode)
     * @param int $section the section number to display
     * @param array|null $values evaluation result
     * @param gradingform_enhancedrubric_instance|null $instance
     * @param boolean $canviewhistory whether current user has capability to view grading history in this context
     * @param boolean $cangrade whether current user has capability to grade in this context
     * @param boolean $cangradesecond whether current user has capability to grade the second section in this context
     * @param boolean $cangradefinal whether current user has capability to grade the final section in this context
     * @param int|null $areaid the grading area id to allow display of the all users history button
     */
    public function display_enhancedrubric(
        $criteria,
        $options,
        $mode,
        $elementname = null,
        $section = gradingform_enhancedrubric_controller::INITIAL,
        $values = null,
        $instance = null,
        $canviewhistory = false,
        $cangrade = false,
        $cangradesecond = false,
        $cangradefinal = false,
        $areaid = null
    ) {
        $criteriastr = '';
        $cnt = 0;
        $possibletotal = 0;
        $actualtotal = 0;
        $rubrictemplate = '';
        $collapse = '';
        $disabled = false;

        if ($instance) {
            $sections = $instance->get_enhancedrubric_sections();
            $lastsection = array_pop($sections);

            if (count($sections) > 1 && $mode != gradingform_enhancedrubric_controller::DISPLAY_VIEW) {
                $export = new stdClass();
                $export->id = 'enhancedrubric-'.$elementname.$section;
                $export->header = get_string('section'.$section, 'gradingform_enhancedrubric');
                $rubrictemplate .= $this->render_from_template('gradingform_enhancedrubric/markingworkflow_section', $export);
                $collapse = ' collapse ';
                $initialgrader = $cangrade && !$cangradesecond;
                $secondgrader = $cangradesecond && !$cangradefinal;
                $finalgrader = $cangradefinal;

                // Make sections editable/collapsed according to current users capabilities.
                switch ($section) {
                    case gradingform_enhancedrubric_controller::INITIAL:
                        if ($initialgrader) {
                            $collapse .= ' show ';
                        }
                        break;
                    case gradingform_enhancedrubric_controller::SECOND:
                        if ($initialgrader) {
                            $disabled = true;
                            $mode = gradingform_enhancedrubric_controller::DISPLAY_REVIEW;
                        }
                        if ($secondgrader) {
                            $collapse .= ' show ';
                        }
                        break;
                    case gradingform_enhancedrubric_controller::FINAL:
                        if ($initialgrader || $secondgrader) {
                            $disabled = true;
                            $mode = gradingform_enhancedrubric_controller::DISPLAY_REVIEW;
                        }
                        if ($finalgrader) {
                            $collapse .= ' show ';
                        }
                        break;
                }
            }
        }

        foreach ($criteria as $id => $criterion) {
            $criterion['class'] = $this->get_css_class_suffix($cnt++, count($criteria) -1);
            $criterion['id'] = $id;
            $levelsstr = '';
            $levelcnt = 0;
            if ($values && isset($values['criteria'][$id])) {
                $criterionvalue = $values['criteria'][$id];
            } else {
                $criterionvalue = null;
            }
            $index = 1;
            foreach ($criterion['levels'] as $levelid => $level) {
                $scores = [];
                $hasscore = false;
                $level['id'] = $levelid;
                $level['class'] = $this->get_css_class_suffix($levelcnt++, count($criterion['levels']) -1);
                $level['checked'] = (isset($criterionvalue['levelid']) && ((int)$criterionvalue['levelid'] === $levelid));
                if ($level['checked'] && ($mode == gradingform_enhancedrubric_controller::DISPLAY_EVAL_FROZEN || $mode == gradingform_enhancedrubric_controller::DISPLAY_REVIEW || $mode == gradingform_enhancedrubric_controller::DISPLAY_VIEW  || $mode == gradingform_enhancedrubric_controller::DISPLAY_HISTORY)) {
                    $level['class'] .= ' checked';
                    // In mode DISPLAY_EVAL the class 'checked' will be added by JS if it is enabled. If JS is not enabled, the 'checked' class will only confuse.
                    $hasscore = true;
                }
                if (isset($criterionvalue['savedlevelid']) && ((int)$criterionvalue['savedlevelid'] === $levelid)) {
                    $level['class'] .= ' currentchecked';
                    $hasscore = true;
                }
                $level['tdwidth'] = 100/count($criterion['levels']);
                $level['index'] = $index;
                $levelsstr .= $this->level_template($mode, $options, $elementname, $section, $id, $level);
                $scores[] = $level['score'];

                if ($hasscore) {
                    $actualtotal += $level['score'];
                }

                $index++;
            }

            sort($scores);
            // Get the maximum score for the level.
            $possibletotal += $scores[count($scores)-1];
            $criteriastr .= $this->criterion_template($mode, $options, $elementname, $criterion, $levelsstr, $criterionvalue, $section);
        }

        $actualtotal = "$actualtotal / $possibletotal";

        if ($mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FULL || $mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) {
            $section = null;
        }

        $rubrictemplate .=  $this->enhancedrubric_template($mode, $options, $elementname, $criteriastr, $actualtotal, $section, $collapse);

        if ($canviewhistory && $instance && ($section === $lastsection)  && $instance->has_history()) {
            if ($mode != gradingform_enhancedrubric_controller::DISPLAY_HISTORY) {
                $linkstr = get_string('viewhistory', 'gradingform_enhancedrubric');
            } else {
                $linkstr = get_string('backtohistory', 'gradingform_enhancedrubric');
            }
            $params = ['instanceid' => $instance->get_id(), 'areaid' => $instance->get_controller()->get_areaid(), 'itemid' => $instance->get_data('itemid')];
            $viewhistory = new moodle_url('/grade/grading/form/enhancedrubric/history.php', $params);
            $viewhistory = html_writer::link($viewhistory, $linkstr);
            $rubrictemplate .= html_writer::div($viewhistory);
        }

        if (($mode == gradingform_enhancedrubric_controller::DISPLAY_PREVIEW ||
            $mode == gradingform_enhancedrubric_controller::DISPLAY_EDIT_FROZEN) &&
            $canviewhistory && $areaid
        ) {
            $manager = get_grading_manager($areaid);
            $controller = $manager->get_controller('enhancedrubric');
            $url = $controller->get_all_history_url();
            $rubrictemplate .= $this->single_button($url, get_string('viewallhistory', 'gradingform_enhancedrubric'));
        }

        return $rubrictemplate;
    }

    /**
     * Help function to return CSS class names for element (first/last/even/odd) with leading space
     *
     * @param int $idx index of this element in the row/column
     * @param int $maxidx maximum index of the element in the row/column
     * @return string
     */
    protected function get_css_class_suffix($idx, $maxidx) {
        $class = '';
        if ($idx == 0) {
            $class .= ' first';
        }
        if ($idx == $maxidx) {
            $class .= ' last';
        }
        if ($idx%2) {
            $class .= ' odd';
        } else {
            $class .= ' even';
        }
        return $class;
    }

    /**
     * Displays for the student the list of instances or default content if no instances found
     *
     * @param array $instances array of objects of type gradingform_enhancedrubric_instance
     * @param string $defaultcontent default string that would be displayed without advanced grading
     * @param boolean $cangrade whether current user has capability to grade in this context
     * @param boolean $canviewhistory whether current user has capability to view grading history in this context
     * @param boolean $cangradesecond whether current user has capability to grade the second section in this context
     * @param boolean $cangradefinal whether current user has capability to grade the final section in this context
     * @return string
     */
    public function display_instances($instances, $defaultcontent, $cangrade, $canviewhistory, $cangradesecond, $cangradefinal) {
        $return = '';
        if (count($instances)) {
            $return .= html_writer::start_tag('div', array('class' => 'advancedgrade'));
            $idx = 0;
            foreach ($instances as $instance) {
                $return .= $this->display_instance($instance, $idx++, $cangrade, $canviewhistory, $cangradesecond, $cangradefinal);
            }
            $return .= html_writer::end_tag('div');
        }
        return $return. $defaultcontent;
    }

    /**
     * Displays one grading instance
     *
     * @param gradingform_enhancedrubric_instance $instance
     * @param int $idx unique number of instance on page
     * @param boolean $cangrade whether current user has capability to grade in this context
     * @param boolean $canviewhistory whether current user has capability to view grading history in this context
     * @param boolean $cangradesecond whether current user has capability to grade the second section in this context
     * @param boolean $cangradefinal whether current user has capability to grade the final section in this context
     */
    public function display_instance(gradingform_enhancedrubric_instance $instance, $idx, $cangrade, $canviewhistory, $cangradesecond, $cangradefinal) {
        $criteria = $instance->get_controller()->get_definition()->enhancedrubric_criteria;
        $options = $instance->get_controller()->get_options();
        $sections = $instance->get_enhancedrubric_sections();
        $section = null;

        if ($cangrade) {
            $mode = gradingform_enhancedrubric_controller::DISPLAY_REVIEW;
            $showdescription = $options['showdescriptionteacher'];
        } else {
            $mode = gradingform_enhancedrubric_controller::DISPLAY_VIEW;
            $showdescription = $options['showdescriptionstudent'];

            [$markingworkflow, $gradereleased, $workflowstatus] = $instance->get_controller()->get_markingworkflow($instance->get_data('itemid'));

            // If there is more than one section then marking workflow must be enabled.
            // If the grade has not been released then use the intitial fillings to
            // calculate it.
            // If the grade has been released then use the last fillings completed to
            // calculate it.
            if (count($sections) === 1) {
                $section = array_pop($sections);
            } else if (!$gradereleased) {
                $section = gradingform_enhancedrubric_controller::INITIAL;
            } else {
                $section = $instance->get_last_section_filled();
            }
        }
        $values = $instance->get_enhancedrubric_filling(false, $section);
        $output = '';
        if ($showdescription) {
            $output .= $this->box($instance->get_controller()->get_formatted_description(), 'gradingform_enhancedrubric-description');
        }

        if (isset($values['section'], $values['section'][$section]) && $values) {
            $value = $values['section'][$section];
            $output .= $this->display_enhancedrubric($criteria, $options, $mode, 'rubric'.$idx, $section, $value, $instance, $canviewhistory, $cangrade, $cangradesecond, $cangradefinal);
        } else {
            $output .= $this->display_enhancedrubric($criteria, $options, $mode, 'rubric'.$idx, $section, null, $instance, $canviewhistory, $cangrade, $cangradesecond, $cangradefinal);
        }

        return $output;
    }

    /**
     * Displays confirmation that students require re-grading
     *
     * @param string $elementname
     * @param int $changelevel
     * @param string $value
     * @return string
     */
    public function display_regrade_confirmation($elementname, $changelevel, $value) {
        $html = html_writer::start_tag('div', array('class' => 'gradingform_enhancedrubric-regrade', 'role' => 'alert'));
        if ($changelevel<=2) {
            $html .= html_writer::label(get_string('regrademessage1', 'gradingform_enhancedrubric'), 'menu' . $elementname . 'regrade');
            $selectoptions = array(
                0 => get_string('regradeoption0', 'gradingform_enhancedrubric'),
                1 => get_string('regradeoption1', 'gradingform_enhancedrubric')
            );
            $html .= html_writer::select($selectoptions, $elementname.'[regrade]', $value, false);
        } else {
            $html .= get_string('regrademessage5', 'gradingform_enhancedrubric');
            $html .= html_writer::empty_tag('input', array('name' => $elementname.'[regrade]', 'value' => 1, 'type' => 'hidden'));
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    /**
     * Generates and returns HTML code to display information box about how rubric score is converted to the grade
     *
     * @param array $scores
     * @return string
     */
    public function display_enhancedrubric_mapping_explained($scores) {
        $html = '';
        if (!$scores) {
            return $html;
        }
        if ($scores['minscore'] <> 0) {
            $html .= $this->output->notification(get_string('zerolevelsabsent', 'gradingform_enhancedrubric'), 'error');
        }
        $html .= $this->output->notification(get_string('rubricmappingexplained', 'gradingform_enhancedrubric', (object)$scores), 'info');
        return $html;
    }

    /**
     * Displays the history of one grading instance
     *
     * @param gradingform_enhancedrubric_instance $instance
     * @param boolean $cangrade whether current user has capability to grade in this context
     * @param boolean $cangradesecond whether current user has capability to grade the second section in this context
     * @param boolean $cangradefinal whether current user has capability to grade the final section in this context
     */
    public function display_instance_history(gradingform_enhancedrubric_instance $instance, $cangrade, $cangradesecond, $cangradefinal) {
        global $DB;

        $mode = gradingform_enhancedrubric_controller::DISPLAY_HISTORY;
        $criteria = $instance->get_controller()->get_definition()->enhancedrubric_criteria;
        $options = $instance->get_controller()->get_options();
        $values = $instance->get_enhancedrubric_filling();
        $grade = $instance->get_enhancedrubric_history();
        $sections = $instance->get_enhancedrubric_sections();
        $history = reset($grade);
        $grader = $DB->get_record('user', ['id' => $history['raterid']]);
        $releasegrade = $instance->get_grade_for_last_section();

        $export = new stdClass();
        $export->grader = fullname($grader);
        $export->date = userdate($history['timemodified']);
        $export->grade = $releasegrade;
        $export->stage = $history['workflowstatus'] ? get_string('markingworkflowstate' . $history['workflowstatus'], 'mod_assign') : '';
        $export->feedback = format_text($history['feedback'], $history['feedbackformat']);
        $export->fillings = '';

        foreach ($sections as $section) {
            if (isset($values['section'], $values['section'][$section]) && $values) {
                $value = $values['section'][$section];
                $export->fillings .= $this->display_enhancedrubric($criteria, $options, $mode, 'rubric', $section, $value, $instance, true, $cangrade, $cangradesecond, $cangradefinal);
            } else {
                $export->fillings .= $this->display_enhancedrubric($criteria, $options, $mode, 'rubric', $section, null, $instance, true, $cangrade, $cangradesecond, $cangradefinal);
            }
        }

        return $this->render_from_template('gradingform_enhancedrubric/history', $export);
    }

    public function render_internal_comments($mode, $options, $canaddinternal, $canviewinternal, $value, $editoroptions) {
        global $CFG;

        if (!$options['enableinternalcomments'] || $mode !== gradingform_enhancedrubric_controller::DISPLAY_EVAL) {
            return '';
        }

        if ($canaddinternal) {
            require_once($CFG->dirroot . '/lib/form/editor.php');
            $editor = new MoodleQuickForm_editor(
                'advancedgrading[internalcomment]',
                get_string('internalcomment', 'gradingform_enhancedrubric'),
                $editoroptions
            );
            $editor->setValue(['text' => $value->{'internalcomment_editor'}['text']]);

            $data = new stdClass();
            $data->labelid = 'id_internalcomment_label';
            $data->fieldid = 'id_internalcomment';
            $data->labeltxt =  get_string('internalcomment', 'gradingform_enhancedrubric');
            $data->html = $editor->toHtml();

            return $this->render_from_template('gradingform_enhancedrubric/internal_comment', $data);
        }

        if ($canviewinternal) {
            require_once($CFG->dirroot . '/lib/form/static.php');
            $editor = new MoodleQuickForm_static(
                'advancedgrading[internalcomment]',
                get_string('internalcomment', 'gradingform_enhancedrubric'),
                ['id' => 'id_internalcomment']
            );
            $editor->setValue($value->{'internalcomment_editor'}['text']);

            $data = new stdClass();
            $data->labelid = 'id_internalcomment_label';
            $data->fieldid = 'id_internalcomment';
            $data->labeltxt =  get_string('internalcomment', 'gradingform_enhancedrubric');
            $data->html = $editor->toHtml();

            return $this->render_from_template('gradingform_enhancedrubric/internal_comment', $data);
        }

        return '';
    }
}
