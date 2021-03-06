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
 * Define all the backup steps that will be used by the backup_pdcertificate_activity_task
 */

/**
 * Define the complete pdcertificate structure for backup, with file and id annotations
 */
class backup_pdcertificate_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $pdcertificate = new backup_nested_element('pdcertificate', array('id'), array(
            'name', 'intro', 'introformat', 'emailteachers', 'emailothers',
            'savecert', 'reportcert', 'delivery', 'pdcertificatetype', 'requiredtime',
            'orientation', 'printconfig', 'datefmt', 'gradefmt', 'statement', 
            'customtext','timecreated', 'timemodified', 'caption', 'certifierid', 'validitytime', 'layout', 'propagategroups'));

        $issues = new backup_nested_element('issues');

        $issue = new backup_nested_element('issue', array('id'), array(
            'pdcertificateid', 'userid', 'timecreated', 'code'));

        // Build the tree
        $pdcertificate->add_child($issues);
        $issues->add_child($issue);

        // Define sources
        $pdcertificate->set_source_table('pdcertificate', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $issue->set_source_table('pdcertificate_issues', array('pdcertificateid' => backup::VAR_PARENTID));
        }

        // Annotate the user id's where required.
        $issue->annotate_ids('user', 'userid');

        // Define file annotations
        $pdcertificate->annotate_files('mod_pdcertificate', 'intro', null); // This file area hasn't itemid
        $issue->annotate_files('mod_pdcertificate', 'issue', 'id');
        $issue->annotate_files('mod_pdcertificate', 'printwmark', 'id');
        $issue->annotate_files('mod_pdcertificate', 'printseal', 'id');
        $issue->annotate_files('mod_pdcertificate', 'printsignature', 'id');

        // Return the root element (pdcertificate), wrapped into standard activity structure
        return $this->prepare_activity_structure($pdcertificate);
    }
}
