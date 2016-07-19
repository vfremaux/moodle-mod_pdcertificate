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
 * Instance add/edit form
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');

class mod_pdcertificate_mod_form extends moodleform_mod {

    var $instance;

    function definition() {
        global $CFG, $DB, $COURSE;

        $mform =& $this->_form;

        $this->instance = $DB->get_record('pdcertificate', array('id' => $this->_instance));

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('pdcertificatename', 'pdcertificate'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'caption', get_string('pdcertificatecaption', 'pdcertificate'), array('size' => 128, 'maxlength' => 255));
        $mform->setType('caption', PARAM_CLEANHTML);

        $this->add_intro_editor(false, get_string('intro', 'pdcertificate'));

        // Issue options
        $mform->addElement('header', 'issueoptions', get_string('issueoptions', 'pdcertificate'));
        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $mform->addElement('select', 'emailteachers', get_string('emailteachers', 'pdcertificate'), $ynoptions);
        $mform->setDefault('emailteachers', 0);
        $mform->addHelpButton('emailteachers', 'emailteachers', 'pdcertificate');

        $mform->addElement('text', 'emailothers', get_string('emailothers', 'pdcertificate'), array('size'=>'40', 'maxsize'=>'200'));
        $mform->setType('emailothers', PARAM_TEXT);
        $mform->addHelpButton('emailothers', 'emailothers', 'pdcertificate');

        $deliveryoptions = array( 0 => get_string('openbrowser', 'pdcertificate'), 1 => get_string('download', 'pdcertificate'), 2 => get_string('emailpdcertificate', 'pdcertificate'));
        $mform->addElement('select', 'delivery', get_string('delivery', 'pdcertificate'), $deliveryoptions);
        $mform->setDefault('delivery', 0);
        $mform->addHelpButton('delivery', 'delivery', 'pdcertificate');

        $mform->addElement('select', 'savecert', get_string('savecert', 'pdcertificate'), $ynoptions);
        $mform->setDefault('savecert', 0);
        $mform->addHelpButton('savecert', 'savecert', 'pdcertificate');

        $reportfile = "$CFG->dirroot/pdcertificates/index.php";
        if (file_exists($reportfile)) {
            $mform->addElement('select', 'reportcert', get_string('reportcert', 'pdcertificate'), $ynoptions);
            $mform->setDefault('reportcert', 0);
            $mform->addHelpButton('reportcert', 'reportcert', 'pdcertificate');
        }

        $this->linkablecourses = pdcertificate_get_linkable_courses($this->instance);
        $this->assignableroles = get_assignable_roles(context_course::instance($COURSE->id));

        $authorities = array();
        $authorities[0] = get_string('noauthority', 'pdcertificate');
        if ($authorities_candidates = get_users_by_capability(context_course::instance($COURSE->id), 'mod/pdcertificate:isauthority', 'u.id,'.get_all_user_name_fields(true, 'u'), 'lastname,firstname')){
            foreach ($authorities_candidates as $ac) {
                $authorities[$ac->id] = fullname($ac);
            }
        }

        $mform->addElement('select', 'certifierid', get_string('certifierid', 'pdcertificate'), $authorities);
        $mform->setDefault('setcertification', 0 + @$CFG->pdcertificate_certification_authority); // choose the default system designed
        $mform->addHelpButton('certifierid', 'certifierid', 'pdcertificate');

        $roleoptions = $this->assignableroles;
        $roleoptions['0'] = get_string('none', 'pdcertificate');
        ksort($roleoptions);
        $mform->addElement('select', 'setcertification',get_string('setcertification', 'pdcertificate'), $roleoptions);
        $mform->setDefault('setcertification', max(array_keys($roleoptions))); // choose the weaker role (further from admin role)
        $mform->addHelpButton('setcertification', 'setcertification', 'pdcertificate');

        $contextoptions = pdcertificate_get_possible_contexts();
        $mform->addElement('select', 'setcertificationcontext',get_string('setcertificationcontext', 'pdcertificate'), $contextoptions);
        $mform->setDefault('setcertificationcontext', max(array_keys($contextoptions))); // choose the weaker context
        $mform->addHelpButton('setcertification', 'setcertification', 'pdcertificate');

        $mform->addElement('checkbox', 'propagategroups', get_string('propagategroups', 'pdcertificate'));
        if (!empty($config->defaultpropagategroups)) {
            $mform->setDefault('propagategroups', 1);
        }
        $mform->addHelpButton('propagategroups', 'propagategroups', 'pdcertificate');

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'lockingoptions', get_string('lockingoptions', 'pdcertificate'));
        
