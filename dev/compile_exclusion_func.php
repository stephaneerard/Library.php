<?php
/*
 //dirs to exclude
'--exclude-file=./bin/compile.php',
'--exclude-file=./bin/shell.php',
'--exclude-dir=./tests',
'--exclude-dir=./.settings',
'--exclude-dir=./phar',
'--exclude-dir=./tests/generated/gen/*',

//files to exclude
'--exclude-file-pattern=*.md',
'--exclude-file-pattern=./.*',
'--exclude-file=./phpunit.xml',
'--exclude-file=./bin/compile.php',
'--exclude-file=./vendor/.composer/installed.json',
*/

$dir = realpath(__DIR__ . '/..');

$exclude_dirs = array(

$dir . '/tests' => 'static',
$dir . '/dev' => 'static',
$dir . '/bin' => 'static',
$dir . '/.settings' => 'static',
$dir . '/phar' => 'static',
$dir . '/tests/generated/gen' => 'static',
$dir . '/cert' => 'static',
$dir . '/.settings' => 'static',

);

$exclude_files = array(

'/*.md' => 'pattern',
$dir . '/.*' => 'pattern',
$dir . '/phpunit.xml' => 'static',
$dir . '/composer.*' => 'pattern',
$dir . '/vendor/.composer/installed.json' => 'static',
$dir . '/vendor/.composer/bin' => 'static',

);


$excludes = array_merge($exclude_dirs, $exclude_files);

return array($excludes, function($name) use ($excludes){
	$matches = false;
	foreach($excludes as $exclude => $type)
	{
		if($type == 'static')
		{
			$matches = is_int(strpos($name, $exclude));
		}
		if($matches) break;
	}

	return $matches;
});
