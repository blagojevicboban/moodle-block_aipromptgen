<?php
// This file is part of Moodle - http://moodle.org/.
//
// GNU GPL v3 or later.

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
