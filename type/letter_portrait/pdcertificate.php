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
 * letter_embedded pdcertificate type
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from view.php
}

$pdf = new PDF('P', 'pt', 'Letter', true, 'UTF-8', false);

$pdf->SetTitle($pdcertificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$x = 28;
$y = 170;

$sealx = 440;
$sealy = 590;

$sigx = 85;
$sigy = 580;

$wmarkx = 78;
$wmarky = 130;
$wmarkw = 450;
$wmarkh = 480;

$brdrx = 10;
$brdry = 10;
$brdrw = 594;
$brdrh = 771;

$qrcx = 30;
$qrcy = 660;

// Text boxes

$headx = 20;
$heady = 20;
$headw = 170;

$custx = 88;
$custy = 580;
$custw = 170;

$footerx = 20;
$footery = 247;
$footerw = 170;

$printconfig = unserialize($pdcertificate->printconfig);

if (empty($user)) $user = $USER;

// Add images and lines
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
pdcertificate_draw_frame_letter($pdf, $pdcertificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.1);
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_SEAL, $sealx, $sealy, '', '');
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 0);

$headertext = pdcertificate_insert_data($pdcertificate->headertext, $pdcertificate, $certrecord, $course, $user);
$customtext = pdcertificate_insert_data($pdcertificate->customtext, $pdcertificate, $certrecord, $course, $user);
$footertext = pdcertificate_insert_data($pdcertificate->footertext, $pdcertificate, $certrecord, $course, $user);

pdcertificate_print_textbox($pdf, $headw, $headx, $heady, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $headertext);
pdcertificate_print_textbox($pdf, $custw, $custx, $custy, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $customtext);
pdcertificate_print_textbox($pdf, $footerw, $footerx, $footery, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $footertext);

if (!empty($printconfig->printqrcode)) {
    pdcertificate_print_qrcode($pdf, $pdcertificate->code, $qrcx, $qrcy);
}
