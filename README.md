# AI for Teachers (Moodle block)

A minimal block plugin that provides an AI prompt builder for teachers on a per-course basis. Students cannot access it.

Features
- Course block visible only to roles with the capability block/ai4teachers:manage (editing teachers and managers by default).
- Opens a form where a teacher selects/enters: subject, student age/grade, lesson title, class type, outcomes, language, prompt purpose, and audience.
- Generates a well-structured prompt string that the teacher can copy into their preferred AI tool.

Install
1. Place this folder under Moodle at blocks/ai4teachers
2. Visit Site administration → Notifications to complete installation.

Usage
- Add the "AI for Teachers" block to a course page.
- Click "Open AI Prompt Builder".
- Fill in the form and copy the generated prompt.

Roadmap
- Save presets per course and user.
- Integrate with external LLM providers (via API key in plugin settings) to preview outputs.
- Add Serbian (Cyrillic) pack and more languages.
- Add templates for different subjects and local curricula.
