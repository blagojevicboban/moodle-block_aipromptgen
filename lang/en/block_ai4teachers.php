<?php
// English strings for block_ai4teachers

$string['pluginname'] = 'AI for Teachers';
$string['openpromptbuilder'] = 'Open AI Prompt Builder';
$string['notallowed'] = 'This tool is only available to teachers and managers in this course.';
$string['privacy:metadata'] = 'The AI for Teachers block does not store personal data.';

// Capabilities
$string['ai4teachers:manage'] = 'Use the AI for Teachers block in a course';
$string['ai4teachers:addinstance'] = 'Add a new AI for Teachers block';
$string['ai4teachers:myaddinstance'] = 'Add the AI for Teachers block to the Dashboard';

// Form strings
$string['form:subjectlabel'] = 'Subject';
$string['form:agerangelabel'] = 'Student age/grade';
$string['form:lessonlabel'] = 'Lesson title';
$string['form:class_typelabel'] = 'Class type';
$string['form:outcomeslabel'] = 'Outcomes / objectives';
$string['form:language'] = 'Prompt language';
$string['form:purpose'] = 'Prompt purpose (e.g., lesson plan, quiz, rubric)';
$string['form:course'] = 'Course';
$string['form:submit'] = 'Generate prompt';
$string['form:result'] = 'Generated AI prompt';
$string['form:audience'] = 'Audience (teacher-facing or student-facing)';
// Actions
$string['form:copy'] = 'Copy to clipboard';
$string['form:copied'] = 'Copied!';
$string['form:download'] = 'Download .txt';
$string['form:reset'] = 'Clear saved prompt';

// Options
$string['option:lessonplan'] = 'Lesson plan';
$string['option:quiz'] = 'Quiz questions';
$string['option:rubric'] = 'Assessment rubric';
$string['option:worksheet'] = 'Worksheet / activities';
$string['option:teacher'] = 'Teacher-facing';
$string['option:student'] = 'Student-facing';
// Language names
$string['lang:sr'] = 'Serbian';
$string['lang:en'] = 'English';
$string['lang:pt'] = 'Portuguese';
$string['lang:sk'] = 'Slovak';

// Prompt labels
$string['label:purpose'] = 'Purpose';
$string['label:audience'] = 'Audience';
$string['label:language'] = 'Language';
$string['label:subject'] = 'Subject';
$string['label:agerange'] = 'Student age/grade';
$string['label:lesson'] = 'Lesson title';
$string['label:classtype'] = 'Class type';
$string['label:outcomes'] = 'Outcomes';

// Prompt templates
$string['prompt:prefix'] = "You are an expert instructional designer helping a teacher in the Moodle course '{$a->course}'.";
$string['prompt:instructions'] = 'Generate the output in the selected language. Align strictly with the purpose and outcomes, at the appropriate level for the specified age/grade. Prefer local curriculum alignment when applicable.';
