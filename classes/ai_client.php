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

namespace block_aipromptgen;

use curl;

/**
 * AI Client class to handle OpenAI and Ollama requests.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_client
{
    /** @var string OpenAI API Key */
    private $openaikey;

    /** @var string OpenAI Model */
    private $openaimodel;

    /** @var string Ollama Endpoint */
    private $ollamaendpoint;

    /** @var string Ollama Model */
    private $ollamamodel;

    /**
     * Constructor. Retrieves configuration settings.
     */
    public function __construct() {
        $this->openaikey = get_config('block_aipromptgen', 'openai_apikey');
        $this->openaimodel = get_config('block_aipromptgen', 'openai_model') ?: 'gpt-3.5-turbo';

        $this->ollamaendpoint = get_config('block_aipromptgen', 'ollama_endpoint');
        $this->ollamamodel = get_config('block_aipromptgen', 'ollama_model') ?: 'llama3';
    }

    /**
     * Send a prompt to the specified provider.
     *
     * @param string $provider 'openai' or 'ollama'
     * @param string $prompt The prompt text
     * @return string The AI response text
     */
    public function send_request(string $provider, string $prompt): string {
        if ($provider === 'openai') {
            return $this->send_to_openai($prompt);
        } else if ($provider === 'ollama') {
            return $this->send_to_ollama($prompt);
        }
        return 'Unknown provider specified.';
    }

    /**
     * Send request to OpenAI.
     *
     * @param string $prompt The prompt to send.
     * @return string The API response.
     */
    private function send_to_openai(string $prompt): string {
        if (empty($this->openaikey)) {
            return get_string('error:noapikey', 'block_aipromptgen');
        }

        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaikey,
        ];

        $payload = json_encode([
            'model' => $this->openaimodel,
            'messages' => [
                ['role' => 'system', 'content' => get_string('system_role', 'block_aipromptgen')],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        return $this->perform_curl_request($endpoint, $payload, $headers);
    }

    /**
     * Send request to Ollama (Synchronous Fallback).
     *
     * @param string $prompt The prompt to send.
     * @return string The API response.
     */
    private function send_to_ollama(string $prompt): string {
        if (empty($this->ollamaendpoint)) {
            return get_string('error:noendpoint', 'block_aipromptgen');
        }

        $endpoint = rtrim($this->ollamaendpoint, '/') . '/api/generate';
        $payload = json_encode([
            'model' => $this->ollamamodel,
            'prompt' => $prompt,
            'stream' => false,
            'options' => ['num_predict' => 512, 'temperature' => 0.7],
        ]);

        // Ollama usually runs on local network/localhost, SSL might be self-signed or HTTP.
        return $this->perform_curl_request($endpoint, $payload, [], 60, true, true);
    }

    /**
     * Execute cURL request.
     *
     * @param string $endpoint
     * @param string $payload
     * @param array $headers
     * @param int $timeout
     * @param bool $ignoresecurity
     * @param bool $isollama
     * @return string
     */
    private function perform_curl_request(
        string $endpoint,
        string $payload,
        array $headers = [],
        int $timeout = 60,
        bool $ignoresecurity = false,
        bool $isollama = false
    ): string {
        $curl = new curl();

        $options = [
            'CURLOPT_TIMEOUT' => $timeout,
            'CURLOPT_CONNECTTIMEOUT' => 20,
            'CURLOPT_RETURNTRANSFER' => true,
        ];

        if ($ignoresecurity) {
            $options['CURLOPT_SSL_VERIFYHOST'] = 0;
            $options['CURLOPT_SSL_VERIFYPEER'] = false;
        }

        // Moodle curl class helper usually takes options as array keys without CURLOPT_ prefix for some properties
        // but $curl->post($url, $params, $options) where options are curl constants.
        // Let's stick to standard Moodle curl usage.

        // Headers are passed to set_header generally or in options? Moodle `curl` class has `setHeader`.
        foreach ($headers as $h) {
            $curl->setHeader($h);
        }

        // Moodle curl::post($url, $params, $options = array())
        // For JSON payload, we send it as string in $params.

        $response = $curl->post($endpoint, $payload, $options);

        if ($curl->errno > 0) {
            return 'cURL Error (' . $curl->errno . '): ' . $curl->error;
        }

        if ($isollama) {
            return $this->process_ollama_response($response, false);
        }

        // OpenAI format.
        $json = json_decode($response, true);
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        } else if (isset($json['error']['message'])) {
            return 'API Error: ' . $json['error']['message'];
        }

        return 'Unknown response format: ' . substr($response, 0, 100) . '...';
    }

    /**
     * Process Ollama response.
     *
     * @param string $rawresponse
     * @param bool $hasschema
     * @return string
     */
    private function process_ollama_response(string $rawresponse, bool $hasschema): string {
        $trim = trim($rawresponse);
        // Check for simple JSON first (stream=false).
        $firstchar = substr($trim, 0, 1);
        if ($firstchar === '{') {
            $json = json_decode($trim, true);
            if (isset($json['response'])) {
                $text = $json['response'];
                if ($hasschema) {
                    // Try to pretty print JSON if it is a schema response.
                    $decoded = json_decode($text);
                    if ($decoded) {
                        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                }
                return $text;
            }
        }

        // Handle NDJSON stream accumulation (if it happened to return that way).
        $fulltext = '';
        foreach (preg_split("/(?:\r\n|\n|\r)/", $rawresponse) as $line) {
            if ($line === '') {
                continue;
            }
            $obj = json_decode($line, true);
            if (!is_array($obj)) {
                continue;
            }
            if (isset($obj['response'])) {
                $fulltext .= $obj['response'];
            }
            if (isset($obj['error'])) {
                return 'Ollama Error: ' . $obj['error'];
            }
        }

        if ($hasschema) {
            // Try to find JSON blob in fulltext.
            $start = strpos($fulltext, '{');
            $end = strrpos($fulltext, '}');
            if ($start !== false && $end !== false) {
                $cand = substr($fulltext, $start, $end - $start + 1);
                $decoded = json_decode($cand);
                if ($decoded) {
                    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return $fulltext ?: $rawresponse;
    }
}
