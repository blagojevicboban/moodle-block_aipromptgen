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
 * English language strings for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @category   string
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['aipromptgen:addinstance'] = 'Add a new AI Prompt Generator block';
$string['aipromptgen:manage'] = 'Use the AI Prompt Generator block in a course';
$string['aipromptgen:myaddinstance'] = 'Add the AI Prompt Generator block to the Dashboard';
$string['classtype:assessment'] = 'Assessment/Test';
$string['classtype:discussion'] = 'Discussion';
$string['classtype:groupwork'] = 'Group work';
$string['classtype:lab'] = 'Lab/Practical';
$string['classtype:lecture'] = 'Lecture';
$string['classtype:project'] = 'Project-based';
$string['classtype:review'] = 'Review/Revision';
$string['form:agerangelabel'] = 'Student age/grade';
$string['form:audience'] = 'Audience (teacher-facing or student-facing)';
$string['form:audiencebrowse'] = 'Browse audiences';
$string['form:backtocourse'] = 'Back to course';
$string['form:class_typelabel'] = 'Class type';
$string['form:classtypebrowse'] = 'Browse class types';
$string['form:copied'] = 'Copied!';
$string['form:copy'] = 'Copy to clipboard';
$string['form:course'] = 'Course';
$string['form:download'] = 'Download .txt';
$string['form:language'] = 'Prompt language';
$string['form:languagebrowse'] = 'Browse languages';
$string['form:lessonbrowse'] = 'Browse…';
$string['form:lessoncount'] = 'Number of classes';
$string['form:lessonduration'] = 'Lesson duration (minutes)';
$string['form:lessonlabel'] = 'Lesson title';
$string['form:outcomesbrowse'] = 'Browse competencies/outcomes';
$string['form:outcomeslabel'] = 'Outcomes / objectives';
$string['form:provider'] = 'Provider';
$string['form:purpose'] = 'Prompt purpose (e.g., lesson plan, quiz, rubric)';
$string['form:reset'] = 'Clear saved prompt';
$string['form:response'] = 'AI response';
$string['form:result'] = 'Generated AI prompt';
$string['form:sendtoai'] = 'Send to AI';
$string['form:sendtochatgpt'] = 'Send to ChatGPT';
$string['form:subjectlabel'] = 'Subject';
$string['form:submit'] = 'Generate prompt';
$string['form:topicbrowse'] = 'Browse course sections';
$string['form:topiclabel'] = 'Teaching topic (area)';
$string['help:agerange'] = 'Type an age or grade, or click Browse to pick exact age or range';
$string['help:audience'] = 'Type an audience or click Browse to pick';
$string['help:classtype'] = 'Type a class type or click Browse to pick from a list';
$string['help:language'] = 'Type a language or click Browse to pick from installed languages';
$string['help:lesson'] = 'Type a lesson title or click Browse to pick a section/activity';
$string['help:lessonbrowse'] = 'Browse sections and activities';
$string['help:outcomes'] = 'List outcomes/objectives (one or more)';
$string['help:outcomesbrowse'] = 'Browse competencies/outcomes';
$string['help:purpose'] = 'Type a purpose or click Browse to pick from a list';
$string['help:subjectchange'] = 'Change the subject name if necessary';
$string['help:topic'] = 'Type a topic or click Browse to pick from course sections';
$string['label:agerange'] = 'Student age/grade';
$string['label:audience'] = 'Audience';
$string['label:classtype'] = 'Class type';
$string['label:language'] = 'Language';
$string['label:lesson'] = 'Lesson title';
$string['label:lessoncount'] = 'Number of classes';
$string['label:lessonduration'] = 'Lesson duration';
$string['label:outcomes'] = 'Outcomes';
$string['label:purpose'] = 'Purpose';
$string['label:subject'] = 'Subject';
$string['label:topic'] = 'Teaching topic (area)';
$string['lang:en'] = 'English';
$string['lang:pt'] = 'Portuguese';
$string['lang:sk'] = 'Slovak';
$string['lang:sr'] = 'Serbian';
$string['lang:sr_cr'] = 'Serbian (Cyrillic)';
$string['notallowed'] = 'This tool is only available to teachers and managers in this course.';
$string['openpromptbuilder'] = 'Open AI Prompt Builder';
$string['option:lessonplan'] = 'Lesson plan';
$string['option:quiz'] = 'Quiz questions';
$string['option:rubric'] = 'Assessment rubric';
$string['option:student'] = 'Student-facing';
$string['option:teacher'] = 'Teacher-facing';
$string['option:worksheet'] = 'Worksheet / activities';
$string['placeholder:agerange'] = 'e.g., 15 or 10–12';
$string['pluginname'] = 'AI tools for teachers - prompt generator';
$string['privacy:metadata'] = 'The AI Prompt Generator block does not store personal data.';
$string['prompt:instructions'] = 'Generate the output fully in the specified language. Ensure that the content is appropriate for the given student age/grade, matches the local curriculum when applicable, and directly supports the stated objectives. Structure the output clearly, using headings, subheadings, and bullet points where appropriate.';
$string['prompt:prefix'] = 'You are an expert instructional designer helping a teacher in the Moodle course {$a->course}.';
$string['setting:apikey'] = 'OpenAI API key';
$string['setting:apikey_desc'] = 'API key for OpenAI. Stored in Moodle configuration.';
$string['setting:model'] = 'OpenAI model';
$string['setting:model_desc'] = 'Chat completion model to use when sending the prompt to ChatGPT.';
$string['setting:ollama_endpoint'] = 'Ollama endpoint';
$string['setting:ollama_endpoint_desc'] = 'Base URL of the local Ollama server (e.g. http://localhost:11434).';
$string['setting:ollama_model'] = 'Ollama model';
$string['setting:ollama_model_desc'] = 'Local model name loaded in Ollama (e.g. llama3, llama3.2, mistral, codellama, phi3:mini).';
$string['setting:ollama_num_predict'] = 'Ollama max tokens (num_predict)';
$string['setting:ollama_num_predict_desc'] = 'Maximum tokens to generate (num_predict option). Lower for faster responses.';
$string['setting:ollama_schema'] = 'Ollama structured output schema';
$string['setting:ollama_schema_desc'] = 'Optional JSON Schema to constrain Ollama responses (leave empty for free-form text).';
$string['setting:ollama_timeout'] = 'Ollama request timeout (seconds)';
$string['setting:ollama_timeout_desc'] = 'Maximum time to wait for Ollama response. Increase for large outputs.';
$string['tooltip:provider_not_configured'] = 'No AI provider is configured.';
