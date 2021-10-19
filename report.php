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
 * Handles viewing the report
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');
$PAGE->requires->js_call_amd('mod_pdcertificate/pdcertificate', 'init');

$id = required_param('id', PARAM_INT); // Course module ID.
$sort = optional_param('sort', '', PARAM_RAW);
$download = optional_param('download', '', PARAM_ALPHA);
$action = optional_param('what', '', PARAM_ALPHA);
$ccode = optional_param('ccode', '', PARAM_TEXT);
$firstnamefilter = optional_param('firstnamefilter', '', PARAM_TEXT);
$lastnamefilter = optional_param('lastnamefilter', '', PARAM_TEXT);
$ccode = optional_param('ccode', '', PARAM_TEXT);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', PDCERT_PER_PAGE, PARAM_INT);

$context = context_module::instance($id);

$params = array('id' => $id, 'page' => $page, 'perpage' => $perpage);
$url = new moodle_url('/mod/pdcertificate/report.php', $params);

if ($download) {
    $url->param('download', $download);
}

$PAGE->set_url($url);

if (!$cm = $DB->get_record('course_modules', array('id' => $id))) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

if (!$pdcertificate = $DB->get_record('pdcertificate', array('id' => $cm->instance))) {
    print_error('Certificate ID was incorrect');
}

// Requires a course login.
require_login($course->id, false, $cm);

// Check capabilities.
$context = context_module::instance($cm->id);
require_capability('mod/pdcertificate:manage', $context);

$renderer = $PAGE->get_renderer('mod_pdcertificate');

// Declare some variables.
$strpdcertificates = get_string('modulenameplural', 'pdcertificate');
$strpdcertificate  = get_string('modulename', 'pdcertificate');
$strto = get_string('awardedto', 'pdcertificate');
$strdate = get_string('receiveddate', 'pdcertificate');
$strgrade = get_string('grade','pdcertificate');
$strcode = get_string('code', 'pdcertificate');
$strstate = get_string('state', 'pdcertificate');
$strreport= get_string('report', 'pdcertificate');
$strlock = get_string('issuelock', 'pdcertificate');
$strtimeoverride= get_string('timeoverride', 'pdcertificate');

if (!$download) {
    $PAGE->navbar->add($strreport);
    $PAGE->set_title(format_string($pdcertificate->name).": $strreport");
    $PAGE->set_heading($course->fullname);
    // Check to see if groups are being used in this choice.
    if ($groupmode = groups_get_activity_groupmode($cm)) {
        groups_get_activity_group($cm, true);
    }
} else {
    $groupmode = groups_get_activity_groupmode($cm);
    // Get all results when $page and $perpage are 0.
    $page = $perpage = 0;
}

// Trigger module viewed event.
$eventparams = array(
    'objectid' => $pdcertificate->id,
    'context' => $context,
);

$event = \mod_pdcertificate\event\course_module_report_viewed::create($eventparams);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('pdcertificate', $pdcertificate);
$event->trigger();

// Check to see if groups are being used.

$group = 0;
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode) {
    $group = groups_get_activity_group($cm, true);
}

// Ensure we are in a group.
$allgroupaccess = has_capability('moodle/site:accessallgroups', $context, $USER->id);
if (!$allgroupaccess) {
    if (!$group) {
        $mygroups = groups_get_all_groups($course->id, $USER->id);
        if (!empty($mygroups)) {
            if (empty($group) || !in_array($group, $mygroups)) {
                $first = array_shift($mygroups);
                $group = $first->id;
            }
        }
    }
}

raise_memory_limit(MEMORY_EXTRA);

$total = array();
$certifiableusers = array();
$state = pdcertificate_get_state($pdcertificate, $cm, $page, $perpage, $group, $total, $certifiableusers);

// Now process certifiable users.

if (!$certifiableusers) {
    $PAGE->navbar->add($strreport);
    $PAGE->set_title(format_string($pdcertificate->name).": $strreport");
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    if ($groupmode) {
        groups_print_activity_menu($cm, $url);
    }
    echo $OUTPUT->notification(get_string('nocertifiables', 'pdcertificate'));
    echo $OUTPUT->footer();
    die;
}

// Call controller for some MVC actions.
if ($action) {
    include $CFG->dirroot.'/mod/pdcertificate/report.controller.php';
}

$filterfirstname = optional_param('filterfirstname', '', PARAM_TEXT);
$filterlastname = optional_param('filterlastname', '', PARAM_TEXT);
$filters = array($filterfirstname, $filterlastname);

