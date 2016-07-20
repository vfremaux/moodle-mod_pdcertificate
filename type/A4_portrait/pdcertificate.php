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
 * A4_embedded pdcertificate type
 *
 * @package    mod
 * @subpackage pdcertificate
 * @copyright  Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($pdcertificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$x = 10;
$y = 40;

$brdrx = 0;
$brdry = 0;
$brdrw = 210;
$brdrh = 297;

$wmarkx = 26;
$wmarky = 58;
$wmarkw = 158;
$wmarkh = 170;

$sealx = 150;
$sealy = 220;

$sigx = 30;
$sigy = 230;

$qrcx = 180;
$qrcy = 10;

// Text boxes

$headx = 20;
$heady = 20;
$headw = 170;

$custx = 20;
$custy = 50;
$custw = 170;

$footerx = 20;
$footery = 247;
$footerw = 170;

$printconfig = unserialize($pdcertificate->printconfig);

if (empty($user)) $user = $USER;

// Add images and lines
pdcertificate_draw_frame($pdf, $pdcertificate);
$pdf->SetAlpha(1);
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);

// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
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
    pdcertificate_print_qrcode($pdf, $certrecord->code, $qrcx, $qrcy);
}
