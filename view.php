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

// Added explicit core includes (normally pulled in via config.php, but required for static analysis tools).
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/weblib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/course/lib.php');

use core_text;

$courseid = optional_param('courseid', 0, PARAM_INT);
$sectionid = optional_param('section', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$paramid = optional_param('id', 0, PARAM_INT); // Could be cmid (on /mod/*) or course id (on /course/view.php).

if (empty($courseid)) {
    // Show an informational message instead of throwing an exception – the block must be run from a course page.
    $PAGE->set_url(new moodle_url('/blocks/aipromptgen/view.php'));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('pluginname', 'block_aipromptgen'));
    $PAGE->set_heading(get_string('pluginname', 'block_aipromptgen'));

    echo $OUTPUT->header();

    $msg = 'This block must be run from within a course.';
    if (class_exists('\core\output\notification')) {
        echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_INFO);
    } else {
        echo $OUTPUT->notification($msg, 'notifymessage');
    }

    // Link back to Home/Dashboard.
    echo html_writer::div(
        html_writer::link(new moodle_url('/'), get_string('back')),
        'mt-3'
    );

    echo $OUTPUT->footer();
    exit;
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
                case 'assign':
                    $icon = '📝';
                    break;
                case 'book':
                    $icon = '📚';
                    break;
                case 'chat':
                    $icon = '💬';
                    break;
                case 'choice':
                    $icon = '☑️';
                    break;
                case 'feedback':
                    $icon = '🗳️';
                    break;
                case 'folder':
                    $icon = '📁';
                    break;
                case 'forum':
                    $icon = '💬';
                    break;
                case 'glossary':
                    $icon = '📔';
                    break;
                case 'h5pactivity':
                    $icon = '▶️';
                    break;
                case 'label':
                    $icon = '🏷️';
                    break;
                case 'lesson':
                    $icon = '📘';
                    break;
                case 'lti':
                    $icon = '🌐';
                    break;
                case 'page':
                    $icon = '📄';
                    break;
                case 'quiz':
                    $icon = '❓';
                    break;
                case 'resource':
                    $icon = '📄';
                    break;
                case 'scorm':
                    $icon = '🎯';
                    break;
                case 'survey':
                    $icon = '📊';
                    break;
                case 'url':
                    $icon = '🔗';
                    break;
                case 'wiki':
                    $icon = '🧭';
                    break;
                case 'workshop':
                    $icon = '🛠️';
                    break;
                default:
                    $icon = '📄';
            }
            // Indent activities visually in the list with icon.
            $group['options']['    ' . $icon . ' ' . $cmname] = $cmname;
        }
        if (!empty($group['options'])) {
            $lessonoptions[] = $group;
        }
    }
} catch (\Throwable $e) {
    // Log; leave topics empty if anything goes wrong.
    if (function_exists('debugging') && defined('DEBUG_DEVELOPER')) {
        debugging('block_aipromptgen view: building topics/lessons failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }
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
                    // Grade_outcome is a legacy class with public props.
                    $name = '';
                    if (!empty($o->shortname)) {
                        $name = format_string($o->shortname);
                    } else if (!empty($o->fullname)) {
                        $name = format_string($o->fullname);
                    }
                    $name = trim((string)$name);
                    if ($name === '') {
                        continue;
                    }
                    $desc = '';
                    if (!empty($o->description)) {
                        $desc = trim(strip_tags(format_text($o->description, FORMAT_HTML)));
                    }
                    $text = $name . ($desc !== '' ? ' — ' . $desc : '');
                    $competencies[] = $text;
                    if (!empty($o->id)) {
                        $seen[(int)$o->id] = true;
                    }
                }
            }
        }
        // Global outcomes (site-level), include them if not already present in local list.
        if (class_exists('grade_outcome') && method_exists('grade_outcome', 'fetch_all_global')) {
            $globals = grade_outcome::fetch_all_global();
            if (!empty($globals) && is_array($globals)) {
                foreach ($globals as $o) {
                    $oid = isset($o->id) ? (int)$o->id : 0;
                    if ($oid && isset($seen[$oid])) {
                        continue;
                    }
                    $name = '';
                    if (!empty($o->shortname)) {
                        $name = format_string($o->shortname);
                    } else if (!empty($o->fullname)) {
                        $name = format_string($o->fullname);
                    }
                    $name = trim((string)$name);
                    if ($name === '') {
                        continue;
                    }
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
    // Log issues with grade outcomes; we'll fall back to competencies below.
    if (function_exists('debugging') && defined('DEBUG_DEVELOPER')) {
        debugging('block_aipromptgen view: collecting grade outcomes failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }
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
    if (empty($coursecompetencies) && class_exists('\\tool_lp\\api')
        && method_exists('\\tool_lp\\api', 'list_course_competencies')) {
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
        if (empty($competencyid)) {
            continue;
        }

        $comp = null;
        if (class_exists('\\core_competency\\api') && method_exists('\\core_competency\\api', 'read_competency')) {
            $comp = \core_competency\api::read_competency($competencyid);
        }
        if (!$comp && class_exists('\\tool_lp\\api') && method_exists('\\tool_lp\\api', 'read_competency')) {
            $comp = \tool_lp\api::read_competency($competencyid);
        }
        if (!$comp) {
            continue;
        }

        // Access fields using persistent getters when available.
        $shortname = method_exists($comp, 'get') ? (string)$comp->get('shortname')
        : ((isset($comp->shortname) ? (string)$comp->shortname : ''));
        $idnumber  = method_exists($comp, 'get') ? (string)$comp->get('idnumber')
        : ((isset($comp->idnumber) ? (string)$comp->idnumber : ''));
        $descraw   = method_exists($comp, 'get') ? $comp->get('description')
        : (isset($comp->description) ? $comp->description : '');
        $descfmt   = method_exists($comp, 'get') ? ($comp->get('descriptionformat') ?? FORMAT_HTML)
        : (isset($comp->descriptionformat) ? $comp->descriptionformat : FORMAT_HTML);

        $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
        if ($name === '') {
            $id = method_exists($comp, 'get') ? (string)$comp->get('id') : (isset($comp->id) ? (string)$comp->id : '');
            $name = $id !== '' ? $id : get_string('competency', 'core_competency');
        }
        $desc = '';
        if (!empty($descraw)) {
            $desc = trim(strip_tags(format_text($descraw, $descfmt)));
        }
        $text = $name;
        if ($desc !== '') {
            $text .= ' — ' . $desc;
        }
        $competencies[] = $text;
    }
} catch (\Throwable $e) {
    // Log if competencies are not configured or user lacks permissions.
    if (function_exists('debugging') && defined('DEBUG_DEVELOPER')) {
        debugging('block_aipromptgen view: reading course competencies failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
    }
}
// Fallback: if no course-level competencies found, try collecting from visible course modules.
if (empty($competencies)) {
    try {
        if (class_exists('\\core_competency\\api')) {
            $seen = [];
            $modinfo = get_fast_modinfo($course);
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
                        } else if (method_exists($link, 'get_competencyid')) {
                            $competencyid = $link->get_competencyid();
                        }
                    }
                    if (empty($competencyid)) {
                        continue;
                    }
                    $cid = (int)$competencyid;
                    if (isset($seen[$cid])) {
                        continue;
                    }
                    $comp = null;
                    if (class_exists('\\core_competency\\api') && method_exists('\\core_competency\\api', 'read_competency')) {
                        $comp = \core_competency\api::read_competency($cid);
                    }
                    if (!$comp && class_exists('\\tool_lp\\api') && method_exists('\\tool_lp\\api', 'read_competency')) {
                        $comp = \tool_lp\api::read_competency($cid);
                    }
                    if (!$comp) {
                        continue;
                    }
                    $shortname = method_exists($comp, 'get') ? (string)$comp->get('shortname')
                    : ((isset($comp->shortname) ? (string)$comp->shortname : ''));
                    $idnumber  = method_exists($comp, 'get') ? (string)$comp->get('idnumber')
                    : ((isset($comp->idnumber) ? (string)$comp->idnumber : ''));
                    $descraw   = method_exists($comp, 'get') ? $comp->get('description')
                    : (isset($comp->description) ? $comp->description : '');
                    $descfmt   = method_exists($comp, 'get') ? ($comp->get('descriptionformat') ?? FORMAT_HTML)
                    : (isset($comp->descriptionformat) ? $comp->descriptionformat : FORMAT_HTML);
                    $name = trim(format_string($shortname !== '' ? $shortname : $idnumber));
                    if ($name === '') {
                        $idtxt = method_exists($comp, 'get') ? (string)$comp->get('id')
                        : (isset($comp->id) ? (string)$comp->id : '');
                        $name = $idtxt !== '' ? $idtxt : get_string('competency', 'core_competency');
                    }
                    $desc = '';
                    if (!empty($descraw)) {
                        $desc = trim(strip_tags(format_text($descraw, $descfmt)));
                    }
                    $text = $name;
                    if ($desc !== '') {
                        $text .= ' — ' . $desc;
                    }
                    $competencies[] = $text;
                    $seen[$cid] = true;
                }
            }
        }
    } catch (\Throwable $e) {
        // Log fallback errors.
        if (function_exists('debugging') && defined('DEBUG_DEVELOPER')) {
            debugging('block_aipromptgen view: collecting module-level competencies failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
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
            if ($name === '') {
                $name = (string)$r->id;
            }
            $desc = '';
            if (!empty($descraw)) {
                $desc = trim(strip_tags(format_text($descraw, $descfmt)));
            }
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
                if ($name === '') {
                    $name = (string)$r->id;
                }
                $desc = '';
                if (!empty($descraw)) {
                    $desc = trim(strip_tags(format_text($descraw, $descfmt)));
                }
                $text = $name . ($desc !== '' ? ' — ' . $desc : '');
                $competencies[] = $text;
            }
        }
    } catch (\Throwable $e) {
        // Log last-resort failure; keep the list empty.
        if (function_exists('debugging') && defined('DEBUG_DEVELOPER')) {
            debugging('block_aipromptgen view: DB fallback for competencies failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
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

// Compute a concrete option code that exists in the language dropdown.
$sm = get_string_manager();
$alllangs = $sm->get_list_of_languages();
$pickcode = function(string $code) use ($alllangs): string {
    $code = trim($code);
    if ($code === '') {
        return '';
    }
    $aliasmap = [
        'sr_cyrl' => 'sr_cr',
        'sr@cyrl' => 'sr_cr',
        'sr_cyr'  => 'sr_cr',
        'sr_latn' => 'sr_lt',
        'sr@latin' => 'sr_lt',
    ];
    $norms = array_unique([
        $code,
        str_replace('-', '_', $code),
        str_replace('_', '-', $code),
        str_replace('@', '_', $code),
    ]);
    foreach ($norms as $c) {
        if (isset($alllangs[$c])) {
            return $c;
        }
        if (isset($aliasmap[$c]) && isset($alllangs[$aliasmap[$c]])) {
            return $aliasmap[$c];
        }
    }
    $base = substr($code, 0, 2);
    if ($base === 'sr') {
        foreach (['sr_lt', 'sr_cr', 'sr'] as $p) {
            if (isset($alllangs[$p])) {
                return $p;
            }
        }
    }
    foreach (array_keys($alllangs) as $k) {
        if (stripos($k, $base) === 0) {
            return $k;
        }
    }
    return isset($alllangs['en']) ? 'en' : (string)array_key_first($alllangs);
};
$defaultlangselect = $pickcode($defaultlangcode);

$actionparams = ['courseid' => $course->id];
if (!empty($sectionid)) {
    $actionparams['section'] = (int)$sectionid;
}
if (!empty($cmid)) {
    $actionparams['cmid'] = (int)$cmid;
}
$actionurl = new moodle_url('/blocks/aipromptgen/view.php', $actionparams);

$form = new \block_aipromptgen\form\prompt_form(
    $actionurl,
    [
        'topics' => $topics ?? [],
        'lessonoptions' => $lessonoptions ?? [],
        'subjectdefault' => $coursedefaultname ?? '',
        'defaultlanguage' => $defaultlangselect ?? 'en',
        'coursename' => $coursedefaultname ?? '',
    ],
    'post',
    '',
    ['id' => 'promptform'],
    true
);

$generated = null;
$airesponse = null;
$refillsubject = null;
$posteddata = null;
$typedname = '';

if ($data = $form->get_data()) {
    // Keep a copy of submitted values so we can repopulate the form after rendering results.
    $posteddata = $data;
    // Capture typed language name (always), and hidden code (may be stale if user didn't blur the field).
    $typedname = trim((string)($data->language ?? ''));
    $langcodehidden = clean_param(($data->languagecode ?? ''), PARAM_ALPHANUMEXT);

    // Resolve installed languages list (code => name) and translations for display names.
    $sm = get_string_manager();
    $installedlangs = $sm->get_list_of_languages();
    $translations = $sm->get_list_of_translations();
    // Prefer translated names if available (so matching by typed name works with localized lists too).
    if (!empty($translations)) {
        foreach ($translations as $code => $name) {
            if (isset($installedlangs[$code]) && is_string($name) && $name !== '') {
                $installedlangs[$code] = $name;
            }
        }
    }

    // Resolve code from typed language first; fall back to hidden code if needed.
    $langcodetyped = '';
    if ($typedname !== '') {
        // Try code in parentheses, e.g., "Português (pt_br)".
        if (preg_match('/\(([a-z]{2,3}(?:[_-][a-z]{2,3})?)\)/i', $typedname, $m)) {
            $langcodetyped = str_replace('-', '_', core_text::strtolower($m[1]));
        }
        // Exact name match against installed lists.
        if ($langcodetyped === '') {
            $typedlow = core_text::strtolower($typedname);
            foreach ($installedlangs as $code => $name) {
                if (core_text::strtolower((string)$name) === $typedlow) {
                    $langcodetyped = (string)$code;
                    break;
                }
            }
        }
        // Match base name with parentheses stripped.
        if ($langcodetyped === '') {
            $typedbase = core_text::strtolower(trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', $typedname)));
            $candidates = [];
            foreach ($installedlangs as $code => $name) {
                $namebase = core_text::strtolower(trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', (string)$name)));
                if ($namebase === $typedbase) {
                    $candidates[] = (string)$code;
                }
            }
            if (!empty($candidates)) {
                if (in_array('sr_lt', $candidates, true)) {
                    $langcodetyped = 'sr_lt';
                } else if (in_array('sr_cr', $candidates, true)) {
                    $langcodetyped = 'sr_cr';
                } else {
                    $langcodetyped = $candidates[0];
                }
            }
        }
        // Synonyms.
        if ($langcodetyped === '') {
            $tl = core_text::strtolower($typedname);
            $syn = [
                'serbian latin' => 'sr_lt',
                'serbian (latin)' => 'sr_lt',
                'srpski latinica' => 'sr_lt',
                'srpski (latinica)' => 'sr_lt',
                'serbian cyrillic' => 'sr_cr',
                'serbian (cyrillic)' => 'sr_cr',
                'srpski ćirilica' => 'sr_cr',
                'srpski (ćirilica)' => 'sr_cr',
                'serbian' => 'sr_lt',
                'srpski' => 'sr_lt',
                'english' => 'en',
                'english (en)' => 'en',
            ];
            if (isset($syn[$tl])) {
                $langcodetyped = $syn[$tl];
            }
        }
    }
    // Choose typed-derived code when available; otherwise keep the hidden code.
    $langcode = $langcodetyped !== '' ? $langcodetyped : $langcodehidden;

    // Helper to normalize a language code to an installed pack (handles aliases like sr/sr_cyrl -> sr_lt/sr_cr).
    $normalizecode = function(string $code) use ($sm): string {
        $code = trim($code);
        if ($code === '') {
            return '';
        }
        $alllangs = $sm->get_list_of_languages();
        $aliasmap = [
            'sr_cyrl' => 'sr_cr',
            'sr@cyrl' => 'sr_cr',
            'sr_cyr'  => 'sr_cr',
            'sr_latn' => 'sr_lt',
            'sr@latin' => 'sr_lt',
        ];
        $variants = array_unique([
            $code,
            str_replace('-', '_', $code),
            str_replace('_', '-', $code),
            str_replace('@', '_', $code),
        ]);
        foreach ($variants as $c) {
            if (isset($alllangs[$c])) {
                return $c;
            }
            if (isset($aliasmap[$c]) && isset($alllangs[$aliasmap[$c]])) {
                return $aliasmap[$c];
            }
        }
        $base = substr($code, 0, 2);
        if ($base === 'sr') {
            foreach (['sr_lt', 'sr_cr', 'sr'] as $p) {
                if (isset($alllangs[$p])) {
                    return $p;
                }
            }
        }
        foreach (array_keys($alllangs) as $k) {
            if (stripos($k, $base) === 0) {
                return $k;
            }
        }
        $cur = (string)current_language();
        if (isset($alllangs[$cur])) {
            return $cur;
        }
        return isset($alllangs['en']) ? 'en' : (string)array_key_first($alllangs);
    };

    // Fallback order for missing code: course language, user language, current UI language.
    if ($langcode === '') {
        global $USER;
        if (!empty($course->lang)) {
            $langcode = (string)$course->lang;
        } else if (!empty($USER->lang)) {
            $langcode = (string)$USER->lang;
        } else {
            $langcode = (string)current_language();
        }
    }
    $langcode = $normalizecode($langcode);

    // UI labels follow current Moodle language automatically via get_string().
    // Prompt content (labels inside the generated text) will use the selected language.
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
        'lessoncount' => $sm->get_string('label:lessoncount', 'block_aipromptgen', null, $langcode),
        'lessonduration' => $sm->get_string('label:lessonduration', 'block_aipromptgen', null, $langcode),
    ];

    // Use free-text values from the form for purpose, audience, and class type.
    $purposevalue = trim((string)($data->purpose ?? ''));
    $audiencevalue = trim((string)($data->audience ?? ''));
    $classtypevalue = trim((string)($data->classtype ?? ''));

    $parts = [];
    $parts[] = $labels['purpose'] . ': ' . $purposevalue;
    $parts[] = $labels['audience'] . ': ' . $audiencevalue;
    // Resolve human-readable language name from installed languages.
    $trans = $sm->get_list_of_translations();
    $langslist = $sm->get_list_of_languages();
    // Prefer the typed language name for display when provided.
    $displaybase = trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', (string)$typedname));
    if ($displaybase === '') {
        $langname = $trans[$langcode] ?? ($langslist[$langcode] ?? $langcode);
        $langname = trim(preg_replace('/\s*\([^\)]*\)\s*$/', '', (string)$langname));
    } else {
        $langname = $displaybase;
    }
    // Append code only if it's meaningful (e.g., sr_lt, sr_cr).
    // Avoid appending '(en)' when user typed a different language but packs are missing.
    if (!empty($langcode)) {
        $lc = core_text::strtolower($langcode);
        $isenglish = (strpos(core_text::strtolower($langname), 'english') !== false);
        if ($lc !== 'en' || $isenglish) {
            $langname .= ' (' . $langcode . ')';
        }
    }
    if ($langname === null || $langname === '') {
        $short = substr($langcode, 0, 2);
        $langname = $trans[$short] ?? ($langslist[$short] ?? $langcode);
    }
    $parts[] = $labels['language'] . ': ' . $langname;
    $subjectval = (string)($data->subject ?? '');
    if (trim($subjectval) === '' && trim($coursedefaultname) !== '') {
        $subjectval = $coursedefaultname;
    }
    $refillsubject = $subjectval;
    $agerangeval = trim((string)($data->agerange ?? ''));
    // Decide unit based on selected language: English => "years", Serbian => "godina"; default to English.
    $ageunit = 'years';
    $lclc = core_text::strtolower($langcode);
    if ($lclc === 'sr' || strpos($lclc, 'sr_') === 0) {
        $ageunit = 'godina';
    } else if ($lclc === 'en' || strpos($lclc, 'en_') === 0) {
        $ageunit = 'years';
    }
    // Format with appropriate unit and normalize ranges to hyphen.
    $agerangedisplay = $agerangeval;
    if ($agerangeval !== '') {
        if (preg_match('/^\d+$/', $agerangeval)) {
            $agerangedisplay = $agerangeval . ' ' . $ageunit;
        } else if (preg_match('/^\s*\d+\s*[\x{2013}-]\s*\d+\s*$/u', $agerangeval)) { // 10-12 or 10–12
            // Normalize any spaces and dashes to a simple hyphen.
            $norm = preg_replace('/\s*[\x{2013}-]\s*/u', '-', $agerangeval);
            $norm = trim($norm);
            $agerangedisplay = $norm . ' ' . $ageunit;
        }
    }
    $topicval = (string)($data->topic ?? '');
    $lessonval = (string)($data->lesson ?? '');
    $lessoncount = (int)($data->lessoncount ?? 0);
    $lessonduration = (int)($data->lessonduration ?? 0);
    $outcomesval = (string)($data->outcomes ?? '');
    $parts[] = $labels['subject'] . ': ' . $subjectval;
    $parts[] = $labels['agerange'] . ': ' . $agerangedisplay;
    if ($topicval !== '') {
        $parts[] = $labels['topic'] . ': ' . $topicval;
    }
    $parts[] = $labels['lesson'] . ': ' . $lessonval;
    if ($lessoncount > 0) {
        $parts[] = $labels['lessoncount'] . ': ' . $lessoncount;
    }
    if ($lessonduration > 0) {
        $parts[] = $labels['lessonduration'] . ': ' . $lessonduration . ' min';
    }
    $parts[] = $labels['classtype'] . ': ' . $classtypevalue;
    if (trim($outcomesval) !== '') {
        $parts[] = $labels['outcomes'] . ': ' . preg_replace('/\s+/', ' ', trim($outcomesval));
    }

    $coursename = format_string($course->fullname);
    $prefix = $sm->get_string('prompt:prefix', 'block_aipromptgen', (object)['course' => $coursename], $langcode);
    $instructions = $sm->get_string('prompt:instructions', 'block_aipromptgen', null, $langcode);
    $generated = $prefix . "\n" . implode("\n", $parts) . "\n" . $instructions;

    // Optional: send to ChatGPT if requested and configured.
    $sendtochat = optional_param('sendtochat', 0, PARAM_BOOL);
    if ($sendtochat && !empty($generated)) {
        $apikey = (string)(get_config('block_aipromptgen', 'openai_apikey') ?? '');
        $model = (string)(get_config('block_aipromptgen', 'openai_model') ?? 'gpt-4o-mini');
        if ($apikey !== '') {
            $endpoint = 'https://api.openai.com/v1/chat/completions';
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $generated],
                ],
                'temperature' => 0.7,
            ];
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apikey,
            ];
            try {
                $curl = new curl();
                $resp = $curl->post($endpoint, json_encode($payload), $headers);
                $json = json_decode($resp);
                if (isset($json->choices[0]->message->content)) {
                    $airesponse = (string)$json->choices[0]->message->content;
                } else if (isset($json->error->message)) {
                    $airesponse = 'Error: ' . (string)$json->error->message;
                } else {
                    $airesponse = 'No response content received.';
                }
            } catch (\Throwable $e) {
                $airesponse = 'Error contacting OpenAI: ' . $e->getMessage();
            }
        } else {
            $airesponse = 'OpenAI is not configured.';
        }
    }

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
    if (!empty($defaultlangselect)) {
        // Set both the visible language name and the hidden language code.
        $trans = $sm->get_list_of_translations();
        $langslist = $sm->get_list_of_languages();
        $defaultlangname = $trans[$defaultlangselect] ?? ($langslist[$defaultlangselect] ?? $defaultlangselect);
        $defaultdata['language'] = $defaultlangname;
        $defaultdata['languagecode'] = $defaultlangselect;
    }
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
} else if ($posteddata) {
    // Refill the form with the user's submitted values (keeps age/grade and others intact).
    $persist = (array)$posteddata;
    if (is_string($refillsubject) && trim($refillsubject) !== '') {
        $persist['subject'] = $refillsubject;
    }
    $form->set_data($persist);
}
$form->display();

// Client-side fallback: if Subject is still empty on first load, set it to the course name.
if (!$form->is_submitted() && $coursedefaultname !== '') {
    $jsfill = "(function(){var el=document.getElementById('id_subject'); if(el && !el.value)"
    . "{ el.value='" . addslashes($coursedefaultname) . "'; }})();";
    $PAGE->requires->js_amd_inline($jsfill);
}

// Inject a lightweight modal to browse and pick a lesson/section into the Lesson textbox.
// Build the modal markup from $lessonoptions prepared above.
echo html_writer::tag('style',
    '.ai4t-modal-backdrop{position:fixed;inset:0;display:none;background:rgba(0,0,0,.4);z-index:1050;}
     .ai4t-modal{position:fixed;top:10%;left:50%;transform:translateX(-50%);width:90%;max-width:720px;
     max-height:70vh;display:none;z-index:1060;background:#fff;border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.3);}
     .ai4t-modal header{display:flex;justify-content:space-between;align-items:center;
     padding:12px 16px;border-bottom:1px solid #ddd;}
     .ai4t-modal .ai4t-body{padding:8px 16px;overflow:auto;max-height:58vh;}
     .ai4t-list{list-style:none;margin:0;padding:0;}
     .ai4t-section{font-weight:600;margin:8px 0 4px;}
     .ai4t-item{padding:6px 8px;border-radius:4px;cursor:pointer;}
     .ai4t-item:hover{background:#f2f2f2;}
     .ai4t-modal footer{padding:10px 16px;border-top:1px solid #ddd;display:flex;justify-content:flex-end;gap:8px;}
    ');

// Modal backdrop and container.
echo html_writer::div('', 'ai4t-modal-backdrop', ['id' => 'ai4t-modal-backdrop']);

// Age modal: pick exact age or range and insert into the textbox.
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal',
    'id' => 'ai4t-age-modal',
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => 'ai4t-age-modal-title',
    'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:agerangelabel', 'block_aipromptgen'), ['id' => 'ai4t-age-modal-title']);
echo html_writer::tag('button', '&times;', [
    'type' => 'button', 'id' => 'ai4t-age-modal-close', 'class' => 'btn btn-link', 'aria-label' => get_string('cancel'),
]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
// Simple controls: one number for exact, or two numbers for range.
echo html_writer::start_tag('div');
// Exact age option with radio.
echo html_writer::start_tag('label', ['style' => 'display:flex;align-items:center;gap:8px;']);
echo html_writer::empty_tag('input', [
    'type' => 'radio', 'name' => 'ai4t-age-mode', 'id' => 'ai4t-age-mode-exact', 'value' => 'exact', 'checked' => 'checked',
]);
echo html_writer::span(s('Exact age'));
echo html_writer::empty_tag('input', ['type' => 'number', 'id' => 'ai4t-age-exact',
'min' => 1, 'max' => 120, 'step' => 1, 'style' => 'width:100px;']);
echo html_writer::end_tag('label');
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', ['style' => 'margin-top:8px;']);
// Range option with radio.
echo html_writer::start_tag('label', ['style' => 'display:flex;align-items:center;gap:8px;']);
echo html_writer::empty_tag('input', [
    'type' => 'radio', 'name' => 'ai4t-age-mode', 'id' => 'ai4t-age-mode-range', 'value' => 'range',
]);
echo html_writer::span(s('Range'));
echo html_writer::empty_tag('input', ['type' => 'number',
'id' => 'ai4t-age-from', 'min' => 1, 'max' => 120, 'step' => 1, 'placeholder' => 'From', 'style' => 'width:100px;']);
echo html_writer::empty_tag('input', ['type' => 'number',
'id' => 'ai4t-age-to', 'min' => 1, 'max' => 120, 'step' => 1, 'placeholder' => 'To', 'style' => 'width:100px;']);
echo html_writer::end_tag('label');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('add'), [
    'type' => 'button', 'class' => 'btn btn-primary', 'id' => 'ai4t-age-modal-insert',
]);
echo html_writer::tag('button', get_string('cancel'), [
    'type' => 'button', 'class' => 'btn btn-secondary', 'id' => 'ai4t-age-modal-cancel',
]);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

// Wire up Age modal open/close and insertion logic.
$agebrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-age-browse');\n"
    . "var modal=document.getElementById('ai4t-age-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-age-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-age-modal-cancel');\n"
    . "var insertBtn=document.getElementById('ai4t-age-modal-insert');\n"
    . "var input=document.getElementById('id_agerange');\n"
    . "var exact=document.getElementById('ai4t-age-exact');\n"
    . "var from=document.getElementById('ai4t-age-from');\n"
    . "var to=document.getElementById('ai4t-age-to');\n"
    . "var modeExact=document.getElementById('ai4t-age-mode-exact');\n"
    . "var modeRange=document.getElementById('ai4t-age-mode-range');\n"
    . "function open(){ if(!modal||!backdrop){return;} prefill();"
    . "modal.style.display='block'; backdrop.style.display='block'; modal.focus(); }\n"
    . "function close(){ if(!modal||!backdrop){return;} modal.style.display='none'; backdrop.style.display='none'; }\n"
    . "function syncEnabled(){ var useExact = modeExact && modeExact.checked;"
    . "if(useExact){ if(exact){ exact.removeAttribute('disabled'); }"
    . " if(from){ from.setAttribute('disabled','disabled'); }"
    . "if(to){ to.setAttribute('disabled','disabled'); } } else { if(exact){ exact.setAttribute('disabled','disabled'); } if(from)"
    . " { from.removeAttribute('disabled'); } if(to){ to.removeAttribute('disabled'); } } }\n"
    . "function prefill(){ if(!input){return;} var v=(input.value||'').trim(); if(!v){ if(modeExact)"
    . "  { modeExact.checked=true; } exact.value=''; from.value=''; to.value=''; syncEnabled(); return; }\n"
    . "  if(/^\\d+$/.test(v)){ exact.value=v; from.value=''; to.value='';"
    . "if(modeExact){ modeExact.checked=true; } syncEnabled(); return; }\n"
    . "  var m=v.match(/^\s*(\\d+)\s*[-\\u2013]\s*(\\d+)\s*$/u);\n"
    . "  if(m){ exact.value=''; from.value=m[1]; to.value=m[2]; if(modeRange){ modeRange.checked=true; } syncEnabled(); return; }\n"
    . "  if(modeExact){ modeExact.checked=true; } exact.value=''; from.value=''; to.value=''; syncEnabled();\n"
    . "}\n"
    . "function onInsert(){ if(!input){ close(); return; } var ev=(exact.value||'').trim();"
    . "var fv=(from.value||'').trim(); var tv=(to.value||'').trim();\n"
    . "  var useExact = modeExact && modeExact.checked;\n"
    . "  if(useExact && ev){ input.value=ev; close(); return; }\n"
    . "  if(!useExact && fv && tv){ var a=parseInt(fv,10); var b=parseInt(tv,10);"
    . "if(!isNaN(a)&&!isNaN(b)){ if(a>b){ var t=a;a=b;b=t; } input.value=a+'-'+b; close(); return; } }\n"
    . "  close();\n"
    . "}\n"
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "if(insertBtn){ insertBtn.addEventListener('click', onInsert); }\n"
    . "if(modeExact){ modeExact.addEventListener('change', syncEnabled); }\n"
    . "if(modeRange){ modeRange.addEventListener('change', syncEnabled); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "})();";
$PAGE->requires->js_amd_inline($agebrowsejs);
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
echo html_writer::end_tag('div');

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
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-lesson-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick);"
    . "items[i].addEventListener('keydown', function(ev)"
    . "{ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
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
    if ($t === '') {
        continue;
    }
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
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-topic-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick);"
    . "items[i].addEventListener('keydown', function(ev)"
    . "{ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
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
        'style' => 'color:#666;',
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
    . "function onInsert(){ if(!ta){ close(); return; } var boxes=document.querySelectorAll('.ai4t-outcome-checkbox:checked');"
    . "var vals=[]; for(var i=0;i<boxes.length;i++)"
    . "{ if(boxes[i].value){ vals.push(boxes[i].value); } } if(vals.length===0)"
    . "{ close(); return; } var cur=ta.value||''; if(cur && !/\\n$/.test(cur)){ cur+='\\n'; }"
    . "ta.value=cur+vals.join('\\n'); close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(insertBtn){ insertBtn.addEventListener('click', onInsert); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "})();";
