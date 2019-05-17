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
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/printlib.php');
require_once($CFG->dirroot.'/local/vflibs/tcpdflib.php');

function pdcertificate_cron_task() {
    global $DB;

    mtrace("PD Certificate cron task start...\n");
    $config = get_config('pdcertificate');

    $cronedpdcertificates = $DB->get_records('pdcertificate', array('croned' => true));

    $doccount = 0;

    if (!empty($cronedpdcertificates)) {
        foreach ($cronedpdcertificates as $cert) {
            pdcertificate_process_certificate($cert, $config);
        }

        mtrace("\nPD Certificate finished...");
    } else {
        mtrace('No PD Certificate to process...');
    }
}

function pdcertificate_refresh_task($instances, $userids = array(), $options = array()) {
    global $DB, $COURSE;

    $config = get_config('pdcertificate');
    if (!empty($options['nolimit'])) {
        $config->maxdocumentspercron = 0;
    }

    if (!empty($instances)) {
        foreach ($instances as $iid => $instance) {

            // Setup global course context to instance course.
            $COURSE = $DB->get_record('course', array('id' => $instance->course));

            if (!empty($options['verbose'])) {
                echo "Processing instance $iid $instance->name\n";
            }

            if (!empty($options['generateall'])) {
                pdcertificate_process_certificate($instance, $config, $options);
                echo "\n";
                continue;
            }

            // Ensure no email is sent.
            $instance->delivery = 0;

            // Get course module and context
            $cm = get_coursemodule_from_instance('pdcertificate', $instance->id);
            $context = context_module::instance($cm->id);

            // Get all issued certificates.
            $certifieduserissues = $DB->get_records('pdcertificate_issues', array('pdcertificateid' => $iid), 'userid', 'id,userid');

            if (!empty($certifieduserissues)) {
                $total = count($certifieduserissues);
                $scale = round(100 * 0.4);
                $i = 0;
                foreach ($certifieduserissues as $issueid => $issue) {
                    if (!empty($options['verbose'])) {
                        $username = $DB->get_field('user', 'username', array('id' => $issue->userid));
                        echo "\tProcessing issue $issueid for $username\n";
                    } else {
                        $done = round($i / $total * $scale);
                        echo str_repeat('*', $done).str_repeat('-', $scale - $done)." ($done %)\r";
                    }
                    $userid = $issue->userid;

                    if (!empty($options['allusers']) || array_key_exists($userid, $userids)) {
                        if (empty($options['dryrun'])) {
                            // Regenerate certificate.
                            pdcertificate_make_certificate($instance, $context, '', $userid, true);
                            $i++;
                        } else {
                            echo "\tDry run. Not processing\n";
                        }
                    }
                }
                echo "\n";
                echo "$i entries processed\n";
            }
        }
    }
}

function pdcertificate_process_certificate($cert, $config, $options = array('verbose' => true)) {
    global $DB;

    $cm = get_coursemodule_from_instance('pdcertificate', $cert->id);

    mtrace("\n\tFull Processing PD Certificate {$cert->id} (cmid: {$cm->id}) in course {$cm->course}...");

    $params = array('pdcertificateid' => $cert->id);
    $emmited = $DB->count_records('pdcertificate_issues', $params);
    if (!empty($options['verbose'])) {
        mtrace("\t\tDelivered: $emmited");
    }

    $context = context_module::instance($cm->id);
    $course = $DB->get_record('course', array('id' => $cm->course));
    $doccount = 0;

    $states = pdcertificate_get_state($cert, $cm, 0, 0, 0, $total, $voidcertifiables);

    if (!empty($states->certifiables)) {
        $certifiables = count($states->certifiables);
        if (!empty($options['verbose'])) {
            mtrace("\t\tCertifiables: $certifiables");
        }
        // Process actually "to be certified" users.
        foreach ($states->certifiables as $cuid) {

            if ($cert->savecert == 0 && $cert->delivery < 2) {
                pdcertificate_get_issue($course, $cu->id, $cert, $cm);
                // We generate no document, but we still need to get consequences.
                pdcertificate_process_chain($userid, $pdcertificate);
                if (!empty($options['verbose'])) {
                    mtrace("\t\tThis certificate (id={$cert->id}) in course {$cert->course} can only deliver interactively.");
                    mtrace("\t\tSkipping document generation and confirmation.");
                }
                continue;
            }

            $cu = $DB->get_record('user', array('id' => $cuid));
            if (!empty($options['verbose'])) {
                mtrace("^\t\tMaking certificate for ".$cu->id.' '.$cu->username);
            }
            if (empty($options['dryrun'])) {
                pdcertificate_get_issue($course, $cuid, $cert, $cm);
                pdcertificate_make_certificate($cert, $context, '', $cuid, true);
                pdcertificate_confirm_issue($cuid, $cert, $cm);
            } else {
                mtrace("\tNOT Updating (dryrun) cert user $cuid");
            }
            $doccount++;

            if (!empty($config->maxdocumentspercron) && $doccount > $config->maxdocumentspercron) {
                // If we reached the limit, let further crons finish generating.
                if (!empty($options['verbose'])) {
                    mtrace("\t\tMax number of documents generated in this run (".$config->maxdocumentspercron.'). Resuming till next turn.');
                }
                return;
            }
        }
    } else {
        mtrace("\t\tNo certifiable users found");
    }

    if ($cert->savecert == 1 || $cert->delivery == 2) {
        if (!empty($options['verbose'])) {
            mtrace("\tChecking undelivered states for $cert->id:");
        }
        $undelivereds = $DB->get_records('pdcertificate_issues', array('pdcertificateid' => $cert->id, 'delivered' => 0));
        if ($undelivereds) {
            foreach ($undelivereds as $undelivered) {
                $uu = $DB->get_record('user', array('id' => $undelivered->userid));
                if (!empty($options['verbose'])) {
                    mtrace("\t\tProcessing undelivered state for {$undelivered->id} {$uu->username}");
                }
                if (empty($options['dryrun'])) {
                    pdcertificate_make_certificate($cert, $context, '', $undelivered->userid, true);
                    pdcertificate_confirm_issue($undelivered->userid, $cert, $cm);
                } else {
                    mtrace("\tNOT Generating (dryrun) cert user {$undelivered->userid}");
                }
            }
        }


        mtrace("\tChecking missing files $cert->id:");

        $sql = "
            SELECT
                issue.userid as id,
                issue.userid as userid
            FROM
                {pdcertificate_issues} issue
            LEFT JOIN
                {files} f
            ON
                f.userid = issue.userid AND
                f.itemid = issue.id
            WHERE
                issue.pdcertificateid = ? AND
                f.component = 'mod_pdcertificate' AND
                f.filearea = 'issue' AND
                f.filesize > 0 AND
                f.id IS NULL
        ";
        $missings = $DB->get_records_sql($sql, array($cert->id));
        if ($missings) {
            foreach ($missings as $uid => $foo) {
                $mu = $DB->get_record('user', array('id' => $uid));
                if (!empty($options['verbose'])) {
                    mtrace("\tFixing missing file for {$uid} {$mu->username}");
                }
                pdcertificate_make_certificate($cert, $context, '', $uid, true);
                pdcertificate_confirm_issue($uid, $cert, $cm);
            }
        }
    }
}