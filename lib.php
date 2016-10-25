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
 * Certificate module core interaction API
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
require_once($CFG->dirroot.'/lib/conditionlib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/printlib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');

/** The border image folder */
define('PDCERT_IMAGE_BORDER', 'borders');
/** The watermark image folder */
define('PDCERT_IMAGE_WATERMARK', 'watermarks');
/** The signature image folder */
define('PDCERT_IMAGE_SIGNATURE', 'signatures');
/** The seal image folder */
define('PDCERT_IMAGE_SEAL', 'seals');

define('PDCERT_PER_PAGE', 30);
define('PDCERT_MAX_PER_PAGE', 200);

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function pdcertificate_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


/**
 * Add pdcertificate instance.
 *
 * @param stdClass $pdcertificate
 * @return int new pdcertificate instance id
 */
function pdcertificate_add_instance($pdcertificate) {
    global $DB;

    // Create the pdcertificate.
    $pdcertificate->timecreated = time();
    $pdcertificate->timemodified = $pdcertificate->timecreated;

    if (empty($pdcertificate->lockoncoursecompletion)) {
        $pdcertificate->lockoncoursecompletion = 0;
    }

    // Compact print options
    $printconfig = new StdClass;
    $printconfig->printhours = $pdcertificate->printhours;
    $printconfig->printoutcome = $pdcertificate->printoutcome;
    $printconfig->printqrcode = @$pdcertificate->printqrcode;
    $printconfig->fontbasesize = $pdcertificate->fontbasesize;
    $printconfig->fontbasefamily = $pdcertificate->fontbasefamily;

    $pdcertificate->printconfig = serialize($printconfig);

    $pdcertificate->id = $DB->insert_record('pdcertificate', $pdcertificate);

    if (isset($pdcertificate->courselinkid) and is_array($pdcertificate->courselinkid)) {
        foreach ($pdcertificate->courselinkid as $key => $linkid) {
            if ($linkid > 0) {
                $clm = new StdClass();
                $clm->pdcertificateid = $pdcertificate->id;
                $clm->courseid = $linkid;
                $clm->mandatory = 0 + @$pdcertificate->courselinkmandatory[$key];
                $clm->roletobegiven = $pdcertificate->courselinkrole[$key];
                // $clm->timemodified = $pdcertificate->timemodified;
                $retval = $DB->insert_record('pdcertificate_linked_courses', $clm) and $retval;
            }
        }
    }

    // Saves pdcertificate images.
    if (isset($pdcertificate->coursemodule)) {
        // Allow not processing files when migrating.
        $context = context_module::instance($pdcertificate->coursemodule);
        $instancefiles = array('printborders', 'printwmark', 'printseal', 'printsignature');
    
        foreach ($instancefiles as $if) {
            $draftitemid = 0 + @$pdcertificate->$if;
            file_save_draft_area_files($draftitemid, $context->id, 'mod_pdcertificate', $if, 0);
        }
    }

    return $pdcertificate->id;
}

/**
 * Update pdcertificate instance.
 *
 * @param stdClass $pdcertificate
 * @return bool true
 */
