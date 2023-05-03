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
        'generateall' => false,
        'instances' => false,
        'cminstances' => false,
        'dryrun' => false,
        'verbose' => false,
        'updatedusersonly' => false,
        'positiveoutputonly' => false,
        'sendfinalmail' => false,
        'mailsto' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        'U' => 'users',
        'g' => 'generateall',
        'I' => 'instances',
        'C' => 'cminstances',
        'd' => 'dryrun',
        'v' => 'verbose',
        'u' => 'updatedusersonly',
        'p' => 'positiveoutputonly',
        'm' => 'sendfinalmail',
        'M' => 'mailsto',
    )
);

// Display help.
if (!empty($options['help'])) {

echo "Options:
\t-h, --help              Print out this help
\t-H,--host               The hostname when in VMoodle environment
\t-g,--generateall        If present, will generate all certifiable users, even if not having yet certificates issues.
\t-U,--users              Users to refresh
\t-I,--instances          Instances of pdcertificates to process
\t-C,--cminstances        Instances of pdcertificates course modules to process (alternative)
\t-d,--dryrun             Dry run, tells what will be done, but does nothing
\t-u,--updatedusersonly   If set, filter out all users that have not been updated after the certificate date
\t-v,--verbose            Verbose mode
\t-p,--positiveoutputonly Ouputs only effective processed entries
\t-m,--sendfinalmail      Send mail to admin when finished.
\t-M,--mailsto            If not empty, sends mail to all recipients (comma separated list of moodle user IDs)

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
if (!$CLI_VMOODLE_PRECHECK) {
    require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
}
echo('Config check : playing for '.$CFG->wwwroot."\n");

$report = '';

if (!isset($CFG->libdir)) {
    $CFG->libdir = $CFG->dirroot.'/lib';
}

require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/cronlib.php');

$allinstances = false;
if (!empty($options['instances'])) {
    $instanceids = explode(',', $options['instances']);
} else {
    echo "Processing all instances\n";
    $report .= "Processing all instances\n";
    $allinstances = true;
}

$allcminstances = false;
if (!empty($options['cminstances'])) {
    $cminstanceids = explode(',', $options['cminstances']);
} else {
    echo "Processing all cms\n";
    $report .= "Processing all cms\n";
    $allcminstances = true;
}

$allusers = false;
$userids = [];
if (!empty($options['users'])) {
    $userids = explode(',', $options['users']);
} else {
    $options['allusers'] = true;
    if (empty($options['updatedusersonly'])) {
        echo "Processing all users\n";
        $report .= "Processing all users\n";
    } else {
        echo "Processing updated users\n";
        $report .= "Processing updated users\n";
    }
}

if (!empty($options['dryrun'])) {
   $options['verbose'] = true;
}

// Scan instances and refresh certificates.
if ($allinstances && $allcminstances) {
    $instances = $DB->get_records('pdcertificate');
} else {
    // We have a closed list of instances.

    // Get them by instance id
    if (!empty($instanceids)) {
        $instances = $DB->get_records_list('pdcertificate', 'id', $instanceids);
    }

    // Get them by course module id
    if (!empty($cminstanceids)) {
        $cminstances = $DB->get_records_list('course_modules', 'id', $cminstanceids);
        if ($cminstances) {
            foreach ($cminstances as $cm) {
                $instances[$cm->instance] = $DB->get_record('pdcertificate', array('id' => $cm->instance));
            }
        }
    }
}

if (!empty($instances)) {
    $options['nolimit'] = true;
    pdcertificate_refresh_task($instances, $userids, $options, $report);
}
$message = "Refresh output: ".$report;
$icount = count($instances);
$subject = $SITE->shortname.": PD Certificate refresh CLI done for {$icount} instances.";

if (!empty($options['sendfinalmail'])) {
    if (empty($options['mailsto'])) {
        $admin = get_admin();
        // $mailcmd = 'mail -s "'.$SITE->shortname.': PD Certificate refresh CLI done for {$icount} instances."  '.$admin->email;
        // exec($mailcmd);
        email_to_user($admin, $admin, $subject, $message, $message);
    } else {
        $mailsto = explode(',', $options['mailsto']);
        foreach ($mailsto as $mt) {
            $user = $DB->get_record('user', ['id' => $mt]);
            if ($user) {
                email_to_user($user, $admin, $subject, $message, $message);
                // $mailcmd = 'mail -s "'.$SITE->shortname.': PD Certificate refresh CLI terminated for {$icount} instances."  '.$mt;
                // exec($mailcmd);
            }
        }
    }
}

echo "Done.\n";
