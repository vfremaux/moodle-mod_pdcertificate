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
 * Version details.
 *
 * @package     mod_pdcertificate
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2017121500; // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2016112900; // Requires this Moodle version.
$plugin->component = 'mod_pdcertificate';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '3.2.0 (Build 2017121500)'; // User-friendly version number.

// Non Moodle attributes.
$plugin->codeincrement = '3.2.0007';