function pdcertificate_update_instance($pdcertificate) {
    global $DB;


    $pdcertificate->courselinkentry = @$_REQUEST['courselinkentry']; // again this weird situation
    // of Quickform loosing params on form bounces

    if (empty($pdcertificate->lockoncoursecompletion)) {
        $pdcertificate->lockoncoursecompletion = 0;
    }

    // Update the pdcertificate.
    $pdcertificate->timemodified = time();
    $pdcertificate->id = $pdcertificate->instance;

    if (isset($pdcertificate->courselinkid) and is_array($pdcertificate->courselinkid)) {
        foreach ($pdcertificate->courselinkid as $key => $linkid) {
            if (isset($pdcertificate->courselinkentry[$key])) {
                if ($linkid > 0) {
                    $clc = new StdClass;
                    $clc->id = $pdcertificate->courselinkentry[$key];
                    $clc->pdcertificateid = $pdcertificate->id;
                    $clc->courseid = $linkid;
                    $clc->mandatory = 0 + @$pdcertificate->courselinkmandatory[$key];
                    $clc->roletobegiven = $pdcertificate->courselinkrole[$key];
                    // $clm->timemodified = $pdcertificate->timemodified;
                    $retval = $DB->update_record('pdcertificate_linked_courses', $clc) and $retval;
                } else {
                    $retval = $DB->delete_records('pdcertificate_linked_courses', array('id' => $pdcertificate->courselinkentry[$key])) and $retval;
                }
            } else if ($linkid > 0) {
                $clc = new StdClass;
                $clc->pdcertificateid = $pdcertificate->id;
                $clc->courseid = $linkid;
                $clc->mandatory = 0 + @$pdcertificate->courselinkmandatory[$key];
                $clc->roletobegiven = $pdcertificate->courselinkrole[$key];
                // $clc->timemodified = $pdcertificate->timemodified;
                if (!$oldone = $DB->get_record('pdcertificate_linked_courses', array('courseid' => $linkid, 'pdcertificateid' => $pdcertificate->id))){
                    $retval = $DB->insert_record('pdcertificate_linked_courses', $clc) and $retval;
                } else {
                    $clc->id = $oldone->id;
                    $DB->update_record('pdcertificate_linked_courses', $clc);
                }
            }
        }
    }

    // compact print options
    $printconfig = new StdClass;
    $printconfig->printhours = $pdcertificate->printhours;
    $printconfig->printoutcome = $pdcertificate->printoutcome;
    $printconfig->printqrcode = 0 + @$pdcertificate->printqrcode;
    $printconfig->fontbasesize = $pdcertificate->fontbasesize;
    $printconfig->fontbasefamily = $pdcertificate->fontbasefamily;

    $pdcertificate->printconfig = serialize($printconfig);

    // Saves pdcertificate images.
    $context = context_module::instance($pdcertificate->coursemodule);
    $instancefiles = array('printborders', 'printwmark', 'printseal', 'printsignature');

    $fs = get_file_storage();

    foreach ($instancefiles as $if) {
        $groupname = $if.'group';
        $draftidarr = (array) $pdcertificate->$groupname;
        $draftitemid = $draftidarr[$if];
        $clearif = 'clear'.$if;
        if (!empty($draftidarr[$clearif])) {
            // Delete existing zone
            $fs->delete_area_files($context->id, 'mod_pdcertificate', $if, 0);
        } else {
            file_save_draft_area_files($draftitemid, $context->id, 'mod_pdcertificate', $if, 0);
        }
    }

    return $DB->update_record('pdcertificate', $pdcertificate);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 * @return bool true if successful
 */
function pdcertificate_delete_instance($id) {
    global $DB;

    // Ensure the pdcertificate exists
    if (!$pdcertificate = $DB->get_record('pdcertificate', array('id' => $id))) {
        return false;
    }

    // Prepare file record object
    if (!$cm = get_coursemodule_from_instance('pdcertificate', $id)) {
        return false;
    }

    $result = true;
    $DB->delete_records('pdcertificate_issues', array('pdcertificateid' => $id));
    if (!$DB->delete_records('pdcertificate', array('id' => $id))) {
        $result = false;
    }

    // Delete any files associated with the pdcertificate
    $context = context_module::instance($cm->id);
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    return $result;
}

/**
 * This function makes a last post process of the cminfo information
 * for module info caching in memory when course displays. Here we
 * can tweek some information to force cminfo behave like some label kind
 * @see : Page format use the pageitem.php strategy for dealing with the 
 * content display rules.
 * @todo : reevaluate strategy. this may still be used for improving standard formats.
 */
function pdcertificate_cm_info_dynamic(&$cminfo) {
    global $DB, $PAGE, $CFG, $COURSE, $USER;

    // Apply role restriction here.
    if ($pdcertificate = $DB->get_record('pdcertificate', array('id' => $cminfo->instance))) {
        if ($pdcertificate->lockoncoursecompletion && !has_capability('mod/pdcertificate:manage', $cminfo->context)) {
            $completioninfo = new completion_info($COURSE);
            if (!$completioninfo->is_course_complete($USER->id)) {
                $cminfo->set_no_view_link();
                $cminfo->set_content('');
                $cminfo->set_user_visible(false);
                return;
            }
        }
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all posts from the specified pdcertificate
 * and clean up any related data.
 *
 * Written by Jean-Michel Vedrine
 *
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function pdcertificate_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'pdcertificate');
    $status = array();

    if (!empty($data->reset_pdcertificate)) {
        $sql = "SELECT cert.id
                FROM {pdcertificate} cert
                WHERE cert.course = :courseid";
        $DB->delete_records_select('pdcertificate_issues', "pdcertificateid IN ($sql)", array('courseid' => $data->courseid));
        $status[] = array('component' => $componentstr, 'item' => get_string('pdcertificateremoved', 'pdcertificate'), 'error' => false);
    }

    // Updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('pdcertificate', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);
    }

    return $status;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the pdcertificate.
 *
 * Written by Jean-Michel Vedrine
 *
 * @param $mform form passed by reference
 */
function pdcertificate_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'pdcertificateheader', get_string('modulenameplural', 'pdcertificate'));
    $mform->addElement('advcheckbox', 'reset_pdcertificate', get_string('deletissuedpdcertificates', 'pdcertificate'));
}

