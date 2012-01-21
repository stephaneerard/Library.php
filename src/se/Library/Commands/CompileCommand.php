<?php
namespace se\Library\Commands;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class CompileCommand extends Command
{

	protected $phar;

	public function configure()
	{
		$this
		->setName('compile')
		->setDescription('Compile your library into a PHAR')

		->addArgument('phar-name', InputArgument::REQUIRED, 'Name of the PHAR archive')
		->addArgument('lib-path', InputArgument::REQUIRED, 'Path to the library to PHAR')
		->addArgument('gen-path', InputArgument::OPTIONAL, 'Path to the generated PHAR', \getcwd() . '/phar/')

		->addOption('exclusion-func', null, InputOption::VALUE_OPTIONAL, 'Exclusion filters')
		->addOption('stub-require', null, InputOption::VALUE_OPTIONAL, 'File to be required in the stub, must be included in PHAR')
		->addOption('stub-file', null, InputOption::VALUE_OPTIONAL, 'Path to the PHAR Stub code-file to use, content is copied', null)
		->addOption('cert-priv-file', null, InputOption::VALUE_OPTIONAL, 'Location of your Certification to sign your PHAR', null)
		->addOption('cert-pub-file', null, InputOption::VALUE_OPTIONAL, 'Location of your Certification to sign your PHAR', null)

		/*->addOption('exclude-dir', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude directories')
		 ->addOption('exclude-dir-pattern', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude directories by pattern')
		->addOption('exclude-file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude files')
		->addOption('exclude-file-pattern', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude files by pattern')
		*/

		->setHelp(sprintf('%sHelp you compiling a library into a PHAR.%s', PHP_EOL, PHP_EOL))
		;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$verbose = $input->getOption('verbose');
		$signingFile = $input->getOption('cert-priv-file');
		$signing = null != $signingFile;
		$pharFile = $input->getArgument('phar-name') . '.phar';
		$genDir = $input->getArgument('gen-path');
		if(!file_exists($genDir))
		{
			mkdir($genDir);
			$verbose && $output->writeln('<info>Creating output directory</info>');
		}
		$targetPhar = $genDir . DIRECTORY_SEPARATOR . $pharFile;

		if(file_exists($targetPhar))
		{
			unlink($targetPhar);
			$verbose && $output->writeln(sprintf('<info>deleting "%s"</info>', $pharFile));
		}

		$verbose && $output->writeln(sprintf('<info>generating PHAR in file "%s"</info>', $targetPhar));

		$phar = new \Phar($targetPhar, 0, $input->getArgument('phar-name'));

		if($signing)
		{
			$verbose && $output->writeln('<info>Signing PHAR with given private key file</info>');
			$private = openssl_get_privatekey(file_get_contents($signingFile));
			$pkey = '';
			openssl_pkey_export($private, $pkey);
			$phar->setSignatureAlgorithm(\Phar::OPENSSL, $pkey);
			$phar['.pubkey'] = file_get_contents($input->getOption('cert-pub-file'));
			copy($input->getOption('cert-pub-file'), $targetPhar . '.pubkey');
		}
		else
		{
			$phar->setSignatureAlgorithm(\Phar::SHA1);
		}
		$phar->startBuffering();

		$this->phar = $phar;

		$filter = false;
		$excludePatterns = array();
		if($input->getOption('exclusion-func'))
		{
			$loaded = require $input->getOption('exclusion-func');
			$filter = $loaded[1];
			$excludePatterns = $loaded[0];
		}

		$finder = new Finder();
		
		$files = $finder->ignoreDotFiles(true)->files();
		
		foreach($excludePatterns as $pattern => $type) 
		{
			if($type == 'static')
			{
				$files->exclude(array($pattern));
			}
			else
			{
				$files->notName($pattern);
			}
		}
		
		
		
		$files->in($input->getArgument('lib-path'));

		foreach($files as $file)
		{
			if($filter((string)$file)) continue;
			$fakePath = str_replace($input->getArgument('lib-path') . '/', '', $file->getPathname());
			$content = self::stripComments(file_get_contents($file));
			$phar->addFromString($fakePath, $content);
			$verbose && $output->writeln(sprintf('<info>adding file %s</info>', $fakePath));
		}

		$stub = $this->getStub($input->getOption('stub-file'), $pharFile, $input->getArgument('phar-name'), $input->getOption('stub-require'));
		$phar->setStub($stub);
		$phar->stopBuffering();

		if(!$signing)
		{
			$phar->compressFiles(\Phar::GZ);
		}

		chmod($targetPhar, 0777);
		$output->writeln('<info>done.</info>');
	}

	private function getStub($stubfile, $pharname, $name, $require)
	{
		if(null !== $stubfile)
		{
			if(!file_exists($stubfile))
			{
				throw new \InvalidArgumentException(sprintf('Stub file "%s" is not readable', $stubfile));
			}
			else 
			{
				return file_get_contents($stubfile);
			}
		}

		return <<<EOF
#!/usr/bin/env php
<?php
Phar::mapPhar('$name');
require 'phar://$name/$require';

__HALT_COMPILER();
EOF;
	}

	static public function stripComments($source)
	{
		return $source;
		if (!function_exists('token_get_all')) {
			return $source;
		}

		$output = '';
		foreach (token_get_all($source) as $token) {
			if (is_string($token)) {
				$output .= $token;
			} elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
				$output .= $token[1];
			}
		}

		// replace multiple new lines with a single newline
		$output = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $output);

		return $output;
	}
}