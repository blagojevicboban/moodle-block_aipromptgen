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

// Ensure the settings page exists and is registered in the admin tree so a Settings link appears.
if (!isset($settings)) {
    $settings = new admin_settingpage('block_aipromptgen', get_string('pluginname', 'block_aipromptgen'));
    $ADMIN->add('blocksettings', $settings);
}

if ($ADMIN->fulltree) {
    // OpenAI API key (password-unmask field).
    $settings->add(new admin_setting_configpasswordunmask(
        'block_aipromptgen/openai_apikey',
        get_string('setting:apikey', 'block_aipromptgen'),
        get_string('setting:apikey_desc', 'block_aipromptgen'),
        ''
    ));

    // Default model to use.
    $settings->add(new admin_setting_configtext(
        'block_aipromptgen/openai_model',
        get_string('setting:model', 'block_aipromptgen'),
        get_string('setting:model_desc', 'block_aipromptgen'),
        'gpt-4o-mini'
    ));
}
