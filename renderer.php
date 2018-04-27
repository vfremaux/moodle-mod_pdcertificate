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
 * Chat module rendering methods
 *
 * @package    mod_pdcertificate
 * @copyright  205 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Certificate module renderer class
 */
class mod_pdcertificate_renderer extends plugin_renderer_base {

    function global_counters($globals) {
        global $OUTPUT;

        $str = $OUTPUT->box_start();

        $totalcountstr = get_string('totalcount', 'pdcertificate');
        $yetcertifiedcountstr = get_string('yetcertified', 'pdcertificate');
        $yetcertifiablecountstr = get_string('yetcertifiable', 'pdcertificate');
        $notyetcertifiablecountstr = get_string('notyetcertifiable', 'pdcertificate');

        $str .= '<table width="100%" class="generaltable">';
        $str .= '<tr valign="top"><td class="header c0"><b>'.$totalcountstr.'</b><td><td>'.$globals->totalcount.'</td></tr>';
        $str .= '<tr valign="top"><td class="header c0"><b>'.$yetcertifiedcountstr.'</b><td><td>'.$globals->totalcertifiedcount.'</td></tr>';
        $str .= '<tr valign="top"><td class="header c0"><b>'.$notyetcertifiablecountstr.'</b><td><td>'.$globals->notyetusers.'</td></tr>';
        $str .= '<tr valign="top"><td class="header c0"><b>'.$yetcertifiablecountstr.'</b><td><td>'.($globals->totalcount - $globals->totalcertifiedcount - $globals->notyetusers).'</td></tr>';
        $str .= '</table>';

        $str .= $OUTPUT->box_end();

        return $str;
    }

    function export_buttons($cm) {
        global $OUTPUT;

        $tablebutton = new html_table();
        $tablebutton->attributes['class'] = 'downloadreport';
        $btndownloadods = $OUTPUT->single_button(new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id, 'download' => 'ods')), get_string('downloadods'));
        $btndownloadxls = $OUTPUT->single_button(new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id, 'download' => 'xls')), get_string('downloadexcel'));
        $btndownloadtxt = $OUTPUT->single_button(new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id, 'download' => 'txt')), get_string('downloadtext'));
        $tablebutton->data[] = array($btndownloadods, $btndownloadxls, $btndownloadtxt);
        return html_writer::tag('div', html_writer::table($tablebutton), array('style' => 'margin:auto; width:50%'));
    }

    /**
     * Prints a table of previously issued pdcertificates--used for reissue.
     *
     * @param stdClass $course
     * @param stdClass $pdcertificate
     * @param stdClass $attempts
     * @return string the attempt table
     */
    function attempts($course, $pdcertificate, $attempts) {
        global $OUTPUT, $DB;

        echo $OUTPUT->heading(get_string('getattempts', 'pdcertificate'));

        $printconfig = unserialize(@$pdcertificate->printconfig);

        // Prepare table header
        $table = new html_table();
        $table->class = 'generaltable';
        $table->head = array(get_string('issued', 'pdcertificate'));
        $table->align = array('left');
        $table->width = '80%';

        if ($pdcertificate->validitytime) {
            $table->head[] = get_string('validuntil', 'pdcertificate');
            $table->align[] = 'right';
            $table->size[] = '';
        }

        $table->head[] = get_string('authority', 'pdcertificate');
        $table->align[] = 'left';
        $table->size[] = '';

        $table->head[] = get_string('deliveredon', 'pdcertificate');
        $table->align[] = 'right';
        $table->size[] = '';

        // One row for each attempt
        $i = 0;
        foreach ($attempts as $attempt) {
            $row = array();

            // prepare strings for time taken and date completed
            $datecompleted = userdate($attempt->timecreated);
            $row[] = $datecompleted;

            if ($pdcertificate->validitytime) {
                $validuntil = $attempt->timecreated + $pdcertificate->validitytime;
                if ($validuntil < time()) {
                    $table->rowclasses[$i] = 'pdcertificate-invalid';
                }
                $row[] = userdate($validuntil);
            }

            if ($attempt->authorityid) {
                $certifier = $DB->get_record('user', array('id' => $attempt->authorityid));
                $row[] = fullname($certifier);
            } else {
                $row[] = '';
            }

            if ($attempt->delivered) {
                $row[] = userdate($attempt->timedelivered);
            }

            $table->data[$attempt->id] = $row;
            $i++;
        }

        echo html_writer::table($table);
    }

    public function namefilter(&$thispageurl) {
        $str = '';

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $firstnamefilter = optional_param('filterfirstname', false, PARAM_TEXT);

        $str .= get_string('firstname').': ';
        for ($i = 0; $i < strlen($letters); $i++) {
            $letter = $letters[$i];
            if ($firstnamefilter == $letter) {
                $str .= '<div class="namefilter-selected">'.$letter.'</div>&nbsp';
            } else {
                $str .= '<a href="'.$thispageurl.'&filterfirstname='.$letter.'" >'.$letter.'</a>&nbsp';
            }
        }
        if (!$firstnamefilter) {
            $str .= '<div class="namefilter-selected">'.get_string('all').'</div>&nbsp';
        } else {
            $str .= '<a href="'.$thispageurl.'&filterfirstname=" >'.get_string('all').'</a>&nbsp';
        }

        $str .= '<br/>';

        $lastnamefilter = optional_param('filterlastname', false, PARAM_TEXT);

        $str .= get_string('lastname').': ';
        for ($i = 0; $i < strlen($letters); $i++) {
            $letter = $letters[$i];
            if ($lastnamefilter == $letter) {
                $str .= '<div class="namefilter-selected">'.$letter.'</div>&nbsp';
            } else {
                $str .= '<a href="'.$thispageurl.'&filterlastname='.$letter.'" >'.$letter.'</a>&nbsp';
            }
        }
        if (!$lastnamefilter) {
            $str .= '<div class="namefilter-selected">'.get_string('all').'</div>&nbsp';
        } else {
            $str .= '<a href="'.$thispageurl.'&filterlastname=" >'.get_string('all').'</a>&nbsp';
        }

        $params = array();
        if ($firstnamefilter) {
            $params['filterfirstname'] = $firstnamefilter;
        }
        if ($lastnamefilter) {
            $params['filterlastname'] = $lastnamefilter;
        }
        $thispageurl->params();

        return $str;
    }
}