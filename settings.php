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
 * Provides some custom settings for the pdcertificate module
 *
 * @package     mod
 * @subpackage  pdcertificate
 * @copyright   Michael Avelar <michaela@moodlerooms.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/pdcertificate/adminsetting.class.php');

$ADMIN->add('root', new admin_externalpage('pdcertificatemigrate',
    get_string('migration', 'pdcertificate'), new moodle_url('/mod/pdcertificate/migrate.php'), 'moodle/site:config'));

$key = 'pdcertificate/defaultpropagategroups';
$label = get_string('defaultpropagategroups', 'pdcertificate');
$desc = get_string('defaultpropagategroups_desc', 'pdcertificate');
$settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

$key = 'pdcertificate/maxdocumentspercron';
$label = get_string('maxdocumentspercron', 'pdcertificate');
$desc = get_string('maxdocumentspercron_desc', 'pdcertificate');
$default = 100;
$settings->add(new admin_setting_configtext($key, $label, $desc, $default));
