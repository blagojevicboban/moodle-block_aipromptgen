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
 * Prompt builder page for the AI for Teachers block.
 *
 * @package    block_aipromptgen
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_login();

// Ensure Moodle's PEAR library is loaded first to avoid PHP fatal
// "Non-static method PEAR::getStaticProperty() cannot be called statically"
// when external/global PEAR versions are present in include_path.
require_once($CFG->libdir . '/pear/PEAR.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$sectionid = optional_param('section', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$paramid = optional_param('id', 0, PARAM_INT); // Could be cmid (on /mod/*) or course id (on /course/view.php).

// If courseid not provided, try to infer from id/cmid.
if ($courseid == 0) {
    // First, if we have an explicit cmid use it to derive course.
    if (!empty($cmid)) {
        try {
            $cm = get_coursemodule_from_id(null, $cmid, 0, MUST_EXIST);
            if (!empty($cm) && !empty($cm->course)) {
                $courseid = (int)$cm->course;
            }
        } catch (\Throwable $e) {
            // Ignore; will try other options below.
        }
    }
    // If still missing and we have a generic id, probe whether it's a cmid or a course id.
    if (empty($courseid) && !empty($paramid)) {
        try {
            $cmprobe = get_coursemodule_from_id(null, $paramid, 0, IGNORE_MISSING);
            if (!empty($cmprobe) && !empty($cmprobe->course)) {
                $cmid = $paramid;
                $courseid = (int)$cmprobe->course;
            }
        } catch (\Throwable $e) {
            // Ignore; fall back to treating id as course id if no cm found.
        }
        if (empty($courseid)) {
            // Treat as course id (e.g., /course/view.php?id=COURSEID or block added on course page).
            $courseid = (int)$paramid;
        }
    }
}
if ($courseid == 0) {
    // Derive course from module if provided.
    try {
        $cm = get_coursemodule_from_id(null, $cmid, 0, false, MUST_EXIST);
        if (!empty($cm->course)) {
            $courseid = (int)$cm->course;
        }
    } catch (\Throwable $e) {
        // Ignore and try other fallbacks.
    }
}
if ($courseid == 0) {
    // As a last resort, use current course in global context if not the site course.
    global $COURSE;
    if (!empty($COURSE) && !empty($COURSE->id) && (int)$COURSE->id !== (int)SITEID) {
        $courseid = (int)$COURSE->id;
    }
}
if (empty($courseid)) {
    throw new moodle_exception('missingparam', 'error', '', 'courseid');
}
$course = get_course($courseid);
$context = context_course::instance($course->id);
require_capability('block/aipromptgen:manage', $context);

$PAGE->set_url(new moodle_url('/blocks/aipromptgen/view.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', 'block_aipromptgen'));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('core');

// Load form.
require_once($CFG->dirroot . '/blocks/aipromptgen/classes/form/prompt_form.php');
// Gather course topics (section names) for suggestions, and build lesson options (sections + activities).
$topics = [];
$lessonoptions = [];
$competencies = [];
try {
    $modinfo = get_fast_modinfo($course);
    foreach ($modinfo->get_section_info_all() as $section) {
        $name = '';
        if (!empty($section->name)) {
            $name = $section->name;
        } else {
            // Fallback to formatted default section name (e.g., Topic 1).
            $name = get_section_name($course, $section);
        }
        $name = trim(format_string($name));
        if ($name !== '' && !in_array($name, $topics, true)) {
            $topics[] = $name;
        }
        // Build grouped lesson options: section as selectable + its visible activities.
        $group = ['text' => $name, 'options' => []];
        if ($name !== '') {
            // Use an icon for the section itself.
            $group['options']['📘 ' . $name] = $name; // Display with icon; value stays clean.
        }
        foreach ($modinfo->get_cms() as $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            if ((int)$cm->sectionnum !== (int)$section->section) {
                continue;
            }
            $cmname = trim(format_string($cm->name));
            if ($cmname === '') {
                continue;
            }
            // Choose a simple emoji icon based on module type.
            $mod = (string)$cm->modname;
            $icon = '📄';
            switch ($mod) {
                case 'assign': $icon = '📝'; break;
                case 'book': $icon = '📚'; break;
                case 'chat': $icon = '💬'; break;
                case 'choice': $icon = '☑️'; break;
                case 'feedback': $icon = '🗳️'; break;
                case 'folder': $icon = '📁'; break;
                case 'forum': $icon = '💬'; break;
                case 'glossary': $icon = '📔'; break;
                case 'h5pactivity': $icon = '▶️'; break;
                case 'label': $icon = '🏷️'; break;
                case 'lesson': $icon = '📘'; break;
                case 'lti': $icon = '🌐'; break;
                case 'page': $icon = '📄'; break;
                case 'quiz': $icon = '❓'; break;
                case 'resource': $icon = '📄'; break;
                case 'scorm': $icon = '🎯'; break;
                case 'survey': $icon = '📊'; break;
                case 'url': $icon = '🔗'; break;
                case 'wiki': $icon = '🧭'; break;
                case 'workshop': $icon = '🛠️'; break;
                default: $icon = '📄';
            }
            // Indent activities visually in the list with icon.
            $group['options']['    ' . $icon . ' ' . $cmname] = $cmname;
        }
        if (!empty($group['options'])) {
            $lessonoptions[] = $group;
        }
    }
} catch (\Throwable $e) {
    // Ignore; leave topics empty if anything goes wrong.
}
// Try to gather course competencies for the Outcomes modal (if competencies subsystem is enabled and accessible).
// First, try to gather Gradebook Outcomes (local to course and global) if the feature is enabled.
try {
    if (!empty($CFG->enableoutcomes)) {
        // Gradebook APIs (functions + outcome class).
        @require_once($CFG->libdir . '/gradelib.php');
        @require_once($CFG->libdir . '/grade/grade_outcome.php');

        $seen = [];
        // Local (course) outcomes.
        if (class_exists('grade_outcome') && method_exists('grade_outcome', 'fetch_all_local')) {
            $locals = grade_outcome::fetch_all_local($course->id);
            if (!empty($locals) && is_array($locals)) {
                foreach ($locals as $o) {
                    // grade_outcome is a legacy class with public props.
                    $name = '';
                    if (!empty($o->shortname)) {
                        $name = format_string($o->shortname);
                    } else if (!empty($o->fullname)) {
                        $name = format_string($o->fullname);
                    }
                    $name = trim((string)$name);
                    if ($name === '') { continue; }
                    $desc = '';
                    if (!empty($o->description)) {
                        $desc = trim(strip_tags(format_text($o->description, FORMAT_HTML)));
                    }
                    $text = $name . ($desc !== '' ? ' — ' . $desc : '');
                    $competencies[] = $text;
                    if (!empty($o->id)) { $seen[(int)$o->id] = true; }
                }
            }
        }
        // Global outcomes (site-level), include them if not already present in local list.
        if (class_exists('grade_outcome') && method_exists('grade_outcome', 'fetch_all_global')) {
            $globals = grade_outcome::fetch_all_global();
            if (!empty($globals) && is_array($globals)) {
                foreach ($globals as $o) {
                    $oid = isset($o->id) ? (int)$o->id : 0;
                    if ($oid && isset($seen[$oid])) { continue; }
                    $name = '';
                    if (!empty($o->shortname)) {
                        $name = format_string($o->shortname);
                    } else if (!empty($o->fullname)) {
                        $name = format_string($o->fullname);
                    }
                    $name = trim((string)$name);
                    if ($name === '') { continue; }
                    $desc = '';
                    if (!empty($o->description)) {
                        $desc = trim(strip_tags(format_text($o->description, FORMAT_HTML)));
                    }
                    $text = $name . ($desc !== '' ? ' — ' . $desc : '');
                    $competencies[] = $text;
                }
            }
        }
    }
} catch (\Throwable $e) {
    // Ignore issues with grade outcomes; we'll fall back to competencies below.
}

// Try to gather course competencies for the Outcomes modal (robust across versions).
try {
    $coursecompetencies = [];
    if (class_exists('\\core_competency\\api')) {
        // Prefer not to hard-fail on is_enabled; some sites expose course competencies even when learning plans are off.
        if (method_exists('\\core_competency\\api', 'list_course_competencies')) {
            $coursecompetencies = \core_competency\api::list_course_competencies($course->id);
        } else if (method_exists('\\core_competency\\api', 'list_course_competencies_in_course')) {
            $coursecompetencies = \core_competency\api::list_course_competencies_in_course($course->id);
        }
    }
    // Fallback to tool_lp API if core_competency call returned empty but tool_lp exposes a method.
    if (empty($coursecompetencies) && class_exists('\\tool_lp\\api') && method_exists('\\tool_lp\\api', 'list_course_competencies')) {
        $coursecompetencies = \tool_lp\api::list_course_competencies($course->id);
    }

    foreach ($coursecompetencies as $cc) {
        // Support both persistent objects and stdClass, depending on Moodle version.
        $competencyid = null;
        if (is_object($cc)) {
            if (method_exists($cc, 'get')) {
                $competencyid = $cc->get('competencyid');
            } else if (property_exists($cc, 'competencyid')) {
                $competencyid = $cc->competencyid;
            } else if (method_exists($cc, 'get_competencyid')) {
                $competencyid = $cc->get_competencyid();
            }
        } else if (is_array($cc) && isset($cc['competencyid'])) {
            $competencyid = $cc['competencyid'];
        }
        if (empty($competencyid)) { continue; }

        $comp = null;
        if (class_exists('\\core_competency\\api') && method_exists('\\core_competency\\api', 'read_competency')) {
            $comp = \core_competency\api::read_competency($competencyid);
        }
        if (!$comp && class_exists('\\tool_lp\\api') && method_exists('\\tool_lp\\api', 'read_competency')) {
            $comp = \tool_lp\api::read_competency($competencyid);
        }
        if (!$comp) { continue; }

        // Access fields using persistent getters when available.
        $shortname = method_exists($comp, 'get') ? (string)$comp->get('shortname') : ((isset($comp->shortname) ? (string)$comp->shortname : ''));
        $idnumber  = method_exists($comp, 'get') ? (string)$comp->get('idnumber')  : ((isset($comp->idnumber) ? (string)$comp->idnumber : ''));
        $descraw   = method_exists($comp, 'get') ? $comp->get('description') : (isset($comp->description) ? $comp->description : '');
        $descfmt   = method_exists($comp, 'get') ? ($comp->get('descriptionformat') ?? FORMAT_HTML) : (isset($comp->descriptionformat) ? $comp->descriptionformat : FORMAT_HTML);

        $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
        if ($name === '') {
            $id = method_exists($comp, 'get') ? (string)$comp->get('id') : (isset($comp->id) ? (string)$comp->id : '');
            $name = $id !== '' ? $id : get_string('competency', 'tool_lp');
        }
        $desc = '';
        if (!empty($descraw)) {
            $desc = trim(strip_tags(format_text($descraw, $descfmt)));
        }
        $text = $name;
        if ($desc !== '') { $text .= ' — ' . $desc; }
        $competencies[] = $text;
    }
} catch (\Throwable $e) {
    // Silently ignore if competencies are not configured or user lacks permissions.
}
// Fallback: if no course-level competencies found, try collecting from visible course modules.
if (empty($competencies)) {
    try {
        if (class_exists('\\core_competency\\api')) {
            $seen = [];
            $modinfo = get_fast_modinfo($course);
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
                        } else if (method_exists($link, 'get_competencyid')) {
                            $competencyid = $link->get_competencyid();
                        }
                    }
                    if (empty($competencyid)) { continue; }
                    $cid = (int)$competencyid;
                    if (isset($seen[$cid])) { continue; }
                    $comp = null;
                    if (class_exists('\\core_competency\\api') && method_exists('\\core_competency\\api', 'read_competency')) {
                        $comp = \core_competency\api::read_competency($cid);
                    }
                    if (!$comp && class_exists('\\tool_lp\\api') && method_exists('\\tool_lp\\api', 'read_competency')) {
                        $comp = \tool_lp\api::read_competency($cid);
                    }
                    if (!$comp) { continue; }
                    $shortname = method_exists($comp, 'get') ? (string)$comp->get('shortname') : ((isset($comp->shortname) ? (string)$comp->shortname : ''));
                    $idnumber  = method_exists($comp, 'get') ? (string)$comp->get('idnumber')  : ((isset($comp->idnumber) ? (string)$comp->idnumber : ''));
                    $descraw   = method_exists($comp, 'get') ? $comp->get('description') : (isset($comp->description) ? $comp->description : '');
                    $descfmt   = method_exists($comp, 'get') ? ($comp->get('descriptionformat') ?? FORMAT_HTML) : (isset($comp->descriptionformat) ? $comp->descriptionformat : FORMAT_HTML);
                    $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                    if ($name === '') {
                        $idtxt = method_exists($comp, 'get') ? (string)$comp->get('id') : (isset($comp->id) ? (string)$comp->id : '');
                        $name = $idtxt !== '' ? $idtxt : get_string('competency', 'tool_lp');
                    }
                    $desc = '';
                    if (!empty($descraw)) {
                        $desc = trim(strip_tags(format_text($descraw, $descfmt)));
                    }
                    $text = $name;
                    if ($desc !== '') { $text .= ' — ' . $desc; }
                    $competencies[] = $text;
                    $seen[$cid] = true;
                }
            }
        }
    } catch (\Throwable $e) {
        // Ignore fallback errors.
    }
}

