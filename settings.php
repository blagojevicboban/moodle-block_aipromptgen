<?php
// This file is part of Moodle - http://moodle.org/.

defined('MOODLE_INTERNAL') || die();

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
