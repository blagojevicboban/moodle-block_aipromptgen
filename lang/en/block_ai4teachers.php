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
 * English language strings for block_ai4teachers.
 *
 * @package    block_ai4teachers
 * @category   string
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'AI for Teachers';
$string['openpromptbuilder'] = 'Open AI Prompt Builder';
$string['notallowed'] = 'This tool is only available to teachers and managers in this course.';
$string['privacy:metadata'] = 'The AI for Teachers block does not store personal data.';

// Capabilities.
$string['ai4teachers:manage'] = 'Use the AI for Teachers block in a course';
$string['ai4teachers:addinstance'] = 'Add a new AI for Teachers block';
$string['ai4teachers:myaddinstance'] = 'Add the AI for Teachers block to the Dashboard';

// Form strings.
$string['form:subjectlabel'] = 'Subject';
$string['form:agerangelabel'] = 'Student age/grade';
$string['form:topiclabel'] = 'Teaching topic (area)';
$string['form:lessonlabel'] = 'Lesson title';
$string['form:class_typelabel'] = 'Class type';
$string['form:outcomeslabel'] = 'Outcomes / objectives';
$string['form:language'] = 'Prompt language';
$string['form:purpose'] = 'Prompt purpose (e.g., lesson plan, quiz, rubric)';
$string['form:course'] = 'Course';
$string['form:submit'] = 'Generate prompt';
$string['form:result'] = 'Generated AI prompt';
$string['form:audience'] = 'Audience (teacher-facing or student-facing)';

// Actions.
$string['form:copy'] = 'Copy to clipboard';
$string['form:copied'] = 'Copied!';
$string['form:download'] = 'Download .txt';
$string['form:reset'] = 'Clear saved prompt';
$string['form:backtocourse'] = 'Back to course';

// Options.
$string['option:lessonplan'] = 'Lesson plan';
$string['option:quiz'] = 'Quiz questions';
$string['option:rubric'] = 'Assessment rubric';
$string['option:worksheet'] = 'Worksheet / activities';
$string['option:teacher'] = 'Teacher-facing';
$string['option:student'] = 'Student-facing';

// Language names.
$string['lang:sr'] = 'Serbian';
$string['lang:en'] = 'English';
$string['lang:pt'] = 'Portuguese';
$string['lang:sk'] = 'Slovak';
$string['lang:sr_cr'] = 'Serbian (Cyrillic)';

// Prompt labels.
$string['label:purpose'] = 'Purpose';
$string['label:audience'] = 'Audience';
$string['label:language'] = 'Language';
$string['label:subject'] = 'Subject';
$string['label:agerange'] = 'Student age/grade';
$string['label:topic'] = 'Teaching topic (area)';
$string['label:lesson'] = 'Lesson title';
$string['label:classtype'] = 'Class type';
$string['label:outcomes'] = 'Outcomes';

// Prompt templates.
$string['prompt:prefix'] = "You are an expert instructional designer helping a teacher in the Moodle course '{$a->course}'.";
$string['prompt:instructions'] = 'Generate the output in the selected language. Align strictly with the purpose and outcomes, at the appropriate level for the specified age/grade. Prefer local curriculum alignment when applicable.';

// Class type options.
$string['classtype:lecture'] = 'Lecture';
$string['classtype:discussion'] = 'Discussion';
$string['classtype:groupwork'] = 'Group work';
$string['classtype:lab'] = 'Lab/Practical';
$string['classtype:project'] = 'Project-based';
$string['classtype:review'] = 'Review/Revision';
$string['classtype:assessment'] = 'Assessment/Test';
