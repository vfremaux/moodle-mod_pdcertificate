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
 * This page reviews a pdcertificate
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
if (is_dir($CFG->dirroot.'/local/vflibs')) {
    require_once($CFG->dirroot.'/local/vflibs/tcpdflib.php');
} else {
    require_once($CFG->libdir.'/pdflib.php');
}

// Retrieve any variables that are passed.
$id = required_param('id', PARAM_INT);    // Course Module ID
$action = optional_param('what', '', PARAM_ALPHA);

if (!$cm = get_coursemodule_from_id('pdcertificate', $id)) {
    print_error('Course Module ID was incorrect');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

if (!$pdcertificate = $DB->get_record('pdcertificate', array('id' => $cm->instance))) {
    print_error('course module is incorrect');
}

// Requires a course login.
require_course_login($course->id, true, $cm);

// Check the capabilities.
$context = context_module::instance($cm->id);
require_capability('mod/pdcertificate:view', $context);

// Initialize $PAGE, compute blocks.
$PAGE->set_url('/mod/pdcertificate/review.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($pdcertificate->name));
$PAGE->set_heading(format_string($course->fullname));

// Get previous cert record.
if (!$certrecord = $DB->get_record('pdcertificate_issues', array('userid' => $USER->id, 'pdcertificateid' => $pdcertificate->id))) {
    notice(get_string('nopdcertificatesissued', 'pdcertificate'), new moodle_url('/course/view.php', array('id' => $course->id)));
    die;
}

// Load the specific pdcertificatetype.
$user = $USER; // ensure we have user
require($CFG->dirroot.'/mod/pdcertificate/type/'.$pdcertificate->pdcertificatetype.'/pdcertificate.php');

if ($action) {
    // Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename.
    $certname = rtrim($pdcertificate->name, '.');
    $filename = clean_filename("$certname.pdf");
    $pdf->Output($filename, 'I'); // open in browser
    exit();
}

echo $OUTPUT->header();

if (has_capability('mod/pdcertificate:manage', $context)) {
    $numusers = count(pdcertificate_get_issues($pdcertificate->id, 'ci.timecreated ASC', '', $cm));
    $url = new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id));
    $link = html_writer::tag('a', get_string('viewpdcertificateviews', 'pdcertificate', $numusers), array('href' => $url));
    echo html_writer::tag('div', $link, array('class' => 'reportlink'));
}

if (!empty($pdcertificate->intro)) {
    echo $OUTPUT->box(format_module_intro('pdcertificate', $pdcertificate, $cm->id), 'generalbox', 'intro');
}

echo html_writer::tag('p', get_string('viewed', 'pdcertificate'). '<br />' . userdate($certrecord->timecreated), array('style' => 'text-align:center'));

$link = new moodle_url('/mod/pdcertificate/review.php', array('id' => $cm->id, 'what' => 'get'));
$linkname = get_string('reviewpdcertificate', 'pdcertificate');
$button = new single_button($link, $linkname);
$button->add_action(new popup_action('click', $link, array('height' => 600, 'width' => 800)));

echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));

echo $OUTPUT->footer($course);
