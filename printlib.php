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
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 */
function pdcertificate_print_text($pdf, $x, $y, $align, $font = 'freeserif', $style, $size = 10, $text) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 */
function pdcertificate_print_textbox($pdf, $w, $x, $y, $align, $font = 'freeserif', $style, $size = 10, $text) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell($w, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Creates rectangles for line border for A4 size paper.
 *
 * @param stdClass $pdf
 * @param stdClass $pdcertificate
 */
function pdcertificate_draw_frame($pdf, $pdcertificate) {

    $printconfig = json_decode(@$pdcertificate->printconfig);

    if (@$printconfig->bordercolor > 0) {
        if ($printconfig->bordercolor == 1) {
            $color = array(0, 0, 0); // Black.
        }
        if ($printconfig->bordercolor == 2) {
            $color = array(153, 102, 51); // Brown.
        }
        if ($printconfig->bordercolor == 3) {
            $color = array(0, 51, 204); // Blue.
        }
        if ($printconfig->bordercolor == 4) {
            $color = array(0, 180, 0); // Green.
        }

        switch ($pdcertificate->orientation) {
            case 'L':
                // Create outer line border in selected color.
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(10, 10, 277, 190);
                // Create middle line border in selected color.
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(13, 13, 271, 184);
                // Create inner line border in selected color.
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(16, 16, 265, 178);
            break;
            case 'P':
                // create outer line border in selected color
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(10, 10, 190, 277);
                // create middle line border in selected color
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(13, 13, 184, 271);
                // create inner line border in selected color
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(16, 16, 178, 265);
            break;
        }
    }
}

/**
 * Creates rectangles for line border for letter size paper.
 *
 * @param stdClass $pdf
 * @param stdClass $pdcertificate
 */
function pdcertificate_draw_frame_letter($pdf, $pdcertificate) {

    $printconfig = json_decode($pdcertificate->printconfig);

    if (@$printconfig->bordercolor > 0) {
        if ($printconfig->bordercolor == 1) {
            $color = array(0, 0, 0); // Black.
        }
        if ($printconfig->bordercolor == 2) {
            $color = array(153, 102, 51); // Brown.
        }
        if ($printconfig->bordercolor == 3) {
            $color = array(0, 51, 204); // Blue.
        }
        if ($printconfig->bordercolor == 4) {
            $color = array(0, 180, 0); // Green.
        }
        switch ($pdcertificate->orientation) {
            case 'L':
                // Create outer line border in selected color.
                $pdf->SetLineStyle(array('width' => 4.25, 'color' => $color));
                $pdf->Rect(28, 28, 736, 556);
                // Create middle line border in selected color.
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(37, 37, 718, 538);
                // Create inner line border in selected color.
                $pdf->SetLineStyle(array('width' => 2.8, 'color' => $color));
                $pdf->Rect(46, 46, 700, 520);
                break;
            case 'P':
                // Create outer line border in selected color.
                $pdf->SetLineStyle(array('width' => 1.5, 'color' => $color));
                $pdf->Rect(25, 20, 561, 751);
                // Create middle line border in selected color.
                $pdf->SetLineStyle(array('width' => 0.2, 'color' => $color));
                $pdf->Rect(40, 35, 531, 721);
                // Create inner line border in selected color.
                $pdf->SetLineStyle(array('width' => 1.0, 'color' => $color));
                $pdf->Rect(51, 46, 509, 699);
            break;
        }
    }
}

function pdcertificate_print_qrcode($pdf, $code, $x, $y) {
    global $CFG;

    $style = array(
            'border' => 2,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => array(255, 255, 255), // False.
            'module_width' => 1, // Width of a single module in points.
            'module_height' => 1 // Height of a single module in points.
    );

    $codeurl = new moodle_url('/mod/pdcertificate/verify.php', array('code' => $code));
    $pdf->write2DBarcode(''.$codeurl, 'QRCODE,H', $x, $y, 35, 35, $style, 'N');
}

function pdcertificate_insert_data($templatestring, $pdcertificate, $certrecord, $course, $user) {
    global $SITE, $DB, $CFG, $COURSE, $PAGE;

    $printconfig = json_decode($pdcertificate->printconfig);
    $renderer = $PAGE->get_renderer('mod_pdcertificate');
    $template = new StdClass;

    $cm = get_coursemodule_from_instance('pdcertificate', $pdcertificate->id);

    $context = context_module::instance($cm->id);
    $fields = 'u.id,'.get_all_user_name_fields(true, 'u');
    if ($teachers = get_users_by_capability($context, 'mod/pdcertificate:printteacher', $fields, $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $teacherfullnames[] = fullname($teacher);
        }
    }

    if ($pdcertificate->certifierid) {
        $certifier = $DB->get_record('user', array('id' => $pdcertificate->certifierid));
    }

    $DATEFORMATS = array('1' => '%B %d, %Y',
                         '2' => '%B %d, %Y',
                         '3' => '%d %B %Y',
                         '4' => '%B %Y',
                         '5' => get_string('userdateformat', 'pdcertificate')
    );

    // Get the most recent active enrolment.
    $sql = "
        SELECT
            ue.timecreated,
            ue.timestart
        FROM
            {enrol} e,
            {user_enrolments} ue
        WHERE
            e.id = ue.enrolid AND
            ue.status = 0 AND
            e.status = 0 AND
            ue.userid = ? AND
            e.courseid = ?
        ORDER BY
            ue.timestart,
            ue.timecreated
    ";
    $enroldates = $DB->get_records_sql($sql, array($user->id, $course->id));
    if ($enroldates) {
        $lastenroldate = 0;
        foreach ($enroldates as $edate) {
            $date = max($edate->timecreated, $edate->timestart);
            if ($lastenroldate < $date) {
                $lastenroldate = $date;
            }
        }
    } else {
        $lastenroldate = '--';
    }

    $replacements = array(
        'info:user_fullname' => fullname($user),
        'user:fullname' => fullname($user),
        'info:user_firstname' => $user->firstname,
        'user:firstname' => $user->firstname,
        'info:user_lastname' => $user->lastname,
        'user:lastname' => $user->lastname,
        'info:user_alternatename' => $user->lastname,
        'user:alternatename' => $user->lastname,
        'info:user_email' => $user->email,
        'user:email' => $user->email,
        'info:user_idnumber' => $user->idnumber,
        'user:idnumber' => $user->idnumber,
        'info:user_country' => $user->country,
        'user:country' => $user->country,
        'info:user_city' => $user->city,
        'user:city' => $user->city,
        'info:user_institution' => $user->institution,
        'user:institution' => $user->institution,
        'info:user_department' => $user->department,
        'user:department' => $user->department,
        'info:user_enrolment_date' => ($lastenroldate == '--') ? '--' : pdcertificate_strftimefixed($DATEFORMATS[$pdcertificate->datefmt], $lastenroldate),
        'user:enrolment_date' => ($lastenroldate == '--') ? '--' : pdcertificate_strftimefixed($DATEFORMATS[$pdcertificate->datefmt], $lastenroldate),
        'info:site_fullname' => format_string($SITE->fullname),
        'info:site_shortname' => $SITE->shortname,
        'info:site_city' => @$CFG->city,
        'info:site_country' => $CFG->country,
        'info:course_shortname' => $course->shortname,
        'info:course_fullname' => format_string($course->fullname),
        'info:course_summary' => format_text($course->summary, $course->summaryformat),
        'info:course_category' => $DB->get_field('course_categories', 'name', array('id' => $course->category)),
        'info:course_idnumber' => $course->idnumber,
        'info:course_grade' => pdcertificate_get_grade($pdcertificate, $course, $user->id),
        'info:certificate_name' => format_string($pdcertificate->name),
        'info:certificate_date' => (empty($certrecord->timecreated)) ? '--' : pdcertificate_strftimefixed($DATEFORMATS[$pdcertificate->datefmt], $certrecord->timecreated),
        'info:certificate_outcome' => format_string(pdcertificate_get_outcome($pdcertificate, $course)),
        'info:certificate_credit_hours_text' => get_string('credithours', 'pdcertificate').': '.$pdcertificate->credithours,
        'info:certificate_credit_hours' => $pdcertificate->credithours,
        'info:certificate_code' => strtoupper($certrecord->code),
        'info:certificate_caption' => format_string($pdcertificate->caption),
        'info:group_specific' => pdcertificate_get_groupspecific_content($pdcertificate)
    );

    // Get certificate instance extradata
    if (!empty($pdcertificate->extradata)) {
        $extradata = json_decode($pdcertificate->extradata);

        if ($extradata) {
            foreach ($extradata as $key => $datum) {
                $replacements['extra:'.$key] = $datum;
            }
        }
    }

    // Get and prepare additional custom info for replacements.
    $profilefields = profile_get_user_fields_with_data($user->id);
    if (!empty($profilefields)) {
        foreach ($profilefields as $field) {
            $shortname = str_replace('profile_field_', '', $field->inputname);
            $replacements['user:'.$shortname] = $field->display_data();
            $replacements['info:user_'.$shortname] = $field->display_data();
        }
    }

    if (completion_info::is_enabled_for_site()) {
        $completion = new completion_info($COURSE);

        $params = array(
            'userid'    => $user->id,
            'course'  => $COURSE->id
        );

        $ccompletion = new completion_completion($params);
        if ($ccompletion->timecompleted) {
            if (empty($ccompletion->timecompleted)) {
                $replacements['info:completion_date'] = '--';
            } else {
                $replacements['info:completion_date'] = pdcertificate_strftimefixed($DATEFORMATS[$pdcertificate->datefmt], $ccompletion->timecompleted);
            }
        } else {
            $replacements['info:completion_date'] = get_string('nc', 'pdcertificate');
        }
    }

    if ($pdcertificate->certifierid) {
        $replacements['info:certifier_name'] = fullname($certifier);
    }

    if (file_exists($CFG->dirroot.'/blocks/use_stats/locallib.php')) {

        // Do not process if the placeholder is not used.
        if (preg_match('/{{info:course_total_time}}/', $templatestring)) {
            require_once($CFG->dirroot.'/blocks/use_stats/locallib.php');
            $now = time();
            $logs = use_stats_extract_logs($course->startdate, $now, $user->id, $course->id);
            $aggregate = use_stats_aggregate_logs($logs, $course->startdate, $now);

            if (array_key_exists('coursetotal', $aggregate)) {
                $replacements['info:course_total_time'] = block_use_stats_format_time(0 + @$aggregate['coursetotal'][$course->id]->elapsed);
            } else {
                $replacements['info:course_total_time'] = '';
            }
        }
    }

    if (isset($teacherfullnames)) {
        $replacements['info:certificate_teachers'] = implode(', ', $teacherfullnames);
        // Keep it for compatibility.
        $replacements['info:pdcertificate_teachers'] = implode(', ', $teacherfullnames);
    }

    if ($pdcertificate->certifierid) {
        if ($certifier = $DB->get_records('user', array('id' => $pdcertificate->certifierid))) {
            $replacements['info:certificate_certifier'] = fullname($certifier);
            // Keep it for compatibility.
            $replacements['info:pdcertificate_certifier'] = fullname($certifier);
        }
    }

    // Track some course module completions.
    if (completion_info::is_enabled_for_site() && $completion->is_enabled()) {
        if (preg_match_all('/info\:module_completion_date_([0-9]+)/', $templatestring, $matches)) {
            foreach ($matches[1] as $cmid) {
                $completiontag = array_shift($matches[0]);
                $params = array($user->id, $cmid);
                $select = " userid = ? AND coursemoduleid = ? AND completionstate >= 1 ";
                $completiondate = $DB->get_field_select('course_modules_completion', 'timemodified', $select, $params);
                if ($completiondate) {
                    $replacements[$completiontag] = pdcertificate_strftimefixed($DATEFORMATS[$pdcertificate->datefmt], $completiondate);
                } else {
                    $replacements[$completiontag] = get_string('nc', 'pdcertificate');
                }
            }
        }
    } else {
        if (preg_match_all('/info\:module_completion_date_([0-9]+)/', $templatestring, $matches)) {
            foreach ($matches[0] as $completiontag)
            $replacements[$completiontag] = get_string('disabled', 'pdcertificate');
        }
    }

    return $renderer->render_from_string($templatestring, $replacements);
}