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
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
require_once($CFG->dirroot.'/mod/certificate/lib.php');

function pdcertificate_migrate_certificates($instances) {
    global $DB;

    $report = '';

    $newmod = $DB->get_record('modules', array('name' => 'pdcertificate'));

    if (!empty($instances)) {
        $instancesnum = count($instances);
        $report = "Migrating $instancesnum certificates \n\n";
        foreach($instances as $c) {
            $report .= pdcertificate_migrate_one_certificate($c, $newmod);
        }
    }

    return $report;
}

function pdcertificate_migrate_one_certificate($certificate, $mod) {
    global $DB;

    $dbman = $DB->get_manager();

    $report = "Migrating $certificate->id $certificate->name \n";

    // Migrating main course module record.
    $cm = get_coursemodule_from_instance('certificate', $certificate->id);

    $issues = $DB->get_records('certificate_issues', array('certificateid' => $certificate->id));

    $table = new xmldb_table('certificate_linked_modules');
    if ($dbman->table_exists($table)) {
        $linkedcourses = $DB->get_records('certificate_linked_courses', array('certificateid' => $certificate->id));
    }

    unset($certificate->id);
    $certificate->printoutcome = '';
    $certificate->printhours = '';

    $certificate->headertext = '';
    $certificate->customtext = '';
    $certificate->footertext = '';


    if (substr($certificate->certificatetype, 0, 2) == 'A4') {
        $certificate->pdcertificatetype = ($certificate->orientation == 'L') ? 'A4_landscape' : 'A4_portrait';
    } else {
        $certificate->pdcertificatetype = ($certificate->orientation == 'L') ? 'letter_landscape' : 'letter_portrait';
    }
    unset($certificate->orientation);
    unset($certificate->certificatetype);
    unset($certificate->requiredtime);

    if (!isset($certificate->certifierid)) {
        $certificate->certifierid = 0;
    }

    if (!isset($certificate->validitytime)) {
        $certificate->validitytime = 0;
    }

    $pdcertificate->id = pdcertificate_add_instance($certificate);

    $context = context_module::instance($cm->id);

    $cm->instanceid = $pdcertificate->id;
    $cm->module = $mod->id;
    $DB->update_record('course_modules', $cm);

    // Migrate issues
    if (!empty($issues)) {
        $issuenum = count($issues);
        $report = "... Migrating $issuenum issues \n";
        foreach ($issues as $issue) {
            $issue->pdcertificateid = $pdcertificate->id;
            unset($issue->certificateid);
            $DB->insert_record('pdcertificate_issues', $issue);
        }
    }

    // Migrate linked courses
    if (!empty($linkedcourses)) {
        $issuenum = count($linkedcourses);
        $report = "... Migrating $linkedcoursesnum links \n";
        foreach ($linkedcourses as $lk) {
            $lk->pdcertificateid = $pdcertificate->id;
            unset($lk->certificateid);
            $DB->insert_record('pdcertificate_linked_courses', $lk);
        }
    }

    // Mutate file store
    $report = "... Migrating file storage \n";
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
    $report = "... Migrating logs \n";
    $sql = "
        UPDATE
            {logstore_standard_log}
        SET
            component = 'mod_pdcertificate',
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
            component = 'mod_pdcertificate',
            table = 'pdcertificate_issues'
        WHERE
            contextid = ? AND
            table = 'certificate_issues'
    ";
    $DB->execute($sql, array('contextid' => $context->id));

    return $report;
}