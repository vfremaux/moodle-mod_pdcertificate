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
 * This file keeps track of upgrades to the pdcertificate module
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_pdcertificate_upgrade($oldversion = 0) {

    global $CFG, $THEME, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2015062800) {
        $table = new xmldb_table('pdcertificate');

        $field = new xmldb_field('layout', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'validitytime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2015062800, 'pdcertificate');
    }

    if ($oldversion < 2016011801) {
        $table = new xmldb_table('pdcertificate');

        $field = new xmldb_field('propagategroups', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'layout');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        pdcertificate_convert_config($dbman);

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2016011801, 'pdcertificate');
    }

    if ($oldversion < 2016041500) {
        $table = new xmldb_table('pdcertificate');

        $field = new xmldb_field('lockoncoursecompletion', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'propagategroups');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2016041500, 'pdcertificate');
    }

    if ($oldversion < 2016061501) {
        $table = new xmldb_table('pdcertificate');

        $field = new xmldb_field('headertext', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'printconfig');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('footertext', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'customtext');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2016061501, 'pdcertificate');
    }

    if ($oldversion < 2017020600) {
        $table = new xmldb_table('pdcertificate');

        $field = new xmldb_field('completiondelivered', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'lockoncoursecompletion');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2017020600, 'pdcertificate');
    }

    if ($oldversion < 2017041201) {

        $table = new xmldb_table('pdcertificate');

        $field = new xmldb_field('setcertification', XMLDB_TYPE_INTEGER, 4, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'caption');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('setcertificationcontext', XMLDB_TYPE_INTEGER, 11, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'setcertification');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('groupspecificcontent', XMLDB_TYPE_INTEGER, 11, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'setcertificationcontext');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Certificate savepoint reached.
        upgrade_mod_savepoint(true, 2017041201, 'pdcertificate');
    }

    return true;
}

function pdcertificate_convert_config($dbman) {
    global $DB;

    $table = new xmldb_table('pdcertificate');

    $field = new xmldb_field('printconfig', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'gradefmt');
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);

        // Process all pdcertificates to convert them.
        if ($pdcertificates = $DB->get_records('pdcertificate', array())) {
            foreach ($pdcertificates as $c) {
                $printconfig = new StdClass();
                $printconfig->printhours = @$pdcertificate->printhours;
                $printconfig->printoutcome = 0 + @$pdcertificate->printoutcome;
                $printconfig->printdate = 0 + @$pdcertificate->printdate;
                $printconfig->printteacher = 0 + @$pdcertificate->printteacher;
                if (isset($pdcertificate->printnumber)) {
                    $printconfig->printcode = 0 + @$pdcertificate->printnumber;
                } else {
                    $printconfig->printcode = 0 + @$pdcertificate->printcode;
                }
                $printconfig->printseal = 0 + @$pdcertificate->printseal; // may be obsolete but let catch it
                $printconfig->printsignature = 0 + @$pdcertificate->printsignature;
                $printconfig->printwmark = 0 + @$pdcertificate->printwmark;
                $printconfig->printqrcode = 0 + @$pdcertificate->printqrcode;
                $printconfig->printgrade = 0 + @$pdcertificate->printgrade;

                $pdcertificate->printconfig = serialize($printconfig);
                $DB->set_field('pdcertificate', 'printconfig', $pdcertificate->printconfig);
            }
        }

        $field = new xmldb_field('printhours', XMLDB_TYPE_CHAR, 255, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printseal', XMLDB_TYPE_CHAR, 255, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printwmark', XMLDB_TYPE_CHAR, 255, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printsignature', XMLDB_TYPE_CHAR, 255, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printgrade', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printoutcome', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printdate', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printteacher', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printnumber', XMLDB_TYPE_INTEGER, 1, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printcode', XMLDB_TYPE_INTEGER, 1, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('printqrcode', XMLDB_TYPE_INTEGER, 1, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
    }

}