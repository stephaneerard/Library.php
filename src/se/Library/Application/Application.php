<?php
namespace se\Library\Application;


use se\Library\Commands;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
	const VERSION = '1.0.1';
	const NAME = 'Library.php';
	
	public function __construct()
	{
		parent::__construct(self::NAME, self::VERSION);
	}
	
	public function getDefaultCommands()
	{
		return array_merge(parent::getDefaultCommands(), array(
			new Commands\CompileCommand(),
			new Commands\GenerateLibraryCommand(),
			new Commands\GenerateClassCommand(),
			new Commands\GenerateCertCommand(),
			new Commands\InstallCommand(),
		));
	}
}