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
 * @package     mod_pdcertificate
 * @category    mod
 * @author      Clifford Tham, Valery Fremaux > 1.8
 *
 * Summary for administrators
 */
define('AJAX_SCRIPT', 1);

require('../../../config.php');
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');

require_login();

$action = required_param('what', PARAM_TEXT);

if ($action == 'overridetime') {
    require_sesskey();
    $to = required_param('to', PARAM_NUMBER);
    $issueid = required_param('iid', PARAM_INT);

    $DB->set_field('pdcertificate_issues', 'credithoursoverride', $to, ['id' => $issueid]);
    echo $issueid;
    exit(0);
}