$certs = pdcertificate_get_issues($pdcertificate->id, 'lastname, firstname', $groupmode, $cm, 0, 0, $filters);

if ($download == 'ods') {
    require_once($CFG->libdir.'/odslib.class.php');

    // Calculate file name.
    $filename = clean_filename("$course->shortname " . rtrim($pdcertificate->name, '.') . '.ods');
    // Creating a workbook.
    $workbook = new MoodleODSWorkbook("-");
    // Send HTTP headers.
    $workbook->send($filename);
    // Creating the first worksheet.
    $myxls = $workbook->add_worksheet($strreport);

    // Print names of all the fields.
    $myxls->write_string(0, 0, get_string("lastname"));
    $myxls->write_string(0, 1, get_string("firstname"));
    $myxls->write_string(0, 2, get_string("idnumber"));
    $myxls->write_string(0, 3, get_string("group"));
    $myxls->write_string(0, 4, $strdate);
    $myxls->write_string(0, 5, $strgrade);
    $myxls->write_string(0, 6, $strcode);

    // Generate the data for the body of the spreadsheet.
    $i = 0;
    $row = 1;
    if ($certs) {
        foreach ($certs as $user) {
            $myxls->write_string($row, 0, $user->lastname);
            $myxls->write_string($row, 1, $user->firstname);
            $studentid = (!empty($user->idnumber)) ? $user->idnumber : " ";
            $myxls->write_string($row, 2, $studentid);
            $ug2 = '';
            if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                foreach ($usergrps as $ug) {
                    $ug2 = $ug2. $ug->name;
                }
            }
            $myxls->write_string($row, 3, $ug2);
            $myxls->write_string($row, 4, userdate($user->timecreated));
            $myxls->write_string($row, 5, pdcertificate_get_grade($pdcertificate, $course, $user->id));
            $myxls->write_string($row, 6, $user->code);
            $row++;
        }
        $pos = 6;
    }
    // Close the workbook.
    $workbook->close();
    exit;
}

if ($download == 'xls') {
    require_once($CFG->libdir.'/excellib.class.php');

    // Calculate file name.
    $filename = clean_filename("$course->shortname " . rtrim($pdcertificate->name, '.') . '.xls');
    // Creating a workbook.
    $workbook = new MoodleExcelWorkbook("-");
    // Send HTTP headers.
    $workbook->send($filename);
    // Creating the first worksheet.
    $myxls = $workbook->add_worksheet($strreport);

    // Print names of all the fields.
    $myxls->write_string(0, 0, get_string('lastname'));
    $myxls->write_string(0, 1, get_string('firstname'));
    $myxls->write_string(0, 2, get_string('idnumber'));
    $myxls->write_string(0, 3, get_string('group'));
    $myxls->write_string(0, 4, $strdate);
    $myxls->write_string(0, 5, $strgrade);
    $myxls->write_string(0, 6, $strcode);

    // Generate the data for the body of the spreadsheet.
    $i = 0;
    $row = 1;
    if ($certs) {
        foreach ($certs as $user) {
            $myxls->write_string($row, 0, $user->lastname);
            $myxls->write_string($row, 1, $user->firstname);
            $studentid = (!empty($user->idnumber)) ? $user->idnumber : " ";
            $myxls->write_string($row,2,$studentid);
            $ug2 = '';
            if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                foreach ($usergrps as $ug) {
                    $ug2 = $ug2 . $ug->name;
                }
            }
            $myxls->write_string($row, 3, $ug2);
            $myxls->write_string($row, 4, userdate($user->timecreated));
            $myxls->write_string($row, 5, pdcertificate_get_grade($pdcertificate, $course, $user->id));
            $myxls->write_string($row, 6, $user->code);
            $row++;
        }
        $pos = 6;
    }
    // Close the workbook.
    $workbook->close();
    exit;
}

if ($download == 'txt') {
    $filename = clean_filename("$course->shortname " . rtrim($pdcertificate->name, '.') . '.txt');

    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    // Print names of all the fields.
    echo get_string("firstname"). "\t" .get_string("lastname") . "\t". get_string("idnumber") . "\t";
    echo get_string("group"). "\t";
    echo $strdate. "\t";
    echo $strgrade. "\t";
    echo $strcode. "\n";

    // Generate the data for the body of the spreadsheet.
    $i = 0;
    $row = 1;
    if ($certs) {
        foreach ($certs as $user) {
            echo $user->lastname;
            echo "\t" . $user->firstname;
            $studentid = " ";
            if (!empty($user->idnumber)) {
                $studentid = $user->idnumber;
            }
            echo "\t" . $studentid . "\t";
            $ug2 = '';
            if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                foreach ($usergrps as $ug) {
                    $ug2 = $ug2. $ug->name;
                }
            }
            echo $ug2 . "\t";
            echo userdate($user->timecreated) . "\t";
            echo pdcertificate_get_grade($pdcertificate, $course, $user->id) . "\t";
            echo $user->code . "\n";
            $row++;
        }
    }
    exit;
}

