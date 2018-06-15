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
 * Language strings for the pdcertificate module
 *
 * @package     mod_pdcertificate
 * @category    mod
 * @copyright   Mark Nelson <markn@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Capabilities.
$string['pdcertificate:addinstance'] = 'Add a PD Certificate instance';
$string['pdcertificate:apply'] = 'Can apply to certification';
$string['pdcertificate:deletepdcertificates'] = 'Can delete generated certificates';
$string['pdcertificate:download'] = 'Download certificate using API';
$string['pdcertificate:getown'] = 'Retrieve my own certificate';
$string['pdcertificate:isauthority'] = 'Is certification authority';
$string['pdcertificate:manage'] = 'Manage a PD Certificate instance';
$string['pdcertificate:printteacher'] = 'Be listed as a teacher on the certificate if the print teacher setting is on';
$string['pdcertificate:regenerate'] = 'Can regenerate certificates';
$string['pdcertificate:view'] = 'View a certificate';

$string['addcourselabel'] = 'Add course';
$string['addcoursetitle'] = 'Add course title';
$string['antecedantcourse'] = '{$a->coursename}. Mandatory requisite: {$a->prerequisite}';
$string['areaintro'] = 'Certificate introduction';
$string['authority'] = 'Authority';
$string['awarded'] = 'Awarded';
$string['awardedto'] = 'Awarded To';
$string['back'] = 'Back';
$string['backtocourse'] = 'Back to course';
$string['certifiableusers'] = 'Ready to certify:<br/><b>{$a} user(s)</b>';
$string['certification'] = 'Certification';
$string['certificationmatchednotdeliverable'] = 'You have matched the requirements for being certified, but the settings of the certificate needs it be delivered to you by an administrative person. You cannot get the pdcertificate document by yourself on this site.';
$string['certifiedusers'] = 'Certified users:<br/><b>{$a} user(s)</b>';
$string['certifierid'] = 'Certifier';
$string['chaining'] = 'Chaining';
$string['clearprintborders'] = 'Clear this file area';
$string['clearprintseal'] = 'Clear this file area';
$string['clearprintsignature'] = 'Clear this file area';
$string['clearprintwmark'] = 'Clear this file area';
$string['code'] = 'Code';
$string['completiondate'] = 'Course Completion';
$string['completiondelivered'] = 'Check the box for waiting effective delivery of the certificate to mark the activity completed';
$string['completiondeliveredgroup'] = 'Special';
$string['course'] = 'For';
$string['coursechaining'] = 'Course chaining';
$string['coursedependencies'] = 'Course dependancies';
$string['coursegrade'] = 'Course Grade';
$string['coursename'] = 'Course';
$string['credithours'] = 'Credit Hours';
$string['cron_task'] = 'PD Certificate scheduled task';
$string['croned'] = 'Generate issues by cron';
$string['customtext'] = 'Custom Text';
$string['date'] = 'On';
$string['datefmt'] = 'Date Format';
$string['datehelp'] = 'Date';
$string['defaultpropagategroups'] = 'Propagate groups when chaining (default)';
$string['defaultpropagategroups_desc'] = 'If checked, group information will be replicated in the chained course';
$string['defaultauthority'] = 'Certification authority (default)';
$string['definitive'] = 'Valid (definitive)';
$string['deletissuedpdcertificates'] = 'Delete issued pdcertificates';
$string['deliveredon'] = 'Delivered on';
$string['delivery'] = 'Delivery';
$string['description'] = 'Description';
$string['designoptions'] = 'Design Options';
$string['destroyselection'] = 'Destroy selection ';
$string['download'] = 'Force download';
$string['editpdcertificatelayout'] = 'Edit Layout';
$string['emailothers'] = 'Email Others';
$string['emailothers_help'] = 'Enter the email addresses here, separated by a comma, of those who should be alerted with an email whenever students receive a certificate.';
$string['emailpdcertificate'] = 'Email (Must also choose save!)';
$string['emailstudenttext'] = 'Attached is your pdcertificate for {$a->course}.';
$string['emailteachers'] = 'Email Teachers';
$string['encryptionstrength'] = 'Encryption strength';
$string['encryptionstrength_desc'] = 'Level of document encryption when the document is protected. Increasing protection level will increase resource consumption when generating the certificate.';
$string['entercode'] = 'Enter pdcertificate code to verify:';
$string['errorinvalidinstance'] = 'Error : certificate instance does not exist';
$string['errornocapabilitytodelete'] = 'You have no capability to delete certificates';
$string['errorcertificatenotinstalled'] = 'The original certificate module seems not be installed on your moodle. You cannot migrate data from it if it is not installed.';
$string['expiredon'] = 'Expired on';
$string['followercourse'] = 'Follower course : {$a->rolename} in {$a->coursename}. This module is prerequisite : {$a->prerequisite}';
$string['followers'] = 'Following course(s) in learning path';
$string['footertext'] = 'Custom Footer Text';
$string['freemono'] = 'Monospace';
$string['freesans'] = 'Sans serif';
$string['freeserif'] = 'Serif';
$string['fullaccesspassword'] = 'Full access password';
$string['generate'] = 'Generate';
$string['generateall'] = 'Generate all possible certificates ({$a})';
$string['generateselection'] = 'Generate selection ';
$string['getattempts'] = 'Get your Certificates';
$string['getpdcertificate'] = 'Get your certificate';
$string['gettestpdcertificate'] = 'Get a test certificate';
$string['grade'] = 'Grade';
$string['gradedate'] = 'Grade Date';
$string['gradefmt'] = 'Grade Format';
$string['gradeletter'] = 'Letter Grade';
$string['gradepercent'] = 'Percentage Grade';
$string['gradepoints'] = 'Points Grade';
$string['groupspecificcontent'] = 'Group specific information';
$string['headertext'] = 'Custom Header Text';
$string['imagetype'] = 'Image Type';
$string['incompletemessage'] = 'In order to download your certificate, you must first complete all required '.'activities. Please return to the course to complete your coursework.';
$string['intro'] = 'Introduction';
$string['invalidcode'] = 'Invalid certificate code';
$string['issued'] = 'Issued';
$string['issueddate'] = 'Date Issued';
$string['issueoptions'] = 'Issue Options';
$string['landscape'] = 'Landscape';
$string['lastviewed'] = 'You last received this certificate on:';
$string['letter'] = 'Letter';
$string['linkedcourse'] = 'Linked to course';
$string['lockingoptions'] = 'Locking Options';
$string['lockoncoursecompletion'] = 'Locked by course completion';
$string['lockoncoursecompletion_help'] = 'If enabled, the certificate will NOT be retrievable if the current course has not be completed.';
$string['managedelivery'] = 'Manage certificates delivery';
$string['mandatoryreq'] = 'Mandatory req';
$string['manualenrolnotavailableontarget'] = 'The Manual Enrol method seems to be disabled on the target course. Chain will fail.';
$string['margins'] = 'Margins (x,y)';
$string['maxdocumentspercron'] = 'Max documents per cron';
$string['migrate'] = 'Migrate from Certificate to PD Certificate';
$string['migration'] = 'PD Certificate Migration Tool';
$string['modulename'] = 'PD Certificate';
$string['modulenameplural'] = 'PD Certificates';
$string['mypdcertificates'] = 'My PD Certificates';
$string['needsmorework'] = 'This certificate needs more work';
$string['noauthority'] = 'No authority';
$string['nocertifiables'] = 'No one to certify';
$string['nofileselected'] = 'Must choose a file to upload!';
$string['nogrades'] = 'No grades available';
$string['none'] = 'None';
$string['nopdcertificates'] = 'There are no certificates';
$string['nopdcertificatesissued'] = 'There are no certificates that have been issued';
$string['nopdcertificatesreceived'] = 'has not received any course certificates.';
$string['notapplicable'] = 'N/A';
$string['notfound'] = 'The certificate number could not be validated.';
$string['notissued'] = 'Not Issued';
$string['notissuedyet'] = 'Not issued yet';
$string['notreceived'] = 'You have not received this certificate';
$string['notyetcertifiable'] = 'Not issueable';
$string['notyetusers'] = 'Not yet certifiable:<br/><b>{$a} user(s)</b>';
$string['openbrowser'] = 'Open in new window';
$string['opendownload'] = 'Click the button below to save your certificate to your computer.';
$string['openemail'] = 'Click the button below and your certificate will be sent to you as an email attachment.';
$string['openwindow'] = 'Click the button below to open your certificate in a new browser window.';
$string['or'] = 'Or';
$string['outcome'] = 'Outcome';
$string['pdcertificate'] = 'Verification for certificate code:';
$string['pdcertificatecaption'] = 'Certificate Caption';
$string['pdcertificatedefaultlock'] = 'Certificate locked by default';
$string['pdcertificatefile'] = 'Certificate file';
$string['pdcertificatefilenoaccess'] = 'you must have a valid account and be logged in to access the certificate document';
$string['pdcertificatelock'] = 'Certificate lock';
$string['pdcertificatename'] = 'Certificate Name';
$string['pdcertificateremoved'] = 'Certificate removed';
$string['pdcertificatereport'] = 'Certificates Report';
$string['pdcertificatesfor'] = 'Certificates for';
$string['pdcertificatetype'] = 'Certificate model';
$string['pdcertificateverification'] = 'Certificates Check';
$string['pdcertificateverifiedstate'] = 'The certificate code you tested is recognized and matches the following information:';
$string['pluginadministration'] = 'Certificate administration';
$string['pluginname'] = 'PD Certificate';
$string['portrait'] = 'Portrait';
$string['prerequisites'] = 'Prerequisites';
$string['previewpdcertificate'] = 'Preview the certificate';
$string['printborders'] = 'Borders or background';
$string['printdate'] = 'Print Date';
$string['printdateformat'] = '';
$string['printerfriendly'] = 'Printer-friendly page';
$string['printfontfamily'] = 'Font family';
$string['printfontsize'] = 'Font base size';
$string['printgrade'] = 'Print Grade';
$string['printhours'] = 'Credit Hours';
$string['printoptions'] = 'Print Options';
$string['printoutcome'] = 'Outcome to print';
$string['printoutcome_help'] = 'You can choose any course outcome to print the name of the outcome and the user\'s received outcome on the certificate.  An example might be: Assignment Outcome: Proficient. This will need using the {info:certificate_outcome} tag in the certificate template text.';
$string['printqrcode'] = 'Print QR Code';
$string['printseal'] = 'Seal or Logo Image';
$string['printsignature'] = 'Signature Image';
$string['printteacher'] = 'Print Teacher Name(s)';
$string['printwmark'] = 'Watermark Image';
$string['propagategroups'] = 'Propagate groups when chaining';
$string['protection'] = 'Do not allow to print the document';
$string['protectionannotforms'] = 'Do not allow to annotate the document and add form fields';
$string['protectionassemble'] = 'Do not allow to assemle, insert, delete or rotate pages';
$string['protectioncopy'] = 'Do not allow to copy content of the document';
$string['protectionextract'] = 'Do not allow to extract text and graphics';
$string['protectionfillforms'] = 'Do not allow to fill form fields';
$string['protectionmodify'] = 'Do not allow to change the document';
$string['protectionoptions'] = 'PDF Protection';
$string['protectionprint'] = 'Do not allow to print the document';
$string['protectionprinthigh'] = 'Do not allow to print in full resolution';
$string['pubkey'] = 'Public key';
$string['qrcodeoffset'] = 'QR Code offset (x,y)';
$string['receivedcerts'] = 'Received certificates';
$string['receiveddate'] = 'Date Received';
$string['regenerate'] = 'Regenerate';
$string['releaseselection'] = 'Release selection ';
$string['removecert'] = 'Issued certificates removed';
$string['removeother'] = 'Remove all other roles';
$string['report'] = 'Report';
$string['reportcert'] = 'Report Certificates';
$string['requiredcoursecompletion'] = 'This certificate requires you have completed the course to be delivered.';
$string['requiredtimenotmet'] = 'You must spend at least a minimum of {$a->requiredtime} minutes in the course before you can access this certificate';
$string['requiredtimenotvalid'] = 'The required time must be a valid number greater than 0';
$string['reviewpdcertificate'] = 'Review your certificate';
$string['rolereq'] = 'Role req';
$string['savecert'] = 'Save Certificates';
$string['savecert_help'] = 'If you choose this option, then a copy of each user\'s certificate pdf file is saved in the course files moddata folder for that certificate. A link to each user\'s saved pdcertificate will be displayed in the certificate report.';
$string['savelayout'] = 'Save Layout';
$string['seal'] = 'Seal';
$string['sealoffset'] = 'Seal offset (x,y)';
$string['setcertification'] = 'Role on certification';
$string['setcertificationcontext'] = 'Context';
$string['sigline'] = 'line';
$string['signature'] = 'Signature';
$string['signatureoffset'] = 'Signature offset (x,y)';
$string['sitecourse'] = 'Site course';
$string['specialgroupoptions'] = 'Special group related options';
$string['state'] = 'Status';
$string['statement'] = 'has completed the course';
$string['summary'] = 'Course summary';
$string['system'] = 'System level';
$string['teacherview'] = 'Teacher tools';
$string['textoptions'] = 'Text Options';
$string['thiscategory'] = 'This category';
$string['thiscourse'] = 'This course';
$string['title'] = 'CERTIFICATE of ACHIEVEMENT';
$string['to'] = 'Awarded to';
$string['totalcount'] = 'Total concerned users';
$string['tryothercode'] = 'Try other code';
$string['unsupportedfiletype'] = 'File must be a jpeg or png file';
$string['uploadimage'] = 'Upload image';
$string['uploadimagedesc'] = 'This button will take you to a new screen where you will be able to upload images.';
$string['userdateformat'] = 'User\'s Language Date Format';
$string['userpassword'] = 'User password';
$string['usersdelivered'] = 'Users delivered : {$a}';
$string['usersgenerated'] = 'Users generated not yet delivered : {$a}';
$string['userstocertify'] = 'Users yet to complete certification : {$a}';
$string['validate'] = 'Verify';
$string['validity'] = 'Validity period';
$string['validitytime'] = 'Validity period';
$string['validuntil'] = 'Valid until';
$string['verifypdcertificate'] = 'Verify Certificate';
$string['view_pageitem_directlink_to_follower'] = 'View with direct links to follower courses';
$string['viewall'] = 'View all';
$string['viewalladvice'] = 'Care that big groups may ask a big load to the moodle server';
$string['viewed'] = 'You received this certificate on:';
$string['viewless'] = 'View less';
$string['viewpdcertificateviews'] = 'View {$a} issued certificates';
$string['viewtranscript'] = 'View Certificates';
$string['watermark'] = 'Watermark';
$string['watermarkoffset'] = 'Watermark offset (x,y)';
$string['withsel'] = 'With selected: ';
$string['yetcertifiable'] = 'Ready to issue';
$string['yetcertified'] = 'Issued';
$string['youcango'] = 'You can continue to this course';
$string['youcantgo'] = 'You do not have enough achievements to continue in this course';

