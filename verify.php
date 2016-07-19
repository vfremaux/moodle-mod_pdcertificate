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
 * Verify an issued pdcertificate by code
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Carlos Fonseca <carlos.alexandre@outlook.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once('verify_form.php');
require_once('lib.php');

//optional_param('id', $USER->id, PARAM_INT);
$code = optional_param('code', null, PARAM_ALPHANUMEXT); // Issued code to be checked
$wsquery = optional_param('ws', false, PARAM_BOOL); // Is the call coming from a WS requirer (Smartphone).

$context = context_system::instance();
$PAGE->set_url('/mod/pdcertificate/verify.php', array('code' => $code));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pdcertificateverification', 'pdcertificate'));
$PAGE->set_heading(get_string('pdcertificateverification', 'pdcertificate'));
$PAGE->set_pagelayout('base');

if (!$wsquery) {

    $verifyform = new verify_form();

    if (!$data = $verifyform->get_data()) {
        if ($code) {
            $verifyform->set_data(array('code' => $code));
        }

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pdcertificateverification', 'pdcertificate'));
        $verifyform->display();
        echo $OUTPUT->footer();
        die;
    }
}

if (!$issuedcert = $DB->get_record('pdcertificate_issues', array('code' => $code))) {
    if ($wsquery) {
        $answer['status'] = 'failed';
        echo json_encode($answer);
        die;
    }

    $tryotherstr = get_string('tryothercode', 'pdcertificate');

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('invalidcode', 'pdcertificate'), 'pdcertificate-invalid-code');
    echo '<p><center><a href="'.$CFG->wwwroot.'/mod/pdcertificate/verify.php">'.$tryotherstr.'</a></center></p>';
    echo $OUTPUT->footer();
    die;
}

if ($user = $DB->get_record('user', array('id' => $issuedcert->userid))) {
    $username = fullname($user);
} else {
    $username = get_string('notavailable');
}

if (!$pdcertificate = $DB->get_record('pdcertificate', array('id' => $issuedcert->pdcertificateid))) {
    if ($wsquery) {
        $answer['status'] = 'failed';
        echo json_encode($answer);
        die;
    }
    print_error('errorinvalidinstance', 'pdcertificate');
}
$cm = get_coursemodule_from_instance('pdcertificate', $pdcertificate->id);
$modulecontext = context_module::instance($cm->id);

$course = $DB->get_record('course', array('id' => $pdcertificate->course));

if (!$wsquery) {
    // Getting course name (it's in filenema <COURSE NAME>-<CERTIFICATE NAME>_<ISSUEID>.pdf.

    $tostr = get_string('awardedto', 'pdcertificate');
    $datestr = get_string('issueddate', 'pdcertificate');
    $codestr = get_string('code', 'pdcertificate');
    $captionstr = get_string('pdcertificatecaption', 'pdcertificate');
    $courseinfostr = get_string('coursename', 'pdcertificate');
    $validuntilstr = get_string('validuntil', 'pdcertificate');
    $expiredstr = get_string('expiredon', 'pdcertificate');
    $definitivestr = get_string('definitive', 'pdcertificate');
    $pdcertificatefilestr = get_string('pdcertificatefile', 'pdcertificate');
    $pdcertificatefilenoaccessstr = get_string('pdcertificatefilenoaccess', 'pdcertificate');

    // Add to log.
    // add_to_log($context->instanceid, 'pdcertificate', 'verify', "verify.php?code=$code", '$issuedcert->id');

    // Trigger module viewed event.
    $eventparams = array(
        'objectid' => $pdcertificate->id,
        'context' => $context,
    );

    $event = \mod_pdcertificate\event\course_module_verified::create($eventparams);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('pdcertificate', $pdcertificate);
    $event->trigger();

    echo $OUTPUT->header();

    echo get_string('pdcertificateverifiedstate', 'pdcertificate');

    $table = new html_table();
    $table->width = '95%';
    $table->attributes = array('class' => 'generaltable pdcertificate-check');
    $table->tablealign = 'center';
    $table->head = array('', '');
    $table->align = array('right', 'left');
    $table->colclasses = array('param', 'value');

    $table->data[] = array($codestr.':', $issuedcert->code);
    $table->data[] = array($captionstr.':', $pdcertificate->caption);
    $table->data[] = array($courseinfostr.':', '<h3>'.$course->fullname.'</h3><br/>'.$course->summary);
    $table->data[] = array($tostr.':', $username);
    $table->data[] = array($datestr.':', userdate($issuedcert->timecreated));
    $expiredate = $issuedcert->timecreated + $pdcertificate->validitytime * DAYSECS;
    if ($pdcertificate->validitytime) {
        $class = ($expiredate > time()) ? 'pdcertificate-valid' : 'pdcertificate-invalid' ;
        $table->data[] = array($expiredstr.':', '<div class="'.$class.'">'.userdate($issuedcert->timecreated + $pdcertificate->validitytime * DAYSECS).'</div>');
    } else {
        $table->data[] = array($validuntilstr.':', '<div class="pdcertificate-valid">'.$definitivestr.'</div>');
    }

    if ($pdcertificate->savecert) {
        if (isloggedin()) {
            $table->data[] = array($pdcertificatefilestr.':', pdcertificate_print_user_files($pdcertificate, $user->id, $modulecontext->id));
        } else {
            $table->data[] = array($pdcertificatefilestr.':', $pdcertificatefilenoaccessstr);
        }
    }

    echo html_writer::table($table);

    echo $OUTPUT->footer();
} else {
    $expiredate = $issuedcert->timecreated + $pdcertificate->validitytime;
    $answer['name'] = $username;
    $answer['status'] = ($expiredate > time()) ? 'valid' : 'invalid';
    $answer['expiration'] = userdate($expiredate);
    $answer['issued'] = userdate($issuedcert->timecrreated);
    echo json_encode($answer);
    die;
}