// Final fallback: query DB directly for course and module competencies if still empty.
if (empty($competencies)) {
    try {
        global $DB;
        // Course-level competencies via competency_coursecomp.
        $sql = 'SELECT c.id, c.shortname, c.idnumber, c.description, c.descriptionformat
                  FROM {competency} c
                  JOIN {competency_coursecomp} cc ON cc.competencyid = c.id
                 WHERE cc.courseid = :cid';
        $recs = $DB->get_records_sql($sql, ['cid' => $course->id]);
        foreach ($recs as $r) {
            $shortname = isset($r->shortname) ? (string)$r->shortname : '';
            $idnumber  = isset($r->idnumber) ? (string)$r->idnumber : '';
            $descraw   = isset($r->description) ? $r->description : '';
            $descfmt   = isset($r->descriptionformat) ? (int)$r->descriptionformat : FORMAT_HTML;
            $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
            if ($name === '') { $name = (string)$r->id; }
            $desc = '';
            if (!empty($descraw)) { $desc = trim(strip_tags(format_text($descraw, $descfmt))); }
            $text = $name . ($desc !== '' ? ' — ' . $desc : '');
            $competencies[] = $text;
        }

        // If still empty, look for module-level links via competency_modulecomp.
        if (empty($competencies)) {
            $sql2 = 'SELECT DISTINCT c.id, c.shortname, c.idnumber, c.description, c.descriptionformat
                       FROM {competency} c
                       JOIN {competency_modulecomp} mc ON mc.competencyid = c.id
                       JOIN {course_modules} cm ON cm.id = mc.cmid
                      WHERE cm.course = :cid2';
            $recs2 = $DB->get_records_sql($sql2, ['cid2' => $course->id]);
            foreach ($recs2 as $r) {
                $shortname = isset($r->shortname) ? (string)$r->shortname : '';
                $idnumber  = isset($r->idnumber) ? (string)$r->idnumber : '';
                $descraw   = isset($r->description) ? $r->description : '';
                $descfmt   = isset($r->descriptionformat) ? (int)$r->descriptionformat : FORMAT_HTML;
                $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                if ($name === '') { $name = (string)$r->id; }
                $desc = '';
                if (!empty($descraw)) { $desc = trim(strip_tags(format_text($descraw, $descfmt))); }
                $text = $name . ($desc !== '' ? ' — ' . $desc : '');
                $competencies[] = $text;
            }
        }
    } catch (\Throwable $e) {
        // As a last resort we keep the list empty.
    }
}
// Prepare a robust course name for defaults.
$coursedefaultname = trim((string)format_string($course->fullname));
if ($coursedefaultname === '' && !empty($course->shortname)) {
    $coursedefaultname = trim((string)format_string($course->shortname));
}

