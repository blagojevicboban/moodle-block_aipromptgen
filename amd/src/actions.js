/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Copy, download and insert actions for the AI Prompt Generator.
 *
 * @module     block_aipromptgen/actions
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str'], function(Str) {
    const $ = function(sel, root) {
        root = root || document;
        return root.querySelector(sel);
    };

    const attachCopyDownload = function() {
        const copyBtn = $('#ai4t-copy');
        const dlBtn = $('#ai4t-download');
        const sendBtn = $('#ai4t-sendtochat');
        const ta = $('#ai4t-generated');
        const form = document.querySelector('form.mform');
        const copied = $('#ai4t-copied');

        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                if (!ta) {
                    return;
                }
                ta.select();
                ta.setSelectionRange(0, ta.value.length);
                const copyPromise = navigator.clipboard && navigator.clipboard.writeText
                    ? navigator.clipboard.writeText(ta.value)
                    : Promise.resolve(document.execCommand('copy'));
                copyPromise.catch(function() {
                    // Silent fail.
                });
                if (copied) {
                    Str.get_string('form:copied', 'block_aipromptgen').then(function(s) {
                        copied.textContent = s;
                        copied.style.display = 'inline';
                        setTimeout(function() {
                            copied.style.display = 'none';
                        }, 1500);
                        return true;
                    }).catch(function() {
                        // Silent fail.
                    });
                }
            });
        }

        if (dlBtn) {
            dlBtn.addEventListener('click', function() {
                if (!ta) {
                    return;
                }
                const title = (document.querySelector('title') && document.querySelector('title').textContent) || 'prompt';
                const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                const blob = new Blob([ta.value || ''], {type: 'text/plain'});
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = slug + '-ai-prompt.txt';
                document.body.appendChild(a);
                a.click();
                setTimeout(function() {
                    URL.revokeObjectURL(a.href);
                    a.remove();
                }, 0);
            });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', function() {
                if (!form) {
                    return;
                }
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'sendtochat';
                i.value = '1';
                form.appendChild(i);
                form.submit();
            });
        }
    };

    return {
        attachCopyDownload: attachCopyDownload
    };
});
