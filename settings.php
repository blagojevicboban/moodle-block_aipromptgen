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
 * Settings for the AI Prompt Generator block.
 *
 * @package    block_aipromptgen
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // OpenAI API key (password-unmask field).
    $settings->add(new admin_setting_configpasswordunmask(
        'block_aipromptgen/openai_apikey',
        get_string('setting:apikey', 'block_aipromptgen'),
        get_string('setting:apikey_desc', 'block_aipromptgen'),
        ''
    ));

    // Default OpenAI model.
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/openai_model',
        get_string('setting:model', 'block_aipromptgen'),
        get_string('setting:model_desc', 'block_aipromptgen'),
        'gpt-4o-mini',
        PARAM_TEXT
    ));

    // Ollama endpoint.
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/ollama_endpoint',
        get_string('setting:ollama_endpoint', 'block_aipromptgen'),
        get_string('setting:ollama_endpoint_desc', 'block_aipromptgen'),
        'http://localhost:11434',
        PARAM_URL
    ));

    // Ollama model (allow dots/colons via PARAM_TEXT).
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/ollama_model',
        get_string('setting:ollama_model', 'block_aipromptgen'),
        get_string('setting:ollama_model_desc', 'block_aipromptgen'),
        'llama3',
        PARAM_TEXT // Allow dots/colons.
    ));

    // Optional JSON Schema for structured output.
    $settings->add(new admin_setting_configtextarea(
        'block_aipromptgen/ollama_schema',
        get_string('setting:ollama_schema', 'block_aipromptgen'),
        get_string('setting:ollama_schema_desc', 'block_aipromptgen'),
        ''
    ));

    // Max tokens (num_predict).
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/ollama_num_predict',
        get_string('setting:ollama_num_predict', 'block_aipromptgen'),
        get_string('setting:ollama_num_predict_desc', 'block_aipromptgen'),
        512,
        PARAM_INT
    ));

    // Timeout seconds.
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/ollama_timeout',
        get_string('setting:ollama_timeout', 'block_aipromptgen'),
        get_string('setting:ollama_timeout_desc', 'block_aipromptgen'),
        90,
        PARAM_INT
    ));
}

