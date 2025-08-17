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
 * Block AI Prompt Generator main block class.
 *
 * @package    block_aipromptgen
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block class for AI Prompt Generator.
 *
 * @package    block_aipromptgen
 */
class block_aipromptgen extends block_base {
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
        $courseid = (int)$this->page->course->id;
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
            $compnames = [];
            $outnames = [];

            if (class_exists('\\core_competency\\api') && \core_competency\api::is_enabled()) {
                // Course-level competencies.
                $coursecompetencies = \core_competency\api::list_course_competencies($courseid);
                foreach ($coursecompetencies as $cc) {
                    $competencyid = null;
                    if (is_object($cc)) {
                        if (method_exists($cc, 'get')) {
                            $competencyid = $cc->get('competencyid');
                        } else if (property_exists($cc, 'competencyid')) {
                            $competencyid = $cc->competencyid;
                        }
                    }
                    if (empty($competencyid)) {
                        continue;
                    }
                    $comp = \core_competency\api::read_competency($competencyid);
                    if (!$comp) {
                        continue;
                    }
                    $shortname = '';
                    if (method_exists($comp, 'get')) {
                        $shortname = (string)$comp->get('shortname');
                    }
                    $idnumber = '';
                    if (method_exists($comp, 'get')) {
                        $idnumber = (string)$comp->get('idnumber');
                    }
                    $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                    if ($name === '') {
                        $idtxt = '';
                        if (method_exists($comp, 'get')) {
                            $idtxt = (string)$comp->get('id');
                        }
                        $name = $idtxt !== '' ? $idtxt : get_string('competency', 'core_competency');
                    }
                    $compnames[] = $name;
                }

                // Fallback: competencies linked to visible modules if none at course level.
                if (empty($compnames)) {
                    $seen = [];
                    $modinfo = get_fast_modinfo($courseid);
                    foreach ($modinfo->get_cms() as $cm) {
                        if (!$cm->uservisible) {
                            continue;
                        }
                        $links = [];
                        try {
                            $links = \core_competency\api::list_course_module_competencies($cm->id);
                        } catch (\Throwable $ignore) {
                            $links = [];
                        }
                        foreach ($links as $link) {
                            $competencyid = null;
                            if (is_object($link)) {
                                if (method_exists($link, 'get')) {
                                    $competencyid = $link->get('competencyid');
                                } else if (property_exists($link, 'competencyid')) {
                                    $competencyid = $link->competencyid;
                                }
                            }
                            if (empty($competencyid)) {
                                continue;
                            }
                            $cid = (int)$competencyid;
                            if (isset($seen[$cid])) {
                                continue;
                            }
                            $comp = \core_competency\api::read_competency($cid);
                            if (!$comp) {
                                continue;
                            }
                            $shortname = '';
                            if (method_exists($comp, 'get')) {
                                $shortname = (string)$comp->get('shortname');
                            }
                            $idnumber = '';
                            if (method_exists($comp, 'get')) {
                                $idnumber = (string)$comp->get('idnumber');
                            }
                            $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                            if ($name === '') {
                                $idtxt = '';
                                if (method_exists($comp, 'get')) {
                                    $idtxt = (string)$comp->get('id');
                                }
                                $name = $idtxt !== '' ? $idtxt : get_string('competency', 'core_competency');
                            }
                            $compnames[] = $name;
                            $seen[$cid] = true;
                        }
                    }
                }
            }

            // Gradebook outcomes: include both local (course) and global if feature enabled.
            if (!empty($CFG->enableoutcomes)) {
                @require_once($CFG->libdir . '/gradelib.php');
                @require_once($CFG->libdir . '/grade/grade_outcome.php');
                if (class_exists('grade_outcome')) {
                    if (method_exists('grade_outcome', 'fetch_all_local')) {
                        $locals = grade_outcome::fetch_all_local($courseid);
                        if (!empty($locals) && is_array($locals)) {
                            foreach ($locals as $o) {
                                $name = '';
                                if (!empty($o->shortname)) {
                                    $name = format_string($o->shortname);
                                } else if (!empty($o->fullname)) {
                                    $name = format_string($o->fullname);
                                }
                                $name = trim((string)$name);
                                if ($name !== '') {
                                    $outnames[] = $name;
                                }
                            }
                        }
                    }
                    if (method_exists('grade_outcome', 'fetch_all_global')) {
                        $globals = grade_outcome::fetch_all_global();
                        if (!empty($globals) && is_array($globals)) {
                            foreach ($globals as $o) {
                                $name = '';
                                if (!empty($o->shortname)) {
                                    $name = format_string($o->shortname);
                                } else if (!empty($o->fullname)) {
                                    $name = format_string($o->fullname);
                                }
                                $name = trim((string)$name);
                                if ($name !== '') {
                                    $outnames[] = $name;
                                }
                            }
                        }
                    }
                }
            }

            $allnames = array_merge($compnames, $outnames);
            if (!empty($allnames)) {
                $allnames = array_values(array_unique($allnames));
                // Sort naturally for readability.
                sort($allnames, SORT_NATURAL | SORT_FLAG_CASE);
                $labelprefix = get_string('competencies', 'core_competency') . ' / ' .
                    get_string('outcomes', 'grades');
                $label = $labelprefix . ': ' . implode(', ', $allnames);
                $complabelhtml = '<div class="ai4t-competencies">' . s($label) . '</div>';
            }
        } catch (\Throwable $e) {
            // Ignore if competencies are not available; label will be omitted.
            debugging($e->getMessage(), DEBUG_DEVELOPER);
        }

        // Include cmid/section params when available.
        if (!empty($this->page->cm) && !empty($this->page->cm->id)) {
            $params['cmid'] = (int)$this->page->cm->id;
        }
        if (!empty($sectionid)) {
            $params['section'] = $sectionid;
        }

        $href = $CFG->wwwroot . '/blocks/aipromptgen/view.php?' . http_build_query($params);
        $linktext = get_string('openpromptbuilder', 'block_aipromptgen');
        $link = '<a class="btn btn-primary" id="ai4t-open" href="' . s($href) . '">' . s($linktext) . '</a>';

        // Prepend competencies label (if available).
        $this->content->text = $complabelhtml . '<div>' . $link . '</div>';
        $this->content->footer = '';

        // Ensure the link carries the current section when present (all-formats friendly).
        $js = "(function(){\n" .
            "var a=document.getElementById('ai4t-open'); if(!a){return;}\n" .
            "try{\n" .
            "  var href=new URL(a.href, window.location.origin);\n" .
            "  var sec=(new URLSearchParams(window.location.search)).get('section');\n" .
            "  if(!sec && window.location.hash){ var m=window.location.hash.match(/section-(\\d+)/); if(m){sec=m[1];} }\n" .
            "  if(sec){ href.searchParams.set('section', sec); a.href=href.toString(); }\n" .
            "  var usp=new URLSearchParams(window.location.search);\n" .
            "  var cmid=usp.get('id');\n" .
            "  if(cmid && /\\/mod\\//.test(window.location.pathname)){ href.searchParams.set('cmid', cmid); a.href=href.toString(); }\n" .
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
