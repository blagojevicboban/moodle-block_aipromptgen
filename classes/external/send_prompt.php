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

namespace block_aipromptgen\external;

defined('MOODLE_INTERNAL') || die();

global $CFG; // Need $CFG for lib includes.
require_once($CFG->libdir . '/externallib.php'); // Provides external_api & related classes.
require_once($CFG->libdir . '/accesslib.php'); // For context_course, require_capability.

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use moodle_exception;
use context_course;
use block_aipromptgen\local\provider\factory as provider_factory;

/**
 * External web service function to send a prompt to configured AI provider and return response.
 *
 * @package   block_aipromptgen
 * @category  external
 * @copyright 2025 AI4Teachers
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_prompt extends external_api {
    /**
     * Describe input parameters.
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
            'prompt' => new external_value(PARAM_RAW, 'Prompt text', VALUE_REQUIRED),
        ]);
    }

    /**
     * Execute the web service call.
     * @param int $courseid Course id.
     * @param string $prompt Prompt body.
     * @return array associative response structure.
     */
    public static function execute(int $courseid, string $prompt): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'prompt' => $prompt,
        ]);
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        \require_capability('block/aipromptgen:manage', $context);

        $provider = provider_factory::make();
        try {
            $response = $provider->complete($params['prompt']);
        } catch (moodle_exception $e) {
            return [
                'success' => false,
                'response' => '',
                'error' => $e->getMessage(),
            ];
        }
        return [
            'success' => true,
            'response' => $response,
            'error' => '',
        ];
    }

    /**
     * Describe return structure.
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether call succeeded'),
            'response' => new external_value(PARAM_RAW, 'AI response'),
            'error' => new external_value(PARAM_RAW, 'Error message if any'),
        ]);
    }
}
