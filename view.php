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
    'templates' => helper::get_templates(),
];
$mform = new \block_aipromptgen\form\prompt_form(null, $customdata);
$mform->set_data(['courseid' => $courseid]);

// Handle Form Submission.
$generatedprompt = '';
$hasgenerated = true; // Always show the result section for live updates.
$airesponse = '';

// Check if this is a "Send to AI" request (manual form submission).
$rawprompt = optional_param('ai4t_generated', '', PARAM_RAW);
$provider = optional_param('sendto', '', PARAM_TEXT);

$client = new ai_client();
if (!empty($provider) && !empty($rawprompt) && confirm_sesskey()) {
    $generatedprompt = $rawprompt;
    if (in_array($provider, ai_client::PROVIDERS)) {
        if (!helper::check_rate_limit()) {
            $airesponse = get_string('error_ratelimit', 'block_aipromptgen');
        } else {
            $airesponse = $client->send_request($provider, $generatedprompt);
        }
    }
} else if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
}

// Provider options for the "Send to AI" menu.
$providernames = [
    'openai' => 'OpenAI',
    'gemini' => 'Gemini',
    'claude' => 'Claude',
    'deepseek' => 'DeepSeek',
    'custom' => get_string('provider:custom', 'block_aipromptgen'),
    'ollama' => 'Ollama',
];
$provideroptions = [];
$providersavailable = false;
foreach (ai_client::PROVIDERS as $name) {
    $configured = $client->is_configured($name);
    $providersavailable = $providersavailable || $configured;
    $provideroptions[] = [
        'value' => $name,
        'label' => $configured ? $providernames[$name] :
            get_string('provider:notconfigured', 'block_aipromptgen', $providernames[$name]),
        'selected' => $name === 'openai',
    ];
}

// Language list: the English name goes into the prompt, the label is shown in the user's language.
$flags = [
    'en' => '🇺🇸', 'es' => '🇪🇸', 'fr' => '🇫🇷', 'de' => '🇩🇪', 'it' => '🇮🇹', 'pt' => '🇵🇹', 'nl' => '🇳🇱', 'pl' => '🇵🇱',
    'ru' => '🇷🇺', 'ja' => '🇯🇵', 'zh' => '🇨🇳', 'sr_lt' => '🇷🇸', 'sr_cr' => '🇷🇸', 'hr' => '🇭🇷', 'bs' => '🇧🇦', 'sk' => '🇸🇰',
];
$languages = [];
foreach ($flags as $code => $flag) {
    $languages[] = [
        'code' => $code,
        'name' => get_string_manager()->get_string('lang:' . $code, 'block_aipromptgen', null, 'en'),
        'label' => get_string('lang:' . $code, 'block_aipromptgen') . ' ' . $flag,
    ];
}

$liststrings = function (array $keys): array {
    return array_map(function ($key) {
        return get_string($key, 'block_aipromptgen');
    }, $keys);
};


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
    'templates' => helper::get_templates(),

    // Lists for the modals.
    'languages' => $languages,
    'purposes' => $liststrings(['purpose:lessonplan', 'purpose:syllabus', 'purpose:quiz', 'purpose:activity',
        'purpose:project', 'purpose:rubric', 'purpose:explanation', 'purpose:summary']),
    'audiences' => $liststrings(['audience:beginners', 'audience:intermediate', 'audience:advanced', 'audience:mixed',
        'audience:specialneeds', 'audience:professional']),
    'classtypes' => $liststrings(['classtype:lecture', 'classtype:workshop', 'classtype:seminar', 'classtype:lab',
        'classtype:online', 'classtype:blended', 'classtype:fieldtrip']),

    // AI provider options.
    'provideroptions' => $provideroptions,
    'providersavailable' => $providersavailable,

    'airesponse_initial' => $airesponse,
];

echo $OUTPUT->render_from_template('block_aipromptgen/prompt_page', $tmpldata);

echo $OUTPUT->footer();
