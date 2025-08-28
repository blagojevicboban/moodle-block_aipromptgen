<?php
// This file is part of Moodle - http://moodle.org/.
//
// GNU GPL v3 or later.

namespace block_aipromptgen\local\provider;

use moodle_exception;
use core\text; // For potential cleaning.

/**
 * OpenAI provider implementation.
 *
 * @package   block_aipromptgen
 */
class openai_provider implements provider_interface {
    /** @var string $apikey */
    protected $apikey;
    /** @var string $model */
    protected $model;

    public function __construct(string $apikey, string $model) {
        $this->apikey = $apikey;
        $this->model = $model ?: 'gpt-4o-mini';
    }

    public function complete(string $prompt): string {
        global $CFG; // For curl class include if needed.
        if (empty($this->apikey)) {
            throw new moodle_exception('missingapikey', 'block_aipromptgen');
        }
        $url = 'https://api.openai.com/v1/chat/completions';
        $payload = json_encode([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful teaching assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);
        $timeout = 60; // Seconds; increased to reduce timeout failures on slower networks.
        // Prefer Moodle core curl for proxy and TLS settings.
        if (!class_exists('curl')) {
            require_once($CFG->libdir . '/filelib.php');
        }
        if (class_exists('curl')) {
            $curl = new \curl();
            $options = [
                'CURLOPT_HTTPHEADER' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apikey,
                ],
                'CURLOPT_TIMEOUT' => $timeout,
                'CURLOPT_CONNECTTIMEOUT' => 15,
            ];
            try {
                // Correct signature: (url, params/body, options array) – headers inside options.
                $response = $curl->post($url, $payload, $options);
            } catch (\Throwable $e) {
                throw new moodle_exception('error:curl', 'block_aipromptgen', '', null, $e->getMessage());
            }
        } else {
            // Fallback to raw curl (unlikely since Moodle ships curl class).
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apikey,
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 15,
            ]);
            $response = curl_exec($ch);
            if ($response === false) {
                $errno = curl_errno($ch);
                $err = curl_error($ch);
                curl_close($ch);
                if ($errno === CURLE_OPERATION_TIMEDOUT) {
                    $err .= ' (timeout after ' . $timeout . 's)';
                }
                throw new moodle_exception('error:curl', 'block_aipromptgen', '', null, $err);
            }
            $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            $data = json_decode($response, true);
            if ($status >= 400 || !isset($data['choices'][0]['message']['content'])) {
                throw new moodle_exception('error:apifailed', 'block_aipromptgen', '', null, substr($response, 0, 200));
            }
            return (string)$data['choices'][0]['message']['content'];
        }
        // If using Moodle curl class, we need to fetch HTTP status manually.
        $info = $curl->get_info();
        $status = isset($info['http_code']) ? (int)$info['http_code'] : 0;
        if ($status === 0) {
            // Try to detect timeout by measuring length and include hint.
            if (empty($response)) {
                throw new moodle_exception('error:curl', 'block_aipromptgen', '', null, 'Empty response (possible timeout after ' . $timeout . 's).');
            }
        }
        $data = json_decode($response, true);
        if ($status >= 400 || !isset($data['choices'][0]['message']['content'])) {
            throw new moodle_exception('error:apifailed', 'block_aipromptgen', '', null, substr($response, 0, 200));
        }
        return (string)$data['choices'][0]['message']['content'];
    }
}
