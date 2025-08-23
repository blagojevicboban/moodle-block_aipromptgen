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

export const init = () => {
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
};
