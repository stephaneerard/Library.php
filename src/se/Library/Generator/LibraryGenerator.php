<?php

namespace se\Library\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LibraryGenerator
{
	protected $finder, $fs, $output, $options, $depth=0;

	public function __construct()
	{
		$this->finder = new Finder();
		$this->fs = new Filesystem();
	}

	public function generate(OutputInterface $output, array $options)
	{
		$this->output = $output;
		$this->options = $options;

		$tokenizeKey = function($value){
			return '#' . $value . '#';
		};
		
		$tokenizeArray = function($array) use($tokenizeKey){
			if(count($array) == 0) return $array;
			$keys = array_map($tokenizeKey, array_keys($array));
			return array_combine($keys, array_values($array));
		};
		
		$allTokens = isset($options['params']['tokens']['dir_file']) ? $tokenizeArray($options['params']['tokens']['dir_file']) : array();
		$this->dirTokens = isset($options['params']['tokens']['dir'])	? $tokenizeArray($options['params']['tokens']['dir']) : array();
		$this->fileTokens = isset($options['params']['tokens']['file'])	? $tokenizeArray($options['params']['tokens']['file']) : array();

		$this->dirTokens = array_merge($allTokens, $this->dirTokens, array('\\' => '/'));
		$this->fileTokens = array_merge($allTokens, $this->fileTokens);
		
		unset($allTokens);
		
		$target = $options['target'];
		$template = $options['template'];

		$files = $this->finder->in($template = $template . '/data');
		
		$this->generateFiles($files, $target, $template);

		return true;
	}

	protected function generateFiles($files, $target, $template)
	{
		foreach($files as $file)
		{
			$_file = (string) $file;
			
			$base = str_replace($template, $target, $file);

			if($file->getType() == 'dir')
			{
				$base = $this->getTokenizedDir($base);
				$this->output->writeln('<info>Creating ' . $base . '</info>');
				$this->createDirectory($base);
			}
			else
			{
				if(basename($_file) == '.__ignore') continue;
				$content = $this->getTokenizedFile(file_get_contents($_file));
				$filepath = $this->getTokenizedDir($base);
				$this->output->writeln('<info>Creating ' . $filepath . '</info>');
				$this->createFile($filepath, $content);
			}
		}
	}
	
	protected function getTokenizedDir($dir)
	{
		return str_replace(array_keys($this->dirTokens), array_values($this->dirTokens), $dir);
	}
	
	protected function getTokenizedFile($file)
	{
		return str_replace(array_keys($this->fileTokens), array_values($this->fileTokens), $file); 
	}

	protected function createFile($filename, $content)
	{
		file_put_contents($filename, $content);
	}

	protected function createDirectory($dir)
	{
		$this->fs->mkdir($dir);
	}
}