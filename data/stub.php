<?php
$loader = require __DIR__ . '/../vendor/.composer/autoload.php';

$application = new se\Library\Application\Application();
$application->run();