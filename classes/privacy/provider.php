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
 * Privacy class for requesting user data.
 *
 * @package   mod_pdcertificate
 * @copyright 2018 - valery fremaux <mylearningfactory.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Valery Fremaux
 */

namespace mod_pdcertificate\privacy;

use \core_privacy\local\metadata\collection;
use \core_privacy\local\metadata\provider as metadataprovider;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\helper;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\plugin\provider as pluginprovider;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/pdcertificate/locallib.php');

/**
 * Privacy class for requesting user data.
 *
 * @package   mod_pdcertificate
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    // This trait must be included.
    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) {

         // The table pdcertificate stores only the certificate properties.
         // However, some users may be certifers authority for some instances.
        $fields = [
            'certifierid' => 'privacy:metadata:pdcertificate:certifierid',
        ];
        $collection->add_database_table('pdcertificate', $fields, 'privacy:metadata:pdcertificate');

        // The table pdcertificate_issues stores certificate delivered to users.
        // Some personal information along with the resource accessed is stored.
        $fields = [
            'userid' => 'privacy:metadata:pdcertificate_issues:userid',
            'timecreated' => 'privacy:metadata:pdcertificate_issues:timecreated',
            'code' => 'privacy:metadata:pdcertificate_issues:code',
            'locked' => 'privacy:metadata:pdcertificate_issues:locked',
            'timedelivered' => 'privacy:metadata:pdcertificate_issues:timedelivered',
            'timeexported' => 'privacy:metadata:pdcertificate_issues:timeexported',
        ];
        $collection->add_database_table('pdcertificate_issues', $fields, 'privacy:metadata:pdcertificate_issues');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist;

        // Fetch all behalf certificates regarding your authority binding.
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm
                    ON cm.id = c.instanceid
                   AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m
                    ON m.id = cm.module
                   AND m.name = :modname
            INNER JOIN {pdcertificate} pdc
                    ON pdc.id = cm.instance
             WHERE pdc.certifierid = :userid";

        $params = [
            'modname' => 'pdcertificate',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        // Fetch all certificates user has isssues in.
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm
                    ON cm.id = c.instanceid
                   AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m
                    ON m.id = cm.module
                   AND m.name = :modname
            INNER JOIN {pdcertificate} pdc
                    ON pdc.id = cm.instance
            INNER JOIN {pdcertificate_issues} pdci
                    ON pdci.pdcertificateid = pdc.id
                 WHERE pdci.userid = :userid";

        $params = [
            'modname' => 'pdcertificate',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        self::export_user_data_issues($contextlist);
    }


    /**
     * Delete all data for all users in the specified context.
     * Although users have some data in those records, certification purpose will
     * superseed GRDP statements 
     * - Authority bindings : might be reset to the default authority in case of user deletion
     * - Certification issues : might or MUST NOT be deleted regarding to superseeding regulations.
     * Use general settings or pdcertificate to choose behaviour.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $config = get_config('pdcertificate');
        $fs = get_file_storage();

        if ($config->allowgrdpdeletion) {
            $fs->delete_area_files($contextid, 'pdcertificate', 'issues');

            $params = ['pdcertificateid' => $context->instance];
            $DB->delete_records('pdcertificate_issues', $params);

            $params = ['id' => $context->instance];
            $DB->set_field('pdcertificate', 'certifierid', 0, $params);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $count = $contextlist->count();
        if (empty($count)) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                return;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $DB->delete_records('pdcertificate_issues', ['pdcertificateid' => $instanceid, 'userid' => $userid]);
            $DB->set_field('pdcertificate', 'certifierid', 0, ['pdcertificateid' => $instanceid, 'certifierid' => $userid]);
        }
    }

    /**
     * Delete all user data for a userlist
     *
     * @param approved_userlist $userlist a list of users approved for deletion.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $count = $userlist->count();
        if (empty($count)) {
            return;
        }

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        foreach ($userlist->get_userids() as $uid) {
            $DB->delete_records('pdcertificate_issues', ['pdcertificateid' => $cm->instance, 'userid' => $uid]);
            $DB->set_field('pdcertificate', 'certifierid', 0, ['pdcertificateid' => $cm->instance, 'certifierid' => $uid]);
            // DO NOT apply reset to authorityid in issues as this is track of the "past" and NOT data ownership at the moment of deletion.
        }
    }

    /**
     * Export personal data for the given approved_contextlist related to pdcertificate issues.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    protected static function export_user_data_issues(approved_contextlist $contextlist) {
        global $DB;

        // Filter out any contexts that are not related to modules.
        $cmids = array_reduce($contextlist->get_contexts(), function($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->instanceid;
            }
            return $carry;
        }, []);

        if (empty($cmids)) {
            return;
        }

        $user = $contextlist->get_user();

        // Get all the pdcertificate activities associated with the above course modules.
        $instanceidstocmids = self::get_instance_ids_to_cmids_from_cmids($cmids);
        $instanceids = array_keys($instanceidstocmids);

        list($insql, $inparams) = $DB->get_in_or_equal($instanceids, SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['userid' => $user->id]);
        $recordset = $DB->get_recordset_select(
            'pdcertificate_issues', "pdcertificateid $insql AND userid = :userid", $params, 'timecreated, id');
        self::recordset_loop_and_export($recordset, 'pdcertificateid', [],
            function($carry, $record) use ($user, $instanceidstocmids) {
                $carry[] = [
                    'timecreated' => transform::datetime($record->timecreated),
                    'code' => $record->code,
                    'timedelivered' => transform::datetime($record->timedelivered),
                    'timeexported' => transform::datetime($record->timeexported),
                    'locked' => transform::yesno($record->locked),
                  ];
                return $carry;
            },

            function($instanceid, $data) use ($user, $instanceidstocmids) {
                $context = \context_module::instance($instanceidstocmids[$instanceid]);
                $contextdata = helper::get_context_data($context, $user);
                $finaldata = (object) array_merge((array) $contextdata, ['issues' => $data]);
                helper::export_context_files($context, $user);
                writer::with_context($context)->export_data([], $finaldata);
            }
        );
    }

    /**
     * Return a dict of pdcertificates IDs mapped to their course module ID.
     *
     * @param array $cmids The course module IDs.
     * @return array In the form of [$pdcertificateid => $cmid].
     */
    protected static function get_instance_ids_to_cmids_from_cmids(array $cmids) {
        global $DB;

        list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
        $sql = "SELECT pdc.id, cm.id AS cmid
                 FROM {pdcertificate} pdc
                 JOIN {modules} m
                   ON m.name = :pdcert
                 JOIN {course_modules} cm
                   ON cm.instance = pdc.id
                  AND cm.module = m.id
                WHERE cm.id $insql";
        $params = array_merge($inparams, ['pdcert' => 'pdcertificate']);

        return $DB->get_records_sql_menu($sql, $params);
    }

    /**
     * Loop and export from a recordset.
     *
     * @param \moodle_recordset $recordset The recordset.
     * @param string $splitkey The record key to determine when to export.
     * @param mixed $initial The initial data to reduce from.
     * @param callable $reducer The function to return the dataset, receives current dataset, and the current record.
     * @param callable $export The function to export the dataset, receives the last value from $splitkey and the dataset.
     * @return void
     */
    protected static function recordset_loop_and_export(\moodle_recordset $recordset, $splitkey, $initial,
                                                        callable $reducer, callable $export) {
        $data = $initial;
        $lastid = null;

        foreach ($recordset as $record) {
            if ($lastid && $record->{$splitkey} != $lastid) {
                $export($lastid, $data);
                $data = $initial;
            }
            $data = $reducer($data, $record);
            $lastid = $record->{$splitkey};
        }
        $recordset->close();

        if (!empty($lastid)) {
            $export($lastid, $data);
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     *
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        // Find users who are certifierid.
        $sql = "
            SELECT
                pdc.certifierid as userid
            FROM
                  {course_modules} cm,
                  {modules} m,
                  {pdcertificate} pdc
            WHERE
                cm.module = m.id AND
                AND m.name = :modname
                cm.instance = pdc.id AND
                cm.id = :contextid AND
                pdc.certifierid <> 0
        ";

        $params = [
            'contextid'     => $context->instanceid,
            'modname'     => 'pdcertificate'
        ];

        $userlist->add_from_sql('userid', $sql, $params);

        // Find users who are authority of some issues.
        $sql = "
            SELECT
                pdci.authorityid as userid
            FROM
                  {course_modules} cm,
                  {modules} m,
                  {pdcertificate} pdc,
                  {pdcertificate_issues} pdci
            WHERE
                cm.module = m.id AND
                AND m.name = :modname
                cm.instance = pdc.id AND
                cm.id = :contextid AND
                pdc.id = pdci.pdcertificateid AND
                pdci.authorityid <> 0
        ";

        $params = [
            'contextid'     => $context->instanceid,
            'modname'     => 'pdcertificate'
        ];

        $userlist->add_from_sql('userid', $sql, $params);

        // Find users with issues.
        $sql = "
            SELECT
                pdci.userid
            FROM
                  {course_modules} cm,
                  {modules} m,
                  {pdcertificate} pdc,
                  {pdcertificate_issues} pdci
            WHERE
                cm.module = m.id AND
                AND m.name = :modname
                cm.instance = pdc.id AND
                pdc.id = pdci.pdcertificateid AND
                cm.id = :contextid
        ";

        $params = [
            'contextid'     => $context->instanceid,
            'modname'     => 'pdcertificate'
        ];
        $userlist->add_from_sql('userid', $sql, $params);

    }
}
