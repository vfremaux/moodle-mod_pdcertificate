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

    mtrace("PD Certificate cron task start...\n");
    $config = get_config('pdcertificate');

    $cronedpdcertificates = $DB->get_records('pdcertificate', array('croned' => true));

    $doccount = 0;

    if (!empty($cronedpdcertificates)) {
        foreach ($cronedpdcertificates as $cert) {
            mtrace("\tProcessing PD Certificate $cert->id...\n");
    
            if ($cert->savecert == 0 && $cert->delivery < 2) {
                mtrace("This certificate (id={$cert->id}) in course {$cert->course} can only deliver interactively.");
                mtrace(" Author may change delivery options. Skipping.\n");
                continue;
            }
    
            $cm = get_coursemodule_from_instance('pdcertificate', $cert->id);
            $context = context_module::instance($cm->id);
    
            pdcertificate_get_state($pdcertificate, $cm, 0, 0, 0, $total, $certifiableusers);
    
            if (!empty($certifiableusers)) {
                foreach ($certifiableusers as $cu) {
                    pdcertificate_make_certificate($cert, $context, '', $cu->id);
                    $doccount++;
    
                    if (!empty($config->maxdocumentspercron) && $doccount > $config->maxdocumentspercron) {
                        // If we reached the limit, let further crons finish generating.
                        mtrace('Max number of documents generated in this run. Resuming till next turn.');
                        return;
                    }
                }
            }
        }
        mtrace('PD Certificate finished...');
    } else {
        mtrace('No PD Certificate to process...');
    }
}