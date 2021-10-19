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
 * CLI interface for capturing and converting all certificates to pdcertificate
 *
 * @package mod_pdcertificate
 * @copyright 2016 Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true; // Force first config to be minimal.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

if (!isset($CFG->dirroot)) {
    die ('$CFG->dirroot must be explicitely defined in moodle config.php for this script to be used');
}

require_once($CFG->dirroot.'/lib/clilib.php'); // Cli only functions.

// CLI options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'host' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
    )
);

// Display help.
if (!empty($options['help'])) {

"Options:
-h, --help              Print out this help
--host                  the hostname

\$ sudo -u www-data /usr/bin/php mod/pdcertificates/cli/migrate_certificates.php --host=http://myvhost.mymoodle.org
";
    // Exit with error unless we're showing this because they asked for it.
    exit(empty($options['help']) ? 1 : 0);
}

// Now get cli options.

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error("Not recognized options ".$unrecognized);
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
echo('Config check : playing for '.$CFG->wwwroot."\n");

// Migrates community certificates to pdcertificates.

// Copy filestores.

$fs = get_file_storage();

$allfiles = $DB->get_records('file', array('component' => 'mod_certificate'));

if ($allfiles) {
    foreach ($allfiles as $f) {
        $f->component = 'mod_pdcertificate';
        $DB->update_record('file', $f);
    }
}

// Copy instances.
$moduleid = $DB->get_field('modules', 'id', array('shortname' => 'pdcertificate'));

$convert_backup_ids = array();

$allinstances = $DB->get_records('certificate');
if ($allinstances) {
    foreach ($allinstances as $instance) {
        // Make new record.

        $newinstance = clone($instance);
        unset($newinstance->id);

        // Make other model conversions/cleanup.

        $newinstance->id = $DB->insert_record('pdcertificate', $newinstance);
        $convert_backup_ids['certificate'][$instance->id] = $newinstance->id;

        // Rebind course module.
        $cm = get_coursemodule_from_instance('certificate', $instance->id);
        $cm->moduleid = $moduleid;
        $DB->update_record('course_modules', $cm);

        // Transfer all issues records.
        $issues = $DB->get_records('certificate_issues', array('certificateid' => $instance->id));
        if ($issues) {
            foreach ($issues as $issue) {
                $newissue = clone($issue);
                unset($newissue->id);
                $newissue->pdcertificateid = $newinstance->id;
                unset($newissue->certificateid);
                $newissue->id = $DB->insert_record('pdcertificate_issues', $newissue);
                $convert_backup_ids['certificate_issues'][$issue->id] = $newissue->id;
            }
            $DB->delete_records('certificate_issues', array('certificateid' => $instance->id));
        }

        // Transfer all course binding records.
        $cbs = $DB->get_records('certificate_linked_course', array('certificateid' => $instance->id));
        if ($cbs) {
            foreach ($cbs as $cb) {
                $newcb = clone($cb);
                unset($newcb->id);
                $newcb->pdcertificateid = $newinstance->id;
                unset($newcb->certificateid);
                $DB->insert_record('pdcertificate_linked_courses', $newcb);
                $convert_backup_ids['certificate_linked_courses'][$cb->id] = $newcb->id;
            }
            $DB->delete_records('certificate_linked_courses', array('certificateid' => $instance->id));
        }

        // Destroy original.
        // Do delete one by one after processing in case of errors....
        $DB->delete_records('certificate', array('id' => $instance->id));
    }
}

// Update log records.

// Update some contents : Update only links that might use instance id. course module ids are not changed.