$string['unlimited'] = "Unlimited";
$string['oneday'] = "One day";
$string['oneweek'] = "One week";
$string['onemonth'] = "One month";
$string['threemonths'] = "Thee months";
$string['sixmonths'] = "Six months";
$string['oneyear'] = "One year";
$string['twoyears'] = "Two years";
$string['threeyears'] = "Three years";
$string['fiveyears'] = "Five years";
$string['tenyears'] = "Ten years";

// Help strings

$string['validitytime_help'] = 'When setting a validity preriod, the certificate state verification will fail when certificate is obsoleted by date.';

$string['pdcertificatetype_help'] = 'This is where you determine the layout of the certificate. The certificate type folder includes four default certificates:
A4 Embedded prints on A4 size paper with embedded font.
A4 Non-Embedded prints on A4 size paper without embedded fonts.
Letter Embedded prints on letter size paper with embedded font.
Letter Non-Embedded prints on letter size paper without embedded fonts.

The non-embedded types use the Helvetica and Times fonts.  If you feel your users will not have these fonts on their computer, or if your language uses characters or symbols that are not accommodated by the Helvetica and Times fonts, then choose an embedded type.  The embedded types use the Dejavusans and DejavuSerif fonts.  This will make the pdf files rather large; thus it is not recommended to use an embedded type unless you must.

