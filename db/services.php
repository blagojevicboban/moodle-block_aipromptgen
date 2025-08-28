<?php
// This file is part of Moodle - http://moodle.org/.
//
// GNU GPL v3 or later.

$functions = [
    'block_aipromptgen_send_prompt' => [
        'classname' => 'block_aipromptgen\\external\\send_prompt',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Send AI prompt and return response (async).',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'block/aipromptgen:manage',
    ],
];