$PAGE->requires->js_amd_inline($outcomesbrowsejs);

// Language modal: list installed languages and set both text and hidden code.
$langoptions = $sm->get_list_of_languages();
$installed = $sm->get_list_of_translations();
if (!empty($installed)) {
    foreach ($installed as $code => $name) {
        if (isset($langoptions[$code]) && is_string($name) && $name !== '') {
            $langoptions[$code] = $name;
        }
    }
}
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal',
    'id' => 'ai4t-language-modal',
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => 'ai4t-language-modal-title',
    'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:language', 'block_aipromptgen'), ['id' => 'ai4t-language-modal-title']);
echo html_writer::tag('button', '&times;', [
    'type' => 'button', 'id' => 'ai4t-language-modal-close', 'class' => 'btn btn-link', 'aria-label' => get_string('cancel'),
]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
foreach ($langoptions as $code => $name) {
    echo html_writer::tag('li', s($name . ' [' . $code . ']'), [
        'class' => 'ai4t-item ai4t-language-item',
        'data-code' => $code,
        'data-name' => $name,
        'tabindex' => 0,
    ]);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('cancel'), [
    'type' => 'button', 'class' => 'btn btn-secondary', 'id' => 'ai4t-language-modal-cancel',
]);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

$languagebrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-language-browse');\n"
    . "var modal=document.getElementById('ai4t-language-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-language-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-language-modal-cancel');\n"
    . "var input=document.getElementById('id_language');\n"
    . "var codeEl=document.getElementById('id_languagecode');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onPick(e){ var t=e.currentTarget; var name=t.getAttribute('data-name'); var code=t.getAttribute('data-code');"
    . " if(input){ input.value=name; } if(codeEl){ codeEl.value=code; } close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-language-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick);"
    . "items[i].addEventListener('keydown', function(ev)"
    . "{ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($languagebrowsejs);

// Auto-sync hidden language code when the user types/pastes a language name without using the modal.
$langsyncjs = "(function(){\n"
    . "var input=document.getElementById('id_language');\n"
    . "var codeEl=document.getElementById('id_languagecode');\n"
    . "function guess(){ if(!input||!codeEl){return;} var t=(input.value||'').trim(); if(!t){return;}\n"
    . "  var m=t.match(/\\(([a-z]{2,3}(?:[_-][a-z]{2,3})?)\\)/i);"
    . "if(m){ codeEl.value=m[1].replace('-', '_').toLowerCase(); return; }\n"
    . "  var items=document.querySelectorAll('.ai4t-language-item'); var tl=t.toLowerCase();\n"
    . "  for(var i=0;i<items.length;i++){ var name=items[i].getAttribute('data-name')||''; if(name.toLowerCase()===tl)"
    . "{ codeEl.value=items[i].getAttribute('data-code'); return; } }\n"
    . "}\n"
    . "if(input){ input.addEventListener('blur', guess); input.addEventListener('change', guess); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($langsyncjs);

// Purpose modal: fixed list of purposes.
$purposelist = [
    get_string('option:lessonplan', 'block_aipromptgen'),
    get_string('option:quiz', 'block_aipromptgen'),
    get_string('option:rubric', 'block_aipromptgen'),
    get_string('option:worksheet', 'block_aipromptgen'),
];
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal', 'id' => 'ai4t-purpose-modal', 'role' => 'dialog',
    'aria-modal' => 'true', 'aria-labelledby' => 'ai4t-purpose-modal-title', 'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:purpose', 'block_aipromptgen'), ['id' => 'ai4t-purpose-modal-title']);
