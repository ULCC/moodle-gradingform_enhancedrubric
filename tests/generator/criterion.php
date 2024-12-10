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
 * @copyright  2018 Adrian Greeve <adriangreeve.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tests\gradingform_enhancedrubric\generator;

/**
 * Convenience class to create rubric criterion.
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2018 Adrian Greeve <adriangreeve.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class criterion {
    /** @var string $description A description of the criterion. */
    public $description;

    /** @var integer $sortorder sort order of the criterion. */
    public $sortorder = 0;

    /** @var array $levels The levels for this criterion. */
    public $levels = [];

    /**
     * Constructor for this test_criterion object
     *
     * @param string $description A description of this criterion.
     * @param array $levels
     */
    public function __construct(string $description, array $levels = []) {
        $this->description = $description;
        foreach ($levels as $definition => $score) {
            $this->add_level($definition, $score['score'], $score['min']);
        }
    }

    /**
     * Adds levels to the criterion.
     *
     * @param string $definition The definition for this level.
     * @param int $score The score received if this level is selected.
     * @param int $min Whether or not this is the minimum score required.
     * @return self
     */
    public function add_level(string $definition, int $score, int $min): self {
        $this->levels[] = [
            'definition' => $definition,
            'score' => $score,
            'min' => $min
        ];

        return $this;
    }

    /**
     * Get the description for this criterion.
     *
     * @return string
     */
    public function get_description(): string {
        return $this->description;
    }

    /**
     * Get the levels for this criterion.
     *
     * @return array
     */
    public function get_levels(): array {
        return $this->levels;
    }

    /**
     * Get all values in an array for use when creating a new guide.
     *
     * @param int $sortorder
     * @return array
     */
    public function get_all_values(int $sortorder): array {
        return [
            'sortorder' => $sortorder,
            'description' => $this->get_description(),
            'levels' => $this->get_all_level_values(),
        ];
    }

    /**
     * Get all level values.
     *
     * @return array
     */
    public function get_all_level_values(): array {
        $result = [];

        foreach ($this->get_levels() as $index => $level) {
            $id = $index + 1;
            $result["NEWID{$id}"] = $level;
        }

        return $result;
    }
}
