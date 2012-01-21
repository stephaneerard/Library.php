<?php
namespace se\Library\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class InstallCommand extends Command
{
	public function configure()
	{
		$this
		->setName('install')
		->setDescription('Installs a library into given path')
		
		->addArgument('path', InputArgument::OPTIONAL, 'Path to where to install the lib', '/usr/local/bin')
		
		->addOption('symlink', 's', InputOption::VALUE_NONE, 'Makes a symlink instead of copying')
		
		->setHelp(sprintf('%sHelp you install a library.%s', PHP_EOL, PHP_EOL))
		;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		if(php_uname('s') != 'Linux')
		{
			throw new \Exception('Only Linux is handled.');
		}

		$fs = new Filesystem();
		
		$lib = \Phar::running();
		
		if(!$lib)
		{
			throw new \Exception('Not running a PHAR');
		}
		
		$file = str_replace('phar://', '', $lib);
		
		$output->writeln('<info>Installing ' . $file . ' into ' . $input->getArgument('path') . '</info>');
		$target = $input->getArgument('path') . '/' . basename($file);
		
		if($input->getOption('symlink'))
		{
			if(file_exists($target))
			{
				$fs->remove($target);
			}
			$fs->symlink($file,  $target, true);
		}
		else
		{
			$fs->copy($file,  $target);
			if(file_exists($file . '.pubkey'))
			{
				$fs->copy($file.'.pubkey', $target.'.pubkey');
			}
		}
		
		chmod($file, 0777);
		
		clearstatcache(true, $target);
		
		if(!file_exists($target))
		{
			throw new \Exception('Problem occured while symlinking/copying. Check you have sufficient permissions');
		}
		else{
			$output->writeln('<info>done.</info>');
		}
	}
}