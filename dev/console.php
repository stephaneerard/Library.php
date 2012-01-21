<?php
namespace se\Library\Binaries;

use se\Library\Application\Application;

$loader = require __DIR__ . '/../vendor/.composer/autoload.php';

$application = new Application();
$application->run();