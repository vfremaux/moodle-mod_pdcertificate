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
 * Event observers used coursetemplates.
 *
 * @package    mod_pdcertificates
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (!function_exists('debug_trace')) {
    function debug_trace($message, $label = '') {
        assert(1);
    }
}

/**
 * Event observer for pdcertificate
 */
class mod_pdcertificate_observer {

    /**
     * This will update the usermodified date in all certificate issues of the updated user.
     * Trigs when the user's profile info (standard profile or custom profile fields) is updated.
     * Works on interactive update AND for webservice driven updates.
     * @param object $event
     */
    public static function on_user_updated(\core\event\user_updated $event) {
        global $DB;

        $DB->set_field('pdcertificate_issues', 'usermodified', time(), array('userid' => $event->objectid));
    }
}