// Resolve a sensible default language: course forced language > user preference > current UI language.
global $USER;
$defaultlangcode = '';
if (!empty($course->lang)) {
    $defaultlangcode = (string)$course->lang;
} else if (!empty($USER->lang)) {
    $defaultlangcode = (string)$USER->lang;
} else {
    $defaultlangcode = (string)current_language();
}

$actionparams = ['courseid' => $course->id];
if (!empty($sectionid)) { $actionparams['section'] = (int)$sectionid; }
if (!empty($cmid)) { $actionparams['cmid'] = (int)$cmid; }
$actionurl = new moodle_url('/blocks/aipromptgen/view.php', $actionparams);

$form = new \block_aipromptgen\form\prompt_form($actionurl, [
    'topics' => $topics,
    'lessonoptions' => $lessonoptions,
    'subjectdefault' => $coursedefaultname,
    'defaultlanguage' => $defaultlangcode,
    'coursename' => $coursedefaultname,
]);

$generated = null;
$refillsubject = null;

if ($data = $form->get_data()) {
    // Preserve underscores in language code (e.g., sr_cr).
    $langcode = clean_param($data->language, PARAM_ALPHANUMEXT);

    // UI labels follow current Moodle language automatically via get_string().
    // Prompt content (labels inside the generated text) will use the selected language.
    $sm = get_string_manager();
    $labels = [
        'purpose' => $sm->get_string('label:purpose', 'block_aipromptgen', null, $langcode),
        'audience' => $sm->get_string('label:audience', 'block_aipromptgen', null, $langcode),
        'language' => $sm->get_string('label:language', 'block_aipromptgen', null, $langcode),
        'subject' => $sm->get_string('label:subject', 'block_aipromptgen', null, $langcode),
        'agerange' => $sm->get_string('label:agerange', 'block_aipromptgen', null, $langcode),
        'topic' => $sm->get_string('label:topic', 'block_aipromptgen', null, $langcode),
        'lesson' => $sm->get_string('label:lesson', 'block_aipromptgen', null, $langcode),
        'classtype' => $sm->get_string('label:classtype', 'block_aipromptgen', null, $langcode),
        'outcomes' => $sm->get_string('label:outcomes', 'block_aipromptgen', null, $langcode),
    ];

    // Map select codes to localized values.
    $purposecode = clean_param($data->purpose ?? '', PARAM_ALPHANUMEXT);
    $audiencecode = clean_param($data->audience ?? '', PARAM_ALPHANUMEXT);
    $classtypecode = clean_param($data->classtype ?? '', PARAM_ALPHANUMEXT);
    $purposeallowed = ['lessonplan', 'quiz', 'rubric', 'worksheet']; 
    $audienceallowed = ['teacher', 'student'];
    $classtypeallowed = ['lecture', 'discussion', 'groupwork', 'lab', 'project', 'review', 'assessment'];
    $purposevalue = in_array($purposecode, $purposeallowed)
        ? $sm->get_string('option:' . $purposecode, 'block_aipromptgen', null, $langcode)
        : s($purposecode);
    $audiencevalue = in_array($audiencecode, $audienceallowed)
        ? $sm->get_string('option:' . $audiencecode, 'block_aipromptgen', null, $langcode)
        : s($audiencecode);
    $classtypevalue = in_array($classtypecode, $classtypeallowed)
        ? $sm->get_string('classtype:' . $classtypecode, 'block_aipromptgen', null, $langcode)
        : s($classtypecode);

    $parts = [];
    $parts[] = $labels['purpose'] . ': ' . $purposevalue;
    $parts[] = $labels['audience'] . ': ' . $audiencevalue;
    // Resolve human-readable language name from installed languages.
    $trans = $sm->get_list_of_translations();
    $langname = $trans[$langcode] ?? null;
    if ($langname === null) {
        $langs = $sm->get_list_of_languages();
        $langname = $langs[$langcode] ?? null;
    }
    if ($langname === null) {
        $short = substr($langcode, 0, 2);
        $langname = $trans[$short] ?? ($langs[$short] ?? $langcode);
    }
    $parts[] = $labels['language'] . ': ' . $langname;
    $subjectval = (string)($data->subject ?? '');
    if (trim($subjectval) === '' && trim($coursedefaultname) !== '') {
        $subjectval = $coursedefaultname;
    }
    $refillsubject = $subjectval;
    $agerangeval = (string)($data->agerange ?? '');
    $topicval = (string)($data->topic ?? '');
    $lessonval = (string)($data->lesson ?? '');
    $outcomesval = (string)($data->outcomes ?? '');
    $parts[] = $labels['subject'] . ': ' . $subjectval;
    $parts[] = $labels['agerange'] . ': ' . $agerangeval;
    if ($topicval !== '') {
        $parts[] = $labels['topic'] . ': ' . $topicval;
    }
    $parts[] = $labels['lesson'] . ': ' . $lessonval;
    $parts[] = $labels['classtype'] . ': ' . $classtypevalue;
    if (trim($outcomesval) !== '') {
        $parts[] = $labels['outcomes'] . ': ' . preg_replace('/\s+/', ' ', trim($outcomesval));
    }

    $coursename = format_string($course->fullname);
    $prefix = $sm->get_string('prompt:prefix', 'block_aipromptgen', (object)['course' => $coursename], $langcode);
    $instructions = $sm->get_string('prompt:instructions', 'block_aipromptgen', null, $langcode);
    $generated = $prefix . "\n" . implode("\n", $parts) . "\n" . $instructions;

}

