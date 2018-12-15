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
        'cminstances' => false,
        'dryrun' => false,
        'output' => false,
        'verbose' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        'U' => 'users',
        'I' => 'instances',
        'C' => 'cminstances',
        'd' => 'dryrun',
        'O' => 'output',
        'v' => 'verbose',
    )
);

// Display help.
if (!empty($options['help'])) {

echo "Options:
\t-h, --help              Print out this help
\t-H,--host               The hostname when in VMoodle environment
\t-U,--users              Users to refresh
\t-I,--instances          Instances of pdcertificates to process
\t-C,--cminstances        Instances of pdcertificates course modules to process (alternative)
\t-O,--output             Output dir. defaults to datataroot/temp.
\t-d,--dryrun             Dry run, tells what will be done, but does nothing
\t-v,--verbose            Verbose mode

\$ sudo -u www-data /usr/bin/php mod/pdcertificates/cli/export_certificates.php --users=2,3,4 instances=10,11,12 --host=http://myvhost.mymoodle.org
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

if (!isset($CFG->libdir)) {
    $CFG->libdir = $CFG->dirroot.'/lib';
}

require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');

$allinstances = false;
if (!empty($options['instances'])) {
    $instanceids = explode(',', $options['instances']);
} else {
    echo "Processing all instances\n";
    $allinstances = true;
}

$allcminstances = false;
if (!empty($options['cminstances'])) {
    $cminstanceids = explode(',', $options['cminstances']);
} else {
    echo "Processing all cms\n";
    $allcminstances = true;
}

$allusers = false;
if (!empty($options['users'])) {
    $userids = explode(',', $options['users']);
} else {
    echo "Processing all users\n";
    $allusers = true;
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

$fs = get_file_storage();

if (!empty($instances)) {

    $directory = uniqid('pdcert_'.strftime('%Y%m%d_%H%M', time()).'_');
    $tempdir = make_temp_directory($directory);
    echo "Storing file sin $tempdir\n";

    foreach ($instances as $iid => $instance) {

        if (!empty($options['verbose'])) {
            echo "Processing instance $iid $instance->name\n";
        }

        // Ensure no email is sent.
        $instance->delivery = 0;

        // Get course module and context
        $cm = get_coursemodule_from_instance('pdcertificate', $instance->id);
        $context = context_module::instance($cm->id);

        // Get all issued certificates.
        $select = " pdcertificateid = ? AND timedelivered > 0 ";
        $certifieduserissues = $DB->get_records_select('pdcertificate_issues', $select, array($iid), 'userid', 'id,userid');

        if (!empty($certifieduserissues)) {
            $total = count($certifieduserissues);
            $scale = round(100 * 0.4);
            $i = 0;

            foreach ($certifieduserissues as $issueid => $issue) {
                if (!empty($options['verbose'])) {
                    $username = $DB->get_field('user', 'username', array('id' => $issue->userid));
                    echo "\tExporting issue $issueid for $username\n";
                } else {
                    $done = round($i / $total * $scale);
                    echo str_repeat('*', $done).str_repeat('-', $scale - $done)." ($done %)\r";
                }
                $userid = $issue->userid;

                if ($allusers || array_key_exists($userid, $userids)) {
                    if (empty($options['dryrun'])) {
                        echo "\tExporting {$userid} from cm {$cm->id} in course {$cm->course}\n";
                        $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'issue', $issue->id, '', false);
                        $issuepdffile = array_pop($files);

                        if ($issuepdffile) {
                            $idnumber = $DB->get_field('user', 'idnumber', array('id' => $userid));
                            $courseshortname = $DB->get_field('course', 'shortname', array('id' => $cm->course));
                            $issuename = 'cert_'.$idnumber.'_'.$courseshortname.'_'.$cm->id.'.pdf';
                            $content = $issuepdffile->get_content();
                            $fileoutput = $tempdir.'/'.$issuename;
                            if ($OUT = fopen($fileoutput, 'wb')) {
                                fputs($OUT, $content);
                                fclose($OUT);
                            }
                        }

                        $i++;
                    } else {
                        echo "\tDry run. Not processing\n";
                    }
                }
            }
        }
    }

    echo "\n";
    echo "$i entries processed\n";

    // Make a final ZIP.
    if (empty($options['dryrun'])) {
        $zip = new ZipArchive();

        if (!empty($options['output'])) {
            $outputdir = $options['output'];
        } else {
            $outputdir = $tempdir;
        }

        $zipname = $outputdir.'/pdcertificate_export_'.strftime('%Y%m%d%H%M').'.zip';
        $ret = $zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($ret !== true) {
            echo "failed opening archive file ".$zipname."\n";
        } else {
            $options = array('add_path' => 'issues/', 'remove_all_path' => true);
            $zip->addPattern('/./', $tempdir, $options);
            $zip->close();
        }
    }
}

echo "Done.\n";

