<?php
// This file is part of Moodle - http://moodle.org/
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
 * Privacy Subsystem implementation for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_aipromptgen\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * Privacy Subsystem implementation for block_aipromptgen.
 *
 * The plugin stores no personal data in Moodle, but the prompts a teacher sends go to the
 * AI provider the teacher chooses.
 *
 * @package    block_aipromptgen
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the data sent to external AI services.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        foreach (\block_aipromptgen\ai_client::PROVIDERS as $provider) {
            $collection->add_external_location_link(
                $provider,
                ['prompt' => 'privacy:metadata:prompt'],
                'privacy:metadata:' . $provider
            );
        }
        return $collection;
    }

    /**
     * No user data is stored in Moodle, so there are no contexts.
     *
     * @param int $userid The user ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * No user data is stored in Moodle, so there are no users.
     *
     * @param userlist $userlist The userlist to fill.
     */
    public static function get_users_in_context(userlist $userlist) {
    }

    /**
     * No user data is stored in Moodle, so there is nothing to export.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
    }

    /**
     * No user data is stored in Moodle, so there is nothing to delete.
     *
     * @param \context $context The context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
    }

    /**
     * No user data is stored in Moodle, so there is nothing to delete.
     *
     * @param approved_contextlist $contextlist The approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
    }

    /**
     * No user data is stored in Moodle, so there is nothing to delete.
     *
     * @param approved_userlist $userlist The approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
    }
}
