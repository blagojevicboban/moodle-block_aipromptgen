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
        return ['course-view' => true, 'site-index' => false, 'mod' => true];
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

        $sectionid = optional_param('section', 0, PARAM_INT);
        $params = ['courseid' => $courseid];
        // If rendered on a module page, include cmid for lesson defaulting.
        if (!empty($this->page->cm) && !empty($this->page->cm->id)) {
            $params['cmid'] = (int)$this->page->cm->id;
        }
        if (!empty($sectionid)) {
            $params['section'] = $sectionid;
        }
        $url = new moodle_url('/blocks/ai4teachers/view.php', $params);
        $link = html_writer::link($url, get_string('openpromptbuilder', 'block_ai4teachers'), [
            'class' => 'btn btn-primary',
            'id' => 'ai4t-open',
        ]);
        $this->content->text = html_writer::div($link);
        $this->content->footer = '';
        // Ensure the link carries the current section when present (all-formats friendly).
        $js = "(function(){\n"
            . "var a=document.getElementById('ai4t-open'); if(!a){return;}\n"
            . "try{\n"
            . "  var href=new URL(a.href, window.location.origin);\n"
            . "  var sec=(new URLSearchParams(window.location.search)).get('section');\n"
            . "  if(!sec && window.location.hash){ var m=window.location.hash.match(/section-(\\\d+)/); if(m){sec=m[1];} }\n"
            . "  if(!sec){ var el=document.querySelector('.course-content .current[id^=\\'section-\\'], .course-content .section.current[id^=\\'section-\\']');\n"
            . "    if(el){ var m2=el.id.match(/section-(\\\d+)/); if(m2){sec=m2[1];} } }\n"
            . "  if(sec){ href.searchParams.set('section', sec); a.href=href.toString(); }\n"
            . "  // Also propagate cmid if viewing a module page (URL like /mod/...&id=CMID).\n"
            . "  var usp=new URLSearchParams(window.location.search);\n"
            . "  var cmid=usp.get('id');\n"
            . "  if(cmid && /\\/mod\\//.test(window.location.pathname)){ href.searchParams.set('cmid', cmid); a.href=href.toString(); }\n"
            . "}catch(e){}\n"
            . "})();";
        $this->page->requires->js_amd_inline($js);
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