echo html_writer::tag('button', '&times;',
['type' => 'button', 'id' => 'ai4t-purpose-modal-close', 'class' => 'btn btn-link', 'aria-label' => get_string('cancel')]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
foreach ($purposelist as $p) {
    echo html_writer::tag('li', s($p), ['class' => 'ai4t-item ai4t-purpose-item', 'data-value' => $p, 'tabindex' => 0]);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('cancel'),
['type' => 'button', 'class' => 'btn btn-secondary', 'id' => 'ai4t-purpose-modal-cancel']);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

$purposebrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-purpose-browse');\n"
    . "var modal=document.getElementById('ai4t-purpose-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-purpose-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-purpose-modal-cancel');\n"
    . "var input=document.getElementById('id_purpose');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onPick(e){ var v=e.currentTarget.getAttribute('data-value'); if(input && v!=null){ input.value=v; } close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-purpose-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick);"
    . "items[i].addEventListener('keydown', function(ev)"
    . "{ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($purposebrowsejs);

// Audience modal: two options.
$audiencelist = [
    get_string('option:student', 'block_aipromptgen'),
    get_string('option:teacher', 'block_aipromptgen'),
];
echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal', 'id' => 'ai4t-audience-modal', 'role' => 'dialog',
    'aria-modal' => 'true', 'aria-labelledby' => 'ai4t-audience-modal-title',
    'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:audience', 'block_aipromptgen'), ['id' => 'ai4t-audience-modal-title']);
