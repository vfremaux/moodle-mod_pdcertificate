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
 * This file contains an event for when a feedback activity is viewed.
 *
 * @package    mod_pdcertificate
 * @copyright  2015 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_pdcertificate\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Event for when a pdcertificate activity is checked remotely from an external device (flashcode).
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      @type int anonymous if pdcertificate is anonymous.
 *      @type int cmid course module id.
 * }
 *
 * @package    mod_pdcertificate
 * @since      Moodle 2.7
 * @copyright  2015 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_verified extends \core\event\course_module_viewed {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'pdcertificate';
    }
}

