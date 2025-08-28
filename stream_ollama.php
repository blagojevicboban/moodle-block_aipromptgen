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
 * Streaming proxy endpoint for Ollama chat responses.
 *
 * Proxies a streaming chat request to a local/self-hosted Ollama daemon and
 * progressively flushes incremental content to the browser so the user sees
 * partial AI output while generation continues.
 *
 * Access is restricted by course capability and provider/setting checks.
 *
 * @package    block_aipromptgen
 * @category   output
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$prompt = required_param('prompt', PARAM_RAW);

$course = get_course($courseid);
$context = context_course::instance($course->id);
require_capability('block/aipromptgen:manage', $context);

// Check provider & streaming enabled.
if (get_config('block_aipromptgen', 'provider') !== 'ollama' || (int)get_config('block_aipromptgen', 'ollama_stream') !== 1) {
    throw new moodle_exception('notallowed', 'block_aipromptgen');
}

@apache_setenv('no-gzip', '1');
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', 'off');
@ini_set('implicit_flush', '1');
while (ob_get_level() > 0) {
    @ob_end_flush();
}
ob_implicit_flush(true);

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache');

$endpoint = rtrim(get_config('block_aipromptgen', 'ollama_endpoint') ?: 'http://localhost:11434', '/');
$model = get_config('block_aipromptgen', 'ollama_model') ?: 'llama3';

$url = $endpoint . '/api/chat';
$payload = json_encode([
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful teaching assistant.'],
        ['role' => 'user', 'content' => $prompt],
    ],
    'stream' => true,
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_WRITEFUNCTION => function($ch, $chunk) {
        // Ollama streams JSON objects per line. Parse incremental content.
        $lines = preg_split('/\r?\n/', $chunk, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if (isset($data['message']['content'])) {
                echo $data['message']['content'];
            }
            if (!empty($data['done'])) {
                // Append newline at end.
                echo "\n";
            }
        }
        @flush();
        return strlen($chunk);
    },
    CURLOPT_RETURNTRANSFER => false, // We stream directly.
    CURLOPT_TIMEOUT => 0, // Let it stream; rely on web server timeout.
]);

$ok = curl_exec($ch);
if ($ok === false) {
    echo "\n[STREAM ERROR] " . curl_error($ch) . "\n";
}
curl_close($ch);
exit;
