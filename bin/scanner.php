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

// Require autoloader
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) exit('Make sure you run `composer install` first, before running this scanner');
require __DIR__ . '/../vendor/autoload.php';

// Check arguments (simple)
if ($argc != 2 || !parse_url($argv[1])) exit('Please use a valid URL you wish to scan as a parameter to this script. Eg. `php bin/scanner.php https://www.bram.us/`' . PHP_EOL);

// Get ignorepatterns
$ignorePatterns = include __DIR__ . '/../conf/ignorePatterns.php';

// Create logger
$logger = new \Monolog\Logger('MCS');
$handler = new \Monolog\Handler\StreamHandler('php://stdout', 200);
$handler->setFormatter(new \Bramus\Monolog\Formatter\ColoredLineFormatter());
$logger->pushHandler($handler);

// Go for it!
$scanner = new \Bramus\MCS\Scanner($argv[1], $logger, (array) $ignorePatterns);
$scanner->scan();
