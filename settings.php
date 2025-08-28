<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

// Ensure the settings page exists and is registered under Block settings.
if ($hassiteconfig) {
    if (!isset($settings) || !($settings instanceof admin_settingpage)) {
        $settings = new admin_settingpage('blocksettingaipromptgen',
            get_string('pluginname', 'block_aipromptgen'));
        $ADMIN->add('blocksettings', $settings);
    }

    if ($ADMIN->fulltree) {
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
    }
}
