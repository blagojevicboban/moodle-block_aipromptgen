<?php
// This file is part of Moodle - http://moodle.org/.
//
// GNU GPL v3 or later.

namespace block_aipromptgen\local\provider;

use moodle_exception;

/**
 * Ollama provider (local server) implementation.
 *
 * @package   block_aipromptgen
 */
class ollama_provider implements provider_interface {
    protected $endpoint;
    protected $model;

    public function __construct(string $endpoint, string $model) {
        $this->endpoint = rtrim($endpoint ?: 'http://localhost:11434', '/');
        $this->model = $model ?: 'llama3';
    }

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
