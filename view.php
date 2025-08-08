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
if ($data = $form->get_data()) {
    // Build a structured prompt based on form inputs.
    $parts = [];
    $parts[] = "Purpose: {$data->purpose}";
    $parts[] = "Audience: {$data->audience}";
    $parts[] = "Language: {$data->language}";
    $parts[] = "Subject: {$data->subject}";
    $parts[] = "Student age/grade: {$data->agerange}";
    $parts[] = "Lesson: {$data->lesson}";
    $parts[] = "Class type: {$data->classtype}";
    if (!empty($data->outcomes)) {
        $parts[] = "Outcomes: " . preg_replace('/\s+/', ' ', trim($data->outcomes));
    }
    $coursename = format_string($course->fullname);
    $prefix = "You are an expert instructional designer helping a teacher in the Moodle course '{$coursename}'. ";
    $instructions = 'Generate content strictly aligned with the purpose and outcomes, at the appropriate level for the specified age/grade. Prefer local curriculum alignment when applicable.';
    $generated = $prefix . "\n" . implode("\n", $parts) . "\n" . $instructions;
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
    echo html_writer::tag('button', get_string('form:copy', 'block_ai4teachers'), [
        'type' => 'button',
        'id' => 'ai4t-copy',
        'class' => 'btn btn-secondary'
    ]);
    echo html_writer::tag('span', '', [
        'id' => 'ai4t-copied',
        'style' => 'margin-left:8px; display:none;'
    ]);
    echo html_writer::end_tag('div');

    // Inline JS for copy-to-clipboard with Moodle's AMD-free minimal approach.
    $copyjs = "(function(){\n" .
        "var btn=document.getElementById('ai4t-copy');\n" .
        "var ta=document.getElementById('ai4t-generated');\n" .
        "var ok=document.getElementById('ai4t-copied');\n" .
        "if(!btn||!ta){return;}\n" .
        "btn.addEventListener('click',function(){\n" .
        "  ta.select(); ta.setSelectionRange(0, 99999);\n" .
        "  try{\n" .
        "    navigator.clipboard && navigator.clipboard.writeText ? navigator.clipboard.writeText(ta.value) : document.execCommand('copy');\n" .
        "    ok.textContent='" . addslashes(get_string('form:copied', 'block_ai4teachers')) . "';\n" .
        "    ok.style.display='inline';\n" .
        "    setTimeout(function(){ ok.style.display='none'; }, 1500);\n" .
        "  }catch(e){}\n" .
        "});\n" .
        "})();";
    $PAGE->requires->js_amd_inline($copyjs);
}

echo $OUTPUT->footer();
