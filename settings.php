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

require_once($CFG->dirroot.'/mod/pdcertificate/lib.php');

if ($ADMIN->fulltree) {

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

    $key = 'pdcertificate/cronsendsbymail';
    $label = get_string('cronsendsbymail', 'pdcertificate');
    $desc = get_string('cronsendsbymail_desc', 'pdcertificate');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1));

    $encoptions = array(0 => 'RC4 40 bit',
                     1 => 'RC4 128 bit',
                     2 => 'AES 128 bit',
                     3 => 'AES 256 bit');
    $key = 'pdcertificate/encryptionstrength';
    $label = get_string('encryptionstrength', 'pdcertificate');
    $desc = get_string('encryptionstrength_desc', 'pdcertificate');
    $default = 1;
    $settings->add(new admin_setting_configselect($key, $label, $desc, $default, $encoptions));

    $key = 'pdcertificate/defaultauthority';
    $label = get_string('defaultauthority', 'pdcertificate');
    $desc = get_string('defaultauthority_desc', 'pdcertificate');
    $authorities = array();
    $authorities[0] = get_string('noauthority', 'pdcertificate');
    $fields = 'u.id,'.get_all_user_name_fields(true, 'u');
    $context = context_system::instance();
    if ($authorities_candidates = get_users_by_capability($context, 'mod/pdcertificate:isauthority', $fields, 'lastname,firstname')) {
        foreach ($authorities_candidates as $ac) {
            $authorities[$ac->id] = fullname($ac);
        }
    }
    $settings->add(new admin_setting_configselect($key, $label, $desc, 0, $authorities));

    if (pdcertificate_supports_feature('emulate/community') == 'pro') {
        include_once($CFG->dirroot.'/mod/pdcertificate/pro/prolib.php');
        $promanager = mod_pdcertificate\pro_manager::instance();
        $promanager->add_settings($ADMIN, $settings);
    } else {
        $label = get_string('plugindist', 'pdcertificate');
        $desc = get_string('plugindist_desc', 'pdcertificate');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }
}