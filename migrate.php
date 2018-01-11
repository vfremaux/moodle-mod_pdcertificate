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
 * Handles viewing a pdcertificate
 *
 * @package     mod_pdcertificate
 * @category    mod
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * library for porting all certificates data to pdcertificate
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/pdcertificate/migrate_form.php');

$action = optional_param('what', '', PARAM_ALPHA);

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

if (!file_exists($CFG->dirroot.'/mod/certificate/lib.php')) {
    print_error('errorcertificatenotinstalled', 'pdcertificate');
}
require_once($CFG->dirroot.'/mod/pdcertificate/migratelib.php');

// Initialize $PAGE.

$url = new moodle_url('/mod/pdcertificate/migrate.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('migration', 'pdcertificate'));
$PAGE->set_heading(get_string('migration', 'pdcertificate'));

$certificatemodule = $DB->get_record('modules', array('name' => 'certificate'));

$sql = "
    SELECT DISTINCT
        c.id,
        c.shortname,
        c.fullname
    FROM
        {course_modules} cm,
        {course} c
    WHERE
        cm.course = c.id AND
        cm.module = ?
    ORDER BY
        c.shortname
";

$courses = $DB->get_records_sql($sql, array($certificatemodule->id));

$mform = new Migrate_Form($url, array('courses' => $courses));

if ($data = $mform->get_data()) {

    $certs = $DB->get_records_list('certificate', 'course', $data->courses);

    if (!empty($certs)) {
        $report = pdcertificate_migrate_certificates($certs);
    }
} 

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('migration', 'pdcertificate'));

if (isset($report)) {
    echo '<pre>';
    echo $report;
    echo '</pre>';
}

$mform->display();

echo $OUTPUT->footer();