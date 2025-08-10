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

$courseid = required_param('courseid', PARAM_INT);
$sectionid = optional_param('section', 0, PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
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
require_once($CFG->dirroot . '/blocks/ai4teachers/classes/form/prompt_form.php');
// Gather course topics (section names) for suggestions, and build lesson options (sections + activities).
$topics = [];
$lessonoptions = [];
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
// Prepare a robust course name for defaults.
$coursedefaultname = trim((string)format_string($course->fullname));
if ($coursedefaultname === '' && !empty($course->shortname)) {
    $coursedefaultname = trim((string)format_string($course->shortname));
}

$form = new \block_aipromptgen\form\prompt_form(null, [
    'topics' => $topics,
    'lessonoptions' => $lessonoptions,
    'subjectdefault' => $coursedefaultname,
]);

$generated = null;
$sessionkey = 'block_aipromptgen_lastprompt_' . $course->id;
if (optional_param('reset', 0, PARAM_BOOL)) {
    unset($SESSION->{$sessionkey});
}

if ($data = $form->get_data()) {
    // Preserve underscores in language code (e.g., sr_cr).
    $langcode = clean_param($data->language, PARAM_ALPHANUMEXT);

    // UI labels follow current Moodle language automatically via get_string().
    // Prompt content (labels inside the generated text) will use the selected language.
    $labels = [
        'purpose' => get_string('label:purpose', 'block_aipromptgen', null, $langcode),
        'audience' => get_string('label:audience', 'block_aipromptgen', null, $langcode),
        'language' => get_string('label:language', 'block_aipromptgen', null, $langcode),
        'subject' => get_string('label:subject', 'block_aipromptgen', null, $langcode),
        'agerange' => get_string('label:agerange', 'block_aipromptgen', null, $langcode),
    'topic' => get_string('label:topic', 'block_aipromptgen', null, $langcode),
        'lesson' => get_string('label:lesson', 'block_aipromptgen', null, $langcode),
        'classtype' => get_string('label:classtype', 'block_aipromptgen', null, $langcode),
        'outcomes' => get_string('label:outcomes', 'block_aipromptgen', null, $langcode),
    ];

    // Map select codes to localized values.
    $purposecode = clean_param($data->purpose, PARAM_ALPHANUMEXT);
    $audiencecode = clean_param($data->audience, PARAM_ALPHANUMEXT);
    $classtypecode = clean_param($data->classtype, PARAM_ALPHANUMEXT);
    $purposeallowed = ['lessonplan', 'quiz', 'rubric', 'worksheet'];
    $audienceallowed = ['teacher', 'student'];
    $classtypeallowed = ['lecture', 'discussion', 'groupwork', 'lab', 'project', 'review', 'assessment'];
    $purposevalue = in_array($purposecode, $purposeallowed)
    ? get_string('option:' . $purposecode, 'block_aipromptgen', null, $langcode)
        : s($purposecode);
    $audiencevalue = in_array($audiencecode, $audienceallowed)
    ? get_string('option:' . $audiencecode, 'block_aipromptgen', null, $langcode)
        : s($audiencecode);
    $classtypevalue = in_array($classtypecode, $classtypeallowed)
    ? get_string('classtype:' . $classtypecode, 'block_aipromptgen', null, $langcode)
        : s($classtypecode);

    $parts = [];
    $parts[] = $labels['purpose'] . ': ' . $purposevalue;
    $parts[] = $labels['audience'] . ': ' . $audiencevalue;
    $parts[] = $labels['language'] . ': '
    . get_string('lang:' . $langcode, 'block_aipromptgen', null, $langcode);
    $parts[] = $labels['subject'] . ": {$data->subject}";
    $parts[] = $labels['agerange'] . ": {$data->agerange}";
    if (!empty($data->topic)) {
        $parts[] = $labels['topic'] . ": {$data->topic}";
    }
    $parts[] = $labels['lesson'] . ": {$data->lesson}";
    $parts[] = $labels['classtype'] . ": {$classtypevalue}";
    if (!empty($data->outcomes)) {
        $parts[] = $labels['outcomes'] . ': '
            . preg_replace('/\s+/', ' ', trim($data->outcomes));
    }

    $coursename = format_string($course->fullname);
    $prefix = get_string(
        'prompt:prefix',
        'block_aipromptgen',
        (object)['course' => $coursename],
        $langcode
    );
    $instructions = get_string('prompt:instructions', 'block_aipromptgen', null, $langcode);
    $generated = $prefix . "\n" . implode("\n", $parts) . "\n" . $instructions;

    // Persist in user session per course.
    $SESSION->{$sessionkey} = $generated;
} else if (!empty($SESSION->{$sessionkey})) {
    $generated = $SESSION->{$sessionkey};
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

// Always set courseid; set subject/others only when not already present.
$defaultdata = [
    'courseid' => $course->id,
];
// Set topic/lesson defaults when available; keeps previous input if empty.
if ($defaulttopic !== '') {
    $defaultdata['topic'] = $defaulttopic;
}
if ($defaultlesson !== '') {
    $defaultdata['lesson'] = $defaultlesson;
}
// Initialize Subject to course name on first load (avoid overriding user input on submit).
if (!$form->is_submitted()) {
    $defaultdata['subject'] = $coursedefaultname;
}
$form->set_data($defaultdata);
// As a final guard, directly set the Subject value on first load.
if (!$form->is_submitted() && $coursedefaultname !== '') {
    $form->set_data(['subject' => $coursedefaultname]);
}
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
                'class' => 'ai4t-item',
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
    . "var items=document.querySelectorAll('.ai4t-item');\n"
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
    echo html_writer::tag('a', get_string('form:reset', 'block_aipromptgen'), [
        'href' => new moodle_url('/blocks/aipromptgen/view.php', ['courseid' => $course->id, 'reset' => 1]),
        'class' => 'btn btn-link',
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
