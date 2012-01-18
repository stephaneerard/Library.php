<?php
namespace se\Library\Application;


use Symfony\Component\Console\Command\HelpCommand;

use Symfony\Component\Console\Command\ListCommand;

use se\Library\Commands\GenerateCommand;
use se\Library\Commands\CompileCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
	const VERSION = '1.0.0';
	const NAME = 'Library.php';
	
	public function __construct()
	{
		parent::__construct(self::NAME, self::VERSION);
	}
	
	public function getDefaultCommands()
	{
		return array_merge(parent::getDefaultCommands(), array(
			new CompileCommand(),
			new GenerateCommand()
		));
	}
}