New type folders can be added to the pdcertificate/type folder. The name of the folder and any new language strings for the new type must be added to the certificate language file.';

$string['printdate_help'] = 'This is the date that will be printed, if a print date is selected. If the course completion date is selected but the student has not completed the course, the date received will be printed. You can also choose to print the date based on when an activity was graded. If a certificate is issued before that activity is graded, the date received will be printed.';

$string['printgrade_help'] = 'You can choose any available course grade items from the gradebook to print the user\'s grade received for that item on the certificate.  The grade items are listed in the order in which they appear in the gradebook. Choose the format of the grade below.';

$string['printsignature_help'] = 'This option allows you to print a signature image. You can print a graphic representation of a signature, or print a line for a written signature. You can offset the signature position from it\'s default location.';

$string['printhours_help'] = 'Enter here the number of credit hours to be printed on the certificate. rhis wil need using the {info:certificate_credit_hours} tag in the certificate template text.';

$string['printqrcode_help'] = 'A square QR scannable code can be printed on the certificate. This code contains an URL that can be used to check if the certificate is valid.';

$string['printseal_help'] = 'This option allows you to select a seal or logo to print on the certificate. You can offset the seal from it\'s default location.';

$string['printteacher_help'] = 'For printing the teacher name on the certificate, set the role of teacher at the module level.  Do this if, for example, you have more than one teacher for the course or you have more than one pdcertificate in the course and you want to print different teacher names on each pdcertificate.  Click to edit the pdcertificate, then click on the Locally assigned roles tab.  Then assign the role of Teacher (editing teacher) to the pdcertificate (they do not HAVE to be a teacher in the course--you can assign that role to anyone).  Those names will be printed on the certificate for teacher.';

