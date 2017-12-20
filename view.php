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
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/pdcertificate/deprecatedlib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
require_once($CFG->dirroot.'/local/vflibs/tcpdflib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID.
$action = optional_param('what', '', PARAM_ALPHA);
$edit = optional_param('edit', -1, PARAM_BOOL);

if (!$cm = $DB->get_record('course_modules', array('id' => $id))) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

if (!$pdcertificate = $DB->get_record('pdcertificate', array('id' => $cm->instance))) {
    print_error('course module is incorrect');
}

require_login($course->id, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/pdcertificate:view', $context);

// Trigger module viewed event.
$eventparams = array(
    'objectid' => $pdcertificate->id,
    'context' => $context,
);

$event = \mod_pdcertificate\event\course_module_viewed::create($eventparams);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('pdcertificate', $pdcertificate);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Initialize $PAGE.

$PAGE->set_url('/mod/pdcertificate/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($pdcertificate->name));
$PAGE->set_heading(format_string($course->fullname));

// Set the context
$context = context_module::instance($cm->id);
$renderer = $PAGE->get_renderer('mod_pdcertificate');


if (($edit != -1) and $PAGE->user_allowed_editing()) {
     $USER->editing = $edit;
}

// Add block editing button.
if ($PAGE->user_allowed_editing()) {
    $editvalue = $PAGE->user_is_editing() ? 'off' : 'on';
    $strsubmit = $PAGE->user_is_editing() ? get_string('blockseditoff') : get_string('blocksediton');
    $url = new moodle_url('/mod/pdcertificate/view.php', array('id' => $cm->id, 'edit' => $editvalue));
    $PAGE->set_button($OUTPUT->single_button($url, $strsubmit));
}

if ($pdcertificate->lockoncoursecompletion && !has_capability('mod/pdcertificate:manage', $context)) {
    $completioninfo = new completion_info($course);
    if (!$completioninfo->is_course_complete($USER->id)) {
        $notifurl = new moodle_url('/course/view.php', array('id' => $course->id));
        echo $OUTPUT->notification(get_string('requiredcoursecompletion', 'pdcertificate'), $notifurl);
    }
}

// Create new pdcertificate record, or return existing record.
$certrecord = pdcertificate_get_issue($course, $USER, $pdcertificate, $cm);

if ($certrecord && !has_any_capability(array('mod/pdcertificate:manage', 'mod/pdcertificate:getown'), $context)) {
    /*
     * student can not access to his pdcertificate because not allowed
     * probably the pdcertificate needs to be delivered by another person
     */
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('certification', 'pdcertificate')); 
    echo $OUTPUT->box(get_string('certificationmatchednotdeliverable', 'pdcertificate'), 'pdcertificate-notice-box'); 
    echo $OUTPUT->footer();
    die;
}

// Create a directory that is writeable so that TCPDF can create temp images.
// In 2.2 onwards the function make_cache_directory was introduced, use that,
// otherwise we will use make_upload_directory.
make_cache_directory('tcpdf');

// Load the specific pdcertificate type.
$user = $USER; // see for self
require($CFG->dirroot.'/mod/pdcertificate/type/'.$pdcertificate->pdcertificatetype.'/pdcertificate.php');
pdcertificate_set_protection($pdcertificate, $pdf);

if (empty($action)) {
    // Not distributing PDF.

    echo $OUTPUT->header();

    $currentgroup = 0;

    // Find out current groups mode.
    if ($course->groupmode) {
        groups_print_activity_menu($cm, new moodle_url('/mod/pdcertificate/view.php', array('id' => $cm->id)));
        $currentgroup = groups_get_activity_group($cm);
        $groupmode = groups_get_activity_groupmode($cm);
    }

    if (!empty($pdcertificate->intro)) {
        echo $OUTPUT->box(format_module_intro('pdcertificate', $pdcertificate, $cm->id), 'generalbox', 'intro');
    }

    if (has_capability('mod/pdcertificate:getown', $context, $USER->id, false)) {
        if ($attempts = pdcertificate_get_attempts($pdcertificate->id)) {
            echo $renderer->attempts($course, $pdcertificate, $attempts);
        }
    }

    if ($pdcertificate->delivery == 0) {
        $str = get_string('openwindow', 'pdcertificate');
    } else if ($pdcertificate->delivery == 1) {
        $str = get_string('opendownload', 'pdcertificate');
    } else if ($pdcertificate->delivery == 2) {
        $str = get_string('openemail', 'pdcertificate');
    }

    $coursecontext = context_course::instance($COURSE->id);

    if (has_capability('mod/pdcertificate:getown', $context, $USER->id, false)) {
        $linkname = get_string('getpdcertificate', 'pdcertificate');
        $link = new moodle_url('/mod/pdcertificate/view.php', array('id' => $cm->id, 'what' => 'get'));
        $button = new single_button($link, $linkname);
        $button->add_action(new popup_action('click', $link, 'view'.$cm->id, array('height' => 600, 'width' => 800)));
        echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center'));
        $confirm = true;
    }
    if (has_capability('mod/pdcertificate:addinstance', $coursecontext)) {

        echo $OUTPUT->heading(get_string('teacherview', 'pdcertificate'));

        $linkname = get_string('gettestpdcertificate', 'pdcertificate');
        $link = new moodle_url('/mod/pdcertificate/view.php', array('id' => $cm->id, 'what' => 'get'));
        $button = new single_button($link, $linkname);
        $button->add_action(new popup_action('click', $link, 'view'.$cm->id, array('height' => 600, 'width' => 800)));
        echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center', 'class' => 'inline-button'));

        if (has_capability('mod/pdcertificate:manage', $context)) {
            $numusers = count(get_users_by_capability($context, 'mod/pdcertificate:apply', 'u.id', '', '', '', $currentgroup, '', true));
            $linkname = get_string('managedelivery', 'pdcertificate', $numusers);
            $link = new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id));
            $button = new single_button($link, $linkname);
            $button->add_action(new popup_action('click', $link, 'manage'.$cm->id, array('height' => 600, 'width' => 800)));
            echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:center', 'class' => 'inline-button'));
        }
    }

    echo $OUTPUT->footer();

    exit;
} else {
    // Output to pdf.

    // Trigger module viewed event.
    $eventparams = array(
        'objectid' => $pdcertificate->id,
        'context' => $context,
    );

    $event = \mod_pdcertificate\event\course_module_issued::create($eventparams);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('pdcertificate', $pdcertificate);
    $event->trigger();

    // Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename.
    $certname = rtrim($pdcertificate->name, '.');
    $filename = clean_filename("$certname.pdf");
    pdcertificate_confirm_issue($user, $pdcertificate, $cm);
    if ($pdcertificate->savecert == 1) {
        // PDF contents are now in $file_contents as a string.
        $file_contents = $pdf->Output('', 'S');
        pdcertificate_save_pdf($file_contents, $certrecord->id, $filename, $context->id);
    }
    if ($pdcertificate->delivery == 0) {
        $pdf->Output($filename, 'I'); // Open in browser.
    } else if ($pdcertificate->delivery == 1) {
        $pdf->Output($filename, 'D'); // Force download when create.
    } else if ($pdcertificate->delivery == 2) {
        pdcertificate_email_student($course, $pdcertificate, $certrecord, $context);
        $pdf->Output($filename, 'I'); // Open in browser.
        $pdf->Output('', 'S'); // Send.
    }
}
