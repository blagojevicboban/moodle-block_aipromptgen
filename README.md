# AI for Teachers - prompt generator (Moodle block)

A block plugin that provides an AI prompt builder for teachers on a per‑course basis. Students cannot access it.

Compatibility
- Moodle 4.3+ and 5.x (tested with Moodle 5).
- Optional: Competencies (Learning plans) and Gradebook Outcomes.

Features
- Visible only to roles with capability (editing teachers/managers by default).
- Per‑course prompt builder form with tooltips and Browse modals:
  - Subject (prefilled with course name on first load if empty)
  - Student age/grade
  - Teaching topic (area) + Browse (course sections)
  - Lesson title + Browse (sections/activities)
  - Class type: text input + Browse (common class types)
  - Outcomes/objectives: textarea + Browse (course competencies and, if enabled, gradebook outcomes; multi‑select)
  - Prompt language: text input + Browse
  - Prompt purpose: text input + Browse (e.g., lesson plan, quiz, rubric)
  - Audience: text input + Browse (teacher‑facing or student‑facing)
- Generated prompt reflects the selected Prompt language.
- Optional: "Send to ChatGPT" button that submits the generated prompt to OpenAI and displays the response (when configured with an API key).

Install
1. Place this folder under Moodle at `blocks/aipromptgen`.
2. Visit Site administration → Notifications to complete installation.
3. (Optional) Enable:
   - Competencies: Site administration → Advanced features → “Competencies”.
   - Outcomes: Site administration → Advanced features → “Enable outcomes”.
   Then link competencies to the course (Course → More → Competencies) and/or define local/global outcomes.
4. (Optional) Configure OpenAI integration (to enable "Send to ChatGPT"):
  - Site administration → Plugins → Blocks → AI for Teachers
  - Set the OpenAI API key and (optionally) the model (defaults to `gpt-4o-mini`).

Usage
1. Add the “AI for Teachers” block to a course page.
2. Click “Open AI Prompt Builder”.
3. Fill the fields (use Browse buttons for quick selection).
4. Click “Generate prompt” and copy or download the generated text.
5. (Optional) Click “Send to ChatGPT” to send the generated prompt and view the response inline (requires API key configuration).

Permissions
- Capability: `block/aipromptgen:manage` (required to view the block and use the builder).

Notes
- Competencies/Outcomes Browse:
  - If competencies are enabled and linked at course or activity level, they will appear in the modal.
  - If Gradebook Outcomes are enabled, local and global outcomes will be listed too.
- Language field:
  - The visible field accepts a language name (with optional code in parentheses). A hidden `languagecode` is maintained automatically when using Browse.
  - The generated prompt honors the selected language and appends a single normalized code to the displayed name.
- Data cleaning/security:
  - All inputs have explicit `$mform->setType(...)` (e.g., `PARAM_TEXT`, `PARAM_INT`, `PARAM_ALPHANUMEXT` for codes).
  - Language string placeholders use single‑quoted strings in lang files to avoid premature interpolation.
- Age/grade modal:
  - Choose an exact age or a range. The prompt formats this as `15 godina` or `15-16 godina` (localized wording can vary by language pack).
- OpenAI integration:
  - When configured, pressing “Send to ChatGPT” sends the generated prompt to OpenAI and displays the AI response on the page. Ensure you have consent and comply with your institution’s data and privacy policies before sending any data to third‑party services.

Troubleshooting
- “A required parameter (courseid) was missing”:
  - Open the builder from within a course or ensure the action URL includes `courseid`. The page also tries to infer it from `cmid`/`id`/current course.
- Outcomes modal shows “None”:
  - Ensure Competencies and/or Outcomes are enabled.
  - Link competencies to the course or activities; define outcomes if needed.
  - Check role permissions to view competencies.


Roadmap
- Save user presets per course.
- Integrations with external LLM providers (preview outputs).
- Additional localized strings and templates
