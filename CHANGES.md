Changes
=======

2.0.0 (2026-11-01)
------------------
* Security: The streaming endpoint accepts only POST requests with a valid sesskey (before, any link could start a paid AI request).
* Security: Links and images in AI responses accept only http(s) and mailto URLs, and quotes are escaped (XSS).
* Security: The Gemini API key is sent in the x-goog-api-key header instead of the URL.
* Security: Moodle curl security applies to the cloud providers; only the admin-configured Ollama and Custom endpoints bypass it.
* Security: The rate limit is counted per user in the Moodle application cache, and also applies without streaming.
* Feature: OpenAI responses stream like the other providers; DeepSeek and Custom also work without streaming.
* Feature: One AI client for all providers (same as tiny_aipromptgen 2.0.0); streaming and non-streaming share the settings.
* Feature: Current default models (gpt-5-mini, gemini-3.8-flash, claude-opus-5-5); no temperature for Claude and GPT-5/o-series;
  at least 16000 output tokens for reasoning models; Claude thinking is not shown.
* Feature: Privacy provider declares the external AI services that receive prompts.
* Fix: Missing error strings (for example a missing API key showed [[error_noapikey]]).
* Fix: Ollama and Custom on local network endpoints were blocked without streaming; Gemini streaming broke on curly brackets;
  API errors showed an empty response.
* Change: Provider labels, purposes, audiences, class types, language names and default templates are language strings;
  Serbian (sr_lt, sr_cr) translations are complete; the duplicate lang/sr folder was removed.
* Change: Requires Moodle 4.1; supported 4.1 to 5.3; CI runs each Moodle branch with a PHP version it supports.
* Change: GPL headers in all JavaScript files; editor and backup files removed from the repository.

1.7.0 (2026-03-05)
------------------
* Feature: Streaming Endpoint Refactor - Switched to POST-based fetch with ReadableStream for better reliability and to avoid URL length limits.
* Feature: Real-time Streaming Status - Added status indicators (Connecting, Receiving, Done) in the AI response modal.
* Feature: Added support for Gemini, Claude, DeepSeek, and Custom OpenAI-compatible endpoints.
* Fix: Permission issues resolved for all plugin files.
* Fix: ESLint and AMD build compliance.

1.6.0 (2026-03-04)
------------------
* Feature: Added support for Gemini API (Google).
* Feature: Added support for Claude API (Anthropic).

1.5.0 (2026-01-31)
----------------__
* Feature: Dynamic Prompt Generation - prompt updates in real-time as you type, removing the need for a "Generate" button.
* Refactor: Removed "Insert into Editor" functionality to simplify the block and decouple it from specific editors.
* Fix: Improved Ollama streaming stability with timeout handling and immediate availability of response actions.
* Fix: Removed manual "Generate prompt" button for a smoother workflow.

1.4 (2026-01-31)
----------------
* Fix: Resolved PHP TypeError (htmlspecialchars) when rendering course competencies in Mustache templates.
* Fix: Achieved full ESLint compliance for all Javascript modules (actions, ui, markdown, stream, etc.).
* Refactor: Split complex logic in `markdown.js` and `ui.js` into smaller, maintainable helper functions.
* Clean: Removed trailing whitespace and addressed all pending linting warnings.

1.3 (2026-01-31)
----------------
* Feature: Refined AI Response Modal with 4 view modes: RAW, TEXT, HTML (Rich preview), and HTML CODE (source).
* Feature: Smart "Copy to Clipboard" with support for copying rich text.
* Feature: Improved UI feedback with copy status indicators.
* Feature: Enhanced Markdown rendering compatibility.
* Fix: Improved auto-formatting for streamed AI responses.
* Fix: UI layout refinements for better modal visibility.

1.2 (2025-12-06)
----------------
* Feature: Added RAW, TEXT, and HTML view modes for AI response improved accessibility and usage.
* Feature: Added "Copy" buttons that respects current view mode.
* Feature: Client-side Markdown rendering with auto-fix for clumped text from streams.
* Fix: Ollama connection now supports local/private IP addresses by bypassing SSL verification for local subnets.
* Fix: Reduced modal height to prevent buttons from being pushed off-screen.
* Fix: Improved TEXT view cleanup (removed stray markdown artifacts).

1.0 (2025-12-04)
----------------
* Stable release.
* Added unified "Send to AI" workflow.
* Added Ollama provider support.
* Refined prompt builder UI.

0.3.0 (2025-08-31)
------------------
* Unified "Send to AI" button with provider select.
* Added Ollama endpoint & model settings.

0.2.0 (2025-08-25)
------------------
* Initial OpenAI ChatGPT send support.
* Prompt builder refinements.

0.1.0
-----
* First public prototype (prompt builder only).
