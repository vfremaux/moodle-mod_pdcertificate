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
 * A scheduled task for forum cron.
 *
 * @todo MDL-44734 This job will be split up properly.
 *
 * @package    mod_pdcertificate
 * @copyright  2014 Dan Poltawski <dan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_pdcertificate\task;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
require_once($CFG->dirroot.'/lib/completionlib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/cronlib.php');

class refresh_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('refresh_task', 'pdcertificate');
    }

    /**
     * Run learningtimecheck cron.
     */
    public function execute() {
        global $CFG, $DB;

        $options = array();
        $options['allusers'] = true;
        $options['generateall'] = false;
        $options['dryrun'] = false;
        $options['verbose'] = ($CFG->debug == DEBUG_DEVELOPER) ? true : false;
        $instances = $DB->get_records('pdcertificate', array('croned' => 1));
        if (!empty($instances)) {
            pdcertificate_refresh_task($instances, array(), $options, $report);
        }
        return true;
    }
}