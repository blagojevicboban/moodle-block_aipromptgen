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

defined('MOODLE_INTERNAL') || die();

/**
 * Block AI for Teachers main block class.
 *
 * @package    block_ai4teachers
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_ai4teachers extends block_base {
    /**
     * Initialise the block title.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_ai4teachers');
    }

    /**
     * Where this block can be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['course-view' => true, 'site-index' => false, 'mod' => false];
    }

    /**
     * Generate the content for the block.
     *
     * @return stdClass
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();

        // Capability check: only users with manage capability see content.
        $courseid = $this->page->course->id;
        $context = context_course::instance($courseid);
        if (!has_capability('block/ai4teachers:manage', $context)) {
            $this->content->text = get_string('notallowed', 'block_ai4teachers');
            $this->content->footer = '';
            return $this->content;
        }

        $url = new moodle_url('/blocks/ai4teachers/view.php', ['courseid' => $courseid]);
        $link = html_writer::link($url, get_string('openpromptbuilder', 'block_ai4teachers'), ['class' => 'btn btn-primary']);
        $this->content->text = html_writer::div($link);
        $this->content->footer = '';
        return $this->content;
    }

    /**
     * Whether multiple instances of this block are allowed.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Whether to hide the block header.
     *
     * @return bool
     */
    public function hide_header() {
        return false;
    }

    /**
     * Whether the block has any content for the current user/context.
     *
     * @return bool
     */
    public function is_empty() {
        $courseid = $this->page->course->id;
        $context = context_course::instance($courseid);
        return !has_capability('block/ai4teachers:manage', $context);
    }
}
