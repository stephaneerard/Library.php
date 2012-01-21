#!/usr/bin/env php
<?php
Phar::mapPhar('$name');


$loader = require __DIR__ . '/../vendor/.composer/autoload.php';

$application = new se\Library\Application\Application();
$application->run();

__HALT_COMPILER();