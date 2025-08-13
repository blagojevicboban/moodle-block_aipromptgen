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

namespace block_aipromptgen\form;

defined('MOODLE_INTERNAL') || die();

// Ensure $CFG is in scope before using it in a namespaced file.
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Prompt builder form for the AI for Teachers block.
 *
 * @package    block_aipromptgen
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class prompt_form extends \moodleform {
    /**
     * Define the form fields and defaults.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
    $subjectdefault = $this->_customdata['subjectdefault'] ?? '';

    $mform->addElement('text', 'subject', get_string('form:subjectlabel', 'block_aipromptgen'));
        $mform->setType('subject', PARAM_TEXT);
        // Set a default only if provided and not empty.
        if (is_string($subjectdefault)) {
            $subjectdefault = trim($subjectdefault);
            if ($subjectdefault !== '') {
                $mform->setDefault('subject', $subjectdefault);
            }
        }
        // Tooltip as requested (non-localized text per requirement).
        $mform->getElement('subject')->setAttributes([
            'title' => 'Change the subject name if necessary',
        ]);
    // Make subject required (server-side; client-side optional to add later if needed).
    //$mform->addRule('subject', get_string('required'), 'required');

    $mform->addElement('text', 'agerange', get_string('form:agerangelabel', 'block_aipromptgen'));
        $mform->setType('agerange', PARAM_TEXT);
        $mform->getElement('agerange')->setAttributes([
            'title' => 'Enter the student age or grade level',
        ]);

        // Topic (editable text with suggestions + a Browse button that opens a modal picker).
    $topics = $this->_customdata['topics'] ?? [];
        $topicelems = [];
        // Use empty string for label instead of null to avoid strrpos() deprecation inside QuickForm.
        $topicelems[] = $mform->createElement('text', 'topic', '', [
            'size' => 60,
            'list' => 'ai4t-topiclist',
            'title' => 'Type a topic or click Browse to pick from course sections',
        ]);
    $topicelems[] = $mform->createElement('button', 'topicbrowse', get_string('form:topicbrowse', 'block_aipromptgen'), [
            'type' => 'button',
            'id' => 'ai4t-topic-browse',
            'class' => 'btn btn-secondary btn-sm',
            'title' => 'Browse course sections',
        ]);
    $mform->addGroup($topicelems, 'topicgroup', get_string('form:topiclabel', 'block_aipromptgen'), ' ', false);
        $mform->setType('topic', PARAM_TEXT);
    // Make topic required (element is inside a group, so use group rule to avoid QuickForm errors).
    $grouprules = [];
    $grouprules['topic'][] = [get_string('required'), 'required'];
    $mform->addGroupRule('topicgroup', $grouprules);
        // Attach HTML5 datalist for suggestions while allowing free text.
        if (!empty($topics) && is_array($topics)) {
            $optionshtml = '';
            foreach ($topics as $t) {
                $optionshtml .= \html_writer::tag('option', s($t));
            }
            $mform->addElement('html', \html_writer::tag('datalist', $optionshtml, ['id' => 'ai4t-topiclist']));
        }

        // Lesson title: keep as textbox, with a Browse button to open a modal picker.
        $lessonelems = [];
        // Use empty string for label instead of null to avoid strrpos() deprecation inside QuickForm.
        $lessonelems[] = $mform->createElement('text', 'lesson', '', [
            'size' => 60,
            'title' => 'Type a lesson title or click Browse to pick a section/activity',
        ]);
    $lessonelems[] = $mform->createElement('button', 'lessonbrowse', get_string('form:lessonbrowse', 'block_aipromptgen'), [
            'type' => 'button',
            'id' => 'ai4t-lesson-browse',
            'class' => 'btn btn-secondary btn-sm',
            'title' => 'Browse sections and activities',
        ]);
    $mform->addGroup($lessonelems, 'lessongroup', get_string('form:lessonlabel', 'block_aipromptgen'), ' ', false);
        $mform->setType('lesson', PARAM_TEXT);

        // Class type as a dropdown (localized via strings).
        $classtypeoptions = [
            'lecture' => get_string('classtype:lecture', 'block_aipromptgen'),
            'discussion' => get_string('classtype:discussion', 'block_aipromptgen'),
            'groupwork' => get_string('classtype:groupwork', 'block_aipromptgen'),
            'lab' => get_string('classtype:lab', 'block_aipromptgen'),
            'project' => get_string('classtype:project', 'block_aipromptgen'),
            'review' => get_string('classtype:review', 'block_aipromptgen'),
            'assessment' => get_string('classtype:assessment', 'block_aipromptgen'),
        ];
        $mform->addElement('select', 'classtype', get_string('form:class_typelabel', 'block_aipromptgen'), $classtypeoptions);
        $mform->setType('classtype', PARAM_ALPHANUMEXT);
        $mform->getElement('classtype')->setAttributes([
            'title' => 'Select the class type',
        ]);

        // Outcomes textarea with a Browse button to pick competencies.
        $outcomeselems = [];
        $outcomeselems[] = $mform->createElement('textarea', 'outcomes', '', [
            'wrap' => 'virtual', 'rows' => 6, 'cols' => 60,
            'title' => 'List outcomes/objectives (one or more)',
        ]);
        $outcomeselems[] = $mform->createElement('button', 'outcomesbrowse', get_string('form:outcomesbrowse', 'block_aipromptgen'), [
            'type' => 'button',
            'id' => 'ai4t-outcomes-browse',
            'class' => 'btn btn-secondary btn-sm',
            'title' => 'Browse course competencies',
        ]);
        $mform->addGroup($outcomeselems, 'outcomesgroup', get_string('form:outcomeslabel', 'block_aipromptgen'), ' ', false);
        $mform->setType('outcomes', PARAM_TEXT);

        // Language dropdown populated from installed Moodle languages.
        $sm = get_string_manager();
        // Start from all languages supported by Moodle core.
        $langoptions = $sm->get_list_of_languages(); // code => English name
        // If some languages are installed, prefer their localized names.
        $installed = $sm->get_list_of_translations(); // code => localized name (installed only)
        if (!empty($installed)) {
            foreach ($installed as $code => $localized) {
                if (isset($langoptions[$code]) && is_string($localized) && $localized !== '') {
                    $langoptions[$code] = $localized;
                }
            }
        }
        $mform->addElement('select', 'language', get_string('form:language', 'block_aipromptgen'), $langoptions);
        // Allow language codes with underscores/dashes (e.g., sr_cr).
        $mform->setType('language', PARAM_ALPHANUMEXT);
        $mform->getElement('language')->setAttributes([
            'title' => 'Choose the language for the generated prompt',
        ]);
        // Default to current UI language when available; else fallback to a close match or English.
        $curlang = current_language();
        $defaultcode = $curlang;
        if (in_array($curlang, ['sr_cyrl', 'sr@cyrl'])) {
            $defaultcode = 'sr_cr';
        }
        if (!array_key_exists($defaultcode, $langoptions)) {
            $short = substr($curlang, 0, 2);
            $defaultcode = array_key_exists($short, $langoptions) ? $short : 'en';
        }
        $mform->setDefault('language', $defaultcode);

        $mform->addElement('select', 'purpose', get_string('form:purpose', 'block_aipromptgen'), [
            'lessonplan' => get_string('option:lessonplan', 'block_aipromptgen'),
            'quiz' => get_string('option:quiz', 'block_aipromptgen'),
            'rubric' => get_string('option:rubric', 'block_aipromptgen'),
            'worksheet' => get_string('option:worksheet', 'block_aipromptgen'),
        ]);
    $mform->setType('purpose', PARAM_ALPHANUMEXT);
        $mform->getElement('purpose')->setAttributes([
            'title' => 'Select the purpose (e.g., lesson plan, quiz, rubric)',
        ]);

        $mform->addElement('select', 'audience', get_string('form:audience', 'block_aipromptgen'), [
            'student' => get_string('option:student', 'block_aipromptgen'),
            'teacher' => get_string('option:teacher', 'block_aipromptgen'),
        ]);
    $mform->setType('audience', PARAM_ALPHANUMEXT);
        $mform->getElement('audience')->setAttributes([
            'title' => 'Who will read the output (teacher or student)',
        ]);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

    $this->add_action_buttons(true, get_string('form:submit', 'block_aipromptgen'));

    // No inline script here; handled on the page to open a modal and populate the textbox.
    }
}
