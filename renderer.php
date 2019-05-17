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

        $printconfig = json_decode(@$pdcertificate->printconfig);

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

        $template = new StdClass;

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $template->firstnamefiltered = optional_param('filterfirstname', false, PARAM_TEXT);
        $template->lastnamefiltered = optional_param('filterlastname', false, PARAM_TEXT);

        $template->firstnamestr = get_string('firstname');
        $template->lastnamestr = get_string('lastname');
        $template->allstr = get_string('all');

        for ($i = 0; $i < strlen($letters); $i++) {
            $lettertpl = new StdClass;
            $lettertpl->letter = $letters[$i];
            if ($template->firstnamefiltered != $lettertpl->letter) {
                $lettertpl->letterurl = $thispageurl.'&filterfirstname='.$lettertpl->letter.'&filterlastname='.$template->lastnamefiltered;
            }
            $template->fnletters[] = $lettertpl;
        }
        if (!empty($template->firstnamefiltered)) {
            $template->nofirstnamefilterurl = $thispageurl.'&filterfirstname=&filterlastname='.$lastnamefilter;
        }

        for ($i = 0; $i < strlen($letters); $i++) {
            $lettertpl = new StdClass;
            $lettertpl->letter = $letters[$i];
            if ($template->lastnamefiltered != $lettertpl->letter) {
                $lettertpl->letterurl = $thispageurl.'&filterfirstname='.$template->firstnamefiltered.'&filterlastname='.$lettertpl->letter;
            }
            $template->lnletters[] = $lettertpl;
        }
        if (!empty($template->lastnamefiltered)) {
            $template->nolastnamefilterurl = $thispageurl.'&filterfirstname='.$template->firstnamefiltered.'&filterlastname=';
        }

        /*
        $params = array();
        if ($firstnamefilter) {
            $params['filterfirstname'] = $firstnamefilter;
        }
        if ($lastnamefilter) {
            $params['filterlastname'] = $lastnamefilter;
        }
        $thispageurl->params();
        */

        return $this->output->render_from_template('mod_pdcertificate/namefilter', $template);
    }

    /**
     * Prints the assessor interface.
     * @param objectref &$table the table of users in current display page
     * @param object $states the global states about certification
     * @param string $baseurl the base url of the report screen
     * @param int $pagesize the current page size.
     */
    public function report_form(&$table, $cm, $state, $baseurl, $pagesize) {

        $baseurlunpaged = new moodle_url('/mod/pdcertificate/report.php', array('id' => $cm->id));

        $template = new StdClass;

        $template->baseurl = $baseurl;
        $template->cmid = $cm->id;
        $template->table = html_writer::table($table);

        if ($pagesize && ($pagesize < $state->totalcount)){
            $template->viewalladvicestr = get_string('viewalladvice', 'pdcertificate');
            $template->viewallurl = $baseurlunpaged.'&perpage=0';
            $template->viewallstr = get_string('viewall', 'pdcertificate');
        } else {
            $template->viewallurl = $baseurlunpaged;
            $template->viewallstr = get_string('viewless', 'pdcertificate');
        }

        if ($state->totalcount - $state->totalcertifiedcount > 0) {
            $template->makeallurl = $baseurlunpaged.'&what=generateall';
            $template->makeallstr = get_string('generateall', 'pdcertificate', $state->totalcount - $state->totalcertifiedcount - $state->notyetusers);
            $template->canmakeall = true;
        }

        $template->selector = '';
        if ($state->selectionrequired) {
            $selector = get_string('withsel', 'pdcertificate');
            $cmdoptions = array('delete' => get_string('destroyselection', 'pdcertificate'),
                                'generate' => get_string('generateselection', 'pdcertificate'));
            $attrs = array('onchange' => 'document.forms.controller.submit();');
            $template->selector .= html_writer::select($cmdoptions, 'what', null, array('choosedots' => get_string('withselection', 'pdcertificate')), $attrs, '', true);
        }

        return $this->output->render_from_template('mod_pdcertificate/report_form', $template);
    }

    /**
     * Renders a template by string with the given context.
     *
     * The provided data needs to be array/stdClass made up of only simple types.
     * Simple types are array,stdClass,bool,int,float,string
     *
     * @since 2.9
     * @param array|stdClass $context Context containing data for the template.
     * @return string|boolean
     */
    public function render_from_string($templatestring, $context) {

        $mustache = $this->get_mustache();
        $loader = new Mustache_Loader_StringLoader();
        $mustache->setLoader($loader);

        try {
            // Grab a copy of the existing helper to be restored later.
            $uniqidhelper = $mustache->getHelper('uniqid');
        } catch (Mustache_Exception_UnknownHelperException $e) {
            // Helper doesn't exist.
            $uniqidhelper = null;
        }

        // Provide 1 random value that will not change within a template
        // but will be different from template to template. This is useful for
        // e.g. aria attributes that only work with id attributes and must be
        // unique in a page.
        $mustache->addHelper('uniqid', new \core\output\mustache_uniqid_helper());

        $renderedtemplate = $mustache->render($templatestring, $context);

        // If we had an existing uniqid helper then we need to restore it to allow
        // handle nested calls of render_from_template.
        if ($uniqidhelper) {
            $mustache->addHelper('uniqid', $uniqidhelper);
        }

        return $renderedtemplate;
    }
}