<?php

class test_client {

    protected $t; // target.

    public function __construct() {

        $this->t = new StdClass;

        // Setup this settings for tests
        $this->t->baseurl = 'http://dev.moodle31.fr'; // The remote Moodle url to push in.
        $this->t->wstoken = '4aee373f7809e71559f9c4ffeb450156'; // the service token for access.
        $this->t->filepath = ''; // Some physical location on your system.

        $this->t->uploadservice = '/webservice/upload.php';
        $this->t->service = '/webservice/rest/server.php';
    }

    public function test_get_certificates($cidsource = 'id', $cid = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificates',
                        'moodlewsrestformat' => 'json',
                        'cidsource' => $cidsource,
                        'cid' => $cid);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_certificate_info($pdcidsource = '', $pdcid = 0, $uidsource = '', $uid = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_info',
                        'moodlewsrestformat' => 'json',
                        'pdcidsource' => $pdcidsource,
                        'pdcid' => $pdcid,
                        'uidsource' => $uidsource,
                        'uid' => $uid);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_certificate_infos($cidsource = '', $cid = 0, $issuedfrom = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_infos',
                        'moodlewsrestformat' => 'json',
                        'cidsource' => $cidsource,
                        'cid' => $cid,
                        'issuedfrom' => $issuedfrom);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

    public function test_get_certificate_file_url($pdcidsource = 'id', $pdcid = 0, $uidsource = 'id', $uid = 0) {

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_file_url',
                        'moodlewsrestformat' => 'json',
                        'pdcidsource' => $pdcidsource,
                        'pdcid' => $pdcid,
                        'uidsource' => $uidsource,
                        'uid' => $uid);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }


    protected function send($serviceurl, $params) {
        $ch = curl_init($serviceurl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        echo "Firing CUrl $serviceurl ... \n";
        if (!$result = curl_exec($ch)) {
            echo "CURL Error : ".curl_errno($ch).' '.curl_error($ch)."\n";
            return;
        }

        echo $result;
        if (preg_match('/EXCEPTION/', $result)) {
            echo $result;
            return;
        }

        $result = json_decode($result);
        print_r($result);
        return $result;
    }
}

// Effective test scénario

$client = new test_client();

$ix = 1;

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificates('id', 2); // Test one course.
$client->test_get_certificates(); // Test all certificates.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_info('id', 1, 'id', 3); // Test one course all users.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_info('cmid', 229, 'id', 3); // Test one course all users.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_info('idnumber', 'TESTPDC', 'id', 3); // Test one user, all courses.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_file_url('id', 1, 'id', 3); // Test one course all users.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_file_url('idnumber', 'TESTPDC', 'username', 'aa1'); // Test one user, all courses.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_infos('id', 2, 0); // Get all infos without restriction.

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_infos('idnumber', 'TESTMODS', '1491140171'); // Get infos for certificates issued from a date

echo "\n\nTest $ix ###########";
$ix++;
$client->test_get_certificate_infos('idnumber', 'TESTMODS', 'last'); // Get infos for certificates issued from a date
