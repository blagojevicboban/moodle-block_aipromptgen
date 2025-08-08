<?php
// This file displays the prompt builder form and generates a prompt string.

require_once(__DIR__ . '/../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);
$context = context_course::instance($course->id);
require_capability('block/ai4teachers:manage', $context);

$PAGE->set_url(new moodle_url('/blocks/ai4teachers/view.php', ['courseid' => $course->id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', 'block_ai4teachers'));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('core');

// Load form.
require_once($CFG->dirroot . '/blocks/ai4teachers/classes/form/prompt_form.php');
$form = new \block_ai4teachers\form\prompt_form(null, []);

$generated = null;
$sessionkey = 'block_ai4teachers_lastprompt_' . $course->id;
if (optional_param('reset', 0, PARAM_BOOL)) {
    unset($SESSION->{$sessionkey});
}

if ($data = $form->get_data()) {
    // Preserve underscores in language code (e.g., sr_cr).
    $langcode = clean_param($data->language, PARAM_ALPHANUMEXT);

    // UI labels follow current Moodle language automatically via get_string().
    // Prompt content (labels inside the generated text) will use the selected language.
    $labels = [
        'purpose' => get_string('label:purpose', 'block_ai4teachers', null, $langcode),
        'audience' => get_string('label:audience', 'block_ai4teachers', null, $langcode),
        'language' => get_string('label:language', 'block_ai4teachers', null, $langcode),
        'subject' => get_string('label:subject', 'block_ai4teachers', null, $langcode),
        'agerange' => get_string('label:agerange', 'block_ai4teachers', null, $langcode),
        'lesson' => get_string('label:lesson', 'block_ai4teachers', null, $langcode),
        'classtype' => get_string('label:classtype', 'block_ai4teachers', null, $langcode),
        'outcomes' => get_string('label:outcomes', 'block_ai4teachers', null, $langcode),
    ];

    // Map select codes to localized values.
    $purposecode = clean_param($data->purpose, PARAM_ALPHANUMEXT);
    $audiencecode = clean_param($data->audience, PARAM_ALPHANUMEXT);
    $classtypecode = clean_param($data->classtype, PARAM_ALPHANUMEXT);
    $purposeallowed = ['lessonplan', 'quiz', 'rubric', 'worksheet'];
    $audienceallowed = ['teacher', 'student'];
    $classtypeallowed = ['lecture', 'discussion', 'groupwork', 'lab', 'project', 'review', 'assessment'];
    $purposevalue = in_array($purposecode, $purposeallowed) ? get_string('option:' . $purposecode, 'block_ai4teachers', null, $langcode) : s($purposecode);
    $audiencevalue = in_array($audiencecode, $audienceallowed) ? get_string('option:' . $audiencecode, 'block_ai4teachers', null, $langcode) : s($audiencecode);
    $classtypevalue = in_array($classtypecode, $classtypeallowed) ? get_string('classtype:' . $classtypecode, 'block_ai4teachers', null, $langcode) : s($classtypecode);

    $parts = [];
    $parts[] = $labels['purpose'] . ': ' . $purposevalue;
    $parts[] = $labels['audience'] . ': ' . $audiencevalue;
    $parts[] = $labels['language'] . ": " . get_string('lang:' . $langcode, 'block_ai4teachers', null, $langcode);
    $parts[] = $labels['subject'] . ": {$data->subject}";
    $parts[] = $labels['agerange'] . ": {$data->agerange}";
    $parts[] = $labels['lesson'] . ": {$data->lesson}";
    $parts[] = $labels['classtype'] . ": {$classtypevalue}";
    if (!empty($data->outcomes)) {
        $parts[] = $labels['outcomes'] . ": " . preg_replace('/\s+/', ' ', trim($data->outcomes));
    }

    $coursename = format_string($course->fullname);
    $prefix = get_string('prompt:prefix', 'block_ai4teachers', (object)['course' => $coursename], $langcode);
    $instructions = get_string('prompt:instructions', 'block_ai4teachers', null, $langcode);
    $generated = $prefix . "\n" . implode("\n", $parts) . "\n" . $instructions;

    // Persist in user session per course.
    $SESSION->{$sessionkey} = $generated;
} else if (!empty($SESSION->{$sessionkey})) {
    $generated = $SESSION->{$sessionkey};
}

echo $OUTPUT->header();
$form->set_data(['courseid' => $course->id]);
$form->display();

if ($generated) {
    echo html_writer::tag('h3', get_string('form:result', 'block_ai4teachers'));
    // Editable textarea for the generated prompt.
    echo html_writer::start_tag('div', ['class' => 'ai4t-result']);
    echo html_writer::tag('textarea', s($generated), [
        'id' => 'ai4t-generated',
        'rows' => 12,
        'class' => 'form-control',
        'style' => 'width:100%;'
    ]);
    echo html_writer::empty_tag('br');
    echo html_writer::start_tag('div', ['class' => 'ai4t-actions']);
    echo html_writer::tag('button', get_string('form:copy', 'block_ai4teachers'), [
        'type' => 'button',
        'id' => 'ai4t-copy',
        'class' => 'btn btn-secondary'
    ]);
    echo html_writer::tag('button', get_string('form:download', 'block_ai4teachers'), [
        'type' => 'button',
        'id' => 'ai4t-download',
        'class' => 'btn btn-secondary',
        'style' => 'margin-left:8px;'
    ]);
    echo html_writer::tag('a', get_string('form:reset', 'block_ai4teachers'), [
        'href' => new moodle_url('/blocks/ai4teachers/view.php', ['courseid' => $course->id, 'reset' => 1]),
        'class' => 'btn btn-link',
        'style' => 'margin-left:8px;'
    ]);
    echo html_writer::tag('span', '', [
        'id' => 'ai4t-copied',
        'style' => 'margin-left:8px; display:none;'
    ]);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    // Inline JS for copy and download.
    $courseslug = preg_replace('/[^a-z0-9]+/i', '-', core_text::strtolower(format_string($course->shortname ?: $course->fullname)));
    $filename = $courseslug . '-ai-prompt.txt';
    $copyjs = "(function(){\n" .
        "var btn=document.getElementById('ai4t-copy');\n" .
        "var dl=document.getElementById('ai4t-download');\n" .
        "var ta=document.getElementById('ai4t-generated');\n" .
        "var ok=document.getElementById('ai4t-copied');\n" .
        "if(btn){btn.addEventListener('click',function(){\n" .
        "  ta.select(); ta.setSelectionRange(0, 99999);\n" .
        "  try{\n" .
        "    if(navigator.clipboard && navigator.clipboard.writeText){navigator.clipboard.writeText(ta.value);}else{document.execCommand('copy');}\n" .
        "    ok.textContent='" . addslashes(get_string('form:copied', 'block_ai4teachers')) . "';\n" .
        "    ok.style.display='inline'; setTimeout(function(){ ok.style.display='none'; }, 1500);\n" .
        "  }catch(e){}\n" .
        "});}\n" .
        "if(dl){dl.addEventListener('click',function(){\n" .
        "  var blob=new Blob([ta.value||''],{type:'text/plain'});\n" .
        "  var a=document.createElement('a');\n" .
        "  a.href=URL.createObjectURL(blob);\n" .
        "  a.download='" . addslashes($filename) . "';\n" .
        "  document.body.appendChild(a); a.click(); setTimeout(function(){URL.revokeObjectURL(a.href); a.remove();},0);\n" .
        "});}\n" .
        "})();";
    $PAGE->requires->js_amd_inline($copyjs);
}

// Back to course button/link
$backurl = new moodle_url('/course/view.php', ['id' => $course->id]);
echo html_writer::div(
    html_writer::link($backurl, get_string('form:backtocourse', 'block_ai4teachers'), ['class' => 'btn btn-secondary mt-3']),
    'mt-3'
);

echo $OUTPUT->footer();
