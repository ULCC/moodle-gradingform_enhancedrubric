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
use gradingform_enhancedrubric\grades\grader\gradingpanel\external\store;
use gradingform_enhancedrubric\grades\grader\gradingpanel\external\fetch;

/**
 * Emnhanced rubric external functions and service definitions.
 *
 * @package    gradingform_enhancedrubric
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'gradingform_enhancedrubric_grader_gradingpanel_fetch' => [
        'classname' => fetch::class,
        'description' => 'Fetch the data required to display the grader grading panel, ' .
            'creating the grade item if required',
        'type' => 'write',
        'ajax' => true,
    ],
    'gradingform_enhancedrubric_grader_gradingpanel_store' => [
        'classname' => store::class,
        'description' => 'Store the grading data for a user from the grader grading panel.',
        'type' => 'write',
        'ajax' => true,
    ],
];


