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
 * Settings definitions for the AI Prompt Generator block.
 *
 * Creates/augments the admin settings page allowing site administrators
 * to configure provider selection, OpenAI credentials/model and optional
 * Ollama endpoint, model and streaming toggle.
 *
 * @package    block_aipromptgen
 * @category   admin
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Ensure the settings page exists and is registered under Block settings.
if ($hassiteconfig) {
    if (!isset($settings) || !($settings instanceof admin_settingpage)) {
        $settings = new admin_settingpage('blocksettingaipromptgen',
            get_string('pluginname', 'block_aipromptgen'));
        $ADMIN->add('blocksettings', $settings);
    }

    if ($ADMIN->fulltree) {
        // Provider selection (OpenAI or Ollama) if not already added elsewhere.
        if (!$settings->get_setting('block_aipromptgen/provider')) {
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
        }
        // OpenAI API key (password-unmask field).
        $setting = new admin_setting_configpasswordunmask(
            'block_aipromptgen/openai_apikey',
            get_string('setting:apikey', 'block_aipromptgen'),
            get_string('setting:apikey_desc', 'block_aipromptgen'),
            ''
        );
        $settings->add($setting);

        // Default model to use.
        $setting = new admin_setting_configtext(
            'block_aipromptgen/openai_model',
            get_string('setting:model', 'block_aipromptgen'),
            get_string('setting:model_desc', 'block_aipromptgen'),
            'gpt-4o-mini', PARAM_RAW_TRIMMED
        );
        $settings->add($setting);

        // Ollama endpoint & model (only show if provider enabled, but inexpensive so always display).
        if (!$settings->get_setting('block_aipromptgen/ollama_endpoint')) {
            $settings->add(new admin_setting_configtext(
                'block_aipromptgen/ollama_endpoint',
                get_string('setting:ollama_endpoint', 'block_aipromptgen'),
                get_string('setting:ollama_endpoint_desc', 'block_aipromptgen'),
                'http://localhost:11434',
                PARAM_RAW_TRIMMED
            ));
        }
        if (!$settings->get_setting('block_aipromptgen/ollama_model')) {
            $settings->add(new admin_setting_configtext(
                'block_aipromptgen/ollama_model',
                get_string('setting:ollama_model', 'block_aipromptgen'),
                get_string('setting:ollama_model_desc', 'block_aipromptgen'),
                'llama3',
                PARAM_ALPHANUMEXT
            ));
        }
        // Streaming enable toggle for Ollama.
        if (!$settings->get_setting('block_aipromptgen/ollama_stream')) {
            $settings->add(new admin_setting_configcheckbox(
                'block_aipromptgen/ollama_stream',
                get_string('setting:ollama_stream', 'block_aipromptgen'),
                get_string('setting:ollama_stream_desc', 'block_aipromptgen'),
                0
            ));
        }
    }
}
