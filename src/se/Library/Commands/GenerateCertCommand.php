<?php

namespace se\Library\Commands;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class GenerateCertCommand extends Command
{
	public function configure()
	{
		$this
		->setName('generate-cert')
		->setDescription('Generates a Certificate to sign your PHARs')

		->addArgument('pub-file', InputArgument::OPTIONAL, 'Path to the public key file', './cert/cert.pub')
		->addArgument('priv-file', InputArgument::OPTIONAL, 'Path to the private key file', './cert/cert')

		->setHelp(sprintf('%sHelp you generate a certificate to sign your PHARs.%s', PHP_EOL, PHP_EOL))
		;
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln(sprintf('<info>Generating pub file to "%s"</info>', $input->getArgument('pub-file')));
		$output->writeln(sprintf('<info>Generating priv file to "%s"</info>', $input->getArgument('priv-file')));

		$res = openssl_pkey_new();

		if (!$res) {
			throw new Exception("Couldn't create key. Check that OpenSSL in PHP is configured properly - an openssl.cnf file is needed. Consult http://www.php.net/manual/en/openssl.installation.php");
		}

		$privkey = null;
		openssl_pkey_export($res, $privkey);

		$pubkey = openssl_pkey_get_details($res);
		$pubkey = $pubkey["key"];

		mkdir(basename($input->getArgument('priv-file')));
		mkdir(basename($input->getArgument('pub-file')));
		
		if (!@file_put_contents($input->getArgument('priv-file'), $privkey)) {
			throw new \Exception("Error writing private key to {$input->getArgument('priv-file')}!");
		}
		if (!@file_put_contents($input->getArgument('pub-file'), $pubkey)) {
			throw new \Exception("Error writing public key to {$input->getArgument('pub-file')}!");
		}
		
		$output->writeln('<info>done.</info>');
	}
}