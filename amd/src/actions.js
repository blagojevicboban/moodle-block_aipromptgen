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
 * Copy / download / send actions for generated prompt.
 * @module     block_aipromptgen/actions
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {getString} from 'core/str';

const $ = (sel, root = document) => root.querySelector(sel);

/**
 * Attach copy / download / send handlers for the generated prompt result.
 */
export const attachCopyDownload = () => {
    const copyBtn = $('#ai4t-copy');
    const dlBtn = $('#ai4t-download');
    const sendBtn = $('#ai4t-sendtochat');
    const ta = $('#ai4t-generated');
    const form = document.querySelector('form.mform');
    const copied = $('#ai4t-copied');

    copyBtn?.addEventListener('click', () => {
        if (!ta) {
            return;
        }
        ta.select();
        ta.setSelectionRange(0, ta.value.length);
        const copyPromise = navigator.clipboard?.writeText
            ? navigator.clipboard.writeText(ta.value)
            : Promise.resolve(document.execCommand('copy'));
        copyPromise.catch(() => {
            // Ignore copy failure silently.
        });
        if (copied) {
            void getString('form:copied', 'block_aipromptgen').then(str => {
                copied.textContent = str;
                copied.style.display = 'inline';
                setTimeout(() => {
                    copied.style.display = 'none';
                }, 1500);
                return true;
            });
        }
    });

    dlBtn?.addEventListener('click', () => {
        if (!ta) {
            return;
        }
        const title = document.querySelector('title')?.textContent || 'prompt';
        const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        const blob = new Blob([ta.value || ''], {type: 'text/plain'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `${slug}-ai-prompt.txt`;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(a.href);
            a.remove();
        }, 0);
    });

    sendBtn?.addEventListener('click', () => {
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
};
