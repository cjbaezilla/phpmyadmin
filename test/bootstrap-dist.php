<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Bootstrap for phpMyAdmin tests
 *
 * @package PhpMyAdmin-test
 */

// Let PHP complain about all errors
error_reporting(E_ALL);

// Adding phpMyAdmin sources to include path
set_include_path(
    get_include_path() . PATH_SEPARATOR . dirname(realpath("../index.php"))
);

// Setting constants for testing
define('PHPMYADMIN', 1);
define('TESTSUITE', 1);
define('PMA_MYSQL_INT_VERSION', 55000);

require_once 'libraries/core.lib.php';
require_once 'libraries/Config.class.php';
$CFG = new PMA_Config();
define('PMA_VERSION', $CFG->get('PMA_VERSION'));
unset($CFG);

session_start();

// You can put some additional code that should run before tests here

// Standard environment for tests
$_SESSION[' PMA_token '] = 'token';
$GLOBALS['lang'] = 'en';
$GLOBALS['is_ajax_request'] = false;


define('PMA_HAS_RUNKIT', function_exists('runkit_constant_redefine'));
$GLOBALS['runkit_internal_override'] = ini_get('runkit.internal_override');


/**
 * Function to emulate headers() function by storing headers in GLOBAL array
 *
 * @param string  $string             header string
 * @param boolean $replace            .
 * @param integer $http_response_code .
 *
 * @return void
 */
function test_header($string, $replace = true, $http_response_code = 200)
{
    if (! isset($GLOBALS['header'])) {
        $GLOBALS['header'] = array();
    }

    $GLOBALS['header'][] = $string;
}

/**
 * Function to emulate headers_hest.
 *
 * @return boolean false
 */
function test_headers_sent()
{
    return false;
}

/**
 * Function to emulate date() function
 *
 * @param string $date_format arg
 *
 * @return string dummy date
 */
function test_date($date_format)
{
    return '0000-00-00 00:00:00';
}

if (PMA_HAS_RUNKIT && $GLOBALS['runkit_internal_override']) {
    echo "Enabling headers testing...\n";
    runkit_function_rename('header', 'test_header_override');
    runkit_function_rename('headers_sent', 'test_headers_sent_override');
    runkit_function_rename('test_header', 'header');
    runkit_function_rename('test_headers_sent', 'headers_sent');
    define('PMA_TEST_HEADERS', true);
} else {
    echo "No headers testing.\n";
    echo "Please install runkit and enable runkit.internal_override!\n";
}

/**
 * Return the tag array to be used with assertTag by parsing
 * a given HTML element
 *
 * @param string $elementHTML HTML for element to be parsed
 * @param array  $arr         Additional array elements like content, parent
 *
 * @return array              Tag array to be used with assertTag
 */
function PMA_getTagArray($elementHTML, $arr = array())
{

    // get attributes
    preg_match_all("/\s+(.*?)\=\s*\"(.*?)\"/is", $elementHTML, $matches);
    foreach ($matches[1] as $key => $val) {
        $arr['attributes'][trim($val)] = trim($matches[2][$key]);
    }
    $matches = array();

    // get tag
    preg_match("/^\<(.*?)(\s|\>)/i", $elementHTML, $matches);
    if (isset($matches[1])) {
        $arr['tag'] = trim($matches[1]);
    }

    return $arr;
}

/**
 * Overrides date function
 *
 * @return boolean whether function was overridden or not
 */
function setupForTestsUsingDate()
{
    if (PMA_HAS_RUNKIT && $GLOBALS['runkit_internal_override']) {
        runkit_function_rename('date', 'test_date_override');
        runkit_function_rename('test_date', 'date');
        return true;
    } else {
        return false;
    }
}

/**
 * Restores date function
 *
 * @return void
 */
function tearDownForTestsUsingDate()
{
    if (PMA_HAS_RUNKIT && $GLOBALS['runkit_internal_override']) {
        runkit_function_rename('date', 'test_date');
        runkit_function_rename('test_date_override', 'date');
    }
}
?>
