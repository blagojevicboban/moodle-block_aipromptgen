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

// Autoloaded class file; MOODLE_INTERNAL guard intentionally omitted per Moodle coding guidelines.

namespace block_aipromptgen\local\provider;

use moodle_exception;

/**
 * Provider factory to instantiate correct provider based on config.
 *
 * @package   block_aipromptgen
 * @copyright 2025 AI4Teachers
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class factory {
    /**
     * Build provider instance based on config.
     *
     * @return provider_interface
     */
    public static function make(): provider_interface {
        // Prefer core AI manager if available.
        if (class_exists('core_ai\\manager') || class_exists('\\core_ai\\manager')) {
            $model = get_config('block_aipromptgen', 'openai_model') ?: get_config('block_aipromptgen', 'ollama_model') ?: null;
            return new coreai_provider($model);
        }
        // Fallback legacy.
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
