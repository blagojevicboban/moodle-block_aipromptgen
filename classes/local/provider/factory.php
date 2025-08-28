<?php
// This file is part of Moodle - http://moodle.org/.
//
// GNU GPL v3 or later.

namespace block_aipromptgen\local\provider;

use moodle_exception;

/**
 * Provider factory to instantiate correct provider based on config.
 *
 * @package block_aipromptgen
 */
class factory {
    /**
     * Build provider instance based on config.
     *
     * @return provider_interface
     */
    public static function make(): provider_interface {
        $provider = get_config('block_aipromptgen', 'provider') ?: 'openai';
        if ($provider === 'ollama') {
            $endpoint = get_config('block_aipromptgen', 'ollama_endpoint') ?: 'http://localhost:11434';
            $model = get_config('block_aipromptgen', 'ollama_model') ?: 'llama3';
            return new ollama_provider($endpoint, $model);
        }
        $apikey = get_config('block_aipromptgen', 'openai_apikey') ?: '';
        $model = get_config('block_aipromptgen', 'openai_model') ?: 'gpt-4o-mini';
        return new openai_provider($apikey, $model);
    }
}
