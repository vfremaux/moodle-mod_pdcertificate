<?php
// This file is part of the mplayer plugin for Moodle - http://moodle.org/
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
 * @package pdcertificate
 * @author Valery Fremaux (valery@edunao.com)
 *
 * This script is an adapter to add a special view to the page format.
 * other alternate viexws can be provided as pageitem_<viewname>.php prefixed files implementing
 * a <modname>_<viewname>_set_instance(&$block) rendering function.
 * This rendering function should fill the content->text member of the provided block reference.
 */
require_once($CFG->dirroot.'/mod/pdcertificate/locallib.php');

/**
 * Implements an alternative representation of this activity for the "page"
 * format.
 * @param objectref &$block the block recevied is an instance of a page_module block. The course_module is
 * located in the 'cm' member of the block.
 */
function pdcertificate_directlink_to_follower_set_instance(&$block) {
    global $DB, $PAGE, $CFG, $COURSE, $OUTPUT, $USER;

    $str = '';

    $context = context_module::instance($block->cm->id);

    $pdcertificate = $DB->get_record('pdcertificate', array('id' => $block->cm->instance));

    // Transfer content from title to content.
    // $block->content->text = $block->title;
    $block->title = format_string($pdcertificate->name);

    $completioninfo = new completion_info($COURSE);
    $modinfo = get_fast_modinfo($COURSE);
    $mod = $modinfo->cms[$block->cm->id];
    $sectionreturn = $block->cm->section;
    $formatrenderer = $PAGE->get_renderer('format_page');

    $nextcourses = $DB->get_records('pdcertificate_linked_courses', array('pdcertificateid' => $pdcertificate->id));

    $ismanager = has_capability('mod/pdcertificate:manage', $context);

    // First process user for certification if possible.
    if (!$errors = pdcertificate_check_conditions($pdcertificate, $block->cm, $USER->id)) {
        if (has_capability('mod/pdcertificate:apply', $context, $USER->id, false)) {
            // Only really process certifiable users.
            pdcertificate_get_issue($COURSE, $USER, $pdcertificate, $block->cm);
            pdcertificate_process_chain($USER, $pdcertificate);

            // Trigger module viewed event.
            $eventparams = array(
                'objectid' => $pdcertificate->id,
                'context' => $context,
            );

            $event = \mod_pdcertificate\event\course_module_issued::create($eventparams);
            $event->add_record_snapshot('course_modules', $block->cm);
            $event->add_record_snapshot('course', $COURSE);
            $event->trigger();
        }
    }

    $str .= '<div class="activity-pdcertificate-followers">';
    if ($nextcourses) {
        $courselinks = '';
        foreach ($nextcourses as $follower) {
            $followercontext = context_course::instance($follower->courseid);
            $coursename = $DB->get_field('course', 'fullname', array('id' => $follower->courseid));
            if (is_enrolled($followercontext, $USER)) {
                $courselabel = '<img class="pdcertificate-follower-image" src="'.$OUTPUT->image_url('greenlight', 'pdcertificate').'" /> '.$coursename;
                $courseurl = new moodle_url('/course/view.php', array('id' => $follower->courseid));
                $courselink = html_writer::link($courseurl, $courselabel,
                    array('class' => 'pdcertificate-output', 'title' => get_string('youcango', 'pdcertificate')));
                $courselinks .= $courselink.'<br/>';
            } else {
                $courselabel = '<span title="'.get_string('youcantgo', 'pdcertificate').'"><img class="pdcertificate-follower-image" src="'.$OUTPUT->image_url('redlight', 'pdcertificate').'" /> '.$coursename;
                $courselinks .= $courselabel.'</span><br/>';
            }
        }
        $str .= '<b>'.get_string('followers', 'pdcertificate').':</b><br/>';
        $str .= $courselinks;
    }

    $str .= '</div>';

    $str .= '<div class="activity-pdcertificate-status">';
    $str .= $formatrenderer->print_cm($COURSE, $mod);

    if ($ismanager) {
       $total = array();
        $certifiableusers = array();
        $group = groups_get_course_group($COURSE);
        $state = pdcertificate_get_state($pdcertificate, $block->cm, 0, 0, $group, $total, $certifiableusers);
        $str .= '<div class="activity-pdcertificate notyet inline-left">';
        $str .= get_string('notyetusers', 'pdcertificate', $state->notyetusers);
        $str .= '</div>';
        $str .= '<div class="activity-pdcertificate certifiable inline-left">';
        $str .= get_string('certifiableusers', 'pdcertificate', $state->totalcount - $state->totalcertifiedcount - $state->notyetusers);
        $str .= '</div>';
        $str .= '<div class="activity-pdcertificate certified inline-left">';
        $str .= get_string('certifiedusers', 'pdcertificate', $state->totalcertifiedcount);
        $str .= '</div>';

        $nextcourses = $DB->get_records('pdcertificate_linked_courses', array('pdcertificateid' => $pdcertificate->id));
        $str .= '<div class="activity-pdcertificate-dependancies">';
        if ($nextcourses) {
            $str .= '<div class="activity-pdcertificate-followers">';
            $str .= '<b>'.get_string('followers', 'pdcertificate').':</b><br/>';
            foreach($nextcourses as $follower) {
                $c = new StdClass;
                $c->coursename = $DB->get_field('course', 'fullname', array('id' => $follower->courseid));
                $c->prerequisite = ($follower->mandatory) ? get_string('yes') : get_string('no');
                if ($follower->roletobegiven) {
                    $role = $DB->get_record('role', array('id' => $follower->roletobegiven));
                    $c->rolename = role_get_name($role, $context);
                } else {
                    $rolename = get_string('none');
                }
                $str .= get_string('followercourse', 'pdcertificate', $c);
            }
            $str .= '</div>';
        } else {
            $str .= '<div class="activity-pdcertificate-followers">';
            $str .= '<br/><br/>';
            $str .= '</div>';
        }

        $prevcourses = $DB->get_records('pdcertificate_linked_courses', array('courseid' => $COURSE->id));
        if ($prevcourses) {
            $str .= '<div class="activity-pdcertificate-prerequisites">';
            $str .= '<b>'.get_string('prerequisites', 'pdcertificate').':</b><br/>';
            foreach($prevcourses as $antecedant) {
                $antecedantcourseid = $DB->get_field('pdcertificate', 'course', array('id' => $antecedant->pdcertificateid));
                $c = new StdClass;
                $c->coursename = $DB->get_field('course', 'fullname', array('id' => $antecedantcourseid));
                $c->prerequisite = ($antecedant->mandatory) ? get_string('yes') : get_string('no');
                if ($antecedant->roletobegiven) {
                    $role = $DB->get_record('role', array('id' => $antecedant->roletobegiven));
                    $c->rolename = role_get_name($role, $context);
                } else {
                    $rolename = get_string('none');
                }
                $str .= get_string('antecedantcourse', 'pdcertificate', $c).'<br/>';
            }
            $str .= '</div>';
        } else {
            $str .= '<div class="activity-pdcertificate-followers">';
            $str .= '<br/><br/>';
            $str .= '</div>';
        }
    }

    $str .= '</div>';

    $block->content->text = $str;
    return true;
}
