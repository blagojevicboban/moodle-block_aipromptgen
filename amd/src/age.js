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
 * Age modal logic separated from orchestrator.
 * @module     block_aipromptgen/age
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const $ = (sel, root = document) => root.querySelector(sel);

class SimpleModal {
    constructor(id) {
        this.node = document.getElementById(id);
        this.backdrop = document.getElementById('ai4t-modal-backdrop');
    }
    open(prefill) {
        if (!this.node) {
            return;
        }
        if (typeof prefill === 'function') {
            try {
                prefill();
            } catch (e) {
                // Ignore prefill errors.
            }
        }
        this.node.style.display = 'block';
        if (this.backdrop) {
            this.backdrop.style.display = 'block';
        }
        this.node.focus();
    }
    close() {
        if (!this.node) {
            return;
        }
        this.node.style.display = 'none';
        if (this.backdrop) {
            this.backdrop.style.display = 'none';
        }
    }
}

export const initAgeModal = () => {
    const modal = new SimpleModal('ai4t-age-modal');
    const openBtn = $('#ai4t-age-browse');
    const closeBtn = $('#ai4t-age-modal-close');
    const cancelBtn = $('#ai4t-age-modal-cancel');
    const insertBtn = $('#ai4t-age-modal-insert');
    const input = $('#id_agerange');
    const exact = $('#ai4t-age-exact');
    const from = $('#ai4t-age-from');
    const to = $('#ai4t-age-to');
    const modeExact = $('#ai4t-age-mode-exact');
    const modeRange = $('#ai4t-age-mode-range');

    const syncEnabled = () => {
        const useExact = modeExact?.checked;
        if (useExact) {
            exact?.removeAttribute('disabled');
            from?.setAttribute('disabled', 'disabled');
            to?.setAttribute('disabled', 'disabled');
            return;
        }
        exact?.setAttribute('disabled', 'disabled');
        from?.removeAttribute('disabled');
        to?.removeAttribute('disabled');
    };

    const prefill = () => {
        if (!input) {
            return;
        }
        const v = (input.value || '').trim();
        if (!v) {
            if (modeExact) {
                modeExact.checked = true;
            }
            exact.value = '';
            from.value = '';
            to.value = '';
            syncEnabled();
            return;
        }
        if (/^\d+$/.test(v)) {
            exact.value = v;
            from.value = '';
            to.value = '';
            if (modeExact) {
                modeExact.checked = true;
            }
            syncEnabled();
            return;
        }
        const m = v.match(/^\s*(\d+)\s*[-\u2013]\s*(\d+)\s*$/u);
        if (m) {
            exact.value = '';
            from.value = m[1];
            to.value = m[2];
            if (modeRange) {
                modeRange.checked = true;
            }
            syncEnabled();
            return;
        }
        if (modeExact) {
            modeExact.checked = true;
        }
        exact.value = '';
        from.value = '';
        to.value = '';
        syncEnabled();
    };

    const onInsert = () => {
        if (!input) {
            modal.close();
            return;
        }
        const ev = (exact.value || '').trim();
        const fv = (from.value || '').trim();
        const tv = (to.value || '').trim();
        const useExact = modeExact?.checked;
        if (useExact && ev) {
            input.value = ev;
            modal.close();
            return;
        }
        if (!useExact && fv && tv) {
            let a = parseInt(fv, 10);
            let b = parseInt(tv, 10);
            if (!Number.isNaN(a) && !Number.isNaN(b)) {
                if (a > b) {
                    [a, b] = [b, a];
                }
                input.value = `${a}-${b}`;
                modal.close();
                return;
            }
        }
        modal.close();
    };

    openBtn?.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        modal.open(prefill);
    });
    closeBtn?.addEventListener('click', () => modal.close());
    cancelBtn?.addEventListener('click', () => modal.close());
    insertBtn?.addEventListener('click', onInsert);
    modeExact?.addEventListener('change', syncEnabled);
    modeRange?.addEventListener('change', syncEnabled);
    document.addEventListener('keydown', ev => {
        if (ev.key === 'Escape') {
            modal.close();
        }
    });
};
