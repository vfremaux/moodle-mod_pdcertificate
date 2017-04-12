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

    $printconfig = unserialize(@$pdcertificate->printconfig);

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

    $printconfig = unserialize($pdcertificate->printconfig);

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

/**
 * Prints border images from the borders folder in PNG or JPG formats.
 *
 * @param stdClass $pdf;
 * @param stdClass $pdcertificate
 * @param int $x x position
 * @param int $y y position
 * @param int $w the width
 * @param int $h the height
 */
function pdcertificate_print_image($pdf, $pdcertificate, $type, $x, $y, $w, $h) {
    global $CFG;

    $fs = get_file_storage();
    $cm = get_coursemodule_from_instance('pdcertificate', $pdcertificate->id);
    $context = context_module::instance($cm->id);

    switch ($type) {
        case PDCERT_IMAGE_BORDER:
            $attr = 'borderstyle';
            $defaultpath = "$CFG->dirroot/mod/pdcertificate/pix/$type/defaultborder.jpg";

            $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printborders', 0, 'itemid, filepath, filename', false);
            $f = array_pop($files);
            if ($f) {
                $filepathname = $f->get_contenthash();
            } else {
                return;
            }
            break;

        case PDCERT_IMAGE_SEAL:
            $attr = 'printseal';

            $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printseal', 0, 'itemid, filepath, filename', false);
            $f = array_pop($files);
            if ($f) {
                $filepathname = $f->get_contenthash();
            } else {
                return;
            }
            break;

        case PDCERT_IMAGE_SIGNATURE:
            $attr = 'printsignature';

            $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printsignature', 0, 'itemid, filepath, filename', false);
            $f = array_pop($files);
            if ($f) {
                $filepathname = $f->get_contenthash();
            } else {
                return;
            }
            break;

        case PDCERT_IMAGE_WATERMARK:
            $attr = 'printwmark';

            $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printwmark', 0, 'itemid, filepath, filename', false);
            $f = array_pop($files);
            if ($f) {
                $filepathname = $f->get_contenthash();
            } else {
                return;
            }
            break;
    }

    $uploadpath = $CFG->dataroot.'/filedir/'.pdcertificate_path_from_hash($filepathname).'/'.$filepathname;

    // Uploaded path will superseed.
    if (file_exists($uploadpath)) {
        $pdf->Image($uploadpath, $x, $y, $w, $h);
    } else if (file_exists($defaultpath)) {
        $pdf->Image($path, $x, $y, $w, $h);
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

function pdcertificate_insert_data($text, $pdcertificate, $certrecord, $course, $user) {
    global $SITE, $DB, $CFG, $COURSE;

    $printconfig = unserialize($pdcertificate->printconfig);

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

    $replacements = array(
        '{info:user_fullname}' => fullname($user),
        '{user:fullname}' => fullname($user),
        '{info:user_firstname}' => $user->firstname,
        '{user:firstname}' => $user->firstname,
        '{info:user_idnumber}' => $user->idnumber,
        '{user:idnumber}' => $user->idnumber,
        '{info:user_lastname}' => $user->firstname,
        '{user:lastname}' => $user->firstname,
        '{info:user_country}' => $user->country,
        '{user:country}' => $user->country,
        '{info:user_city}' => $user->city,
        '{user:city}' => $user->city,
        '{info:user_institution}' => $user->institution,
        '{user:institution}' => $user->institution,
        '{info:user_department}' => $user->department,
        '{user:department}' => $user->department,
        '{info:site_fullname}' => $SITE->fullname,
        '{info:site_shortname}' => $SITE->shortname,
        '{info:site_city}' => @$CFG->city,
        '{info:site_country}' => $CFG->country,
        '{info:course_shortname}' => $course->shortname,
        '{info:course_fullname}' => $course->fullname,
        '{info:course_summary}' => $course->summary,
        '{info:course_category}' => $DB->get_field('course_categories', 'name', array('id' => $course->category)),
        '{info:course_idnumber}' => $course->idnumber,
        '{info:course_grade}' => pdcertificate_get_grade($pdcertificate, $course),
        '{info:certificate_date}' => strftime($DATEFORMATS[$pdcertificate->datefmt], $certrecord->timecreated),
        '{info:certificate_outcome}' => pdcertificate_get_outcome($pdcertificate, $course),
        '{info:certificate_credit_hours}' => get_string('credithours', 'pdcertificate').': '.$printconfig->printhours,
        '{info:certificate_code}' => strtoupper($certrecord->code),
        '{info:group_specific}' => pdcertificate_get_groupspecific_content($pdcertificate)
    );

    // Get and prepare additional custom info for replacements.
    $sql = "
        SELECT
            uif.shortname,
            uid.data
        FROM
            {user_info_field} uif,
            {user_info_data} uid
        WHERE
            uif.id = uid.fieldid AND
            uid.userid = ?
    ";
    $userdata = $DB->get_records_sql($sql, array($user->id));

    if (!empty($userdata)) {
        foreach ($userdata as $userdatum) {
            $replacements['{user:'.$userdatum->shortname.'}'] = $userdatum->data;
        }
    }

    if (completion_info::is_enabled_for_site()) {
        $completion = new completion_info($COURSE);

        $params = array(
            'userid'    => $user->id,
            'course'  => $COURSE->id
        );

        $ccompletion = new completion_completion($params);
        $replacements['{info:completion_date}'] = strftime($DATEFORMATS[$pdcertificate->datefmt], $ccompletion->timecompleted);
    }

    if ($pdcertificate->certifierid) {
        $replacements['{info:certifier_name}'] = fullname($certifier);
    }

    if (file_exists($CFG->dirroot.'/blocks/use_stats/locallib.php')) {
        require_once($CFG->dirroot.'/blocks/use_stats/locallib.php');
        $now = time();
        $logs = use_stats_extract_logs($course->startdate, $now, $user->id, $course->id);
        $aggregate = use_stats_aggregate_logs($logs, 'module', 0, $course->startdate, $now);

        if (array_key_exists('coursetotal', $aggregate)) {
            $replacements['{info:course_total_time}'] = block_use_stats_format_time(0 + @$aggregate['coursetotal'][$course->id]->elapsed);
        } else {
            $replacements['{info:course_total_time}'] = '';
        }
    }

    if (isset($teacherfullnames)) {
        $replacements['{info:certificate_teachers}'] = implode(', ', $teacherfullnames);
        // Keep it for compatibility.
        $replacements['{info:pdcertificate_teachers}'] = implode(', ', $teacherfullnames);
    }

    if ($pdcertificate->certifierid) {
        if ($certifier = $DB->get_records('user', array('id' => $pdcertificate->certifierid))) {
            $replacements['{info:certificate_certifier}'] = fullname($certifier);
            // Keep it for compatibility.
            $replacements['{info:pdcertificate_certifier}'] = fullname($certifier);
        }
    }

    foreach ($replacements as $patt => $replacement) {
        $text = str_replace($patt, $replacement, $text);
    }

    // Eliminate remaining unresolved injection patterns.
    $text = preg_replace('/\{info:.*?\}/', '', $text);

    return $text;
}