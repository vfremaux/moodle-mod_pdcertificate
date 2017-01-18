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
 * This page lists all the instances of pdcertificate in a particular course
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

// Ensure that the course specified is valid.
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

// Requires a login.
require_course_login($course);

// Declare variables
$currentsection = "";
$printsection = "";
$timenow = time();

// Strings used multiple times.
$strpdcertificates = get_string('modulenameplural', 'pdcertificate');
$strissued  = get_string('issued', 'pdcertificate');
$strname  = get_string("name");
$strsectionname = get_string('sectionname', 'format_'.$course->format);

// Print the header.
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/pdcertificate/index.php', array('id'=>$course->id));
$PAGE->navbar->add($strpdcertificates);
$PAGE->set_title($strpdcertificates);
$PAGE->set_heading($course->fullname);

// Trigger instances list viewed event.
$event = \mod_pdcertificate\event\course_module_instance_list_viewed::create(array('context' => $context));
$event->add_record_snapshot('course', $course);
$event->trigger();

// Get the pdcertificates, if there are none display a notice.
if (!$pdcertificates = get_all_instances_in_course('pdcertificate', $course)) {
    echo $OUTPUT->header();
    notice(get_string('nopdcertificates', 'pdcertificate'), "$CFG->wwwroot/course/view.php?id=$course->id");
    echo $OUTPUT->footer();
    exit();
}

if ($usesections = course_format_uses_sections($course->format)) {
    $sections = get_all_sections($course->id);
}

$table = new html_table();

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strissued);
} else {
    $table->head  = array ($strname, $strissued);
}

foreach ($pdcertificates as $pdcertificate) {
    if (!$pdcertificate->visible) {
        // Show dimmed if the mod is hidden.
        $link = html_writer::tag('a', $pdcertificate->name, array('class' => 'dimmed',
            'href' => $CFG->wwwroot . '/mod/pdcertificate/view.php?id=' . $pdcertificate->coursemodule));
    } else {
        // Show normal if the mod is visible.
        $link = html_writer::tag('a', $pdcertificate->name, array('class' => 'dimmed',
            'href' => $CFG->wwwroot . '/mod/pdcertificate/view.php?id=' . $pdcertificate->coursemodule));
    }
    if ($pdcertificate->section !== $currentsection) {
        if ($pdcertificate->section) {
            $printsection = $pdcertificate->section;
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $pdcertificate->section;
    }
    // Get the latest pdcertificate issue.
    if ($certrecord = $DB->get_record('pdcertificate_issues', array('userid' => $USER->id, 'pdcertificateid' => $pdcertificate->id))) {
        $issued = userdate($certrecord->timecreated);
    } else {
        $issued = get_string('notreceived', 'pdcertificate');
    }
    if (($course->format == 'weeks') || ($course->format == 'topics')) {
        $table->data[] = array ($pdcertificate->section, $link, $issued);
    } else {
        $table->data[] = array ($link, $issued);
    }
}

echo $OUTPUT->header();
echo '<br />';
echo html_writer::table($table);
echo $OUTPUT->footer();