echo html_writer::tag('button', '&times;', ['type' => 'button',
'id' => 'ai4t-audience-modal-close', 'class' => 'btn btn-link', 'aria-label' => get_string('cancel')]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
foreach ($audiencelist as $a) {
    echo html_writer::tag('li', s($a), ['class' => 'ai4t-item ai4t-audience-item', 'data-value' => $a, 'tabindex' => 0]);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('cancel'),
['type' => 'button', 'class' => 'btn btn-secondary', 'id' => 'ai4t-audience-modal-cancel']);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

$audiencebrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-audience-browse');\n"
    . "var modal=document.getElementById('ai4t-audience-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-audience-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-audience-modal-cancel');\n"
    . "var input=document.getElementById('id_audience');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onPick(e){ var v=e.currentTarget.getAttribute('data-value'); if(input && v!=null){ input.value=v; } close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-audience-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick);"
    . "items[i].addEventListener('keydown', function(ev)"
    . "{ if(ev.key==='Enter' || ev.key===' '){ ev.preventDefault(); onPick(ev); } }); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($audiencebrowsejs);

// Add a fourth modal for browsing Class types and inserting into the textbox.
// Use the same small set as before and localize labels for display.
$classtypes = [
    'lecture' => get_string('classtype:lecture', 'block_aipromptgen'),
    'discussion' => get_string('classtype:discussion', 'block_aipromptgen'),
    'groupwork' => get_string('classtype:groupwork', 'block_aipromptgen'),
    'lab' => get_string('classtype:lab', 'block_aipromptgen'),
    'project' => get_string('classtype:project', 'block_aipromptgen'),
    'review' => get_string('classtype:review', 'block_aipromptgen'),
    'assessment' => get_string('classtype:assessment', 'block_aipromptgen'),
];

echo html_writer::start_tag('div', [
    'class' => 'ai4t-modal',
    'id' => 'ai4t-classtype-modal',
    'role' => 'dialog',
    'aria-modal' => 'true',
    'aria-labelledby' => 'ai4t-classtype-modal-title',
    'style' => 'display:none;',
]);
echo html_writer::start_tag('header');
echo html_writer::tag('h3', get_string('form:class_typelabel', 'block_aipromptgen'), ['id' => 'ai4t-classtype-modal-title']);
echo html_writer::tag('button', '&times;', [
    'type' => 'button',
    'id' => 'ai4t-classtype-modal-close',
    'class' => 'btn btn-link',
    'aria-label' => get_string('cancel'),
]);
echo html_writer::end_tag('header');
echo html_writer::start_tag('div', ['class' => 'ai4t-body']);
echo html_writer::start_tag('ul', ['class' => 'ai4t-list']);
foreach ($classtypes as $code => $label) {
    echo html_writer::tag('li', s($label), [
        'class' => 'ai4t-item ai4t-classtype-item',
        'data-value' => $label, // Insert human-readable label.
        'tabindex' => 0,
    ]);
}
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');
echo html_writer::start_tag('footer');
echo html_writer::tag('button', get_string('cancel'), [
    'type' => 'button',
    'class' => 'btn btn-secondary',
    'id' => 'ai4t-classtype-modal-cancel',
]);
echo html_writer::end_tag('footer');
echo html_writer::end_tag('div');

$classtypebrowsejs = "(function(){\n"
    . "var openBtn=document.getElementById('ai4t-classtype-browse');\n"
    . "var modal=document.getElementById('ai4t-classtype-modal');\n"
    . "var backdrop=document.getElementById('ai4t-modal-backdrop');\n"
    . "var closeBtn=document.getElementById('ai4t-classtype-modal-close');\n"
    . "var cancelBtn=document.getElementById('ai4t-classtype-modal-cancel');\n"
    . "var input=document.getElementById('id_classtype');\n"
    . "function open(){ if(modal&&backdrop){ modal.style.display='block'; backdrop.style.display='block'; modal.focus(); } }\n"
    . "function close(){ if(modal&&backdrop){ modal.style.display='none'; backdrop.style.display='none'; } }\n"
    . "function onPick(e){ var v=e.currentTarget.getAttribute('data-value'); if(input && v!=null){ input.value=v; } close(); }\n"
    . "if(openBtn){ openBtn.addEventListener('click', function(e){ if(e){e.preventDefault(); e.stopPropagation();} open(); }); }\n"
    . "if(closeBtn){ closeBtn.addEventListener('click', close); }\n"
    . "if(cancelBtn){ cancelBtn.addEventListener('click', close); }\n"
    . "if(backdrop){ backdrop.addEventListener('click', close); }\n"
    . "document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'){ close(); } });\n"
    . "var items=document.querySelectorAll('.ai4t-classtype-item');\n"
    . "for(var i=0;i<items.length;i++){ items[i].addEventListener('click', onPick);"
    . "items[i].addEventListener('keydown', function(ev){ if(ev.key==='Enter' || ev.key===' ')"
    . "{ ev.preventDefault(); onPick(ev); } }); }\n"
    . "})();";
$PAGE->requires->js_amd_inline($classtypebrowsejs);

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
    // Send to ChatGPT button if API key configured.
    if (!empty(get_config('block_aipromptgen', 'openai_apikey'))) {
        echo html_writer::tag('button', get_string('form:sendtochatgpt', 'block_aipromptgen'), [
            'type' => 'button',
            'id' => 'ai4t-sendtochat',
            'class' => 'btn btn-primary',
            'style' => 'margin-left:8px;',
        ]);
    }
    echo html_writer::tag('span', '', [
        'id' => 'ai4t-copied',
        'style' => 'margin-left:8px; display:none;',
    ]);
    echo html_writer::end_tag('div');
    // Render AI response when present; otherwise leave a placeholder container.
    if (!empty($airesponse)) {
        echo html_writer::tag('h4', get_string('form:response', 'block_aipromptgen'));
        echo html_writer::tag('pre', s($airesponse), [
            'class' => 'form-control',
            'style' => 'white-space:pre-wrap;padding:12px;',
        ]);
    } else if (!empty(get_config('block_aipromptgen', 'openai_apikey'))) {
        echo html_writer::div('', 'ai4t-airesponse', ['id' => 'ai4t-airesponse']);
    }
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
        . "var send=document.getElementById('ai4t-sendtochat');\n"
        . "var form=document.querySelector('form.mform');\n"
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
    . "if(send && form){ send.addEventListener('click', function(){\n"
    . "  try{ var i=document.createElement('input'); i.type='hidden'; i.name='sendtochat';"
    . "i.value='1'; form.appendChild(i); form.submit(); }catch(e){}\n"
    . "}); }\n"
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
