<?php
// This file is part of Moodle - http://moodle.org/
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
 * Block AI Prompt Generator main block class.
 *
 * @package    block_aipromptgen
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_aipromptgen\helper;

/**
 * Block class for AI Prompt Generator.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_aipromptgen extends block_base
{
    /**
     * Initialise the block title.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_aipromptgen');
    }

    /**
     * Where this block can be added.
     *
     * @return array
     */
    public function applicable_formats() {
        return [
            'course-view' => true,
            'site-index' => false,
            'mod' => true,
        ];
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
        global $CFG;

        // Capability check: only users with manage capability see content.
        $courseid = (int) $this->page->course->id;
        $context = $this->page->context;
        if (!has_capability('block/aipromptgen:manage', $context)) {
            $this->content->text = get_string('notallowed', 'block_aipromptgen');
            $this->content->footer = '';
            return $this->content;
        }

        $sectionid = optional_param('section', 0, PARAM_INT);
        $params = ['courseid' => $courseid];

        // Prepare a label at the top listing course competencies and grade outcomes (comma-separated).
        $complabelhtml = '';
        try {
            $allnames = helper::get_course_competencies_and_outcomes($courseid);

            // Clean up strings for display (remove descriptions if necessary, helper returns "Name — Description").
            // For the block label, we might only want the names to save space, or keep as is.
            // Current helper returns "Name — Description". Let's strip description for the small block view.
            $shortnames = [];
            foreach ($allnames as $full) {
                $parts = explode(' — ', $full);
                if (!empty($parts[0])) {
                    $shortnames[] = $parts[0];
                }
            }
            $shortnames = array_unique($shortnames);

            if (!empty($shortnames)) {
                $labelprefix = get_string('competencies', 'core_competency') . ' / ' .
                    get_string('outcomes', 'grades');
                $label = $labelprefix . ': ' . implode(', ', $shortnames);
                $complabelhtml = '<div class="ai4t-competencies" style="' .
                    'font-size:0.9em;color:#666;margin-bottom:8px;">' . s($label) . '</div>';
            }
        } catch (\Throwable $e) {
            // Ignore if competencies are not available; label will be omitted.
            debugging($e->getMessage(), DEBUG_DEVELOPER);
        }

        // Include cmid/section params when available.
        if (!empty($this->page->cm) && !empty($this->page->cm->id)) {
            $params['cmid'] = (int) $this->page->cm->id;
        }
        if (!empty($sectionid)) {
            $params['section'] = $sectionid;
        }

        $href = new moodle_url('/blocks/aipromptgen/view.php', $params);
        $linktext = get_string('openpromptbuilder', 'block_aipromptgen');
        // Use a class that matches standard Moodle buttons.
        $link = '<a class="btn btn-primary btn-block" id="ai4t-open" href="' . $href->out(true) . '">' . s($linktext) . '</a>';

        // Prepend competencies label (if available).
        $this->content->text = $complabelhtml . '<div>' . $link . '</div>';
        $this->content->footer = '';

        // Ensure the link carries the current section when present (all-formats friendly).
        // Inline JS is acceptable here for specific block-instance behavior not worth a full module.
        $js = "(function(){\n" .
            "var a=document.getElementById('ai4t-open'); if(!a){return;}\n" .
            "try{\n" .
            "  var href=new URL(a.href, window.location.origin);\n" .
            "  var sec=(new URLSearchParams(window.location.search)).get('section');\n" .
            "  if(!sec && window.location.hash){ var m=window.location.hash.match(/section-(\\d+)/); if(m){sec=m[1];} }\n" .
            "  if(sec){ href.searchParams.set('section', sec); a.href=href.toString(); }\n" .
            "  var usp=new URLSearchParams(window.location.search);\n" .
            "  var cmid=usp.get('id');\n" .
            "  if(cmid && /\\/mod\\//.test(window.location.pathname))" .
            "  { href.searchParams.set('cmid', cmid); a.href=href.toString(); }\n" .
            "}catch(e){}\n" .
            "})();";
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
        $context = $this->page->context;
        return !has_capability('block/aipromptgen:manage', $context);
    }

    /**
     * Declare that this block has a global settings.php.
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }
}
