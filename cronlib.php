<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Certificate module core interaction API
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function pdcertificate_cron_task() {
    global $DB;

    $cronedpdcertificates = $DB->get_records('pdcertificate', array('croned' => true));

    foreach ($cronedpdcertificate as $cert) {

        $users = get_enrolled_users($course);

        $cm = get_coursemodule_from_instance('pdcertificzate', $cert->id);

        $state = pdcertificate_get_state($pdcertificate, $cm, 0, 0, 0, $total, $certifiableusers);


    }
}