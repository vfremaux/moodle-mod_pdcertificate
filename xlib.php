<?php

require_once $CFG->dirroot.'/mod/pdcertificate/lib.php';

function pdcertificate_get_user_pdcertificates($course, $userid) {
    global $DB, $USER;

    $module = $DB->get_record('modules', array('name' => 'pdcertificate'));

    $sql = "
        SELECT DISTINCT
            ce.*,
            c.shortname,
            c.fullname,
            cm.id as cmid
        FROM
            {course_modules} cm,
            {course} c,
            {pdcertificate} ce
        WHERE
            cm.module = ? AND
            cm.instance = ce.id AND
            ce.course = c.id AND
            c.id = ?
        GROUP BY
            ce.id
        ORDER BY
            c.shortname
    ";

    if ($instances = $DB->get_records_sql($sql, array($module->id, $course->id))) {

        foreach ($instances as $key => $instance) {

            $context = context_module::instance($instance->cmid);

            // Rip off all those you ($USER) do not have manager access in
            if (!has_capability('mod/pdcertificate:manage', $context)) {
                unset($instances[$key]);
            }

            // Rip off those where user is not ready. Care that pdcertificate_check_conditions() is negative logic
            $cm = $DB->get_record('course_modules', array('id' => $instance->cmid));
            $instances[$key]->issued = $DB->record_exists('pdcertificate_issues', array('pdcertificateid' => $instance->id, 'userid' => $userid));
            if (!$instances[$key]->issued && pdcertificate_check_conditions($instance, $cm, $userid)) {
                unset($instances[$key]);
            }
        }
        return $instances;
    }

    return array();

}

/**
 *
 */
function pdcertificate_get_my_pdcertificates() {
    global $USER, $DB;

    $sql = "
        SELECT
            pdci.*,
            pdc.id as modid,
            pdc.name as modname,
            cm.id as cmid
        FROM
            {course_modules} cm,
            {modules} m,
            {pdcertificate} pdc,
            {pdcertificate_issues} pdci
        WHERE
            cm.instance = pdc.id AND
            cm.deletioninprogress != 1 AND
            m.id = cm.module AND
            m.name = 'pdcertificate' AND
            pdc.id = pdci.pdcertificateid AND
            pdci.userid = ?
    ";

    $issues = $DB->get_records_sql($sql, [$USER->id]);

    return $issues;
}