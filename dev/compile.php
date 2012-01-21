<?php
namespace se\Library\Binaries;
use Symfony\Component\Filesystem\Filesystem;

$loader = require __DIR__ . '/../vendor/.composer/autoload.php';
$phar = \Phar::running();
if(empty($phar))
{
	$fs = new Filesystem();
	$fs->remove(__DIR__ . '/../tests/generated/gen');
}

use se\Library\Application\Application;

$_SERVER['argv'] = array(
	$_SERVER['argv'][0],
	'compile',						// Command to run : CompileCommand
	'library', 						// phar-name argument
	realpath(__DIR__.'/..'), 		// lib-path argument
	realpath(__DIR__.'/..') . '/bin',		// gen-path argument

	//'--stub-file=' . __DIR__ . '/../dev/console.php',
	'--stub-require=data/stub.php',
	'--exclusion-func=' . realpath(__DIR__ . '/compile_exclusion_func.php'),
	
	//certificates
	'--cert-priv-file='. realpath(__DIR__ . '/../cert/cert'),
	'--cert-pub-file='. realpath(__DIR__ . '/../cert/cert.pub'),
	'--verbose',
);

$application = new Application();
$application->run();