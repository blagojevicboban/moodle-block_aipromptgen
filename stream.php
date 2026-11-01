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
 * Server-Sent Events streaming endpoint for AI responses.
 *
 * Streams the response of any supported provider to the browser as start/chunk/error/done events.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
require_login($courseid);
$provider = optional_param('provider', 'ollama', PARAM_ALPHA);

// Disable buffering for streaming.
while (ob_get_level()) {
    @ob_end_clean();
}
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

/**
 * Emit a Server-Sent Event.
 * Splits multi-line data so each line is sent as a distinct data: field for the same event type.
 *
 * @param string $data  The payload to send (may contain newlines).
 * @param string $event The SSE event name (e.g. start, chunk, error, done).
 * @return void
 */
function block_aipromptgen_send_event(string $data, string $event = 'message'): void {
    echo "event: {$event}\n";
    foreach (preg_split('/\r?\n/', $data) as $line) {
        echo 'data: ' . $line . "\n";
    }
    echo "\n";
    @flush();
}

/**
 * Emit an error event followed by the done event, and stop.
 *
 * @param string $message The error message.
 * @return void
 */
function block_aipromptgen_send_error(string $message): void {
    block_aipromptgen_send_event($message, 'error');
    block_aipromptgen_send_event('[DONE]', 'done');
    exit;
}

// Every request calls a paid AI API, so accept only POST requests with a valid sesskey (no CSRF via a plain link).
$sesskey = optional_param('sesskey', '', PARAM_RAW);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || $sesskey === '' || !confirm_sesskey($sesskey)) {
    block_aipromptgen_send_error(get_string('invalidsesskey', 'error'));
}

if (empty($courseid)) {
    block_aipromptgen_send_error(get_string('error_nocourseid', 'block_aipromptgen'));
}

try {
    $course = get_course($courseid);
    require_capability('block/aipromptgen:manage', context_course::instance($course->id));
} catch (Exception $e) {
    block_aipromptgen_send_error(get_string('error_access', 'block_aipromptgen', $e->getMessage()));
}

$rawprompt = optional_param('prompt', '', PARAM_RAW_TRIMMED);
if ($rawprompt === '') {
    // Fallback: concatenate basic fields (topic, lesson, outcomes). This is simplified vs full view builder.
    $topic = optional_param('topic', '', PARAM_TEXT);
    $lesson = optional_param('lesson', '', PARAM_TEXT);
    $outcomes = optional_param('outcomes', '', PARAM_RAW_TRIMMED);
    $rawprompt = "Topic: {$topic}\nLesson: {$lesson}\nOutcomes: {$outcomes}";
}

if (!in_array($provider, \block_aipromptgen\ai_client::PROVIDERS)) {
    block_aipromptgen_send_error(get_string('error_unknownprovider', 'block_aipromptgen', s($provider)));
}

if (!\block_aipromptgen\helper::check_rate_limit()) {
    block_aipromptgen_send_error(get_string('error_ratelimit', 'block_aipromptgen'));
}

// Let a slow local model finish (the Ollama timeout setting can be several minutes).
core_php_time_limit::raise(0);
\core\session\manager::write_close();

$client = new \block_aipromptgen\ai_client();
block_aipromptgen_send_event(get_string('status_started', 'block_aipromptgen'), 'start');
$error = $client->stream_request($provider, $rawprompt, function (string $text): void {
    block_aipromptgen_send_event($text, 'chunk');
});
if ($error !== null) {
    block_aipromptgen_send_event($error, 'error');
}
block_aipromptgen_send_event('[DONE]', 'done');
exit;
