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
            mtrace("\n\tProcessing PD Certificate $cert->id...");

            $params = array('pdcertificateid' => $cert->id);
            $emmited = $DB->count_records('pdcertificate_issues', $params);
            mtrace("\tPD Certificate $cert->id:");
            mtrace("\t\tDelivered: $emmited");

            $cm = get_coursemodule_from_instance('pdcertificate', $cert->id);
            $context = context_module::instance($cm->id);
            $course = $DB->get_record('course', array('id' => $cm->course));

            $states = pdcertificate_get_state($cert, $cm, 0, 0, 0, $total, $voidcertifiables);

            if (!empty($states->certifiables)) {
                $certifiables = count($states->certifiables);
                mtrace("\t\tCertifiables: $certifiables");
                // Process actually "to be certified" users.
                foreach ($states->certifiables as $cuid) {

                    if ($cert->savecert == 0 && $cert->delivery < 2) {
                        pdcertificate_get_issue($course, $cu->id, $cert, $cm);
                        // We generate no document, but we still need to get consequences.
                        pdcertificate_process_chain($userid, $pdcertificate);
                        mtrace("\t\tThis certificate (id={$cert->id}) in course {$cert->course} can only deliver interactively.");
                        mtrace("\t\tSkipping document generation and confirmation.");
                        continue;
                    }

                    $cu = $DB->get_record('user', array('id' => $cuid));
                    mtrace("^\t\tMaking certificate for ".$cu->id.' '.$cu->username);
                    pdcertificate_get_issue($course, $cuid, $cert, $cm);
                    pdcertificate_make_certificate($cert, $context, '', $cuid, true);
                    pdcertificate_confirm_issue($cuid, $cert, $cm);
                    $doccount++;

                    if (!empty($config->maxdocumentspercron) && $doccount > $config->maxdocumentspercron) {
                        // If we reached the limit, let further crons finish generating.
                        mtrace("\t\tMax number of documents generated in this run (".$config->maxdocumentspercron.'). Resuming till next turn.');
                        return;
                    }
                }
            } else {
                mtrace("\t\tNo certifiable users found");
            }

            if ($cert->savecert == 1 || $cert->delivery == 2) {
                mtrace("\tChecking undelivered states for $cert->id:");
                $undelivereds = $DB->get_records('pdcertificate_issues', array('pdcertificateid' => $cert->id, 'delivered' => 0));
                if ($undelivereds) {
                    foreach ($undelivereds as $undelivered) {
                        $uu = $DB->get_record('user', array('id' => $undelivered->userid));
                        mtrace("\t\tProcessing undelivered state for {$undelivered->id} {$uu->username}");
                        pdcertificate_make_certificate($cert, $context, '', $undelivered->userid, true);
                        pdcertificate_confirm_issue($undelivered->userid, $cert, $cm);
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
                        mtrace("\tFixing missing file for {$uid} {$mu->username}");
                        pdcertificate_make_certificate($cert, $context, '', $uid, true);
                        pdcertificate_confirm_issue($uid, $cert, $cm);
                    }
                }
            }
        }

        mtrace("\nPD Certificate finished...");
    } else {
        mtrace('No PD Certificate to process...');
    }
}