        $this->restrictoptions = array();
        $this->restrictoptions[0]  = get_string('no');
        for ($i = 100; $i > 0; $i--) {
            $this->restrictoptions[$i] = $i.'%';
        }

        $mform->addElement('checkbox', 'locked', get_string('pdcertificatedefaultlock', 'pdcertificate'));
        $mform->addHelpButton('locked', 'pdcertificatelock', 'pdcertificate');

        $validityoptions = array(
            '0' => get_string('unlimited', 'pdcertificate'),
            '1' => get_string('oneday', 'pdcertificate'),
            '7' => get_string('oneweek', 'pdcertificate'),
            '30' => get_string('onemonth', 'pdcertificate'),
            '90' => get_string('threemonths', 'pdcertificate'),
            '180' => get_string('sixmonths', 'pdcertificate'),
            '365' => get_string('oneyear', 'pdcertificate'),
            '730' => get_string('twoyears', 'pdcertificate'),
            '1095' => get_string('threeyears', 'pdcertificate'),
            '1895' => get_string('fiveyears', 'pdcertificate'),
            '3650' => get_string('tenyears', 'pdcertificate'),
        );

        $mform->addElement('select', 'validitytime', get_string('validity', 'pdcertificate'), $validityoptions);
        $mform->setDefault('validitytime', 0);
        $mform->addHelpButton('validitytime', 'validitytime', 'pdcertificate');