if ($download == 'zip') {
    $userids = optional_param_array('userids', false, PARAM_INT);
    $zipfile = pdcertificate_make_zip_file($pdcertificate, $cm, $userids);
    if ($zipfile) {
        send_stored_file($zipfile, 0, 0, true);
        exit;
    }
}

$usercount = count(pdcertificate_get_issues($pdcertificate->id, $DB->sql_fullname(), $groupmode, $cm, 0, 0, $filters));

// Create the table for the users.
/*
$table = new html_table();
$table->width = '100%';
$table->tablealign = 'center';
$table->head  = array($strto, $strdate, $strgrade, $strcode);
$table->align = array('left', 'left', 'center', 'center');

foreach ($certs as $user) {
    $name = $OUTPUT->user_picture($user) . fullname($user);
    $date = userdate($user->timecreated) . pdcertificate_print_user_files($pdcertificate, $user->id, $context->id);
    $code = $user->code;
    $table->data[] = array ($name, $date, pdcertificate_get_grade($pdcertificate, $course, $user->id), $code);
}
*/

echo $OUTPUT->header();

groups_print_activity_menu($cm, new moodle_url('/mod/pdcertificate/report.php', array('id' => $id)));

echo '<br />';
echo $OUTPUT->heading(get_string('summary', 'pdcertificate'));

echo $OUTPUT->box_start();
echo $renderer->global_counters($state);
echo $OUTPUT->box_end();

echo $OUTPUT->heading(get_string('modulenameplural', 'pdcertificate'));

$selallselnonecmd = '<a href="#" class="smalltext pdcertificate-select-all">'.get_string('selectall', 'pdcertificate').'</a> /';
$selallselnonecmd .= '<a href="#" class="smalltext pdcertificate-select-none">'.get_string('selectnone', 'pdcertificate').'</a>';

$table = new html_table();
$head = array ($selallselnonecmd, $strto, $strdate, $strgrade, $strcode, $strstate);
if (pdcertificate_supports_feature('issues/lockable')) {
    $head[] = $strlock;
}
if (pdcertificate_supports_feature('issues/timeoverrideable')) {
    $head[] = $strtimeoverride;
}
$table->head  = $head;
$table->align = array ('CENTER', 'LEFT', 'LEFT', 'CENTER', 'CENTER', 'LEFT');
$table->width = '100%';

