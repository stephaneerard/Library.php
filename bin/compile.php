<?php
namespace se\Library\Binaries;
$loader = require __DIR__ . '/../vendor/.composer/autoload.php';

use se\Library\Application\Application;

$inPhar = ('' != \Phar::running());

$_SERVER['argv'] = array(
	$_SERVER['argv'][0],
	'compile',						// Command to run : CompileCommand
	'library', 						// phar-name argument
	realpath(__DIR__.'/..'), 		// lib-path argument
	realpath(__DIR__.'/..'),		// gen-path argument
	
	//dirs to exclude
	'--exclude-dir=bin',
	'--exclude-dir=data',
	'--exclude-dir=tests',
	'--exclude-dir=.settings',
	
	//files to exclude
	'--exclude-file-pattern=composer.*',
	'--exclude-file-pattern=.*',
	'--exclude-file=phpunit.xml',
	'--exclude-file=vendor/.composer/installed.json',
);

$application = new Application();
$application->run();