        $completioninfo = new completion_info($COURSE);
        if ($completioninfo->is_enabled(null)) {
            $mform->addElement('checkbox', 'lockoncoursecompletion', get_string('lockoncoursecompletion', 'pdcertificate'));
            $mform->setDefault('lockoncoursecompletion', 0);
            $mform->addHelpButton('lockoncoursecompletion', 'lockoncoursecompletion', 'pdcertificate');
        }

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'coursechaining', get_string('coursechaining', 'pdcertificate'));

        $this->linkedcourses = pdcertificate_get_linked_courses($this->instance);

        $formgroup = array();
        $formgroup[] =& $mform->createElement('static', 'linkedcourselabel', 'Linked course', get_string('linkedcourse', 'pdcertificate').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $formgroup[] =& $mform->createElement('static', 'linkedcoursemandatory', 'Mandatory', get_string('mandatoryreq', 'pdcertificate').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $formgroup[] =& $mform->createElement('static', 'linkedcourserole', 'Role', get_string('rolereq', 'pdcertificate'));
        $mform->addGroup($formgroup, 'courselabel', get_string('coursedependencies', 'pdcertificate'), array(' '), false);
        $mform->addHelpButton('courselabel', 'chaining', 'pdcertificate');

/// The linked course portion goes here, but is forced in in the 'definition_after_data' function so that we can get any elements added in the form and not overwrite them with what's in the database.

        $mform->addElement('submit', 'addcourse', get_string('addcourselabel', 'pdcertificate'),
                           array('title' => get_string('addcoursetitle', 'pdcertificate')));
        $mform->registerNoSubmitButton('addcourse');

        // Text Options
        $mform->addElement('header', 'textoptions', get_string('printoptions', 'pdcertificate'));

        $dateformatoptions = array( 1 => 'January 1, 2000', 2 => 'January 1st, 2000', 3 => '1 January 2000',
            4 => 'January 2000', 5 => get_string('userdateformat', 'pdcertificate'));
        $mform->addElement('select', 'datefmt', get_string('datefmt', 'pdcertificate'), $dateformatoptions);
        $mform->setDefault('datefmt', 0);
        $mform->addHelpButton('datefmt', 'datefmt', 'pdcertificate');

        $gradeformatoptions = array( 1 => get_string('gradepercent', 'pdcertificate'), 2 => get_string('gradepoints', 'pdcertificate'),
            3 => get_string('gradeletter', 'pdcertificate'));
        $mform->addElement('select', 'gradefmt', get_string('gradefmt', 'pdcertificate'), $gradeformatoptions);
        $mform->setDefault('gradefmt', 0);
        $mform->addHelpButton('gradefmt', 'gradefmt', 'pdcertificate');

        $mform->addElement('text', 'printhours', get_string('printhours', 'pdcertificate'), array('size'=>'5', 'maxlength' => '255'));
        $mform->setType('printhours', PARAM_TEXT);
        $mform->addHelpButton('printhours', 'printhours', 'pdcertificate');

        $outcomeoptions = pdcertificate_get_outcomes();
        if ($outcomeoptions) {
            $mform->addElement('select', 'printoutcome', get_string('printoutcome', 'pdcertificate'), $outcomeoptions);
            $mform->setDefault('printoutcome', 0);
            $mform->addHelpButton('printoutcome', 'printoutcome', 'pdcertificate');
        } else {
            $mform->addElement('hidden', 'printoutcome', 0);
            $mform->setType('printoutcome', PARAM_INT);
        }

        $sizeoptions = array(9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20);
        $mform->addElement('select', 'fontbasesize', get_string('printfontsize', 'pdcertificate'), $sizeoptions);
        $mform->setDefault('fontbasesize', 12);

        $familyoptions = array('freesans' => get_string('freesans', 'pdcertificate'),
            'freeserif' => get_string('freeserif', 'pdcertificate'),
            'freemono' => get_string('freemono', 'pdcertificate')
        );
        $mform->addElement('select', 'fontbasefamily', get_string('printfontfamily', 'pdcertificate'), $familyoptions);
        $mform->setDefault('fontbasesize', 12);

        $mform->addElement('textarea', 'headertext', get_string('headertext', 'pdcertificate'), array('cols'=>'80', 'rows'=>'4', 'wrap'=>'virtual'));
        $mform->setType('headertext', PARAM_RAW);
        $mform->addHelpButton('headertext', 'headertext', 'pdcertificate');
        $mform->setDefault('headertext', get_string('defaultcertificateheader_tpl', 'pdcertificate'));

        $mform->addElement('textarea', 'customtext', get_string('customtext', 'pdcertificate'), array('cols'=>'80', 'rows'=>'20', 'wrap'=>'virtual'));
        $mform->setType('customtext', PARAM_RAW);
        $mform->addHelpButton('customtext', 'customtext', 'pdcertificate');
        $mform->setDefault('headertext', get_string('defaultcertificatebody_tpl', 'pdcertificate'));

        $mform->addElement('textarea', 'footertext', get_string('footertext', 'pdcertificate'), array('cols'=>'80', 'rows'=>'4', 'wrap'=>'virtual'));
        $mform->setType('footertext', PARAM_RAW);
        $mform->addHelpButton('footertext', 'footertext', 'pdcertificate');
        $mform->setDefault('footertext', get_string('defaultcertificatefooter_tpl', 'pdcertificate'));

        $mform->addElement('checkbox', 'printqrcode', get_string('printqrcode', 'pdcertificate'), 1);

        // this needs groupspecifichtml block installed for providing group addressed content
        if ($COURSE->groupmode != NOGROUPS && is_dir($CFG->dirroot.'/blocks/groupspecifichtml')) {
            $groupspecificoptions = pdcertificate_get_groupspecific_block_instances();
            $mform->addElement('select', 'groupspecificcontent', get_string('groupspecificcontent', 'pdcertificate'),$groupspecificoptions);
            $mform->setDefault('groupspecificcontent', 0);
            $mform->addHelpButton('groupspecificcontent', 'groupspecificcontent', 'pdcertificate');
        }

        // Design Options
        $mform->addElement('header', 'designoptions', get_string('designoptions', 'pdcertificate'));

        $mform->addElement('select', 'pdcertificatetype', get_string('pdcertificatetype', 'pdcertificate'), pdcertificate_types());
        $mform->setDefault('pdcertificatetype', 'A4_lanscape');
        $mform->addHelpButton('pdcertificatetype', 'pdcertificatetype', 'pdcertificate');

        $group = array();
        $group[] = $mform->createElement('filepicker', 'printborders', get_string('printborders', 'pdcertificate'), array('courseid' => $COURSE->id, 'accepted_types' => '.jpg'));
        $group[] = $mform->createElement('checkbox', 'clearprintborders', '', get_string('clearprintborders', 'pdcertificate'));
        $mform->addGroup($group, 'printbordersgroup', get_string('printborders', 'pdcertificate'), '', array(''), false);

        $group = array();
        $group[] = $mform->createElement('filepicker', 'printwmark', get_string('printwmark', 'pdcertificate'), array('courseid' => $COURSE->id, 'accepted_types' => '.jpg'));
        $group[] = $mform->createElement('checkbox', 'clearprintwmark', '', get_string('clearprintwmark', 'pdcertificate'));
        $mform->addGroup($group, 'printwmarkgroup', get_string('printwmark', 'pdcertificate'), '', array(''), false);

        $group = array();
        $group[] = $mform->createElement('text', 'watermarkoffsetx', '');
        $group[] = $mform->createElement('text', 'watermarkoffsety', '');
        $mform->addGroup($group, 'watermarkoffsetgroup', get_string('watermarkoffset', 'pdcertificate'), '', array(''), false);
        $mform->setType('watermarkoffsetgroup[watermarkoffsetx]', PARAM_INT);
        $mform->setType('watermarkoffsetgroup[watermarkoffsety]', PARAM_INT);

        $group = array();
        $group[] = $mform->createElement('filepicker', 'printsignature', get_string('printsignature', 'pdcertificate'), array('courseid' => $COURSE->id, 'accepted_types' => '.jpg'));
        $group[] = $mform->createElement('checkbox', 'clearprintsignature', '', get_string('clearprintsignature', 'pdcertificate'));
        $mform->addGroup($group, 'printsignaturegroup', get_string('printsignature', 'pdcertificate'), '', array(''), false);

        $group = array();
        $group[] = $mform->createElement('text', 'signatureoffsetx', '');
        $group[] = $mform->createElement('text', 'signatureoffsety', '');
        $mform->addGroup($group, 'signatureoffsetgroup', get_string('signatureoffset', 'pdcertificate'), '', array(''), false);
        $mform->setType('signatureoffsetgroup[signatureoffsetx]', PARAM_INT);
        $mform->setType('signatureoffsetgroup[signatureoffsety]', PARAM_INT);

        $group = array();
        $group[] = $mform->createElement('filepicker', 'printseal', get_string('printseal', 'pdcertificate'), array('courseid' => $COURSE->id, 'accepted_types' => '.xml'));
        $group[] = $mform->createElement('checkbox', 'clearprintseal', '', get_string('clearprintseal', 'pdcertificate'));
        $mform->addGroup($group, 'printsealgroup', get_string('printseal', 'pdcertificate'), '', array(''), false);

        $group = array();
        $group[] = $mform->createElement('text', 'sealoffsetx', '');
        $group[] = $mform->createElement('text', 'sealoffsety', '');
        $mform->addGroup($group, 'sealoffsetgroup', get_string('sealoffset', 'pdcertificate'), '', array(''), false);
        $mform->setType('sealoffsetgroup[sealoffsetx]', PARAM_INT);
        $mform->setType('sealoffsetgroup[sealoffsety]', PARAM_INT);

//-------------------------------------------------------------------------------

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

    }

    /**
     *
     */
    function set_data($defaults) {

        // Saves draft customization image files into definitive filearea.
        $instancefiles = array('printborders', 'printwmark', 'printseal', 'printsignature');

        // Extract print options and feed print defaults
        $printconfigarr = (array)unserialize(@$defaults->printconfig);
        foreach ($printconfigarr as $key => $value) {
            $defaults->$key = $value;
        }

        foreach($instancefiles as $if){
            $draftitemid = file_get_submitted_draft_itemid($if);
            $maxbytes = -1;
            $maxfiles = 1;
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_pdcertificate', $if, 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => $maxfiles));
            $groupname = $if.'group';
            $defaults->$groupname = array($if => $draftitemid);
        }

        parent::set_data($defaults);
    }

/**
 * Add the linked activities portion only after the entire form has been created. That way,
 * we can act on previous added values that haven't been committed to the database.
 * Check for an 'addlink' button. If the linked activities fields are all full, add an empty one.
 */
    function definition_after_data() {
        global $COURSE;

        // Start process core datas (conditions, etc.)..
        parent::definition_after_data();

        /// This gets called more than once, and there's no way to tell which time this is, so set a
        /// variable to make it as called so we only do this processing once.
        if (!empty($this->def_after_data_done)) {
            return;
        }
        $this->def_after_data_done = true;

        $mform    =& $this->_form;
        $fdata = $mform->getSubmitValues();

    /// Get the existing linked activities from the database, unless this form has resubmitted itself, in
    /// which case they will be in the form already.
        $linkids = array();
        $linkgrade = array();
        $linkentry = array();
        $courselinkids = array();
        $courselinkmandatory = array();
        $courselinkentry = array();
        $courselinkrole = array();
        
        if (empty($fdata)) {
            if ($linkedcourses = pdcertificate_get_linked_courses($this->instance)){
                foreach ($linkedcourses as $cidx => $linkedcourse) {
                    $courselinkids[$cidx] = $linkedcourse->courseid;
                    $courselinkmandatory[$cidx] = $linkedcourse->mandatory;
                    $courselinkrole[$cidx] = $linkedcourse->roletobegiven;
                    $courselinkentry[$cidx] = $linkedcourse->id;
                }
            }
        } else {
            foreach ($fdata['courselinkid'] as $cidx => $linkid) {
                $courselinkids[$cidx] = $linkid;
                $courselinkrole[$cidx] = @$fdata['courselinkrole'][$idx];
                $courselinkmandatory[$cidx] = @$fdata['courselinkmandatory'][$idx]; // checkbox may not emit any value
            }
        }

        $i = 1;
        foreach ($courselinkids as $cidx => $linkid) {
            $formgroup = array();
            $formgroup[] =& $mform->createElement('select', 'courselinkid['.$cidx.']', '', $this->linkablecourses);
            $mform->setDefault('courselinkid['.$cidx.']', $linkid);
            $formgroup[] =& $mform->createElement('checkbox', 'courselinkmandatory['.$cidx.']');
            $mform->setDefault('courselinkmandatory['.$cidx.']', $courselinkmandatory[$cidx]);
            $formgroup[] =& $mform->createElement('select', 'courselinkrole['.$cidx.']', '', $this->assignableroles);
            $mform->setDefault('courselinkrole['.$cidx.']', $courselinkrole[$cidx]);

            $group =& $mform->createElement('group', 'courselab'.$cidx, $i, $formgroup, array(' '), false);
            $mform->insertElementBefore($group, 'addcourse');
            if (!empty($courselinkentry[$cidx])) {
                $mform->addElement('hidden', 'courselinkentry['.$cidx.']', $courselinkentry[$cidx]);
            }
            $i++;
        }

        // add a blank pod marked as -n
        $numlcourses = count($courselinkids);
        $formgroup = array();
        $formgroup[] =& $mform->createElement('select', 'courselinkid['.$numlcourses.']', '', $this->linkablecourses);
        $mform->setDefault('courselinkid['.$numlcourses.']', 0);
        $formgroup[] =& $mform->createElement('checkbox', 'courselinkmandatory['.$numlcourses.']');
        $mform->setDefault('courselinkmandatory['.$numlcourses.']', '');
        $formgroup[] =& $mform->createElement('select', 'courselinkrole['.$numlcourses.']', '', $this->assignableroles);
        $mform->setDefault('courselinkrole['.$numlcourses.']', max(array_keys($this->assignableroles))); // for security, do not preassign too high level role
        $group =& $mform->createElement('group', 'courselab'.$numlcourses, ($numlcourses+1), $formgroup, array(' '), false);
        $mform->insertElementBefore($group, 'addcourse');
    }

    // here the pdcertificate will add is own extra rule to achieve itself.
    function add_completion_rules() {
        global $DB;

        $mform =& $this->_form;

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completiondelivered', '', get_string('completiondelivered', 'pdcertificate'));
        $mform->setType('completiondelivered', PARAM_INT);
        $mform->addGroup($group, 'completiondeliveredgroup', get_string('completiondeliveredgroup', 'pdcertificate'), array(' '), false);

        return array('completiondeliveredgroup');
   }

    function completion_rule_enabled($data) {
        return true;
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        // $default_values['completiondelivered'] = @$default_values['completiondelivered'];
    }
}