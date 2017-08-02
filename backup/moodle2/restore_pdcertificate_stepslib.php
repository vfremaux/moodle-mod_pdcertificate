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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_pdcertificate_activity_task
 */

/**
 * Structure step to restore one pdcertificate activity
 */
class restore_pdcertificate_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('pdcertificate', '/activity/pdcertificate');

        if ($userinfo) {
            $paths[] = new restore_path_element('pdcertificate_issue', '/activity/pdcertificate/issues/issue');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_pdcertificate($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (!isset($data->layout)) {
            $data->layout = '';
        }

        // insert the pdcertificate record
        $newitemid = $DB->insert_record('pdcertificate', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_pdcertificate_issue($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->pdcertificateid = $this->get_new_parentid('pdcertificate');
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('pdcertificate_issues', $data);
        $this->set_mapping('pdcertificate_issue', $oldid, $newitemid);
    }

    protected function after_execute() {
        // Add pdcertificate related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_pdcertificate', 'issue', 'pdcertificate_issue');
        $this->add_related_files('mod_pdcertificate', 'printwmark', 'pdcertificate_issue');
        $this->add_related_files('mod_pdcertificate', 'printseal', 'pdcertificate_issue');
        $this->add_related_files('mod_pdcertificate', 'printsignature', 'pdcertificate_issue');
    }
}
