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
 * ES6 orchestrator module: aggregates individual feature modules for the AI Prompt Generator block.
 * Split into smaller modules for clarity, testability, and alignment with Moodle JS module guidelines.
 *
 * @module     block_aipromptgen/ui
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {initAgeModal} from 'block_aipromptgen/age';
import {attachPicker, attachOutcomesModal, initLanguageModal} from 'block_aipromptgen/pickers';
import {attachCopyDownload} from 'block_aipromptgen/actions';

// Unified provider send (OpenAI => submit; Ollama => SSE stream) via hidden field.
const initProviderSend = () => {
    const sendBtn = document.getElementById('ai4t-sendtoai');
    const select = document.getElementById('ai4t-provider');
    const gen = document.getElementById('ai4t-generated');
    // Ensure hidden provider field exists (compat with server resolver).
    let hidden = document.getElementById('ai4t-sendto');
    if (!hidden) {
        const form = document.getElementById('promptform');
        if (form) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'sendto';
            hidden.id = 'ai4t-sendto';
            form.appendChild(hidden);
        }
    }
    if (!sendBtn || !select || !gen || !hidden) {
        return;
    }
    const resp = document.getElementById('ai4t-airesponse');
    const findForm = () => document.getElementById('promptform') || document.getElementById('mform1') || sendBtn.closest('form');
    const refreshState = () => {
        const opt = select.options[select.selectedIndex];
        const unconfigured = opt && /✕\s*$/.test(opt.textContent || '');
        sendBtn.disabled = (!gen.value.trim() || unconfigured);
    };
    const scrollToResponse = () => {
        const heading = document.getElementById('ai4t-response-heading');
        if (heading) {
            try {
                heading.scrollIntoView({behavior: 'smooth', block: 'start'});
            } catch (e) {
                heading.scrollIntoView();
            }
        }
    };
    const startStream = () => {
        if (!window.EventSource) { // Fallback: normal submit.
            const form = findForm();
            if (form) {
                form.submit();
            }
            return;
        }
        const courseInput = document.querySelector('input[name=courseid]');
        const courseid = courseInput ? courseInput.value : '';
        hidden.value = 'ollama';
        if (resp) {
            resp.textContent = '';
            resp.setAttribute('aria-busy', 'true');
        }
        const statusId = 'ai-response-status';
        let statusEl = document.getElementById(statusId);
        if (!statusEl && resp) {
            statusEl = document.createElement('div');
            statusEl.id = statusId;
            statusEl.className = 'small text-muted';
            resp.parentNode?.insertBefore(statusEl, resp);
        }
        if (statusEl) {
            statusEl.textContent = 'Streaming...';
        }
        // Use Moodle root (M.cfg.wwwroot) if available for absolute URL.
        const root = (window.M && window.M.cfg && M.cfg.wwwroot) ? M.cfg.wwwroot : '';
        const base = root + '/blocks/aipromptgen/stream.php';
        let prompt = gen.value || gen.textContent || '';
        if (!prompt) {
            // Minimal fallback build.
            const fd = new FormData(findForm() || undefined);
            prompt = 'Topic: ' + (fd.get('topic') || '') + '\n' +
                     'Lesson: ' + (fd.get('lesson') || '') + '\n' +
                     'Outcomes: ' + (fd.get('outcomes') || '');
        }
        const es = new EventSource(base + '?courseid=' + encodeURIComponent(courseid) +
            '&provider=ollama&prompt=' + encodeURIComponent(prompt));
        let first = true;
        es.addEventListener('start', () => {
            if (statusEl) {
                statusEl.textContent = 'Started';
            }
            scrollToResponse();
        });
        es.addEventListener('chunk', ev => {
            if (resp) {
                resp.textContent += ev.data;
                if (first) {
                    scrollToResponse();
                    first = false;
                }
            }
        });
        es.addEventListener('error', ev => {
            if (resp) {
                resp.textContent += '\n[Error] ' + (ev.data || '');
            }
            if (statusEl) {
                statusEl.textContent = 'Error';
            }
            scrollToResponse();
        });
        es.addEventListener('done', () => {
            if (statusEl) {
                statusEl.textContent = 'Done';
            }
            if (resp) {
                resp.removeAttribute('aria-busy');
            }
            scrollToResponse();
            es.close();
        });
    };
    select.addEventListener('change', refreshState);
    gen.addEventListener('input', refreshState);
    sendBtn.addEventListener('click', e => {
        if (sendBtn.disabled) {
            return;
        }
        const provider = select.value;
        if (provider === 'ollama') {
            e.preventDefault();
            startStream();
            return;
        }
        hidden.value = provider;
        const form = findForm();
        if (form) {
            form.submit();
        }
    });
    refreshState();
};

export const init = () => {
    // Auto-scroll to generated section if present after postback.
    const genWrapper = document.getElementById('ai4t-generated-wrapper');
    if (genWrapper) {
        try {
            genWrapper.scrollIntoView({behavior: 'smooth', block: 'start'});
        } catch (e) {
            genWrapper.scrollIntoView();
        }
    }
    initAgeModal();
    attachPicker({
        openId: 'ai4t-lesson-browse',
        modalId: 'ai4t-modal',
        closeId: 'ai4t-modal-close',
        cancelId: 'ai4t-modal-cancel',
        itemSelector: '.ai4t-lesson-item',
        targetId: 'id_lesson'
    });
    attachPicker({
        openId: 'ai4t-topic-browse',
        modalId: 'ai4t-topic-modal',
        closeId: 'ai4t-topic-modal-close',
        cancelId: 'ai4t-topic-modal-cancel',
        itemSelector: '.ai4t-topic-item',
        targetId: 'id_topic'
    });
    attachOutcomesModal();
    initLanguageModal();
    attachPicker({
        openId: 'ai4t-purpose-browse',
        modalId: 'ai4t-purpose-modal',
        closeId: 'ai4t-purpose-modal-close',
        cancelId: 'ai4t-purpose-modal-cancel',
        itemSelector: '.ai4t-purpose-item',
        targetId: 'id_purpose'
    });
    attachPicker({
        openId: 'ai4t-audience-browse',
        modalId: 'ai4t-audience-modal',
        closeId: 'ai4t-audience-modal-close',
        cancelId: 'ai4t-audience-modal-cancel',
        itemSelector: '.ai4t-audience-item',
        targetId: 'id_audience'
    });
    attachPicker({
        openId: 'ai4t-classtype-browse',
        modalId: 'ai4t-classtype-modal',
        closeId: 'ai4t-classtype-modal-close',
        cancelId: 'ai4t-classtype-modal-cancel',
        itemSelector: '.ai4t-classtype-item',
        targetId: 'id_classtype'
    });
    attachCopyDownload();
    initProviderSend();
};