$string['printwmark_help'] = 'A watermark file can be placed in the background of the certificate. A watermark is a faded graphic. Fading is performed internally. A watermark could be a logo, seal, crest, wording, or whatever you want to use as a graphic background.';

$string['reportcert_help'] = 'If you choose yes here, then this certificate\'s date received, code number, and the course name will be shown on the user certificate reports.  If you choose to print a grade on this certificate, then that grade will also be shown on the certificate report.';

$string['setcertificationcontext_help'] = 'The context level where the certified role will be given';

$string['certifierid_help'] = 'Defining a certfifier account will print the certification authority identity if the certificate template allows it';

$string['setcertification_help'] = 'The role that will be assigned to the user when certified. Note that this is NOT en enrollment. To enroll a user after certification, use course chaining feature.';

$string['chaining_help'] = 'Chaining courses allow people getting the certificate to be assigned a new role in a new course';

$string['headertext_help'] = '';
$string['customtext_help'] = '';
$string['footertext_help'] = '';

$string['delivery_help'] = 'Choose here how you would like your students to get their certificate.
Open in Browser: Opens the certificate in a new browser window.
Force Download: Opens the browser file download window.
Email Certificate: Choosing this option sends the certificate to the student as an email attachment.
After a user receives their certificate, if they click on the certificate link from the course homepage, they will see the date they received their pdcertificate and will be able to review their received certificate.';

