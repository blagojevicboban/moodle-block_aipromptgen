<?php
// Privacy subsystem for block_ai4teachers: no personal data stored.

namespace block_ai4teachers\privacy;

defined('MOODLE_INTERNAL') || die();

class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return get_string('privacy:metadata', 'block_ai4teachers');
    }
}
