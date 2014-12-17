<?php

/**
 * A (quick and dirty) scanner to scanning all (linked) pages of an https-enabled website for Mixed Content
 * @author Bramus! <bramus@bram.us>
 * @version 1.0
 *
 * NO NEED TO TOUCH THIS FILE ... PLEASE REFER TO THE README.MD FILE ;-)
 */

// Error settings
error_reporting(E_ERROR);
ini_set('display_errors', 'on');

// Check if we're at the CLI
if (php_sapi_name() != 'cli') exit('Please run this file on the command line. E.g. `php bin/scanner.php $url`' . PHP_EOL);

// Check arguments (simple)
if ($argc != 2 || !parse_url($argv[1])) exit('Please use a valid URL you wish to scan as a parameter to this script. Eg. `php bin/scanner.php https://www.bram.us/`' . PHP_EOL);

// Require needed Scanner class
require __DIR__ . '/../vendor/autoload.php';

// Get ignorepatterns
$ignorePatterns = include __DIR__ . '/../conf/ignorePatterns.php';

// Go for it!
$scanner = new \Bramus\MCS\Scanner($argv[1], (array) $ignorePatterns);
$scanner->scan();
