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
		->addArgument('gen-path', InputArgument::OPTIONAL, 'Path to the generated PHAR', \getcwd())

		->addOption('exclude-dir', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude directories')
		->addOption('exclude-dir-pattern', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude directories by pattern')
		->addOption('exclude-file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude files')
		->addOption('exclude-file-pattern', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Exclude files by pattern')

		->setHelp(sprintf('%sHelp you compiling a library into a PHAR.%s', PHP_EOL, PHP_EOL))
		;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$pharFile = $input->getArgument('phar-name') . '.phar';
		$genDir = $input->getArgument('gen-path');
		$targetPhar = $genDir . DIRECTORY_SEPARATOR . $pharFile;

		if(file_exists($targetPhar))
		{
			unlink($targetPhar);
			$output->writeln(sprintf('<info>deleting "%s"</info>', $pharFile));
		}

		$output->writeln(sprintf('<info>generating PHAR in file "%s"</info>', $targetPhar));

		$phar = new \Phar($targetPhar, 0, $input->getArgument('phar-name'));
		$phar->setSignatureAlgorithm(\Phar::SHA1);
		$phar->startBuffering();

		$this->phar = $phar;

		$finder = new Finder();

		$finder
		->files()
		->exclude($input->getOption('exclude-dir'))
		->ignoreDotFiles(true)
		;

		foreach(array_merge($input->getOption('exclude-file'), $input->getOption('exclude-file-pattern')) as $pattern)
		{
			$finder->notName($pattern);
		}


		$files = $finder->in($input->getArgument('lib-path'));

		foreach($files as $file)
		{
			$fakePath = str_replace($input->getArgument('lib-path') . '/', '', $file->getPathname());
			$content = self::stripComments(file_get_contents($fakePath));
			$phar->addFromString($fakePath, $content);
		}

		$phar['_cli_stub.php'] = $this->getCliStub();
		$phar['_web_stub.php'] = $this->getWebStub();
		$phar->setDefaultStub('_cli_stub.php', '_web_stub.php');
		$phar->stopBuffering();
		$phar->compressFiles(\Phar::GZ);

	}

	protected function getCliStub()
	{
		return <<<'EOF'
<?php
require __DIR__ . '/vendor/.composer/autoload.php';
use se\Library\Application\Application;
$application = new Application();
$application->run();

__HALT_COMPILER();
EOF;
	}

	protected function getWebStub()
	{
		return "<?php throw new \LogicException('This PHAR file can only be used from the CLI.'); __HALT_COMPILER();";
	}

	static public function stripComments($source)
	{
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