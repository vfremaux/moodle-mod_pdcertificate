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
 * Forum external functions and service definitions.
 *
 * @package    mod_pdcertificate
 * @copyright  2016 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(

    'mod_pdcertificate_get_certificates' => array(
        'classname' => 'mod_pdcertificate_external',
        'methodname' => 'get_certificates',
        'classpath' => 'mod/pdcertificate/externallib.php',
        'description' => 'Get the list of certificates of a course',
        'type' => 'read',
        'capabilities' => 'mod/pdcertificate:download'
    ),

    'mod_pdcertificate_get_certificate_file_url' => array(
        'classname' => 'mod_pdcertificate_external',
        'methodname' => 'get_certificate_file_url',
        'classpath' => 'mod/pdcertificate/externallib.php',
        'description' => 'Get the exact download url for a certificate',
        'type' => 'read',
        'capabilities' => 'mod/pdcertificate:download'
    ),

    'mod_pdcertificate_get_certificate_info' => array(
        'classname' => 'mod_pdcertificate_external',
        'methodname' => 'get_certificate_info',
        'classpath' => 'mod/pdcertificate/externallib.php',
        'description' => 'Get certificate metadata info for a certificate',
        'type' => 'read',
        'capabilities' => 'mod/pdcertificate:download'
    ),

    'mod_pdcertificate_get_certificate_infos' => array(
        'classname' => 'mod_pdcertificate_external',
        'methodname' => 'get_certificate_infos',
        'classpath' => 'mod/pdcertificate/externallib.php',
        'description' => 'Get certificate infos for a set of users',
        'type' => 'read',
        'capabilities' => 'mod/pdcertificate:download'
    ),

);