$string['emailteachers_help'] = 'If enabled, then teachers are alerted with an email whenever students receive a certificate.';

$string['emailteachermail'] = '
{$a->student} has received their pdcertificate: \'{$a->pdcertificate}\'
for {$a->course}.

You can review it here:

    {$a->url}';

$string['emailteachermailhtml'] = '
{$a->student} has received their certificate: \'<i>{$a->pdcertificate}</i>\'
for {$a->course}.

You can review it here:

    <a href="{$a->url}">Certificate Report</a>.';

$string['gradefmt_help'] = 'There are three available formats if you choose to print a grade on the certificate:

Percentage Grade: Prints the grade as a percentage.
Points Grade: Prints the point value of the grade.
Letter Grade: Prints the percentage grade as a letter.';

$string['propagategroups_help'] = 'If checked, group information will be replicated in the chained course for this certificate instance';

$string['pdcertificatelock_help'] = 'You may ask the certificates are administratively locked for delivery, until some external condition is available, such as an online paiment.';

$string['datefmt_help'] = 'Choose a date format to print the date on the certificate. Or, choose the last option to have the date printed in the format of the user\'s chosen language.';

$string['emailteachers_help'] = 'If enabled, then teachers are alerted with an email whenever students receive a certificate.';

$string['groupspecificcontent_help'] = 'If some Group Specific Html blocks are present in the course, you may retrieve the group specific content from one of those blocks to print on the certificate.';

$string['defaultcertificateheader_tpl'] = '';

$string['defaultcertificatebody_tpl'] = '
<center>
<h1>Certificate of achievement</h1>
<br/>
<br/>
<p>We, {info:certifier_name}, attest {info:user_fullname} has followed with full success the course :</p>
<br/>
<br/>
<h2>{info:course_fullname}</h2>
<br/>
<p>Completed on : <b>{info:completion_date}</b></p>
<p>Delivered on : <b>{info:certificate_date}</b></p>
</center>
';

$string['defaultcertificatefooter_tpl'] = '<center>{info:site_fullname}</center>';

$string['croned_help'] = 'If enabled, the cron will scan this pdcertificate to generate pending positive issues.';

$string['maxdocumentspercron_desc'] = 'Max amount of PDF documents one single execution can generate con croned certificates.
Consider setting this limit on big course audience to avoid a long running cron task. Further document generation will be delayed in time.';

$string['modulename_help'] = 'The PD (Profesional Development) Certificate allows generating a wide variety of documents such as certificates,
letters, assignments from HTML templates inserting a big choice of internal, user, course or site related information. The certifricate can
be used to trigger the enrolment in a "following course" or change role assignation on the current course. Certificates can be retrived in an
external document system by web services';

$string['pubkey_help'] = 'A public key given as alternative of a ^password. This key will be associated with the configured permissions.';

$string['userpassword_help'] = 'This password level is usually given to simple users to read the document and access to basic functions.';

$string['fullaccesspassword_help'] = 'This pasword level is given to advanced and power users to access including editing the content.';

$string['defaultauthority_desc'] = 'The default authority user that will be setup in any new instance';
