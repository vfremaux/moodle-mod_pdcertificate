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
}