echo $OUTPUT->header();
// Determine a default topic based on current section if provided.
$defaulttopic = '';
if (!empty($sectionid)) {
    try {
        $modinfo = get_fast_modinfo($course);
        $sectioninfo = $modinfo->get_section_info($sectionid, MUST_EXIST);
        $defaulttopic = !empty($sectioninfo->name)
            ? format_string($sectioninfo->name)
            : get_section_name($course, $sectioninfo);
        $defaulttopic = trim((string)$defaulttopic);
    } catch (\Throwable $e) {
        $defaulttopic = '';
    }
}

// Determine a default lesson title from cmid if provided.
$defaultlesson = '';
if (!empty($cmid)) {
    try {
        $cm = get_coursemodule_from_id(null, $cmid, $course->id, false, MUST_EXIST);
        // Prefer module name; fall back to instance name if needed.
        if (!empty($cm->name)) {
            $defaultlesson = format_string($cm->name);
        } else if (!empty($cm->instance)) {
            // Some modules rely on instance-level naming; modinfo provides name reliably.
            $modinfo = get_fast_modinfo($course);
            if (isset($modinfo->cms[$cmid])) {
                $defaultlesson = format_string($modinfo->cms[$cmid]->name);
            }
        }
        $defaultlesson = trim((string)$defaultlesson);
    } catch (\Throwable $e) {
        $defaultlesson = '';
    }
}

