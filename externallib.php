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
                'cidsource' => new external_value(PARAM_ALPHA, 'source for the course id, can be either \'id\', \'shortname\' or \'idnumber\'', VALUE_DEFAULT, 'id'),
                'cid' => new external_value(PARAM_TEXT, 'Resource id', VALUE_DEFAULT, 0),
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
        $validparams = self::validate_parameters(self::get_certificates_parameters(), $parameters);

        // Make a non blocking call.
        $course = self::get_course($cidsource, $cid, false);

        $params = array();
        $courses = array(); // A cache for courses.

        $courseclause = '';
        if (!empty($course)) {
            $courseclause = ' course = ? ';
            $params = array($course->id);
        }

        $pdcs = $DB->get_records_select('pdcertificate', $courseclause, $params);

        $results = array();

        if ($pdcs) {
            foreach ($pdcs as $pdc) {
                $cm = get_coursemodule_from_instance('pdcertificate', $pdc->id);

                if (!array_key_exists($pdc->course, $courses)) {
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
                $pdcout->cmid = $cm->id;
                $pdcout->pdcid = $cm->instance;
                $pdcout->pdcidnumber = $cm->idnumber;

                if ($pdc->certifierid) {
                    $authority = $DB->get_record('user', array('id' => $pdc->certifierid));
                    $pdcout->certifier = fullname($authority);
                } else {
                    $pdcout->certifier = '';
                }

                $results[] = $pdcout;
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
                    'cmid' => new external_value(PARAM_INT, 'PDCertificate course module ID'),
                    'pdcid' => new external_value(PARAM_INT, 'PDCertificate primary ID'),
                    'pdcidnumber' => new external_value(PARAM_TEXT, 'PDCertificate ID Number'),
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
                'pdcidsource' => new external_value(PARAM_ALPHA, 'Source for pdcertificate identifier, can be \'id\', \'cmid\' or \'idnumber\''),
                'pdcid'  => new external_value(PARAM_TEXT, 'PDCertificate id'),
                'uidsource' => new external_value(PARAM_TEXT, 'Source of user identifier, can be in set id,idnumber,email,username '),
                'uid'  => new external_value(PARAM_TEXT, 'The user id value')
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
    public static function get_certificate_file_url($pdcidsource, $pdcid, $uidsource, $uid) {
        global $DB, $CFG;

        $parameters = array(
            'pdcidsource'  => $pdcidsource,
            'pdcid'  => $pdcid,
            'uidsource'  => $uidsource,
            'uid'  => $uid,
        );

        $params = self::validate_parameters(self::get_certificate_file_url_parameters(), $parameters);

        list($pdc, $cm, $user) = self::get_objects($pdcidsource, $pdcid, $uidsource, $uid);

        $context = context_module::instance($cm->id);

        $params = array('userid' => $user->id, 'pdcertificateid' => $pdc->id);
        $issue = $DB->get_record('pdcertificate_issues', $params);

        if (!$issue) {
            throw new invalid_response_exception('Not certified');
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'issue', $issue->id, 'itemid, filepath', false);
        if ($files) {
            // Should be only one.
            $file = array_pop($files);

            $filepath = $file->get_filepath();
            $filename = $file->get_filename();

            return moodle_url::make_webservice_pluginfile_url($context->id, 'mod_pdcertificate', 'issue', $issue->id, $filepath, $filename,
                                               $forcedownload = true)->out();
        }

        throw new invalid_response_exception('Missing file or unstored certificate');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificate_file_url_returns() {
        return new external_value(PARAM_URL, 'An url to the file');
    }

    // Get certificate info ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificate_info_parameters() {
        return new external_function_parameters(
            array(
                'pdcidsource'  => new external_value(PARAM_ALPHA, 'Source for pdcertificate identifier, can be \'id\', \'cmid\' or \'idnumber\''),
                'pdcid'  => new external_value(PARAM_TEXT, 'PD Certificate or course module identifier'),
                'uidsource'  => new external_value(PARAM_TEXT, 'Source of user identifier, can be in set \'id\', \'idnumber\', \'email\' or \'username\''),
                'uid'  => new external_value(PARAM_TEXT, 'the user identifier value')
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param string $pdcidsource
     * @param string $pdcid
     * @param string $uidsource
     * @param string $uid
     * @return an info object
     * @throws moodle_exception
     */
    public static function get_certificate_info($pdcidsource, $pdcid, $uidsource, $uid) {
        global $DB;

        $parameters = array(
            'pdcidsource'  => $pdcidsource,
            'pdcid'  => $pdcid,
            'uidsource'  => $uidsource,
            'uid' => $uid
        );
        $params = self::validate_parameters(self::get_certificate_info_parameters(), $parameters);

        list($pdc, $cm, $user) = self::get_objects($pdcidsource, $pdcid, $uidsource, $uid);

        $course = $DB->get_record('course', array('id' => $pdc->course), 'id,idnumber,shortname');

        // Search issue for user.

        $params = array('pdcertificateid' => $pdc->id, 'userid' => $user->id);
        if ($issue = $DB->get_record('pdcertificate_issues', $params)) {
            $issueout = new StdClass();
            $issueout->id = $issue->id;
            $issueout->courseid = $pdc->course;
            $issueout->courseidnumber = $course->idnumber;
            $issueout->courseshortname = $course->shortname;
            $issueout->certid = $pdc->id;
            $issueout->certname = $pdc->name;
            $issueout->certidnumber = $cm->idnumber;
            $issueout->userid = $user->id;
            $issueout->user = fullname($user);
            $issueout->username = $user->username;
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
                $issueout->authority = 'N.C.';
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
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Issue id'),
                'courseid' => new external_value(PARAM_INT, 'Course id'),
                'courseidnumber' => new external_value(PARAM_TEXT, 'Course idnumber'),
                'courseshortname' => new external_value(PARAM_TEXT, 'Course shortname'),
                'certid' => new external_value(PARAM_INT, 'PD Certificate id'),
                'certname' => new external_value(PARAM_TEXT, 'PDCertificate name'),
                'certidnumber' => new external_value(PARAM_TEXT, 'PDCertificate ID Number'),
                'userid' => new external_value(PARAM_INT, 'Primary user id'),
                'user' => new external_value(PARAM_TEXT, 'Appliant readable identity'),
                'username' => new external_value(PARAM_TEXT, 'Appliant username'),
                'useridnumber' => new external_value(PARAM_TEXT, 'Appliant IDNumber'),
                'issuecode' => new external_value(PARAM_TEXT, 'Numeric unique code of the issue'),
                'timecreated' => new external_value(PARAM_INT, 'Issue creation date (Linux timestamp)'),
                'timedelivered' => new external_value(PARAM_INT, 'Time of delivery (Linux timestamp)'),
                'locked' => new external_value(PARAM_BOOL, 'Is certificate locked?'),
                'authority' => new external_value(PARAM_TEXT, 'Authority person (readable name)')
            )
        );
    }

    // Get certificate info for a set of users ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificate_users_info_parameters() {
        return new external_function_parameters(
            array(
                'pdcidsource'  => new external_value(PARAM_ALPHA, 'Source for pdcertificate identifier, can be \'id\', \'cmid\' or \'idnumber\''),
                'pdcid'  => new external_value(PARAM_TEXT, 'PD Certificate or course module identifier'),
                'uidsource'  => new external_value(PARAM_TEXT, 'Source of user identifier, can be in set \'id\', \'idnumber\', \'email\' or \'username\''),
                'uids'  => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'the user identifier value')
                ),
            )
        );
    }

    /**
     * Search courses following the specified criteria.
     *
     * @param string $pdcidsource
     * @param string $pdcid
     * @param string $uidsource
     * @param string $uids
     * @return an info object
     * @throws moodle_exception
     */
    public static function get_certificate_users_info($pdcidsource, $pdcid, $uidsource, $uids) {
        global $DB;

        $parameters = array(
            'pdcidsource'  => $pdcidsource,
            'pdcid'  => $pdcid,
            'uidsource'  => $uidsource,
            'uids' => $uids
        );

        $params = self::validate_parameters(self::get_certificate_users_info_parameters(), $parameters);

        $bulkresults = array();

        foreach ($uids as $uid) {
            try {
                $bulkresults[] = self::get_certificate_info($pdcidsource, $pdcid, $uidsource, $uid);
            } catch (moodle_exception $ex) {

                list($pdc, $cm, $user) = self::get_objects($pdcidsource, $pdcid, $uidsource, $uid);

                $course = $DB->get_record('course', array('id' => $pdc->course), 'id,idnumber,shortname');

                if ($ex->errorcode == 'notissued') {
                    $issueout = new StdClass();
                    $issueout->id = $issue->id;
                    $issueout->courseid = $pdc->course;
                    $issueout->courseidnumber = $course->idnumber;
                    $issueout->courseshortname = $course->shortname;
                    $issueout->certid = $pdc->id;
                    $issueout->certname = $pdc->name;
                    $issueout->certidnumber = $cm->idnumber;
                    $issueout->userid = $user->id;
                    $issueout->user = fullname($user);
                    $issueout->username = $user->username;
                    $issueout->useridnumber = $user->idnumber;
                    $bulkresults[] = $issueout;
                } else {
                    // Other error exception. Report it to output.
                    throw $ex;
                }
            }
        }

        return $bulkresults;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificate_users_info_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Issue id'),
                    'courseid' => new external_value(PARAM_INT, 'Course id'),
                    'courseidnumber' => new external_value(PARAM_TEXT, 'Course idnumber'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Course shortname'),
                    'certid' => new external_value(PARAM_INT, 'PD Certificate id'),
                    'certname' => new external_value(PARAM_TEXT, 'PDCertificate name'),
                    'certidnumber' => new external_value(PARAM_TEXT, 'PDCertificate ID Number'),
                    'userid' => new external_value(PARAM_INT, 'Primary user id'),
                    'user' => new external_value(PARAM_TEXT, 'Appliant readable identity'),
                    'username' => new external_value(PARAM_TEXT, 'Appliant username'),
                    'useridnumber' => new external_value(PARAM_TEXT, 'Appliant IDNumber'),
                    'issuecode' => new external_value(PARAM_TEXT, 'Numeric unique code of the issue', VALUE_OPTIONAL),
                    'timecreated' => new external_value(PARAM_INT, 'Issue creation date (Linux timestamp)', VALUE_OPTIONAL),
                    'timedelivered' => new external_value(PARAM_INT, 'Time of delivery (Linux timestamp)', VALUE_OPTIONAL),
                    'locked' => new external_value(PARAM_BOOL, 'Is certificate locked?', VALUE_OPTIONAL),
                    'authority' => new external_value(PARAM_TEXT, 'Authority person (readable name)', VALUE_OPTIONAL)
                )
            )
        );
    }

    // Get set of certificates info ----------------------------------------------.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_certificate_infos_parameters() {
        return new external_function_parameters(
            array(
                'cidsource' => new external_value(PARAM_ALPHA, 'source for the id, can be either \'id\', \'shortname\' or \'idnumber\''),
                'cid' => new external_value(PARAM_TEXT, 'The course id'),
                'issuedfrom' => new external_value(PARAM_TEXT, 'Return only issues from that date', VALUE_DEFAULT, 'last'),
            )
        );
    }

    /**
     * Get all users certification info for a course instance.
     *
     * @param string $cidsource the source field for the course identifier.
     * @param string $cid the course id
     * @param string $issuedfrom a linux timestamp
     *
     * @return external_description
     */
    public static function get_certificate_infos($cidsource, $cid, $issuedfrom) {
        global $DB;

        $parameters = array(
            'cidsource'  => $cidsource,
            'cid'  => $cid,
            'issuedfrom' => $issuedfrom
        );
        $params = self::validate_parameters(self::get_certificate_infos_parameters(), $parameters);

        $course = self::get_course($cidsource, $cid);

        $sqlparams = array();
        $courseclause = '';
        $timeclause = '';

        if (!empty($course)) {
            $courseclause = '
                AND
                    pd.course = ?
            ';
            $sqlparams[] = $course->id;
        }

        if ($issuedfrom == 'last') {
            $timeclause = 'AND
                pdi.timeexported = 0
            ';
        }

        if ($issuedfrom > 0) {
            $timeclause = 'AND
                pdi.timeexported > ?
            ';
            $sqlparams[] = $issuedfrom;
        }

        $sql = "
            SELECT
                pdi.id as id,
                pdi.timecreated as timecreated,
                pdi.timedelivered as timedelivered,
                pdi.locked as locked,
                pdi.code as code,
                pdi.authorityid as authorityid,
                pd.id as pdid,
                pd.name as pdname,
                u.id as userid,
                u.idnumber as uidnum,
                u.username,
                cm.idnumber as cmidnumber
            FROM
                {pdcertificate_issues} pdi,
                {pdcertificate} pd,
                {course_modules} cm,
                {modules} m,
                {user} u
            WHERE
                pdi.pdcertificateid = pd.id AND
                pdi.userid = u.id AND
                pd.id = cm.instance AND
                cm.module = m.id AND
                m.name = 'pdcertificate'
                $courseclause
                $timeclause
        ";

        $records = $DB->get_records_sql($sql, $sqlparams);

        $results = array();

        $now = time();

        if ($records) {
            foreach ($records as $rec) {
                $certdata = new StdClass;
                $certdata->id = $rec->id;
                $certdata->certid = $rec->pdid;
                $certdata->certname = format_string($rec->pdname);
                $certdata->certidnumber = $rec->cmidnumber;
                $certdata->userid = $rec->userid;
                $certdata->username = $rec->username;
                $certdata->useridnumber = $rec->uidnum;
                $certdata->issuecode = $rec->code;
                $certdata->timecreated = $rec->timecreated;
                $certdata->timedelivered = $rec->timedelivered;
                $certdata->locked = $rec->locked;

                if ($authority = $DB->get_record('user', array('id' => $rec->authorityid))) {
                    $certdata->authority = fullname($authority);
                } else {
                    $certdata->authority = 'N.C.';
                }

                $results[] = $certdata;
                $DB->set_field('pdcertificate_issues', 'timeexported', $now, array('id' => $rec->id));
            }
        }

        return $results;
    }

    private static function get_course($cidsource, $cid, $blocking = true) {
        global $DB;

        switch ($cidsource) {
            case 'id':
                $field = 'id';
                break;

            case 'idnumber':
                $field = 'idnumber';
                break;

            case 'shortname':
                $field = 'shortname';
                break;

            default:
                throw new invalid_parameter_exception('Not accepted source for course id');
        }

        if (!$course = $DB->get_record('course', array($field => $cid))) {
            if ($blocking) {
                throw new invalid_parameter_exception('Bad course id '.$cid);
            }
        }

        return $course;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_certificate_infos_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Issue id'),
                    'certid' => new external_value(PARAM_INT, 'PD Certificate id'),
                    'certname' => new external_value(PARAM_TEXT, 'PDCertificate name'),
                    'certidnumber' => new external_value(PARAM_TEXT, 'PDCertificate ID Number'),
                    'userid' => new external_value(PARAM_INT, 'Appliant primary id'),
                    'username' => new external_value(PARAM_TEXT, 'Appliant identity'),
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

    // Internal APIs -------------------------------------------------------------------.

    protected static function get_objects($pdcidsource, $pdcid, $uidsource, $uid) {
        global $DB;

        // Explicit mapping avoids injection.
        if ($pdcidsource == 'id') {

            if (!$pdc = $DB->get_record('pdcertificate', array('id' => $pdcid))) {
                throw new moodle_exception('missingmodule');
            }

            $cm = get_coursemodule_from_instance('pdcertificate', $pdc->id);
        } else {

            switch ($pdcidsource) {
                case 'idnumber':
                    $field = 'idnumber';
                    break;

                case 'cmid':
                    $field = 'id';
                    break;

                default:
                    throw new invalid_parameter_exception("Invalid instance identifier source '$pdcidsource'");
            }

            try {
                $cm = $DB->get_record('course_modules', array($field => $pdcid));
            } catch (Exception $e) {
                throw new invalid_parameter_exception('Course module missing.');
            }

            $module = $DB->get_record('modules', array('id' => $cm->module));
            if ($module->name != 'pdcertificate') {
                throw new invalid_parameter_exception('Invalid module type. This course module is NOT a pdcertificate');
            }

            $pdc = $DB->get_record('pdcertificate', array('id' => $cm->instance));
        }

        // Explicit mapping avoids injection.
        switch ($uidsource) {
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

        if (!$user = $DB->get_record('user', array($field => $uid))) {
            throw new invalid_parameter_exception('The user '.$uid.' does not exist');
        }

        $context = context_module::instance($cm->id);
        if (!has_capability('mod/pdcertificate:apply', $context, $user->id)) {
            throw new invalid_parameter_exception('The user '.$uid.' cannot apply to certification');
        }

        return array($pdc, $cm, $user);
    }
}
