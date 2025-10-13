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
 * Server-Sent Events streaming endpoint for AI (Ollama) responses.
 *
 * Provides an SSE (text/event-stream) interface that proxies incremental NDJSON
 * output from a local Ollama server to the browser as start/chunk/error/done events.
 * Only the block capability 'block/aipromptgen:manage' is required (same gate as view.php).
 *
 * @package    block_aipromptgen
 * @category   output
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$provider = optional_param('provider', 'ollama', PARAM_ALPHA);

$course = get_course($courseid);
$context = context_course::instance($course->id);
// Match main view capability (teachers/managers allowed via block capability).
require_capability('block/aipromptgen:manage', $context);

// Disable buffering for streaming.
while (ob_get_level()) {
    ob_end_flush();
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
function send_event(string $data, string $event = 'message'): void {
    // Each line should stay reasonably small; split on newlines.
    foreach (preg_split('/\r?\n/', $data) as $line) {
        echo "event: {$event}\n";
        echo 'data: ' . $line . "\n\n";
    }
    @flush();
}

// Very light prompt assembly: accept raw prompt if supplied; else build from form fields similar to view.
$rawprompt = optional_param('prompt', '', PARAM_RAW_TRIMMED);
if ($rawprompt === '') {
    // Fallback: concatenate basic fields (topic, lesson, outcomes). This is simplified vs full view builder.
    $topic = optional_param('topic', '', PARAM_TEXT);
    $lesson = optional_param('lesson', '', PARAM_TEXT);
    $outcomes = optional_param('outcomes', '', PARAM_RAW_TRIMMED);
    $rawprompt = "Topic: {$topic}\nLesson: {$lesson}\nOutcomes: {$outcomes}";
}

// Only Ollama streaming implemented here.
if ($provider !== 'ollama') {
    send_event('Unsupported provider for streaming: ' . $provider, 'error');
    send_event('[DONE]', 'done');
    exit;
}

$endpoint = (string)(get_config('block_aipromptgen', 'ollama_endpoint') ?? '');
$model = (string)(get_config('block_aipromptgen', 'ollama_model') ?? '');
if ($endpoint === '' || $model === '') {
    send_event('Ollama not configured', 'error');
    send_event('[DONE]', 'done');
    exit;
}

$schemastr = (string)(get_config('block_aipromptgen', 'ollama_schema') ?? '');
$schema = null;
if ($schemastr !== '') {
    $tmp = json_decode($schemastr, true);
    if (is_array($tmp)) {
        $schema = $tmp;
    }
}

$maxpredict = (int)(get_config('block_aipromptgen', 'ollama_num_predict') ?? 256);
if ($maxpredict <= 0) {
    $maxpredict = 256;
}

$timeout = (int)(get_config('block_aipromptgen', 'ollama_timeout') ?? 180);
if ($timeout < 30) {
    $timeout = 180;
}
@set_time_limit($timeout + 30);

$url = rtrim($endpoint, '/') . '/api/generate';
$body = [
    'model' => $model,
    'prompt' => $rawprompt,
    'stream' => true,
    'options' => [
        'num_predict' => $maxpredict,
        'temperature' => $schema ? 0 : 0.7,
    ],
];
if ($schema) {
    $body['format'] = $schema;
}
$payload = json_encode($body, JSON_UNESCAPED_UNICODE);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => false, // Stream directly.
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_WRITEFUNCTION => function($ch, $chunk) use (&$schema) {
        static $buffer = '';
        $buffer .= $chunk;
        // Split on newlines for NDJSON.
        while (($pos = strpos($buffer, "\n")) !== false) {
            $line = trim(substr($buffer, 0, $pos));
            $buffer = substr($buffer, $pos + 1);
            if ($line === '') {
                continue;
            }
            $obj = json_decode($line, true);
            if (!is_array($obj)) {
                continue;
            }
            if (isset($obj['error'])) {
                send_event('Error: ' . $obj['error'], 'error');
                continue;
            }
            if (isset($obj['response'])) {
                send_event($obj['response'], 'chunk');
            }
            if (!empty($obj['done'])) {
                send_event('[DONE]', 'done');
            }
        }
        return strlen($chunk);
    },
]);

// Bypass Moodle curl security for local endpoints.
if (preg_match('~^https?://(localhost|127\.0\.0\.1)~i', $url)) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
}

send_event('Streaming start', 'start');
$ok = curl_exec($ch);
if ($ok === false) {
    send_event('cURL error: ' . curl_error($ch), 'error');
}
curl_close($ch);
send_event('[DONE]', 'done');
exit;

