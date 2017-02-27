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
 * upgrade processes for this module.
 *
 * @package     mod_pdcertificate
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2016 Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/externallib.php');
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');

class mod_pdcertificate_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificates_parameters() {
        return new external_function_parameters(
            array(
                'cidsource' => new external_value(PARAM_ALPHA, 'source for the course id, can be either \'id\', \'shortname\' or \'idnumber\''),
                'cid' => new external_value(PARAM_TEXT, 'Resource id'),
            )
        );
    }

    /**
     * Get all certificates from a course
     *
     * @param string $cidsource the source field for the course identifier.
     * @param string $cid the courseid id. If 0, will get all the certificates of the site
     *
     * @return external_description
     */
    public static function get_certificates($cidsource, $cid) {
        global $CFG, $DB;

        $parameters = array(
            'cidsource'  => $cidsource,
            'cid'  => $cid
        );
        $validparams = self::validate_parameters(self::commit_version_parameters(), $parameters);

        $courses = array();

        $params = array();
        $courseclause = '';
        if ($cid) {

            switch ($cidsource) {
                case 'shortname':
                    $field = 'shortname';
                    break;
                case 'idnumber':
                    $field = 'idnumber';
                    break;
                case 'id':
                    $field = 'id';
                    break;
            }

            $courseclause = ' course = ? ';
            $params[] = $course->id;
        }

        $pdcs = $DB->get_records_select('pdcertificate', $courseclause, $params);

        if ($pdcs) {
            foreach ($pdcs as $pdc) {
                $cm = get_coursemodule_from_instance('pdcertificate', $pdc->id);

                if (array_key_exists($pdc->course, $courses)) {
                    if (!$course = $DB->get_record('course', array('id' => $pdc->course))) {
                        throw new moodle_exception('coursemisconf');
                    }
                    $courses[$pdc->course] = $course;
                }

                $pdcout = new Stdclass();
                $pdcout->id = $pdc->id;
                $pdcout->courseid = $pdc->course;
                $pdcout->courseidnumber = $courses[$pdc->course]->idnumber;
                $pdcout->courseshortname = $courses[$pdc->course]->shortname;
                $pdcout->coursefullname = $courses[$pdc->course]->fullname;
                $pdcout->name = $pdc->name;
                $pdcout->idnumber = $cm->idnumber;

                if ($pdc->certifierid) {
                    $authority = $DB->get_record('user', array('id' => $pdc->certifierid));
                    $pdcout->certifier = fullname($authority);
                } else {
                    $pdcout->certifier = '';
                }

                $results[] = $pdc;
            }
        }

        return $results;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificates_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'PDCertificate id'),
                    'courseid' => new external_value(PARAM_INT, 'Course id'),
                    'courseidnumber' => new external_value(PARAM_TEXT, 'Course idnumber'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Course shortname'),
                    'coursefullname' => new external_value(PARAM_TEXT, 'Course fullname'),
                    'name' => new external_value(PARAM_TEXT, 'PDCertificate name'),
                    'idnumber' => new external_value(PARAM_INT, 'PDCertificate ID Number'),
                    'certifier' => new external_value(PARAM_TEXT, 'Authority person')
                )
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificate_file_url_parameters() {
        return new external_function_parameters(
            array(
                'pdcidsource'  => new external_value(PARAM_ALPHA, 'Source for pdcertificate identifier, can be \'id\' or \'idnumber\''),
                'pdcid'  => new external_value(PARAM_TEXT, 'Version resource id'),
                'useridsource'  => new external_value(PARAM_TEXT, 'Source of user identifier, can be in set id,idnumber,email,username '),
                'userid'  => new external_value(PARAM_TEXT, 'Version resource id')
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param int $versionid
     * @return array of course objects and warnings
     * @throws moodle_exception
     */
    public static function get_certificate_file_url($pdcidsource, $pdcid, $useridsource, $userid) {
        global $DB, $CFG;

        $parameters = array(
            'pdcidsource'  => $pdcidsource,
            'pdcid'  => $pdcid,
            'useridsource'  => $useridsource,
            'userid'  => $userid,
        );
        $params = self::validate_parameters(self::get_certificate_file_url_parameters(), $parameters);

        list($pdc, $user) = get_objects($pdcidsource, $pdcid, $useridsource, $userid);

        if (!$cm = get_coursemodule_from_instance('pdcertificate', $version->pdcertificateid)) {
            throw new moodle_exception('badcoursemodule');
        }
        $context = context_module::instance($cm->id);
        $urlbase = "$CFG->httpswwwroot/webservice/pluginfile.php";
        $context = context_user::instance($USER->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'issue', $version->id, 'itemid, filepath', false);
        if ($files) {
            // Should be only one.
            $file = array_pop($files);

            $filepath = $file->get_filepath();
            $filename = $file->get_filename();
            return self::make_file_url($urlbase, "/{$context->id}/mod_pdcertificate/issue/{$version->id}".$filepath.$filename, true);
        }

        throw new moodle_exception('missingfile');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificate_file_url_returns() {
        return new external_value(PARAM_URL, 'An url to the file');
    }

    // Get last branch file url ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificate_info_parameters() {
        return new external_function_parameters(
            array(
                'pdcidsource'  => new external_value(PARAM_ALPHA, 'Source for pdcertificate identifier, can be \'id\' or \'idnumber\''),
                'pdcid'  => new external_value(PARAM_TEXT, 'Version resource id'),
                'useridsource'  => new external_value(PARAM_TEXT, 'Source of user identifier, can be in set id,idnumber,email,username '),
                'userid'  => new external_value(PARAM_TEXT, 'Version resource id')
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param string $pdcidsource
     * @param string $pdcid
     * @return array of course objects and warnings
     * @throws moodle_exception
     */
    public static function get_certificate_info($pdcidsource, $pdcid, $useridsource, $userid) {
        global $DB;

        $parameters = array(
            'pdcidsource'  => $pdcidsource,
            'pdcid'  => $pdid,
            'useridsource'  => $useridsource,
            'userid' => $userid
        );
        $params = self::validate_parameters(self::get_last_branch_file_url_parameters(), $parameters);

        list($pdc, $user) = self::get_objects();

        $cm = get_coursemodule_from_instance('pdcertificate', $pdc->id);

        // Search issue for user

        $params = array('pdcertificateid' => $pd->id, 'userid' => $user->id);
        if ($issue = $DB->get_record('pdcertificate_issues', $params)) {
            $issueout = new StdClass();
            $issueout->id = $issue->id;
            $issueout->certid = $pdc->id;
            $issueout->certname = $pdc->name;
            $issueout->certidnumber = $cm->idnumber;
            $issueout->user = fullname($user);
            $issueout->useridnumber = $user->idnumber;
            $issueout->issuecode = $issue->code;
            $issueout->timecreated = $issue->timecreated;
            $issueout->timedelivered = $issue->timedelivered;
            $issueout->validitytime = $pdc->validitytime;
            $issueout->locked = $issue->locked;

            if ($issue->authorityid) {
                $authority = $DB->get_record('user', array('id' => $issue->authorityid));
                $issueout->authority = fullname($authority);
            } else {
                $issueout->authority = '';
            }

            return $issueout;
        }

        throw new moodle_exception('notissued');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificate_info_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Issue id'),
                    'certid' => new external_value(PARAM_INT, 'PD Certificate id'),
                    'certname' => new external_value(PARAM_TEXT, 'PDCertificate name'),
                    'certidnumber' => new external_value(PARAM_INT, 'PDCertificate ID Number'),
                    'user' => new external_value(PARAM_TEXT, 'Appliant identity'),
                    'useridnumber' => new external_value(PARAM_TEXT, 'Appliant IDnumber'),
                    'issuecode' => new external_value(PARAM_TEXT, 'Numeric unique code'),
                    'timecreated' => new external_value(PARAM_INT, 'Issue id'),
                    'timedelivered' => new external_value(PARAM_INT, 'Time of delivery'),
                    'locked' => new external_value(PARAM_BOOL, 'Is certificate locked?'),
                    'authority' => new external_value(PARAM_TEXT, 'Authority person')
                )
            )
        );
    }

    // Get last branch file url ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificate_infos_parameters() {
        return new external_function_parameters(
            array(
                'pdcidsource' => new external_value(PARAM_ALPHA, 'source for the id, can be either \'id\' or \'idnumber\''),
                'pdcid' => new external_value(PARAM_TEXT, 'Resource id'),
            )
        );
    }

    /**
     * Commits the version that has ben previously uploaded using the webservice/upload.php facility.
     *
     * @param string $vridsource the source field for the resource identifier.
     * @param string $vrid the pdcertificate id
     * @param int $draftitemid the temporary draft id of the uploaded file. This has been given by the upload return.
     *
     * @return external_description
     */
    public static function get_certificate_infos($vridsource, $vrid, $draftitemid, $jsoninfo) {
        global $CFG;

        $parameters = array(
            'vridsource'  => $vridsource,
            'vrid'  => $vrid,
            'draftitemid'  => $draftitemid,
            'jsoninfo'  => $jsoninfo
        );
        $params = self::validate_parameters(self::commit_version_parameters(), $parameters);

        if (versionned_resource::plugin_supports('api/commit')) {
            include_once($CFG->dirroot.'/mod/pdcertificate/pro/lib.php');
            $vid = mod_versionned_resource_commit($vridsource, $vrid, $draftitemid, $jsoninfo);
            return $vid;
        } else {
            throw new moodle_exception('unsupportedinversion');
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificate_infos_returns() {
        return new external_value(PARAM_INT, 'Version id');
    }

    // Internal APIs -------------------------------------------------------------------.

    protected function get_objects($pdcidsource, $pdcid, $useridsource, $userid) {
        global $DB;

        // Explicit mapping avoids injection.
        switch ($pdcidsource) {
            case 'idnumber':
                $field = 'idnumber';
                break;
            default:
                $field = 'id';
                break;
        }

        if (!$pdc = $DB->get_record('pdcertificate', array($field => $pdcid))) {
            throw new moodle_exception('missingmodule');
        }

        // Explicit mapping avoids injection.
        switch ($useridsource) {
            case 'username':
                $field = 'username';
                break;
            case 'email':
                $field = 'email';
                break;
            case 'idnumber':
                $field = 'idnumber';
                break;
            default:
                $field = 'id';
                break;
        }

        if (!$user = $DB->get_record('user', array($field => $userid))) {
            throw new moodle_exception('missinguser');
        }

        return array($pdc, $user);
    }
}
