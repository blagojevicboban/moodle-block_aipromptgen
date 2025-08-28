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
 * Version details for the AI for Teachers block.
 *
 * @package    block_aipromptgen
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Moodle supplies $settings (admin_settingpage) when building the admin tree.
if ($ADMIN->fulltree && isset($settings)) {
    // Provider selection (OpenAI default, Ollama optional self-hosted).
    $settings->add(new admin_setting_configselect(
        'block_aipromptgen/provider',
        get_string('setting:provider', 'block_aipromptgen'),
        get_string('setting:provider_desc', 'block_aipromptgen'),
        'openai',
        [
            'openai' => get_string('setting:provider_openai', 'block_aipromptgen'),
            'ollama' => get_string('setting:provider_ollama', 'block_aipromptgen'),
        ]
    ));
    // OpenAI API key (password-unmask field).
    $settings->add(new admin_setting_configpasswordunmask(
        'block_aipromptgen/openai_apikey',
        get_string('setting:apikey', 'block_aipromptgen'),
        get_string('setting:apikey_desc', 'block_aipromptgen'),
        ''
    ));

    // OpenAI default model.
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/openai_model',
        get_string('setting:model', 'block_aipromptgen'),
        get_string('setting:model_desc', 'block_aipromptgen'),
        'gpt-4o-mini',
        PARAM_TEXT
    ));

    // Ollama endpoint (local server) – default local daemon.
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/ollama_endpoint',
        get_string('setting:ollama_endpoint', 'block_aipromptgen'),
        get_string('setting:ollama_endpoint_desc', 'block_aipromptgen'),
        'http://localhost:11434',
        PARAM_RAW_TRIMMED
    ));
    // Ollama model (must exist locally, e.g., llama3, mistral, codellama, etc.).
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/ollama_model',
        get_string('setting:ollama_model', 'block_aipromptgen'),
        get_string('setting:ollama_model_desc', 'block_aipromptgen'),
        'llama3',
        PARAM_ALPHANUMEXT
    ));
}
