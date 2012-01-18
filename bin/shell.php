<?php
namespace se\Library\Binaries;

use Symfony\Component\Console\Shell;

use se\Library\Application\Application;

$loader = require __DIR__ . '/../vendor/.composer/autoload.php';

$application = new Application();
$shell = new Shell($application);
$shell->run();