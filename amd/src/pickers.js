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
 * Generic picker + outcomes + language modal logic.
 * @module     block_aipromptgen/pickers
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/**
 * Generic single-value picker binding.
 * @param {Object} config
 */
export const attachPicker = (config) => {
    const {
        openId, modalId, closeId, cancelId,
        itemSelector, targetId, backdropId = 'ai4t-modal-backdrop'
    } = config;
    const openBtn = document.getElementById(openId);
    const modal = document.getElementById(modalId);
    const backdrop = document.getElementById(backdropId);
    const closeBtn = document.getElementById(closeId);
    const cancelBtn = document.getElementById(cancelId);
    const target = document.getElementById(targetId);

    const open = () => {
        if (modal && backdrop) {
            modal.style.display = 'block';
            backdrop.style.display = 'block';
            modal.focus();
        }
    };
    const close = () => {
        if (modal && backdrop) {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        }
    };
    const pick = (el) => {
        const v = el.getAttribute('data-value');
        if (target && v !== null) {
            target.value = v;
        }
        close();
    };

        openBtn?.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            open();
        });
    closeBtn?.addEventListener('click', close);
    cancelBtn?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);
    document.addEventListener('keydown', ev => {
        if (ev.key === 'Escape') {
            close();
        }
    });
    $$(itemSelector).forEach(li => {
        li.addEventListener('click', () => pick(li));
        li.addEventListener('keydown', ev => {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                pick(li);
            }
        });
    });
};

/**
 * Attach outcomes multi-select modal behaviour.
 */
export const attachOutcomesModal = () => {
    const openBtn = $('#ai4t-outcomes-browse');
    const modal = $('#ai4t-outcomes-modal');
    const backdrop = $('#ai4t-modal-backdrop');
    const closeBtn = $('#ai4t-outcomes-modal-close');
    const cancelBtn = $('#ai4t-outcomes-modal-cancel');
    const insertBtn = $('#ai4t-outcomes-modal-insert');
    const ta = $('#id_outcomes');

    const open = () => {
        if (modal && backdrop) {
            modal.style.display = 'block';
            backdrop.style.display = 'block';
            modal.focus();
        }
    };
    const close = () => {
        if (modal && backdrop) {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        }
    };

    openBtn?.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        open();
    });
    closeBtn?.addEventListener('click', close);
    cancelBtn?.addEventListener('click', close);
        insertBtn?.addEventListener('click', () => {
            if (!ta) {
                close();
                return;
            }
            const vals = $$('.ai4t-outcome-checkbox:checked').map(cb => cb.value).filter(Boolean);
            if (!vals.length) {
                close();
                return;
            }
            let cur = ta.value || '';
            if (cur && !/\n$/.test(cur)) {
                cur += '\n';
            }
            ta.value = cur + vals.join('\n');
            close();
        });
    backdrop?.addEventListener('click', close);
    document.addEventListener('keydown', ev => {
        if (ev.key === 'Escape') {
            close();
        }
    });
};

/**
 * Attach language modal + sync logic.
 */
export const initLanguageModal = () => {
    const openBtn = $('#ai4t-language-browse');
    const modal = $('#ai4t-language-modal');
    const backdrop = $('#ai4t-modal-backdrop');
    const closeBtn = $('#ai4t-language-modal-close');
    const cancelBtn = $('#ai4t-language-modal-cancel');
    const input = $('#id_language');
    const codeEl = $('#id_languagecode');

    const open = () => {
        if (modal && backdrop) {
            modal.style.display = 'block';
            backdrop.style.display = 'block';
            modal.focus();
        }
    };
    const close = () => {
        if (modal && backdrop) {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        }
    };
        const sync = () => {
            if (!input || !codeEl) {
                return;
            }
            const t = (input.value || '').trim();
            if (!t) {
                return;
            }
            const m = t.match(/\(([a-z]{2,3}(?:[_-][a-z]{2,3})?)\)/i);
            if (m) {
                codeEl.value = m[1].replace('-', '_').toLowerCase();
                return;
            }
            $$('.ai4t-language-item').some(li => {
                const name = li.getAttribute('data-name') || '';
                if (name.toLowerCase() === t.toLowerCase()) {
                    codeEl.value = li.getAttribute('data-code');
                    return true;
                }
                return false;
            });
        };

    openBtn?.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        open();
    });
    closeBtn?.addEventListener('click', close);
    cancelBtn?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);
    document.addEventListener('keydown', ev => {
        if (ev.key === 'Escape') {
            close();
        }
    });
    $$('.ai4t-language-item').forEach(li => {
        li.addEventListener('click', () => {
            if (input) {
                input.value = li.getAttribute('data-name') || '';
            }
            if (codeEl) {
                codeEl.value = li.getAttribute('data-code') || '';
            }
            close();
        });
        li.addEventListener('keydown', ev => {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                li.click();
            }
        });
    });
    input?.addEventListener('blur', sync);
    input?.addEventListener('change', sync);
};