/**
 * Course reset form defaults.
 *
 * Written by Jean-Michel Vedrine
 *
 * @param stdClass $course
 * @return array
 */
function pdcertificate_reset_course_form_defaults($course) {
    return array('reset_pdcertificate' => 1);
}

/**
 * Returns information about received pdcertificate.
 * Used for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $pdcertificate
 * @return stdClass the user outline object
 */
function pdcertificate_user_outline($course, $user, $mod, $pdcertificate) {
    global $DB;

    $result = new stdClass;
    if ($issue = $DB->get_record('pdcertificate_issues', array('pdcertificateid' => $pdcertificate->id, 'userid' => $user->id))) {
        $result->info = get_string('issued', 'pdcertificate');
        $result->time = $issue->timecreated;
    } else {
        $result->info = get_string('notissued', 'pdcertificate');
    }

    return $result;
}

/**
 * Returns information about received pdcertificate.
 * Used for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $page
 * @return string the user complete information
 */
function pdcertificate_user_complete($course, $user, $mod, $pdcertificate) {
   global $DB, $OUTPUT;

   if ($issue = $DB->get_record('pdcertificate_issues', array('pdcertificateid' => $pdcertificate->id, 'userid' => $user->id))) {
        echo $OUTPUT->box_start();
        echo get_string('issued', 'pdcertificate') . ": ";
        echo userdate($issue->timecreated);
        pdcertificate_print_user_files($pdcertificate->id, $user->id);
        echo '<br />';
        echo $OUTPUT->box_end();
    } else {
        print_string('notissuedyet', 'pdcertificate');
    }
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of pdcertificate.
 *
 * @param int $pdcertificateid
 * @return stdClass list of participants
 */
function pdcertificate_get_participants($pdcertificateid) {
    global $DB;

    $sql = "SELECT DISTINCT u.id, u.id
            FROM {user} u, {pdcertificate_issues} a
            WHERE a.pdcertificateid = :pdcertificateid
            AND u.id = a.userid";
    return  $DB->get_records_sql($sql, array('pdcertificateid' => $pdcertificateid));
}

/**
 * Returns a list of teachers by group
 * for sending email alerts to teachers
 *
 * @param stdClass $pdcertificate
 * @param stdClass $user
 * @param stdClass $course
 * @param stdClass $cm
 * @return array the teacher array
 */
function pdcertificate_get_teachers($pdcertificate, $user, $course, $cm) {
    global $USER, $DB;

    $context = context_module::instance($cm->id);
    $potteachers = get_users_by_capability($context, 'mod/pdcertificate:manage', '', '', '', '', '', '', false, false);
    if (empty($potteachers)) {
        return array();
    }
    $teachers = array();
    if (groups_get_activity_groupmode($cm, $course) == SEPARATEGROUPS) {   // Separate groups are being used
        if ($groups = groups_get_all_groups($course->id, $user->id)) {  // Try to find all groups
            foreach ($groups as $group) {
                foreach ($potteachers as $t) {
                    if ($t->id == $user->id) {
                        continue; // do not send self
                    }
                    if (groups_is_member($group->id, $t->id)) {
                        $teachers[$t->id] = $t;
                    }
                }
            }
        } else {
            // user not in group, try to find teachers without group
            foreach ($potteachers as $t) {
                if ($t->id == $USER->id) {
                    continue; // do not send self
                }
                if (!groups_get_all_groups($course->id, $t->id)) { //ugly hack
                    $teachers[$t->id] = $t;
                }
            }
        }
    } else {
        foreach ($potteachers as $t) {
            if ($t->id == $USER->id) {
                continue; // do not send self
            }
            $teachers[$t->id] = $t;
        }
    }

    return $teachers;
}

/**
 * Serves pdcertificate issues and other files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool|nothing false if file not found, does not return anything if found - just send the file
 */
function pdcertificate_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if (!$pdcertificate = $DB->get_record('pdcertificate', array('id' => $cm->instance))) {
        return false;
    }

    require_login($course, false, $cm);

    require_once($CFG->libdir.'/filelib.php');

    $fs = get_file_storage();

    if ($filearea === 'issue') {
        $certrecord = (int)array_shift($args);

        if (!$certrecord = $DB->get_record('pdcertificate_issues', array('id' => $certrecord))) {
            return false;
        }

        if ($USER->id != $certrecord->userid and !has_capability('mod/pdcertificate:manage', $context)) {
            return false;
        }

        $relativepath = implode('/', $args);
        $fullpath = "/{$context->id}/mod_pdcertificate/issue/$certrecord->id/$relativepath";

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
        send_stored_file($file, 0, 0, true); // download MUST be forced - security!
    } else {
        if (!in_array($filearea, array('printseal', 'printborders', 'printwatermark', 'printsignature'))) {
            return false;
        }

        $relativepath = implode('/', $args);
        $fullpath = "/{$context->id}/mod_pdcertificate/{$filearea}{$certrecord->id}/$relativepath";

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }
        send_stored_file($file, 0, 0, true); // download MUST be forced - security!
    }
}

