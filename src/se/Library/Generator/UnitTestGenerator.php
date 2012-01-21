<?php

namespace se\Library\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class UnitTestGenerator
{
	protected $finder, $fs, $output, $options;
	
	public function __construct()
	{
		$this->finder = new Finder();
		$this->fs = new Filesystem();
	}
	
	public function generate(OutputInterface $output, array $options)
	{
		$this->output = $output;
		$this->options = $options;
		
		$target = $options['target'];
		$template = $options['template'];

		$files = $this->finder->in($template = $template . '/data');
		foreach($files as $file)
		{
			$base = str_replace($template, $target, $file);
			$output->writeln('<info>Creating ' . $base . '</info>');
			if($file->getType() == 'dir')
			{
				$this->createDirectory($base);
			}
			else
			{
				$this->createFile($file, $base);
			}
		}
		return true;
	}
	
	protected function createFile($source, $target)
	{
		$this->fs->copy($source, $target);
	}
	
	protected function createDirectory($dir)
	{
		$this->fs->mkdir($dir);
	}
}