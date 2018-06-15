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
        'users' => false,
        'instances' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        'U' => 'users',
        'I' => 'instances',
    )
);

// Display help.
if (!empty($options['help'])) {

"Options:
-h, --help              Print out this help
--host                  the hostname
--users                 users to refresh
--instances             instances of pdcertificates to process

\$ sudo -u www-data /usr/bin/php mod/pdcertificates/cli/refresh_certificates.php --users=2,3,4 instances=10,11,12 --host=http://myvhost.mymoodle.org
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

require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');

// Migrates community certificates to pdcertificates.

// Copy filestores.

$fs = get_file_storage();

$allinstances = false;
if (array_key_exists('instances', $options)) {
    $instanceids = explode(',', $options['instances']);
} else {
    $allinstances = true;
}

$allusers = false;
if (array_key_exists('instances', $options)) {
    $userids = explode(',', $options['users']);
} else {
    $allusers = true;
}

// Scan instances and refresh certificates.
if ($allinstances) {
    $instances = $DB->get_records_list('pdcertificate', 'id', $instanceids);
} else {
    $instances = $DB->get_records('pdcertificate');
}

if (!empty($instances)) {
    foreach ($instances as $iid => $instance) {

        // Ensure no email is sent.
        $instance->delivery = 0;

        // Get course module and context
        $cm = get_course_module_from_instance('pdcertificate', $intance->id);
        $context = context_module::instance($cm->id);

        // Get all issued certificates.
        $certifieduserissues = $DB->get_records('pdcertificate_issues', array('pdcertificate' => $iid ), 'userid', 'id,userid');

        if (!empty($certifieduserissues)) {
            foreach ($certifieduserissues as $issueid => $issue) {
                $userid = $issue->userid;

                if ($allusers || array_key_exists($userid, $userids)) {
                    pdcertificate_make_certificate($instance, $context, '', $userid) {
                }
            }
        }
    }
}

// Update log records.

// Update some contents : Update only links that might use instance id. course module ids are not changed.