/**
 * Used for course participation report (in case pdcertificate is added).
 *
 * @return array
 */
function pdcertificate_get_view_actions() {
    return array('view', 'view all', 'view report');
}

/**
 * Used for course participation report (in case pdcertificate is added).
 *
 * @return array
 */
function pdcertificate_get_post_actions() {
    return array('received');
}

/**
 * Prepare to print an activity grade.
 *
 * @param stdClass $course
 * @param int $moduleid
 * @param int $userid
 * @return stdClass|bool return the mod object if it exists, false otherwise
 */
function pdcertificate_get_mod_grade($course, $moduleid, $userid) {
    global $DB;

    $cm = $DB->get_record('course_modules', array('id' => $moduleid));
    $module = $DB->get_record('modules', array('id' => $cm->module));

    if ($grade_item = grade_get_grades($course->id, 'mod', $module->name, $cm->instance, $userid)) {
        $item = new grade_item();
        $itemproperties = reset($grade_item->items);
        foreach ($itemproperties as $key => $value) {
            $item->$key = $value;
        }
        $modinfo = new stdClass;
        $modinfo->name = utf8_decode($DB->get_field($module->name, 'name', array('id' => $cm->instance)));
        $grade = $item->grades[$userid]->grade;
        $item->gradetype = GRADE_TYPE_VALUE;
        $item->courseid = $course->id;

        $modinfo->points = grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 2);
        $modinfo->percentage = grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, $decimals = 2);
        $modinfo->letter = grade_format_gradevalue($grade, $item, true, GRADE_DISPLAY_TYPE_LETTER, $decimals = 0);

        if ($grade) {
            $modinfo->dategraded = $item->grades[$userid]->dategraded;
        } else {
            $modinfo->dategraded = time();
        }
        return $modinfo;
    }

    return false;
}

/**
 * Obtains the automatic completion state for this pdcertificate 
 *
 * @global object
 * @global object
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not. (If no conditions, then return
 *   value depends on comparison type)
 */
function pdcertificate_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get pdcertificate details
    if (!($pdcertificate = $DB->get_record('pdcertificate', array('id' => $cm->instance)))) {
        throw new Exception("Can't find pdcertificate {$cm->instance}");
    }

    $result = $type; // Default return value
    
    // completion condition 1 : being delivered to user

    if ($pdcertificate->completiondelivered) {
    }

    return $result;
}

function pdcertificate_get_string($identifier, $subplugin, $a = '', $lang = ''){
    global $CFG;
    
    static $typestrings = array();
    
    if (empty($typestrings[$subplugin])){
    
        if (empty($lang)) $lang = current_language();
        
        if (file_exists($CFG->dirroot.'/mod/pdcertificate/type/'.$subplugin.'/lang/en/'.$subplugin.'.php')){
            include $CFG->dirroot.'/mod/pdcertificate/type/'.$subplugin.'/lang/en/'.$subplugin.'.php';
        } else {
            debugging('English lang file must exist', DEBUG_DEVELOPER);
        }
    
        // override with lang file if exists
        if (file_exists($CFG->dirroot.'/mod/pdcertificate/type/'.$subplugin.'/lang/'.$lang.'/'.$subplugin.'.php')){
            include $CFG->dirroot.'/mod/pdcertificate/type/'.$subplugin.'/lang/'.$lang.'/'.$subplugin.'.php';
        }
        $typestrings[$subplugin] = $string;
    }
    
    if (array_key_exists($identifier, $typestrings[$subplugin])){
        $result = $typestrings[$subplugin][$identifier];
        if ($a !== NULL) {
            if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $search = array();
                $replace = array();
                foreach ($a as $key => $value) {
                    if (is_int($key)) {
                        // we do not support numeric keys - sorry!
                        continue;
                    }
                    $search[]  = '{$a->'.$key.'}';
                    $replace[] = (string)$value;
                }
                if ($search) {
                    $result = str_replace($search, $replace, $result);
                }
            } else {
                $result = str_replace('{$a}', (string)$a, $result);
            }
        }
        // Debugging feature lets you display string identifier and component
        if (!empty($CFG->debugstringids) || optional_param('strings', 0, PARAM_INT)) {
            $result .= ' {' . $identifier . '/' . $subplugin . '}';
        }
        return $result;
    }

    if (!empty($CFG->debugstringids) && optional_param('strings', 0, PARAM_INT)) {
        return "[[$identifier/$subplugin]]";
    } else {
        return "[[$identifier]]";
    }
}
