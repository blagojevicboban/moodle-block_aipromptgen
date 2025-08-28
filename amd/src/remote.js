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
 * AJAX sending of generated prompt to AI provider via external function endpoint.
 * Progressive enhancement: if the generated textarea is absent (no prompt yet) the button retains
 * its original / no-op behaviour. Provides user feedback (loading, error, response) inline.
 *
 * Responsibilities:
 *  - Read current prompt text and course id
 *  - Call external function block_aipromptgen_send_prompt via core/ajax
 *  - Render loading, error and success states with minimal DOM mutation
 *  - Keep UI responsive and restore original button state
 *
 * @module     block_aipromptgen/remote
 * @author     Boban Blagojevic <your-email@example.com>
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString as getStr} from 'core/str';

const $ = (sel, root = document) => root.querySelector(sel);

/**
 * Wire the send button to perform AJAX call.
 */
export const wireSendButton = () => {
    const btn = $('#ai4t-sendtochat');
    if (!btn) {
        return;
    }
    const ta = $('#ai4t-generated');
    // If no textarea yet (prompt not generated) keep existing (synchronous) behaviour.
    if (!ta) {
        return;
    }
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopImmediatePropagation();
        const prompt = ta.value.trim();
        if (!prompt) {
            return;
        }
        const form = document.querySelector('form.mform');
        const courseInput = form?.querySelector('input[name="courseid"]');
        const courseid = courseInput ? parseInt(courseInput.value, 10) : 0;
        const containerId = 'ai4t-airesponse';
        let container = document.getElementById(containerId);
        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.className = 'ai4t-airesponse mt-3';
            ta.parentElement?.appendChild(container);
        }
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.classList.add('disabled');
        // Start loading indicator (fire-and-forget, intentionally not awaited).
        void getStr('loading', 'core')
            .then(str => { // Return to satisfy promise/always-return.
                container.innerHTML = '<div class="alert alert-info" role="status">' + str + '…</div>';
                return true;
            })
            .catch(() => { // Return fallback.
                container.innerHTML = '<div class="alert alert-info" role="status">Loading…</div>';
                return true;
            });
        // Ajax call promise chain (not returned from handler; full catch provided).
        Ajax.call([
            {methodname: 'block_aipromptgen_send_prompt', args: {courseid, prompt}}
        ])[0]
            .then(resp => {
                if (!resp || !resp.success) {
                    container.innerHTML = '<div class="alert alert-danger">' + (resp?.error || 'Error') + '</div>';
                } else {
                    const safe = resp.response.replace(/&/g, '&amp;').replace(/</g, '&lt;');
                    container.innerHTML = '<h4>' + (btn.getAttribute('data-response-label') || 'AI response') + '</h4>' +
                        '<pre class="form-control" style="white-space:pre-wrap;padding:12px;">' + safe + '</pre>';
                }
                return null; // Always return for lint rule.
            })
            .catch(err => {
                const msg = (err && (err.error || err.message)) ? (err.error || err.message) : 'Unknown error';
                container.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
                return null;
            })
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('disabled');
                btn.textContent = originalText;
            });
    }, {once: false});
};

export default {wireSendButton};
