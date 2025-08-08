<?php
// This file is part of Moodle - http://moodle.org/

namespace block_ai4teachers\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class prompt_form extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'subject', get_string('form:subjectlabel', 'block_ai4teachers'));
        $mform->setType('subject', PARAM_TEXT);

        $mform->addElement('text', 'agerange', get_string('form:agerangelabel', 'block_ai4teachers'));
        $mform->setType('agerange', PARAM_TEXT);

        $mform->addElement('text', 'lesson', get_string('form:lessonlabel', 'block_ai4teachers'));
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

        $mform->addElement('textarea', 'outcomes', get_string('form:outcomeslabel', 'block_ai4teachers'), 'wrap="virtual" rows="6" cols="60"');
        $mform->setType('outcomes', PARAM_TEXT);

        // Language as a dropdown with preset values and default to current language.
        $langoptions = [
            'sr' => get_string('lang:sr', 'block_ai4teachers'),
            'en' => get_string('lang:en', 'block_ai4teachers'),
            'pt' => get_string('lang:pt', 'block_ai4teachers'),
            'sk' => get_string('lang:sk', 'block_ai4teachers'),
        ];
        $mform->addElement('select', 'language', get_string('form:language', 'block_ai4teachers'), $langoptions);
        $mform->setType('language', PARAM_ALPHA);
        $curlang = current_language();
        $defaultcode = substr($curlang, 0, 2);
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
    }
}