// Only set defaults before submission; avoid overwriting submitted values.
if (!$form->is_submitted()) {
    $defaultdata = [
        'courseid' => $course->id,
    ];
    // Set topic/lesson defaults when available.
    if ($defaulttopic !== '') {
        $defaultdata['topic'] = $defaulttopic;
    }
    if ($defaultlesson !== '') {
        $defaultdata['lesson'] = $defaultlesson;
    }
    // Initialize Subject to course name on first load.
    if (trim((string)$coursedefaultname) !== '') {
        $defaultdata['subject'] = $coursedefaultname;
    }
    $form->set_data($defaultdata);
}
$form->set_data($form->is_submitted() && is_string($refillsubject) && trim($refillsubject) !== ''
    ? ['subject' => $refillsubject]
    : []);
$form->display();

// Client-side fallback: if Subject is still empty on first load, set it to the course name.
if (!$form->is_submitted() && $coursedefaultname !== '') {
    $jsfill = "(function(){var el=document.getElementById('id_subject'); if(el && !el.value){ el.value='" . addslashes($coursedefaultname) . "'; }})();";
    $PAGE->requires->js_amd_inline($jsfill);
}

// Inject a lightweight modal to browse and pick a lesson/section into the Lesson textbox.
// Build the modal markup from $lessonoptions prepared above.
echo html_writer::tag('style',
    '.ai4t-modal-backdrop{position:fixed;inset:0;display:none;background:rgba(0,0,0,.4);z-index:1050;}
     .ai4t-modal{position:fixed;top:10%;left:50%;transform:translateX(-50%);width:90%;max-width:720px;max-height:70vh;display:none;z-index:1060;background:#fff;border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.3);}
     .ai4t-modal header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #ddd;}
     .ai4t-modal header h3{margin:0;font-size:1.1rem;}
     .ai4t-modal .ai4t-body{padding:8px 16px;overflow:auto;max-height:58vh;}
     .ai4t-list{list-style:none;margin:0;padding:0;}
     .ai4t-section{font-weight:600;margin:8px 0 4px;}
     .ai4t-item{padding:6px 8px;border-radius:4px;cursor:pointer;}
     .ai4t-item:hover{background:#f2f2f2;}
     .ai4t-modal footer{padding:10px 16px;border-top:1px solid #ddd;display:flex;justify-content:flex-end;gap:8px;}
    ');

// Modal backdrop and container.
echo html_writer::div('', 'ai4t-modal-backdrop', ['id' => 'ai4t-modal-backdrop']);
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal',
    'id' => 'ai4t-modal',
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => 'ai4t-modal-title',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:lessonlabel', 'block_aipromptgen'), ['id' => 'ai4t-modal-title']);
// Close button (uses a simple × symbol).
echo html_writer::tag('button', '&times;', [
    'type' => 'button',
    'id' => 'ai4t-modal-close',
    'class' => 'btn btn-link',
    'aria-label' => get_string('cancel'),
]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);

// Render the list of sections and activities.
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
foreach ($lessonoptions as $group) {
    $sectionname = trim((string)($group['text'] ?? ''));
    if ($sectionname !== '') {
        echo html_writer::tag('li', s($sectionname), ['class' => 'ai4t-section']);
    }
    if (!empty($group['options']) && is_array($group['options'])) {
        foreach ($group['options'] as $display => $value) {
            // Each item is clickable; data-value holds the clean lesson title to insert.
            echo html_writer::tag('li', s($display), [
                'class' => 'ai4t-lesson-item',
                'data-value' => $value,
                'tabindex' => 0,
            ]);
        }
    }
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('cancel'), [
    'type' => 'button',
    'class' => 'btn btn-secondary',
    'id' => 'ai4t-modal-cancel',
]);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div'); // .ai4t-modal

// Wire up open/close and selection handlers.
$browsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-lesson-browse');\n"
    . "var modal=document.getElementById('ai4t-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-modal-cancel');\n"
    . "var input=document.getElementById('id_lesson');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onPick(e){ var v=e.currentTarget.getAttribute('data-value'); if(input && v!=null){ input.value=v; } close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', open); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-lesson-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick); items[i].addEventListener('keydown', function(ev){ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($browsejs);

// Add a second modal dedicated to browsing Topics (course sections only).
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal',
    'id' => 'ai4t-topic-modal',
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => 'ai4t-topic-modal-title',
    'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:topiclabel', 'block_aipromptgen'), ['id' => 'ai4t-topic-modal-title']);
echo html_writer::tag('button', '&times;', [
    'type' => 'button',
    'id' => 'ai4t-topic-modal-close',
    'class' => 'btn btn-link',
    'aria-label' => get_string('cancel'),
]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
foreach ($topics as $t) {
    $t = trim((string)$t);
    if ($t === '') { continue; }
    echo html_writer::tag('li', s($t), [
        'class' => 'ai4t-item ai4t-topic-item',
        'data-value' => $t,
        'tabindex' => 0,
    ]);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('cancel'), [
    'type' => 'button',
    'class' => 'btn btn-secondary',
    'id' => 'ai4t-topic-modal-cancel',
]);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

$topicbrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-topic-browse');\n"
    . "var modal=document.getElementById('ai4t-topic-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-topic-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-topic-modal-cancel');\n"
    . "var input=document.getElementById('id_topic');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onPick(e){ var v=e.currentTarget.getAttribute('data-value'); if(input && v!=null){ input.value=v; } close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', open); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-topic-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick); items[i].addEventListener('keydown', function(ev){ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($topicbrowsejs);

// Add a third modal for browsing Course Competencies and appending to Outcomes.
// Remove duplicates and sort for a tidy list.
if (!empty($competencies)) {
    $competencies = array_values(array_unique($competencies));
    core_collator::asort($competencies);
}
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal',
    'id' => 'ai4t-outcomes-modal',
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => 'ai4t-outcomes-modal-title',
    'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:outcomeslabel', 'block_aipromptgen'), ['id' => 'ai4t-outcomes-modal-title']);
echo html_writer::tag('button', '&times;', [
    'type' => 'button',
    'id' => 'ai4t-outcomes-modal-close',
    'class' => 'btn btn-link',
    'aria-label' => get_string('cancel'),
]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
if (!empty($competencies)) {
    foreach ($competencies as $c) {
        echo html_writer::start_tag('li', ['class' => 'ai4t-item']);
        echo html_writer::start_tag('label');
        echo html_writer::empty_tag('input', [
            'type' => 'checkbox',
            'class' => 'ai4t-outcome-checkbox',
            'value' => $c,
        ]);
        echo html_writer::span(s($c), '');
        echo html_writer::end_tag('label');
        echo html_writer::end_tag('li');
    }
} else {
    echo html_writer::tag('li', get_string('none'), [
        'class' => 'ai4t-item',
        'style' => 'color:#666;'
    ]);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('add'), [
    'type' => 'button',
    'class' => 'btn btn-primary',
    'id' => 'ai4t-outcomes-modal-insert',
]);
echo html_writer::tag('button', get_string('cancel'), [
    'type' => 'button',
    'class' => 'btn btn-secondary',
    'id' => 'ai4t-outcomes-modal-cancel',
]);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

$outcomesbrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-outcomes-browse');\n"
    . "var modal=document.getElementById('ai4t-outcomes-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-outcomes-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-outcomes-modal-cancel');\n"
    . "var insertBtn=document.getElementById('ai4t-outcomes-modal-insert');\n"
    . "var ta=document.getElementById('id_outcomes');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onInsert(){ if(!ta){ close(); return; } var boxes=document.querySelectorAll('.ai4t-outcome-checkbox:checked'); var vals=[]; for(var i=0;i<boxes.length;i++){ if(boxes[i].value){ vals.push(boxes[i].value); } } if(vals.length===0){ close(); return; } var cur=ta.value||''; if(cur && !/\\n$/.test(cur)){ cur+='\\n'; } ta.value=cur+vals.join('\\n'); close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', open); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(insertBtn){ insertBtn.addEventListener('click', onInsert); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "})();";
