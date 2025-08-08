<?php
// This file is part of Moodle - http://moodle.org/

class block_ai4teachers extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_ai4teachers');
    }

    public function applicable_formats() {
        return ['course-view' => true, 'site-index' => false, 'mod' => false];
    }

    public function get_content() {
        global $COURSE, $OUTPUT, $PAGE;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();

        // Capability check: only users with manage capability see content.
        $context = context_course::instance($COURSE->id);
        if (!has_capability('block/ai4teachers:manage', $context)) {
            $this->content->text = get_string('notallowed', 'block_ai4teachers');
            $this->content->footer = '';
            return $this->content;
        }

        $url = new moodle_url('/blocks/ai4teachers/view.php', ['courseid' => $COURSE->id]);
        $link = html_writer::link($url, get_string('openpromptbuilder', 'block_ai4teachers'), ['class' => 'btn btn-primary']);
        $this->content->text = html_writer::div($link);
        $this->content->footer = '';
        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function hide_header() {
        return false;
    }

    public function is_empty() {
        global $COURSE;
        $context = context_course::instance($COURSE->id);
        return !has_capability('block/ai4teachers:manage', $context);
    }
}
