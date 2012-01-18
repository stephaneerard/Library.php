<?php
namespace se\Library\Commands;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class GenerateCommand extends Command
{
	public function configure()
	{
		$this
		->setName('generate')
		->setDescription('Generates a library skeleton')
		
		->addArgument('gen-path', InputArgument::OPTIONAL, 'Path to the generated PHAR', getcwd())
		
		->addOption('template-path', 'p', InputOption::VALUE_OPTIONAL, 'Path to the template to use')
		->addOption('interfactive', 'i', InputOption::VALUE_NONE, 'Interactive mode')
		
		->setHelp(sprintf('%sHelp you generate a library.%s', PHP_EOL, PHP_EOL))
		;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		
	}
}