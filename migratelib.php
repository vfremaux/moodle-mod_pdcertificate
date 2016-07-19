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

defined('MOODLE_INTERNAL') || die();

/**
 * Handles viewing a pdcertificate
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * library for porting all certificates data to pdcertificate
 */
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php';
require_once($CFG->dirroot.'/mod/certificate/lib.php';

function pdcertificate_migrate_certificates($instances) {
    global $DB;

    $newmod = $DB->get_record('modules', array('name' => 'pdcertificate';

    if (!empty($certificates)) {
        mtrace('Migrating certificate '.$certificate->id);
        foreach($certificates as $c) {
            pdcertificate_migrate_one_certificate($c, $newmod);
        }
    }
}

function pdcertificate_migrate_one_certificate($certificate, $mod) {
    global $DB;

    $cm = get_course_module_from_instance('certificate', $certificate->id);

    unset($certificate->id);
    $pdcertificate->id = pdcertificate_add_instance();

    $context = context_module::instance($cm->id);

    $cm->instanceid = $pdcertificate->id;
    $cm->module = $mod->id;
    $DB->update_record('course_modules', $cm);

    // Mutate file store
    $sql = "
        UPDATE
            {files}
        SET
            component = 'mod_pdcertificate'
        WHERE
            contextid = ?
    ";
    $DB->execute($sql, array('contextid' => $context->id));

    // Mutate events

    $sql = "
        UPDATE
            {logstore_standard_log}
        SET
            component = 'mod_pdcertificate'
            table = 'pdcertificate'
        WHERE
            contextid = ? AND
            table = 'certificate'
    ";
    $DB->execute($sql, array('contextid' => $context->id));

    $sql = "
        UPDATE
            {logstore_standard_log}
        SET
            component = 'mod_pdcertificate'
            table = 'pdcertificate_issues'
        WHERE
            contextid = ? AND
            table = 'certificate_issues'
    ";
    $DB->execute($sql, array('contextid' => $context->id));
}