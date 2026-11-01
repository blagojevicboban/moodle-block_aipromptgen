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
 * AI client: builds and sends requests to all supported AI providers.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_aipromptgen;

use curl;
use moodle_exception;

/**
 * AI client for OpenAI, Gemini, Claude, DeepSeek, a custom OpenAI-compatible API and Ollama.
 *
 * The same request is used with and without streaming, so both paths send the same model,
 * system prompt and limits.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_client {
    /** @var string[] Supported providers. */
    public const PROVIDERS = ['openai', 'gemini', 'claude', 'deepseek', 'custom', 'ollama'];

    /** @var string[] Model used when the admin setting is empty. */
    public const DEFAULT_MODELS = [
        'openai' => 'gpt-5-mini',
        'gemini' => 'gemini-3.8-flash',
        'claude' => 'claude-opus-5-5',
        'deepseek' => 'deepseek-chat',
        'custom' => '',
        'ollama' => 'llama3',
    ];

    /** @var int Output token ceiling for models that reason before they answer (reasoning counts towards the limit). */
    private const REASONING_MIN_TOKENS = 16000;

    /** @var int Timeout in seconds for cloud providers. */
    private const CLOUD_TIMEOUT = 300;

    /** @var \stdClass Plugin configuration. */
    private $config;

    /** @var float AI response temperature (0.0 - 2.0). */
    private $temperature;

    /** @var int Maximum tokens in the AI response. */
    private $maxtokens;

    /**
     * Constructor. Reads the plugin configuration.
     */
    public function __construct() {
        $this->config = get_config('block_aipromptgen');

        $rawtemp = $this->setting('temperature');
        $this->temperature = $rawtemp !== '' ? (float)$rawtemp : 0.7;

        $rawmax = $this->setting('max_tokens');
        $this->maxtokens = ($rawmax !== '' && (int)$rawmax > 0) ? (int)$rawmax : 1024;
    }

    /**
     * Read a trimmed plugin setting.
     *
     * @param string $name Setting name.
     * @return string Value, or '' when not set.
     */
    private function setting(string $name): string {
        return trim((string)($this->config->$name ?? ''));
    }

    /**
     * Get the configured model for a provider, falling back to the default model.
     *
     * @param string $provider Provider name.
     * @return string
     */
    private function model(string $provider): string {
        $model = $this->setting($provider . '_model');
        return $model !== '' ? $model : self::DEFAULT_MODELS[$provider];
    }

    /**
     * Get the configured system prompt, falling back to the default lang string.
     *
     * @return string
     */
    public function get_system_prompt(): string {
        $configured = $this->setting('system_prompt');
        return $configured !== '' ? $configured : get_string('system_role', 'block_aipromptgen');
    }

    /**
     * Whether a provider has the settings it needs (API key or endpoint).
     *
     * @param string $provider Provider name.
     * @return bool
     */
    public function is_configured(string $provider): bool {
        switch ($provider) {
            case 'custom':
                return $this->setting('custom_endpoint') !== '';
            case 'ollama':
                return $this->setting('ollama_endpoint') !== '';
            default:
                return in_array($provider, self::PROVIDERS) && $this->setting($provider . '_apikey') !== '';
        }
    }

    /**
     * Build the HTTP request for a provider.
     *
     * @param string $provider Provider name (one of self::PROVIDERS).
     * @param string $prompt The user prompt.
     * @param bool $stream Whether the response should be streamed.
     * @return array url, headers, payload, format (chat|claude|gemini|ollama), selfhosted, timeout, schema
     * @throws moodle_exception When the provider is unknown or not configured.
     */
    public function build_request(string $provider, string $prompt, bool $stream): array {
        if (!in_array($provider, self::PROVIDERS)) {
            throw new moodle_exception('error_unknownprovider', 'block_aipromptgen', '', s($provider));
        }
        if (!$this->is_configured($provider)) {
            $missing = [
                'openai' => 'error_noapikey',
                'gemini' => 'error_nogeminiapikey',
                'claude' => 'error_noclaudeapikey',
                'deepseek' => 'error_nodeepseek_apikey',
                'custom' => 'error_nocustom_endpoint',
                'ollama' => 'error_noendpoint',
            ];
            throw new moodle_exception($missing[$provider], 'block_aipromptgen');
        }

        $model = $this->model($provider);
        $system = $this->get_system_prompt();
        $request = [
            'headers' => ['Content-Type: application/json'],
            'format' => 'chat',
            'selfhosted' => false,
            'timeout' => self::CLOUD_TIMEOUT,
            'schema' => false,
        ];
        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $prompt],
        ];

        switch ($provider) {
            case 'openai':
                $request['url'] = 'https://api.openai.com/v1/chat/completions';
                $request['headers'][] = 'Authorization: Bearer ' . $this->setting('openai_apikey');
                $body = ['model' => $model, 'messages' => $messages, 'max_completion_tokens' => $this->maxtokens];
                // GPT-5 and o-series models reason first and accept only the default temperature.
                if (preg_match('~^(o\d|gpt-5)(?!.*-chat)~i', $model)) {
                    $body['max_completion_tokens'] = max($this->maxtokens, self::REASONING_MIN_TOKENS);
                } else {
                    $body['temperature'] = $this->temperature;
                }
                break;

            case 'deepseek':
            case 'custom':
                if ($provider === 'deepseek') {
                    $request['url'] = 'https://api.deepseek.com/v1/chat/completions';
                    $apikey = $this->setting('deepseek_apikey');
                } else {
                    $request['url'] = $this->setting('custom_endpoint');
                    $apikey = $this->setting('custom_apikey');
                    $request['selfhosted'] = true;
                }
                if ($apikey !== '') {
                    $request['headers'][] = 'Authorization: Bearer ' . $apikey;
                }
                $body = [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxtokens,
                ];
                break;

            case 'claude':
                $request['url'] = 'https://api.anthropic.com/v1/messages';
                $request['format'] = 'claude';
                $request['headers'][] = 'x-api-key: ' . $this->setting('claude_apikey');
                $request['headers'][] = 'anthropic-version: 2023-06-01';
                // Current Claude models always think before answering and reject a non-default temperature.
                $body = [
                    'model' => $model,
                    'max_tokens' => max($this->maxtokens, self::REASONING_MIN_TOKENS),
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ];
                break;

            case 'gemini':
                // The key goes in a header, not in the URL, so it cannot leak into error messages or logs.
                $request['url'] = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) .
                    ($stream ? ':streamGenerateContent?alt=sse' : ':generateContent');
                $request['format'] = 'gemini';
                $request['headers'][] = 'x-goog-api-key: ' . $this->setting('gemini_apikey');
                $body = [
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'system_instruction' => ['parts' => [['text' => $system]]],
                    'generationConfig' => [
                        'temperature' => $this->temperature,
                        // Thinking tokens of Gemini 2.5+ count towards this limit.
                        'maxOutputTokens' => max($this->maxtokens, self::REASONING_MIN_TOKENS),
                    ],
                ];
                break;

            case 'ollama':
                $request['url'] = rtrim($this->setting('ollama_endpoint'), '/') . '/api/generate';
                $request['format'] = 'ollama';
                $request['selfhosted'] = true;
                $timeout = (int)$this->setting('ollama_timeout');
                $request['timeout'] = $timeout >= 30 ? $timeout : 180;
                $numpredict = (int)$this->setting('ollama_num_predict');
                $schema = json_decode($this->setting('ollama_schema'), true);
                $request['schema'] = is_array($schema);
                $body = [
                    'model' => $model,
                    'system' => $system,
                    'prompt' => $prompt,
                    'options' => [
                        'num_predict' => $numpredict > 0 ? $numpredict : $this->maxtokens,
                        // A JSON schema needs deterministic output.
                        'temperature' => $request['schema'] ? 0 : $this->temperature,
                    ],
                ];
                if ($request['schema']) {
                    $body['format'] = $schema;
                }
                break;
        }

        // Every provider has an explicit stream flag except Gemini, which uses a different URL.
        if ($provider !== 'gemini') {
            $body['stream'] = $stream;
        }
        $request['payload'] = json_encode($body, JSON_UNESCAPED_UNICODE);
        return $request;
    }

    /**
     * Create a curl client for a request.
     *
     * @param array $request Request from build_request().
     * @return array [curl, options]
     */
    private function make_curl(array $request): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        // Moodle's curl security blocks localhost and private networks, where self-hosted servers usually run.
        $curl = new curl(['ignoresecurity' => $request['selfhosted']]);
        foreach ($request['headers'] as $header) {
            $curl->setHeader($header);
        }
        $options = [
            'CURLOPT_TIMEOUT' => $request['timeout'],
            'CURLOPT_CONNECTTIMEOUT' => 20,
        ];
        // Local servers often use a self-signed certificate.
        if ($request['selfhosted'] && helper::is_private_endpoint($request['url'])) {
            $options['CURLOPT_SSL_VERIFYHOST'] = 0;
            $options['CURLOPT_SSL_VERIFYPEER'] = false;
        }
        return [$curl, $options];
    }

    /**
     * Send a prompt to a provider and return the whole response.
     *
     * @param string $provider Provider name.
     * @param string $prompt The prompt text.
     * @return string The AI response text, or an error message.
     */
    public function send_request(string $provider, string $prompt): string {
        try {
            $request = $this->build_request($provider, $prompt, false);
        } catch (moodle_exception $e) {
            return $e->getMessage();
        }

        [$curl, $options] = $this->make_curl($request);
        $options['CURLOPT_RETURNTRANSFER'] = true;
        $response = (string)$curl->post($request['url'], $request['payload'], $options);
        if ($curl->get_errno()) {
            return get_string('error_connection', 'block_aipromptgen', $curl->error);
        }

        if ($request['format'] === 'ollama') {
            return $this->process_ollama_response($response, $request['schema']);
        }

        $json = json_decode($response, true);
        $text = is_array($json) ? $this->extract_text($request['format'], $json, false) : null;
        if ($text !== null) {
            return $text;
        }
        return $this->error_from_body($response);
    }

    /**
     * Send a prompt to a provider and pass the response text to a callback as it arrives.
     *
     * @param string $provider Provider name.
     * @param string $prompt The prompt text.
     * @param callable $onchunk Called with each piece of response text.
     * @return string|null Error message, or null on success.
     */
    public function stream_request(string $provider, string $prompt, callable $onchunk): ?string {
        try {
            $request = $this->build_request($provider, $prompt, true);
        } catch (moodle_exception $e) {
            return $e->getMessage();
        }

        $format = $request['format'];
        $buffer = '';
        $other = '';
        $emitted = false;
        $error = null;

        // Ollama sends NDJSON; the other providers send Server-Sent Events ("data: {...}" lines).
        $handleline = function (string $line) use ($format, $onchunk, &$other, &$emitted, &$error): void {
            $line = trim($line);
            if ($line === '') {
                return;
            }
            if ($format !== 'ollama') {
                if (strpos($line, 'event:') === 0) {
                    return;
                }
                if (strpos($line, 'data:') !== 0) {
                    // Not an event: usually a JSON error body after an HTTP error status.
                    $other .= $line . "\n";
                    return;
                }
                $line = trim(substr($line, 5));
                if ($line === '[DONE]') {
                    return;
                }
            }
            $obj = json_decode($line, true);
            if (!is_array($obj)) {
                $other .= $line . "\n";
                return;
            }
            $message = $this->error_message($obj);
            if ($message !== null) {
                $error = get_string('error_api', 'block_aipromptgen', $message);
                return;
            }
            $text = $this->extract_text($format, $obj, true);
            if ($text !== null && $text !== '') {
                $emitted = true;
                $onchunk($text);
            }
        };

        [$curl, $options] = $this->make_curl($request);
        $options['CURLOPT_RETURNTRANSFER'] = false;
        $options['CURLOPT_WRITEFUNCTION'] = function ($ch, $chunk) use (&$buffer, $handleline) {
            $buffer .= $chunk;
            while (($pos = strpos($buffer, "\n")) !== false) {
                $handleline(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);
            }
            return strlen($chunk);
        };

        $curl->post($request['url'], $request['payload'], $options);
        $handleline($buffer);

        if ($curl->get_errno()) {
            return get_string('error_connection', 'block_aipromptgen', $curl->error);
        }
        if ($error !== null) {
            return $error;
        }
        if (!$emitted && trim($other) !== '') {
            return $this->error_from_body($other);
        }
        return null;
    }

    /**
     * Extract the response text from a decoded response or stream event.
     *
     * @param string $format Response format (chat, claude, gemini, ollama).
     * @param array $obj Decoded JSON.
     * @param bool $stream Whether $obj is a stream event.
     * @return string|null Text, or null when $obj carries no text.
     */
    private function extract_text(string $format, array $obj, bool $stream): ?string {
        switch ($format) {
            case 'chat':
                $key = $stream ? 'delta' : 'message';
                $content = $obj['choices'][0][$key]['content'] ?? null;
                return is_string($content) ? $content : null;

            case 'claude':
                if ($stream) {
                    // Thinking blocks stream as thinking_delta events; show only the answer.
                    $istext = ($obj['type'] ?? '') === 'content_block_delta' && ($obj['delta']['type'] ?? '') === 'text_delta';
                    return $istext ? (string)$obj['delta']['text'] : null;
                }
                if (!isset($obj['content']) || !is_array($obj['content'])) {
                    return null;
                }
                // The first content block can be a thinking block, so join all text blocks.
                $text = '';
                foreach ($obj['content'] as $block) {
                    if (($block['type'] ?? '') === 'text') {
                        $text .= $block['text'];
                    }
                }
                return $text;

            case 'gemini':
                $parts = $obj['candidates'][0]['content']['parts'] ?? null;
                if (!is_array($parts)) {
                    return null;
                }
                $text = '';
                foreach ($parts as $part) {
                    if (isset($part['text']) && empty($part['thought'])) {
                        $text .= $part['text'];
                    }
                }
                return $text;

            case 'ollama':
                return isset($obj['response']) ? (string)$obj['response'] : null;
        }
        return null;
    }

    /**
     * Get the error message from a decoded API response, if it is an error.
     *
     * @param array $obj Decoded JSON.
     * @return string|null
     */
    private function error_message(array $obj): ?string {
        if (!isset($obj['error'])) {
            return null;
        }
        if (is_array($obj['error'])) {
            return (string)($obj['error']['message'] ?? json_encode($obj['error']));
        }
        return (string)$obj['error'];
    }

    /**
     * Turn a response body without text into an error message.
     *
     * @param string $body Raw response body.
     * @return string
     */
    private function error_from_body(string $body): string {
        $json = json_decode(trim($body), true);
        if (is_array($json)) {
            // Some APIs wrap the error in an array.
            $first = isset($json[0]) && is_array($json[0]) ? $json[0] : $json;
            $message = $this->error_message($first);
            if ($message !== null) {
                return get_string('error_api', 'block_aipromptgen', $message);
            }
        }
        return get_string('error_unknownresponse', 'block_aipromptgen', \core_text::substr(trim($body), 0, 200));
    }

    /**
     * Process an Ollama response.
     *
     * @param string $rawresponse Raw response body.
     * @param bool $hasschema Whether a JSON schema was requested.
     * @return string
     */
    private function process_ollama_response(string $rawresponse, bool $hasschema): string {
        $fulltext = '';
        foreach (preg_split("/(?:\r\n|\n|\r)/", trim($rawresponse)) as $line) {
            $obj = json_decode($line, true);
            if (!is_array($obj)) {
                continue;
            }
            $message = $this->error_message($obj);
            if ($message !== null) {
                return get_string('error_api', 'block_aipromptgen', $message);
            }
            $fulltext .= (string)($obj['response'] ?? '');
        }

        if ($hasschema) {
            // Pretty print the JSON object of a schema response.
            $start = strpos($fulltext, '{');
            $end = strrpos($fulltext, '}');
            if ($start !== false && $end !== false) {
                $decoded = json_decode(substr($fulltext, $start, $end - $start + 1));
                if ($decoded) {
                    return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return $fulltext !== '' ? $fulltext : $this->error_from_body($rawresponse);
    }
}