$PAGE->requires->js_amd_inline($outcomesbrowsejs);

if ($generated) {
    echo html_writer::tag('h3', get_string('form:result', 'block_aipromptgen'));
    // Editable textarea for the generated prompt.
    echo html_writer::start_tag('div', ['class' => 'ai4t-result']);
    echo html_writer::tag('textarea', s($generated), [
        'id' => 'ai4t-generated',
        'rows' => 12,
        'class' => 'form-control',
        'style' => 'width:100%;',
    ]);
    echo html_writer::empty_tag('br');
    echo html_writer::start_tag('div', ['class' => 'ai4t-actions']);
    echo html_writer::tag('button', get_string('form:copy', 'block_aipromptgen'), [
        'type' => 'button',
        'id' => 'ai4t-copy',
        'class' => 'btn btn-secondary',
    ]);
    echo html_writer::tag('button', get_string('form:download', 'block_aipromptgen'), [
        'type' => 'button',
        'id' => 'ai4t-download',
        'class' => 'btn btn-secondary',
        'style' => 'margin-left:8px;',
    ]);
    echo html_writer::tag('span', '', [
        'id' => 'ai4t-copied',
        'style' => 'margin-left:8px; display:none;',
    ]);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    // Inline JS for copy and download.
    $courseslug = preg_replace(
        '/[^a-z0-9]+/i',
        '-',
        core_text::strtolower(format_string($course->shortname ?: $course->fullname))
    );
    $filename = $courseslug . '-ai-prompt.txt';
    $copyjs = "(function(){\n"
        . "var btn=document.getElementById('ai4t-copy');\n"
        . "var dl=document.getElementById('ai4t-download');\n"
        . "var ta=document.getElementById('ai4t-generated');\n"
        . "var ok=document.getElementById('ai4t-copied');\n"
        . "if(btn){btn.addEventListener('click',function(){\n"
        . "  ta.select(); ta.setSelectionRange(0, 99999);\n"
        . "  try{\n"
        . "    if(navigator.clipboard && navigator.clipboard.writeText){\n"
        . "      navigator.clipboard.writeText(ta.value);\n"
        . "    } else {\n"
        . "      document.execCommand('copy');\n"
        . "    }\n"
    . "    ok.textContent='" . addslashes(get_string('form:copied', 'block_aipromptgen')) . "';\n"
        . "    ok.style.display='inline'; setTimeout(function(){ ok.style.display='none'; }, 1500);\n"
        . "  }catch(e){}\n"
        . "});}\n"
        . "if(dl){dl.addEventListener('click',function(){\n"
        . "  var blob=new Blob([ta.value||''],{type:'text/plain'});\n"
        . "  var a=document.createElement('a');\n"
        . "  a.href=URL.createObjectURL(blob);\n"
        . "  a.download='" . addslashes($filename) . "';\n"
        . "  document.body.appendChild(a); a.click(); setTimeout(function(){URL.revokeObjectURL(a.href); a.remove();},0);\n"
        . "});}\n"
        . "})();";
    $PAGE->requires->js_amd_inline($copyjs);
}

// Back to course button/link.
$backurl = new moodle_url('/course/view.php', ['id' => $course->id]);

echo html_writer::div(
    html_writer::link(
        $backurl,
    get_string('form:backtocourse', 'block_aipromptgen'),
        ['class' => 'btn btn-secondary mt-3']
    ),
    'mt-3'
);

echo $OUTPUT->footer();
