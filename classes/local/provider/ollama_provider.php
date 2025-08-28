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

// Autoloaded class file; MOODLE_INTERNAL guard intentionally omitted.

namespace block_aipromptgen\local\provider;

use moodle_exception;

/**
 * Ollama provider (local server) implementation.
 *
 * @package   block_aipromptgen
 * @copyright 2025 AI4Teachers
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ollama_provider implements provider_interface {
    /**
     * Base endpoint URL for the Ollama server (no trailing slash after normalisation).
     *
     * @var string
     */
    protected $endpoint;
    /**
     * Model name to use for completion (e.g. llama3).
     *
     * @var string
     */
    protected $model;

    /**
     * Constructor.
     *
     * @param string $endpoint Ollama endpoint base URL
     * @param string $model Model name
     */
    public function __construct(string $endpoint, string $model) {
        $this->endpoint = rtrim($endpoint ?: 'http://localhost:11434', '/');
        $this->model = $model ?: 'llama3';
    }

    /**
     * Send a prompt to the Ollama API and return the AI response.
     *
     * @param string $prompt Prompt text
     * @return string AI response
     * @throws moodle_exception On curl or API failure
     */
    public function complete(string $prompt): string {
        $url = $this->endpoint . '/api/chat';
        $payload = json_encode([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful teaching assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'stream' => false,
        ]);
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($curl);
        if ($response === false) {
            $err = curl_error($curl);
            curl_close($curl);
            throw new moodle_exception('error:curl', 'block_aipromptgen', '', null, $err);
        }
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        $data = json_decode($response, true);
        if ($status >= 400 || !isset($data['message']['content'])) {
            // Some Ollama versions return choices style; fallback try.
            if (isset($data['choices'][0]['message']['content'])) {
                return (string)$data['choices'][0]['message']['content'];
            }
            throw new moodle_exception('error:apifailed', 'block_aipromptgen', '', null, substr($response, 0, 200));
        }
        return (string)$data['message']['content'];
    }
}
