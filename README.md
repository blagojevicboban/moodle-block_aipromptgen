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
- Optional: AI send (unified): Generate prompt then use a single "Send to AI" button with provider select (OpenAI or local Ollama) to fetch an inline response (when configured).

Install
1. Place this folder under Moodle at `blocks/aipromptgen`.
2. Visit Site administration → Notifications to complete installation.
3. (Optional) Enable:
   - Competencies: Site administration → Advanced features → “Competencies”.
   - Outcomes: Site administration → Advanced features → “Enable outcomes”.
   Then link competencies to the course (Course → More → Competencies) and/or define local/global outcomes.
4. (Optional) Configure AI providers (Site administration → Plugins → Blocks → AI for Teachers):
  - OpenAI: API key + model (default `gpt-4o-mini`).
  - Ollama: Local endpoint base URL (e.g. `http://localhost:11434`) + model name (e.g. `llama3`, `mistral`).

Usage
1. Add the “AI for Teachers” block to a course page.
2. Click “Open AI Prompt Builder”.
3. Fill the fields (use Browse buttons for quick selection).
4. Click “Generate prompt” and copy or download the generated text.
5. (Optional) Select a provider and click “Send to AI” to send the generated prompt and view the response inline (requires provider configuration).

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
- AI provider integration:
  - When configured, pressing “Send to AI” sends the generated prompt to the selected provider (OpenAI or Ollama) and displays the AI response on the page. Ensure institutional data/privacy compliance before sending data to external services (OpenAI). Local Ollama requests remain on your server.

Troubleshooting
- “A required parameter (courseid) was missing”:
  - Open the builder from within a course or ensure the action URL includes `courseid`. The page also tries to infer it from `cmid`/`id`/current course.
- Outcomes modal shows “None”:
  - Ensure Competencies and/or Outcomes are enabled.
  - Link competencies to the course or activities; define outcomes if needed.
  - Check role permissions to view competencies.


Roadmap
- Save user presets per course.
- AJAX (non‑blocking) streaming responses.
- Additional localized strings and templates.

Changelog (summary)
- 0.3.0 (2025-08-31): Unified "Send to AI" button with provider select; added Ollama endpoint & model settings; added related language strings.
- 0.2.0 (2025-08-25): Initial OpenAI ChatGPT send support and prompt builder refinements.
- 0.1.0: First public prototype (prompt builder only).
