<?php
// This file is part of Moodle - http://moodle.org/.
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
 * External function definitions (services) for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @category   external
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_aipromptgen_send_prompt' => [
        'classname' => 'block_aipromptgen\\external\\send_prompt',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Send AI prompt and return response (async).',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'block/aipromptgen:manage',
    ],
];
