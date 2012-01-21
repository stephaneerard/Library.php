<?php
require 'PHPUnit/Autoload.php';

$_SERVER['argv'] = array('--coverage-html=tests/coverage');

PHPUnit_TextUI_Command::main();