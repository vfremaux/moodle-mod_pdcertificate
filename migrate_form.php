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
 * Handles viewing a pdcertificate
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class Migrate_Form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $courses = array();
        foreach ($this->_customdata['courses'] as $c) {
            $courses[$c->id] = "[$c->shortname] $c->fullname";
        }

        $attrs = array('size' => 20, 'style' => 'width:800px');
        $select = $mform->addElement('select', 'courses', get_string('courses'), $courses, $attrs);
        $select->setMultiple(true);

        $this->add_action_buttons(true, get_string('migrate', 'pdcertificate'));
    }
}