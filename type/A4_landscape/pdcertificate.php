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

$fs = get_file_storage();

$pdf = new VFTCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// In pdcertificate, printconfig comes from certiticate instance.
$printconfig = json_decode($pdcertificate->printconfig);
if (!$printconfig && !empty($pdcertificate->printconfig)) {
    // Maybe an old syntax.
    $printconfig = unserialize($pdcertificate->printconfig);
}
if (empty($printconfig)) {
    $printconfig = new StdClass;
}
$printconfig->plugin = 'pdcertificate';

if (empty($printconfig->fontbasefamily)) {
    $printconfig->fontbasefamily = 'arial';
}

if (empty($printconfig->fontbasesize)) {
    $printconfig->fontbasesize = 9;
}

// $moredata should be provided at least as empty array. If not ensure it exists
if (!isset($moredata)) {
    $moredata = [];
}

$pdf->init($printconfig);

$pdf->SetTitle($pdcertificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printborders', 0, 'filename', true);
if ($files) {
    $border = new StdClass;
    $border->x = 0;
    $border->y = 0;
    $border->w = 297;
    $border->h = 210;
    if (!empty($printconfig->borderx)) {
        $border->x = $printconfig->borderx;
    }
    if (!empty($printconfig->bordery)) {
        $border->y = $printconfig->bordery;
    }

    $file = array_pop($files);
    $border->image = $file;
    $pdf->addCustomObject('border', $border);
    $printconfig->printborders = true;
} else {
    // Override whatever comes from user. We have NO borders file.
    $printconfig->printborders = false;
}

$files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printseal', 0, 'filename', true);
if ($files) {
    $seal = new Stdclass;
    $seal->x = 200;
    $seal->y = 144;

    if (!empty($printconfig->sealx)) {
        $seal->x = $printconfig->sealx;
    }
    if (!empty($printconfig->sealy)) {
        $seal->y = $printconfig->sealy;
    }

    $file = array_pop($files);
    $seal->image = $file;
    $pdf->addCustomObject('seal', $seal);
    $printconfig->printseal = true;
} else {
    // Override whatever comes from user. We have NO seal file.
    $printconfig->printseal = false;
}

$files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printsignature', 0, 'filename', true);
if ($files) {
    $signature = new StdClass;
    $signature->x = 47;
    $signature->y = 155;

    if (!empty($printconfig->signaturex)) {
        $signature->x = $printconfig->signaturex;
    }
    if (!empty($printconfig->signaturey)) {
        $signature->y = $printconfig->signaturey;
    }

    $file = array_pop($files);
    $signature->image = $file;
    $pdf->adDcustomObject('signature', $signature);
    $printconfig->printsignature = true;
} else {
    // Override whatever comes from user. We have NO signature file.
    $printconfig->printsignature = false;
}

/*
 * We need override "settings" watermark not proviced by pdcertificate with
 * per instance watermark image.
 */
$files = $fs->get_area_files($context->id, 'mod_pdcertificate', 'printwmark', 0, 'filename', true);
if ($files) {
    $watermark = new StdClass;
    $watermark->x = 20;
    $watermark->y = 20;
    $watermark->w = $pdf->getPageWidth() - 40;
    $watermark->h = $pdf->getPageHeight() - 40;

    if (!empty($printconfig->watermarkx)) {
        $watermark->x = $printconfig->watermarkx;
    }
    if (!empty($printconfig->watermarky)) {
        $watermark->y = $printconfig->watermarky;
    }

    if (!empty($printconfig->watermarkw)) {
        $watermark->w = $printconfig->watermarkw;
    }
    if (!empty($printconfig->watermarkh)) {
        $watermark->h = $printconfig->watermarkh;
    }

    $file = array_pop($files);
    $watermark->image = $file;
    // Replace the standard setting base definition.
    $pdf->adDcustomObject('wmark', $watermark);
    $printconfig->printwatermark = true;
} else {
    // Override whatever comes from user. We have NO signature file.
    $printconfig->printwatermark = false;
}

if (!empty($printconfig->printqrcode)) {
    $qrcode = new StdClass;
    $qrcode->x = 250;
    $qrcode->y = 155;
    $qrcode->w = 50;
    $qrcode->h = 50;

    if (!empty($printconfig->qrcodex)) {
        $qrcode->x = $printconfig->qrcodex;
    }
    if (!empty($printconfig->qrcodey)) {
        $qrcode->y = $printconfig->qrcodey;
    }
    if (!empty($printconfig->qrcodew)) {
        $qrcode->w = $printconfig->qrcodew;
    }
    if (!empty($printconfig->qrcodeh)) {
        $qrcode->h = $printconfig->qrcodeh;
    }
    $pdf->addCustomObject('qrcode', $qrcode);
}

// Text boxes.

$custx = $pdf->getBaseX();
$custy = 50;
$custw = $pdf->getPageWidth() - (2 * $custx);

if (isset($printconfig->custx)) {
    $custx = $printconfig->custx;
}
if (isset($printconfig->custy)) {
    $custy = $printconfig->custy;
}

if (empty($user)) {
    $user = $USER;
}

// Add images and lines.
pdcertificate_draw_frame($pdf, $pdcertificate);

// Start with borders (deepest)
if ($printconfig->printborders) {
    $pdf->renderCustomObject('border');
}

// Set alpha to semi-transparency.
if ($printconfig->printwatermark) {
    $pdf->renderWatermark();
}

$pdf->SetAlpha(1);
if ($printconfig->printsignature) {
    $pdf->renderCustomObject('signature');
}
if ($printconfig->printseal) {
    $pdf->renderCustomObject('seal');
}

// Add text.
$pdf->SetTextColor(0, 0, 0);

$headertext = pdcertificate_insert_data(format_text($pdcertificate->headertext), $pdcertificate, $certrecord, $course, $user, $moredata);
$customtext = pdcertificate_insert_data(format_text($pdcertificate->customtext), $pdcertificate, $certrecord, $course, $user, $moredata);
$footertext = pdcertificate_insert_data(format_text($pdcertificate->footertext), $pdcertificate, $certrecord, $course, $user, $moredata);

$header = $pdf->getCustomObject('header');
$footer = $pdf->getCustomObject('footer');

pdcertificate_print_textbox($pdf, $header->w, $header->x, $header->y, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $headertext);
pdcertificate_print_textbox($pdf, $custw, $custx, $custy, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $customtext);
pdcertificate_print_textbox($pdf, $footer->w, $footer->x, $footer->y, 'L', $printconfig->fontbasefamily, '', $printconfig->fontbasesize, $footertext);

if (!empty($printconfig->printqrcode)) {
    $verifyurl = new moodle_url('/mod/pdcertificate/verify.php', array('code' => $certrecord->code));
    $pdf->renderQRCode($verifyurl);
}