$state->selectionrequired = 0;
foreach ($certifiableusers as $user) {
    $errors = pdcertificate_check_conditions($pdcertificate, $cm, $user->id);
    if (empty($errors)) $state->selectionrequired = 1;
    $name = $OUTPUT->user_picture($user).' '.fullname($user);

    if (!empty($certs) && array_key_exists($user->id, $certs)) {
        $check = (!empty($errors)) ? '' : '<input type="checkbox" class="pdcertificate-sel" name="userids[]" value="'.$user->id.'" />';
        $cert = $certs[$user->id];
        $date = userdate($cert->timecreated).pdcertificate_print_user_files($pdcertificate, $user->id, $context->id);
        if (has_capability('mod/pdcertificate:manage', $context) && $pdcertificate->savecert) {
            if (empty($errors)) $state->selectionrequired = 1;
            if (has_capability('mod/pdcertificate:regenerate', $context)) {
                // TODO : Move this capability to a more local cap.
                $params = ['id' => $cm->id, 'what' => 'regenerate', 'ccode' => $cert->code, 
                        'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage];
                $redrawurl = new moodle_url('/mod/pdcertificate/report.php', $params);
                $date .= ' <a href="'.$redrawurl.'">'.get_string('regenerate', 'pdcertificate').'</a>';
            }

            // Delete link.
            if (has_capability('mod/pdcertificate:deletepdcertificates', context_system::instance())) {
                $params = ['id' => $cm->id, 'what' => 'deletesingle', 'ccode' => $cert->code,
                        'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage];
                $deleteurl = new moodle_url('/mod/pdcertificate/report.php', $params);
                $date .= ' <a href="'.$deleteurl.'" title="'.get_string('delete').'">'.$OUTPUT->pix_icon('t/delete', get_string('delete'), 'core').'</a>';
            }
        }
        if (@$user->reportgrade !== null) {
            $grade = $cert->reportgrade;
        } else {
            $grade = get_string('notapplicable','pdcertificate');
        }
        $code = $cert->code;
        $certstate = '';
        if (pdcertificate_supports_feature('issues/lockable')) {
            if (has_capability('mod/pdcertificate:unlockissues', $context)) {
                if ($cert->locked) {
                    $icon = $OUTPUT->pix_icon('/t/locked', get_string('locked', 'pdcertificate'), 'core');
                    $params = ['id' => $cm->id, 'what' => 'unlock', 'ccode' => $cert->code,
                            'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage];
                    $unlockurl = new moodle_url('/mod/pdcertificate/report.php', $params);
                    $lockstate = '<a href="'.$unlockurl.'">'.$icon.'</a>';
                } else {
                    $icon = $OUTPUT->pix_icon('/t/unlocked', get_string('unlocked', 'pdcertificate'), 'core');
                    $params = ['id' => $cm->id, 'what' => 'lock', 'ccode' => $cert->code, 
                            'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage];
                    $lockurl = new moodle_url('/mod/pdcertificate/report.php', $params);
                    $lockstate = '<a href="'.$lockurl.'">'.$icon.'</a>';
                }
            } else {
                $lockstate = ($cert->locked) ? $OUTPUT->pix_icon('/t/locked', get_string('locked', 'pdcertificate'), 'core') : $OUTPUT->pix_icon('/t/unlocked', get_string('unlocked', 'pdcertificate'), 'core');
            }
        }
        if (pdcertificate_supports_feature('issues/timeoverrideable')) {
            $timeoverride = '<input type="text" id="id-timeoverride-'.$cert->id.'" class="pdcertificate-time-override" data-iid="'.$cert->id.'" name="timeoverride-'.$cert->id.'" size="4" />';
        }
    } else {
        $check = (!empty($errors)) ? '' : '<input type="checkbox" class="pdcertificate-sel" name="userids[]" value="'.$user->id.'" />';
        $date = '';
        $grade = '';
        $code = '';
        $generatelink = new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id, 'what' => 'generate', 'userids[]' => $user->id));
        $certifylink = '<a href="'.$generatelink.'">'.get_string('generate', 'pdcertificate').'</a>';
        $certstate = (empty($errors)) ? $certifylink : $errors;
        if (pdcertificate_supports_feature('issues/lockable')) {
            $lockstate = ($cert->locked) ? $OUTPUT->pix_icon('/t/locked', get_string('locked', 'pdcertificate'), 'core') : $OUTPUT->pix_icon('/t/unlocked', get_string('unlocked', 'pdcertificate'), 'core');
        }
        if (pdcertificate_supports_feature('issues/timeoverridable')) {
            $timeoverride = '<input type="text" class="pdcertificate-time-override" data-id="'.$cert->id.'" name="timeoverride-'.$cert->id.'" size="4" />';
        }
    }
    $row = array ($check, $name, $date, $grade, $code, $certstate);
    if (pdcertificate_supports_feature('issues/lockable')) {
        $row[] = $lockstate;
    }
    if (pdcertificate_supports_feature('issues/timeoverrideable')) {
        $row[] = $timeoverride;
    }
    $table->data[] = $row;
}

$pagingurl = new moodle_url($url, array('filterfirstname' => $firstnamefilter, 'filterlastname' => $lastnamefilter));
$pagingurl->remove_params('action');

$firstnamefilter = optional_param('filterfirstname', false, PARAM_TEXT);
$lastnamefilter = optional_param('filterlastname', false, PARAM_TEXT);

if ($perpage) {
    echo $OUTPUT->paging_bar(0 + $state->totalcount, $page, $perpage, $pagingurl);
}
echo '<br />';

echo $renderer->pagesizeswitch($id);

echo $renderer->namefilter($url);

echo $renderer->report_form($table, $cm, $pdcertificate, $state, $url, $perpage);

if ($perpage){
    echo $OUTPUT->paging_bar($state->totalcount, $page, $perpage, new moodle_url($pagingurl));
}

// Create table to store buttons.
echo $renderer->export_buttons($cm, $pdcertificate);

echo '<br/><center>';
echo $OUTPUT->single_button(new moodle_url("/course/view.php", array('id' => $course->id)), get_string('backtocourse', 'pdcertificate'));
echo '</center>';

echo $OUTPUT->footer($course);
