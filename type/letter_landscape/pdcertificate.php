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

defined('MOODLE_INTERNAL') || die();

$pdf = new PDF('L', 'pt', 'Letter', true, 'UTF-8', false);

$pdf->SetTitle($pdcertificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$printconfig = unserialize($pdcertificate->printconfig);

// Define variables.
// Landscape.
$x = 20;
$y = 20;

if (!empty($printconfig->margingroup['marginx'])) {
    $x = $printconfig->margingroup['marginx'];
}
if (!empty($printconfig->margingroup['marginy'])) {
    $y = $printconfig->margingroup['marginy'];
}

$sealx = 190;
$sealy = 144;

if (!empty($printconfig->sealoffsetgroup['sealoffsetx'])) {
    $sealx = $printconfig->sealoffsetgroup['sealoffsetx'];
}
if (!empty($printconfig->sealoffsetgroup['sealoffsety'])) {
    $sealy = $printconfig->sealoffsetgroup['sealoffsety'];
}

$sigx = 30;
$sigy = 170;

if (!empty($printconfig->signatureoffsetgroup['signatureoffsetx'])) {
    $sigx = $printconfig->signatureoffsetgroup['signatureoffsetx'];
}
if (!empty($printconfig->signatureoffsetgroup['signatureoffsety'])) {
    $sigy = $printconfig->signatureoffsetgroup['signatureoffsety'];
}

$wmarkx = 100;
$wmarky = 90;
$wmarkw = 600;
$wmarkh = 420;

if (!empty($printconfig->watermarkoffsetgroup['watermarkoffsetx'])) {
    $wmarkx = $printconfig->watermarkoffsetgroup['watermarkoffsetx'];
}
if (!empty($printconfig->watermarkoffsetgroup['watermarkoffsety'])) {
    $wmarky = $printconfig->watermarkoffsetgroup['watermarkoffsety'];
}

$brdrx = 0;
$brdry = 0;
$brdrw = 279;
$brdrh = 215;

$qrcx = 250;
$qrcy = 155;

if (!empty($printconfig->qrcodeoffsetgroup['qrcodex'])) {
    $qrcx = $printconfig->qrcodeoffsetgroup['qrcodex'];
}
if (!empty($printconfig->qrcodeoffsetgroup['qrcodey'])) {
    $qrcy = $printconfig->qrcodeoffsetgroup['qrcodey'];
}

// Text boxes.

$headx = $x;
$heady = $y;
$headw = 279 - (2 * $x);

$custx = 133;
$custy = 440;
$custw = 279 - (2 * $x);

$footerx = 20;
$footery = 195;
$footerw = 279 - (2 * $x);

if (empty($user)) {
    $user = $USER;
}

// Add images and lines.
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
pdcertificate_draw_frame_letter($pdf, $pdcertificate);

// Set alpha to semi-transparency.
$pdf->SetAlpha(0.1);
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_SEAL, $sealx, $sealy, '', '');
pdcertificate_print_image($pdf, $pdcertificate, PDCERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text.
$pdf->SetTextColor(0, 0, 0);

$headertext = pdcertificate_insert_data(format_text($pdcertificate->headertext), $pdcertificate, $certrecord, $course, $user);
$customtext = pdcertificate_insert_data(format_text($pdcertificate->customtext), $pdcertificate, $certrecord, $course, $user);
$footertext = pdcertificate_insert_data(format_text($pdcertificate->footertext), $pdcertificate, $certrecord, $course, $user);

pdcertificate_print_textbox($pdf, $headw, $headx, $heady, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $headertext);
pdcertificate_print_textbox($pdf, $custw, $custx, $custy, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $customtext);
pdcertificate_print_textbox($pdf, $footerw, $footerx, $footery, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $footertext);

if (!empty($printconfig->printqrcode)) {
    pdcertificate_print_qrcode($pdf, $certrecord->code, $qrcx, $qrcy);
}
