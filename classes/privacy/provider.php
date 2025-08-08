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

namespace block_ai4teachers\privacy;

/**
 * Privacy Subsystem implementation for block_ai4teachers.
 *
 * Declares that this plugin does not store any personal data.
 *
 * @package    block_ai4teachers
 * @category   privacy
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Get the language string identifier with the reason for no data storage.
     *
     * @return string The language string with the reason.
     */
    public static function get_reason() : string {
        return get_string('privacy:metadata', 'block_ai4teachers');
    }
}
