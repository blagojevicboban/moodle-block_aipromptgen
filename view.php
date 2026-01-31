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
 * Main view page for the AI Prompt Generator block.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/blocks/aipromptgen/classes/form/prompt_form.php');

use block_aipromptgen\helper;
use block_aipromptgen\ai_client;

// Get the course ID from the URL or fallback to the site page.
$courseid = optional_param('courseid', SITEID, PARAM_INT);

// Login and capability checks.
require_login($courseid);
$context = context_course::instance($courseid);
require_capability('block/aipromptgen:manage', $context);

// Page setup.
$PAGE->set_url(new moodle_url('/blocks/aipromptgen/view.php', ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'block_aipromptgen'));
$PAGE->set_heading(get_string('pluginname', 'block_aipromptgen'));

// Initialize JS.
$PAGE->requires->js_call_amd('block_aipromptgen/ui', 'init');

// Fetch Data.
$course = get_course($courseid);
[$topics, $lessonoptions] = helper::get_course_content($course);
$competencies = helper::get_course_competencies_and_outcomes($courseid);

// Prepare form data.
$customdata = [
    'topics' => $topics,
    'coursename' => $course->fullname,
    // Add other defaults as needed by the form class.
];
$mform = new \block_aipromptgen\form\prompt_form(null, $customdata);
$mform->set_data(['courseid' => $courseid]);

// Handle Form Submission.
$generatedprompt = '';
$hasgenerated = false;
$airesponse = '';

// Check if this is a "Send to AI" request (manual form submission).
$rawprompt = optional_param('ai4t_generated', '', PARAM_RAW);
$provider = optional_param('sendto', '', PARAM_TEXT);

if (!empty($provider) && !empty($rawprompt) && confirm_sesskey()) {
    $generatedprompt = $rawprompt;
    $hasgenerated = true;

    if ($provider === 'openai' || $provider === 'ollama') {
        $client = new ai_client();
        $airesponse = $client->send_request($provider, $generatedprompt);
    }
} else if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} else if ($data = $mform->get_data()) {
    // Generate Prompt String.
    $subject = !empty($data->subject) ? $data->subject : $course->fullname;
    $age = $data->agerange ?? '';
    $topic = $data->topic ?? '';
    $lesson = $data->lesson ?? '';

    // Process lesson count and duration.
    $count = isset($data->lessoncount) ? (int) $data->lessoncount : 1;
    $duration = isset($data->lessonduration) ? (int) $data->lessonduration : 45;

    $outcomes = $data->outcomes ?? '';
    $langname = $data->language ?? 'English'; // Default.
    $purpose = $data->purpose ?? '';
    $audience = $data->audience ?? '';
    $classtype = $data->classtype ?? '';

    // Construct the prompts.

    $p = "You are an expert teacher. Create a detailed lesson plan.\n";
    $p .= "Subject: " . $subject . "\n";
    if ($age !== '') {
        $p .= "Student Age: " . $age . " years old\n";
    }
    if ($topic !== '') {
        $p .= "Topic: " . $topic . "\n";
    }
    if ($lesson !== '') {
        $p .= "Lesson Title: " . $lesson . "\n";
    }
    $p .= "Number of lessons: " . $count . "\n";
    $p .= "Duration per lesson: " . $duration . " minutes\n";
    if ($classtype !== '') {
        $p .= "Class Type: " . $classtype . "\n";
    }
    if ($purpose !== '') {
        $p .= "Purpose: " . $purpose . "\n";
    }
    if ($audience !== '') {
        $p .= "Target Audience: " . $audience . "\n";
    }
    if ($outcomes !== '') {
        $p .= "Learning Outcomes/Competencies:\n" . $outcomes . "\n";
    }
    $p .= "Language: " . $langname . "\n";

    $p .= "\nPlease provide a structured lesson plan with objectives, activities, and timeline.";

    $generatedprompt = $p;
    $hasgenerated = true;

    // No auto-send here; user must click "Send to AI".
}

// Render Page.
echo $OUTPUT->header();

// 1. Render Form.
$mform->display();

// 2. Render Template for Results and Modals.
$tmpldata = [
    'courseid' => $courseid,
    'sesskey' => sesskey(),
    'hasgenerated' => $hasgenerated,
    'generatedprompt' => $generatedprompt,
    'backurl' => (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false),

    // Modal Data.
    'topics' => $topics,
    'lessonoptions' => $lessonoptions,
    'competencies' => $competencies,

    // Static lists for modals.
    'languages' => [
        ['code' => 'en', 'name' => 'English', 'label' => 'English 🇺🇸'],
        ['code' => 'es', 'name' => 'Spanish', 'label' => 'Spanish 🇪🇸'],
        ['code' => 'fr', 'name' => 'French', 'label' => 'French 🇫🇷'],
        ['code' => 'de', 'name' => 'German', 'label' => 'German 🇩🇪'],
        ['code' => 'it', 'name' => 'Italian', 'label' => 'Italian 🇮🇹'],
        ['code' => 'pt', 'name' => 'Portuguese', 'label' => 'Portuguese 🇵🇹'],
        ['code' => 'nl', 'name' => 'Dutch', 'label' => 'Dutch 🇳🇱'],
        ['code' => 'pl', 'name' => 'Polish', 'label' => 'Polish 🇵🇱'],
        ['code' => 'ru', 'name' => 'Russian', 'label' => 'Russian 🇷🇺'],
        ['code' => 'ja', 'name' => 'Japanese', 'label' => 'Japanese 🇯🇵'],
        ['code' => 'zh', 'name' => 'Chinese', 'label' => 'Chinese 🇨🇳'],
        ['code' => 'sr_lt', 'name' => 'Serbian (Latin)', 'label' => 'Serbian (Latin) 🇷🇸'],
        ['code' => 'sr_cr', 'name' => 'Serbian (Cyrillic)', 'label' => 'Serbian (Cyrillic) 🇷🇸'],
        ['code' => 'hr', 'name' => 'Croatian', 'label' => 'Croatian 🇭🇷'],
        ['code' => 'bs', 'name' => 'Bosnian', 'label' => 'Bosnian 🇧🇦'],
    ],
    'purposes' => [
        'Lesson Plan',
        'Syllabus',
        'Quiz/Assessment',
        'Activity Design',
        'Project Outline',
        'Rubric',
        'Explanation',
        'Summary',
    ],
    'audiences' => [
        'Beginners',
        'Intermediate',
        'Advanced',
        'Mixed Ability',
        'Special Needs',
        'Professional',
    ],
    'classtypes' => [
        'Lecture',
        'Workshop',
        'Seminar',
        'Lab',
        'Online/Virtual',
        'Blended',
        'Field Trip',
    ],

    // AI Provider Options.
    'provideroptions' => [
        [
            'value' => 'openai',
            'label' => 'OpenAI' . (get_config('block_aipromptgen', 'openai_apikey') ? '' : ' (✕ Not configured)'),
            'selected' => true,
        ],
        [
            'value' => 'ollama',
            'label' => 'Ollama' . (get_config('block_aipromptgen', 'ollama_endpoint') ? '' : ' (✕ Not configured)'),
            'selected' => false,
        ],
    ],
    'providersavailable' => (get_config('block_aipromptgen', 'openai_apikey') ||
        get_config('block_aipromptgen', 'ollama_endpoint')),

    'airesponse_initial' => $airesponse,
];

echo $OUTPUT->render_from_template('block_aipromptgen/prompt_page', $tmpldata);

echo $OUTPUT->footer();
