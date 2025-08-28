<?php
// This file is part of Moodle - http://moodle.org/.
//
// GNU GPL v3 or later.

namespace block_aipromptgen\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use moodle_exception;
use required_capability_exception;
use context_course;
use block_aipromptgen\local\provider\factory as provider_factory;

/**
 * External function for sending prompt asynchronously.
 *
 * @package block_aipromptgen
 */
class send_prompt extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
            'prompt' => new external_value(PARAM_RAW, 'Prompt text', VALUE_REQUIRED),
        ]);
    }

    public static function execute(int $courseid, string $prompt): array {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'prompt' => $prompt,
        ]);
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('block/aipromptgen:manage', $context);

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

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether call succeeded'),
            'response' => new external_value(PARAM_RAW, 'AI response'),
            'error' => new external_value(PARAM_RAW, 'Error message if any'),
        ]);
    }
}
