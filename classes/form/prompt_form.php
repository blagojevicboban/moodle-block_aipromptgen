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

namespace block_ai4teachers\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Prompt builder form for the AI for Teachers block.
 *
 * @package    block_ai4teachers
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

        $mform->addElement('text', 'subject', get_string('form:subjectlabel', 'block_ai4teachers'));
        $mform->setType('subject', PARAM_TEXT);
        // Tooltip as requested (non-localized text per requirement).
        $mform->getElement('subject')->setAttributes([
            'title' => 'Promenite naziv predmeta ukoliko je potrebno',
        ]);
        // Make subject required (client-side and server-side validation).
        $mform->addRule('subject', null, 'required', null, 'client');
        $mform->addRule('subject', get_string('required'), 'required');

        $mform->addElement('text', 'agerange', get_string('form:agerangelabel', 'block_ai4teachers'));
        $mform->setType('agerange', PARAM_TEXT);

    // Topic (editable text with suggestions from current course via datalist).
        $topics = $this->_customdata['topics'] ?? [];
        $mform->addElement('text', 'topic', get_string('form:topiclabel', 'block_ai4teachers'));
        $mform->setType('topic', PARAM_TEXT);
        // Attach HTML5 datalist for suggestions while allowing free text.
        $mform->getElement('topic')->setAttributes(['list' => 'ai4t-topiclist']);
    // Make topic required.
    $mform->addRule('topic', null, 'required', null, 'client');
    $mform->addRule('topic', get_string('required'), 'required');
        if (!empty($topics) && is_array($topics)) {
            $optionshtml = '';
            foreach ($topics as $t) {
                $optionshtml .= \html_writer::tag('option', s($t));
            }
            $mform->addElement('html', \html_writer::tag('datalist', $optionshtml, ['id' => 'ai4t-topiclist']));
        }

        // Lesson title: keep as textbox, with a Browse button to open a modal picker.
        $lessonelems = [];
        $lessonelems[] = $mform->createElement('text', 'lesson', null, ['size' => 60]);
        $lessonelems[] = $mform->createElement('button', 'lessonbrowse', get_string('form:lessonbrowse', 'block_ai4teachers'), [
            'type' => 'button',
            'id' => 'ai4t-lesson-browse',
            'class' => 'btn btn-secondary',
        ]);
        $mform->addGroup($lessonelems, 'lessongroup', get_string('form:lessonlabel', 'block_ai4teachers'), ' ', false);
        $mform->setType('lesson', PARAM_TEXT);

        // Class type as a dropdown (localized via strings).
        $classtypeoptions = [
            'lecture' => get_string('classtype:lecture', 'block_ai4teachers'),
            'discussion' => get_string('classtype:discussion', 'block_ai4teachers'),
            'groupwork' => get_string('classtype:groupwork', 'block_ai4teachers'),
            'lab' => get_string('classtype:lab', 'block_ai4teachers'),
            'project' => get_string('classtype:project', 'block_ai4teachers'),
            'review' => get_string('classtype:review', 'block_ai4teachers'),
            'assessment' => get_string('classtype:assessment', 'block_ai4teachers'),
        ];
        $mform->addElement('select', 'classtype', get_string('form:class_typelabel', 'block_ai4teachers'), $classtypeoptions);
        $mform->setType('classtype', PARAM_ALPHANUMEXT);

        $mform->addElement('textarea', 'outcomes', get_string('form:outcomeslabel', 'block_ai4teachers'), [
            'wrap' => 'virtual', 'rows' => 6, 'cols' => 60,
        ]);
        $mform->setType('outcomes', PARAM_TEXT);

        // Language as a dropdown with preset values and default to current language.
        $langoptions = [
            'sr' => get_string('lang:sr', 'block_ai4teachers'),
            'sr_cr' => get_string('lang:sr_cr', 'block_ai4teachers'),
            'en' => get_string('lang:en', 'block_ai4teachers'),
            'pt' => get_string('lang:pt', 'block_ai4teachers'),
            'sk' => get_string('lang:sk', 'block_ai4teachers'),
        ];
        $mform->addElement('select', 'language', get_string('form:language', 'block_ai4teachers'), $langoptions);
        // Allow language codes with underscore (e.g., sr_cr).
        $mform->setType('language', PARAM_ALPHANUMEXT);
        $curlang = current_language();
        $defaultcode = substr($curlang, 0, 2);
        // If UI is in Serbian Cyrillic, default to sr_cr for prompt language.
        if (in_array($curlang, ['sr_cr', 'sr_cyrl', 'sr@cyrl'])) {
            $defaultcode = 'sr_cr';
        }
        if (!array_key_exists($defaultcode, $langoptions)) {
            $defaultcode = 'en';
        }
        $mform->setDefault('language', $defaultcode);

        $mform->addElement('select', 'purpose', get_string('form:purpose', 'block_ai4teachers'), [
            'lessonplan' => get_string('option:lessonplan', 'block_ai4teachers'),
            'quiz' => get_string('option:quiz', 'block_ai4teachers'),
            'rubric' => get_string('option:rubric', 'block_ai4teachers'),
            'worksheet' => get_string('option:worksheet', 'block_ai4teachers'),
        ]);

        $mform->addElement('select', 'audience', get_string('form:audience', 'block_ai4teachers'), [
            'teacher' => get_string('option:teacher', 'block_ai4teachers'),
            'student' => get_string('option:student', 'block_ai4teachers'),
        ]);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons(true, get_string('form:submit', 'block_ai4teachers'));

    // No inline script here; handled on the page to open a modal and populate the textbox.
    }
}
