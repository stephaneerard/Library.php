<?php
require 'PHPUnit/Autoload.php';
$loader = require __DIR__ . '/../vendor/.composer/autoload.php';
$loader->add('#NAMESPACE_D#\\Test', __DIR__);

return $loader;