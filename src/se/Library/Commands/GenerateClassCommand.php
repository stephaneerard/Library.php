<?php
namespace se\Library\Commands;


use Composer\Json\JsonFile;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class GenerateClassCommand extends Command
{

	/**
	 * @var JsonFile
	 */
	protected $params;

	public function configure()
	{
		$this
		->setName('generate-class')
		->setDescription('Generates a Class skeleton')

		->addArgument('name', InputArgument::REQUIRED, 'Name of the class')
		->addArgument('ns', InputArgument::REQUIRED, 'Namespace')
		->addArgument('params', InputArgument::OPTIONAL, 'Parameters file, .json')
		->addOption('unit', 'u', InputOption::VALUE_OPTIONAL, 'With unit test', true)

		->addOption('template', 'p', InputOption::VALUE_OPTIONAL, 'Path to the template to use', 'default')

		->setHelp(sprintf('%sHelp you generate a Class library.%s', PHP_EOL, PHP_EOL))
		;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$params = $input->getArgument('params');
		if($params)
		{
			$params = new JsonFile($params);
			$params = $params->read();
		}
		$genPath = realpath($input->getArgument('target'));
		$templatePath = $input->getOption('template');
		if($templatePath == 'default')
		{
			$templatePath = __DIR__ . '/../../../../data/templates/default-class';
		}

		$generator = require $templatePath . '/generator.php';

		$result = $generator->generate($output, array(
		'target' => $genPath, 
		'template' => $templatePath,
		'params' => $params
		));
		
		if(isset($params['post-commands']))
		{
			chdir($genPath);
			foreach($params['post-commands'] as $cmd)
			{
				exec($cmd);
			}
		}
		
		($result && $output->writeln('<info>done.</info>')) 
		|| !$result && $output->writeln('<error>an error occured</error>');
		
		return $result;
	}
	
	

}