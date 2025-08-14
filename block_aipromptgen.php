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
    global $CFG;

        // Capability check: only users with manage capability see content.
        $courseid = $this->page->course->id;
        $context = context_course::instance($courseid);
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
                    if (empty($competencyid)) { continue; }
                    $comp = \core_competency\api::read_competency($competencyid);
                    if (!$comp) { continue; }
                    $shortname = method_exists($comp, 'get') ? (string)$comp->get('shortname') : ((isset($comp->shortname) ? (string)$comp->shortname : ''));
                    $idnumber  = method_exists($comp, 'get') ? (string)$comp->get('idnumber')  : ((isset($comp->idnumber) ? (string)$comp->idnumber : ''));
                    $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                    if ($name === '') {
                        $idtxt = method_exists($comp, 'get') ? (string)$comp->get('id') : (isset($comp->id) ? (string)$comp->id : '');
                        $name = $idtxt !== '' ? $idtxt : get_string('competency', 'tool_lp');
                    }
                    $compnames[] = $name;
                }
                // Fallback: competencies linked to visible modules if none at course level.
                if (empty($compnames)) {
                    $seen = [];
                    $modinfo = get_fast_modinfo($courseid);
                    foreach ($modinfo->get_cms() as $cm) {
                        if (!$cm->uservisible) { continue; }
                        $links = [];
                        try {
                            $links = \core_competency\api::list_course_module_competencies($cm->id);
                        } catch (\Throwable $ignore) { $links = []; }
                        foreach ($links as $link) {
                            $competencyid = null;
                            if (is_object($link)) {
                                if (method_exists($link, 'get')) {
                                    $competencyid = $link->get('competencyid');
                                } else if (property_exists($link, 'competencyid')) {
                                    $competencyid = $link->competencyid;
                                }
                            }
                            if (empty($competencyid)) { continue; }
                            $cid = (int)$competencyid;
                            if (isset($seen[$cid])) { continue; }
                            $comp = \core_competency\api::read_competency($cid);
                            if (!$comp) { continue; }
                            $shortname = method_exists($comp, 'get') ? (string)$comp->get('shortname') : ((isset($comp->shortname) ? (string)$comp->shortname : ''));
                            $idnumber  = method_exists($comp, 'get') ? (string)$comp->get('idnumber')  : ((isset($comp->idnumber) ? (string)$comp->idnumber : ''));
                            $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                            if ($name === '') {
                                $idtxt = method_exists($comp, 'get') ? (string)$comp->get('id') : (isset($comp->id) ? (string)$comp->id : '');
                                $name = $idtxt !== '' ? $idtxt : get_string('competency', 'tool_lp');
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
                                if ($name !== '') { $outnames[] = $name; }
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
                                if ($name !== '') { $outnames[] = $name; }
                            }
                        }
                    }
                }
            }
            $allnames = array_merge($compnames, $outnames);
            if (!empty($allnames)) {
                $allnames = array_values(array_unique($allnames));
                if (class_exists('core_collator')) { core_collator::asort($allnames); }
                $labelprefix = get_string('competencies', 'tool_lp') . ' / ' . get_string('outcomes', 'grades');
                $label = $labelprefix . ': ' . implode(', ', $allnames);
                $complabelhtml = html_writer::div(s($label), 'ai4t-competencies');
            }
        } catch (\Throwable $e) {
            // Ignore if competencies are not available; label will be omitted.
        }
        // If rendered on a module page, include cmid for lesson defaulting.
        if (!empty($this->page->cm) && !empty($this->page->cm->id)) {
            $params['cmid'] = (int)$this->page->cm->id;
        }
        if (!empty($sectionid)) {
            $params['section'] = $sectionid;
        }
        $url = new moodle_url('/blocks/aipromptgen/view.php', $params);
    $link = html_writer::link($url, get_string('openpromptbuilder', 'block_aipromptgen'), [
            'class' => 'btn btn-primary',
            'id' => 'ai4t-open',
        ]);
    // Prepend competencies label (if available).
    $this->content->text = $complabelhtml . html_writer::div($link);
        $this->content->footer = '';
        // Ensure the link carries the current section when present (all-formats friendly).
        $js = "(function(){\n"
            . "var a=document.getElementById('ai4t-open'); if(!a){return;}\n"
            . "try{\n"
            . "  var href=new URL(a.href, window.location.origin);\n"
            . "  var sec=(new URLSearchParams(window.location.search)).get('section');\n"
            . "  if(!sec && window.location.hash){ var m=window.location.hash.match(/section-(\\d+)/); if(m){sec=m[1];} }\n"
            . "  if(!sec){ var el=document.querySelector('.course-content .current[id^=\\'section-\\'], .course-content .section.current[id^=\\'section-\\']');\n"
            . "    if(el){ var m2=el.id.match(/section-(\\d+)/); if(m2){sec=m2[1];} } }\n"
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
        return !has_capability('block/aipromptgen:manage', $context);
    }
}
