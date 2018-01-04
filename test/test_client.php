<?php

class test_client {

    static protected $t; // target.

    static protected $tests;

    public function __construct() {

        self::$t = new StdClass;

        // Setup this settings for tests.
        self::$t->baseurl = 'http://dev.moodle31.fr'; // The remote Moodle url to push in.
        self::$t->wstoken = '4aee373f7809e71559f9c4ffeb450156'; // the service token for access.
        self::$t->filepath = ''; // Some physical location on your system.

        self::$t->uploadservice = '/webservice/upload.php';
        self::$t->service = '/webservice/rest/server.php';
    }

    public static function test_get_certificates($cidsource = 'id', $cid = 0) {

        if (empty(self::$t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => self::$t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificates',
                        'moodlewsrestformat' => 'json',
                        'cidsource' => $cidsource,
                        'cid' => $cid);

        $serviceurl = self::$t->baseurl.self::$t->service;

        return self::send($serviceurl, $params);
    }

    public static function test_get_certificate_info($pdcidsource = '', $pdcid = 0, $uidsource = '', $uid = 0) {

        if (empty(self::$t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => self::$t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_info',
                        'moodlewsrestformat' => 'json',
                        'pdcidsource' => $pdcidsource,
                        'pdcid' => $pdcid,
                        'uidsource' => $uidsource,
                        'uid' => $uid);

        $serviceurl = self::$t->baseurl.self::$t->service;

        return self::send($serviceurl, $params);
    }

    public static function test_get_certificate_users_info($pdcidsource = '', $pdcid = 0, $uidsource = '', $uids = array()) {

        if (empty(self::$t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => self::$t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_users_info',
                        'moodlewsrestformat' => 'json',
                        'pdcidsource' => $pdcidsource,
                        'pdcid' => $pdcid,
                        'uidsource' => $uidsource,
                        'uids' => $uids);

        $serviceurl = self::$t->baseurl.self::$t->service;

        return self::send($serviceurl, $params);
    }

    public static function test_get_certificate_infos($cidsource = '', $cid = 0, $issuedfrom = 0) {

        if (empty(self::$t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => self::$t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_infos',
                        'moodlewsrestformat' => 'json',
                        'cidsource' => $cidsource,
                        'cid' => $cid,
                        'issuedfrom' => $issuedfrom);

        $serviceurl = self::$t->baseurl.self::$t->service;

        return self::send($serviceurl, $params);
    }

    public static function test_get_certificate_file_url($pdcidsource = 'id', $pdcid = 0, $uidsource = 'id', $uid = 0) {

        if (empty(self::$t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => self::$t->wstoken,
                        'wsfunction' => 'mod_pdcertificate_get_certificate_file_url',
                        'moodlewsrestformat' => 'json',
                        'pdcidsource' => $pdcidsource,
                        'pdcid' => $pdcid,
                        'uidsource' => $uidsource,
                        'uid' => $uid);

        $serviceurl = self::$t->baseurl.self::$t->service;

        return self::send($serviceurl, $params);
    }


    protected static function send($serviceurl, $params) {
        $ch = curl_init($serviceurl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        echo "Firing CUrl $serviceurl ... \n";
        print_r($params);
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

    public static function define_tests() {

        self::$tests = array();

        $test = new StdClass;
        $test->function = 'test_get_certificates';
        $test->params = array('id', 2); // Test one course.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_info';
        $test->params = array('id', 1, 'id', 3); // Test one certificate one user.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_info';
        $test->params = array('cmid', 229, 'id', 3); // Test one certificate per course module one user.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_info';
        $test->params = array('idnumber', 'TESTPDC', 'id', 3); // Test one certificate per idnumber one user.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_file_url';
        $test->params = array('id', 1, 'id', 3); // Test one certificate one user.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_file_url';
        $test->params = array('idnumber', 'TESTPDC', 'username', 'aa1'); // Test one certificate one user.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_infos';
        $test->params = array('id', 2, 0); // Get all infos without restriction.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_infos';
        $test->params = array('idnumber', 'TESTMODS', '1491140171'); // Get all infos with date restriction.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_infos';
        $test->params = array('idnumber', 'TESTMODS', 'last'); // Get all infos without restriction.
        self::$tests[] = $test;

        $test = new StdClass;
        $test->function = 'test_get_certificate_users_info';
        $test->params = array('id', 1, 'id', array(2,3,4,5)); // Get all infos without restriction.
        self::$tests[] = $test;

    }

    public static function run_tests($argv) {
        $calledtests = @$argv[1];

        $calledtestixs = array();
        $all = false;
        if (empty($calledtests) || ($calledtests == 'all')) {
            $all = true;
        } else {
            $calledtestixs = explode(',', $calledtests);
        }

        $ix = 1;
        foreach (self::$tests as $test) {

            if (in_array($ix, $calledtestixs) || $all) {
                echo "Running test $ix ######################\n";
                call_user_func_array("test_client::".$test->function, $test->params);
            }
            $ix++;
        }
    }
}

// Effective test scenario.
$client = new test_client(); // Initialise class.

\test_client::define_tests();
\test_client::run_tests($argv);