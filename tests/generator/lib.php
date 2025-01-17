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
 * Generator for the gradingforum_rubric plugin.
 *
 * @package    gradingform_enhancedrubric
 * @category   test
 * @copyright  2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/enhancedrubric.php');
require_once(__DIR__ . '/criterion.php');

use tests\gradingform_enhancedrubric\generator\enhancedrubric;
use tests\gradingform_enhancedrubric\generator\criterion;

/**
 * Generator for the gradingforum_rubric plugintype.
 *
 * @package    gradingform_enhancedrubric
 * @category   test
 * @copyright  2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradingform_enhancedrubric_generator extends component_generator_base {

    /**
     * Create an instance of a rubric.
     *
     * @param context $context
     * @param string $component
     * @param string $area
     * @param string $name
     * @param string $description
     * @param array $criteria The list of criteria to add to the generated rubric
     * @return gradingform_enhancedrubric_controller
     */
    public function create_instance(
        context $context,
        string $component,
        string $area,
        string $name,
        string $description,
        array $criteria
    ): gradingform_enhancedrubric_controller {
        global $USER;

        if ($USER->id === 0) {
            throw new \coding_exception('Creation of a rubric must currently be run as a user.');
        }

        // Fetch the controller for this context/component/area.
        $generator = \testing_util::get_data_generator();
        $gradinggenerator = $generator->get_plugin_generator('core_grading');
        $controller = $gradinggenerator->create_instance($context, $component, $area, 'enhancedrubric');

        // Generate a definition for the supplied rubric.
        $rubric = $this->get_enhancedrubric($name, $description);
        foreach ($criteria as $name => $criterion) {
            $rubric->add_criteria($this->get_criterion($name, $criterion));
        }

        // Update the controller wih the rubric definition.
        $controller->update_definition($rubric->get_definition());

        return $controller;
    }

    /**
     * Get a new rubric for use with the rubric controller.
     *
     * Note: This is just a helper class used to build a new definition. It does not persist the data.
     *
     * @param string $name
     * @param string $description
     * @return rubric
     */
    protected function get_enhancedrubric(string $name, string $description): enhancedrubric {
        return new enhancedrubric($name, $description);
    }

    /**
     * Get a new rubric for use with a gradingform_enhancedrubric_generator_rubric.
     *
     * Note: This is just a helper class used to build a new definition. It does not persist the data.
     *
     * @param string $description
     * @param array $levels Set of levels in the form definition => score
     * @return gradingform_enhancedrubric_generator_criterion
     */
    protected function get_criterion(string $description, array $levels = []): criterion {
        return new criterion($description, $levels);
    }

    /**
     * Given a controller instance, fetch the level and criterion information for the specified values.
     *
     * @param gradingform_controller $controller
     * @param string $description The description to match the criterion on
     * @param float $score The value to match the level on
     * @return array
     */
    public function get_level_and_criterion_for_values(
        gradingform_controller $controller,
        string $description,
        float $score
    ): array {
        $definition = $controller->get_definition();
        $criteria = $definition->enhancedrubric_criteria;

        $criterion = $level = null;

        $criterion = array_reduce($criteria, function($carry, $criterion) use ($description) {
            if ($criterion['description'] === $description) {
                $carry = $criterion;
            }

            return $carry;
        }, null);

        if ($criterion) {
            $criterion = (object) $criterion;
            $level = array_reduce($criterion->levels, function($carry, $level) use ($score) {
                if ($level['score'] == $score) {
                    $carry = $level;
                }
                return $carry;
            });
            $level = $level ? (object) $level : null;
        }

        return [
            'criterion' => $criterion,
            'level' => $level,
        ];
    }

    /**
     * Get submitted form data for the supplied controller, itemid, and values.
     * The returned data is in the format used by rubric when handling form submission.
     *
     * @param gradingform_enhancedrubric_controller $controller
     * @param int $itemid
     * @param array $values A set of array values where the array key is the name of the criterion, and the value is an
     * array with the desired score, and any remark.
     */
    public function get_submitted_form_data(gradingform_enhancedrubric_controller $controller, int $itemid, array $values): array {
        $result = [
            'itemid' => $itemid,
            'criteria' => [],
        ];
        foreach ($values as $criterionname => ['score' => $score, 'remark' => $remark]) {
            [
                'criterion' => $criterion,
                'level' => $level,
            ] = $this->get_level_and_criterion_for_values($controller, $criterionname, $score);
            $result['criteria'][$criterion->id] = [
                'levelid' => $level->id,
                'remark' => $remark,
            ];
        }
        $section = ['section' => []];
        $section['section'][1] = $result;

        return $section;
    }

    /**
     * Generate a rubric controller with sample data required for testing of this class.
     *
     * @param context $context
     * @param string $component
     * @param string $area
     * @return gradingform_enhancedrubric_controller
     */
    public function get_test_enhancedrubric(context $context, string $component, string $area): gradingform_enhancedrubric_controller {
        $criteria = [
            'Spelling is important' => [
                'Nothing but mistakes' => ['score' => 0, 'min' => 0],
                'Several mistakes' => ['score' => 1, 'min' => 1],
                'No mistakes' => ['score' => 2, 'min' => 0],
            ],
            'Pictures' => [
                'No pictures' => ['score' => 0, 'min' => 0],
                'One picture' => ['score' => 1, 'min' => 1],
                'More than one picture' => ['score' => 2, 'min' => 0],
            ],
        ];

        return $this->create_instance($context, $component, $area, 'testenhancedrubric', 'Description text', $criteria);
    }

    /**
     * Fetch a set of sample data.
     *
     * @param gradingform_enhancedrubric_controller $controller
     * @param int $itemid
     * @param float $spellingscore
     * @param string $spellingremark
     * @param float $picturescore
     * @param string $pictureremark
     * @return array
     */
    public function get_test_form_data(
        gradingform_enhancedrubric_controller $controller,
        int $itemid,
        float $spellingscore,
        string $spellingremark,
        float $picturescore,
        string $pictureremark
    ): array {
        $generator = \testing_util::get_data_generator();
        $rubricgenerator = $generator->get_plugin_generator('gradingform_enhancedrubric');
        return $rubricgenerator->get_submitted_form_data($controller, $itemid, [
            'Spelling is important' => [
                'score' => $spellingscore,
                'remark' => $spellingremark,
            ],
            'Pictures' => [
                'score' => $picturescore,
                'remark' => $pictureremark,
            ],
        ]);
    }
}
