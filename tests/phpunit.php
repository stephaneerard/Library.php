<?php
namespace se\Library\Test;

use se\Library\Test\LibraryTestSuite;

class PhpunitRunner
{
	public function __construct($suite, $dir)
	{
		$runner = new \PHPUnit_TextUI_TestRunner();
		$suite = new $suite;
		$suite->addTestFiles($this->getTestFiles($dir));
		$runner->doRun($suite, array('verbose'=>true));
	}

	function getTestFiles($dir)
	{
		$files = array();
		$dir = new \DirectoryIterator($dir);
		foreach($dir as $file)
		{
			if(in_array($file->getFilename(), array('.', '..'))) continue;
			if($file->getType() == 'dir')
			{
				$files = array_merge($files, $this->getTestFiles($file->getPathname()));
			}
			elseif(strpos($file->getFilename(), 'Test.php'))
			{
				$files[] = $file->getPathname();
			}
		}
		return $files;
	}
}

$loader = require __DIR__ . '/bootstrap.php';
$loader->add('se\\Library\Test', __DIR__);

new PhpunitRunner(new LibraryTestSuite(), __DIR__);
