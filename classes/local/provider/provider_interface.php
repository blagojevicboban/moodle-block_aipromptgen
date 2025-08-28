<?php
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

// Autoloaded interface file; MOODLE_INTERNAL guard intentionally omitted.

namespace block_aipromptgen\local\provider;

/**
 * Interface every AI provider must implement.
 *
 * @package   block_aipromptgen
 * @copyright 2025 AI4Teachers
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface provider_interface {
    /**
     * Send prompt to AI and return string response.
     *
     * @param string $prompt Prompt text
     * @return string Response
     */
    public function complete(string $prompt): string;
}
