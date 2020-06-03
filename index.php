<?php

// require library
require "src/IniFileManager.php";

// config ini file
$file = __DIR__ . "/config.ini";

// instance
$ini = new IniFileManager($file);

// do something...
echo $ini->getItem('database', 'port') . PHP_EOL;
$ini->setItem('database', 'port', 9999);
echo $ini->getItem('database', 'port') . PHP_EOL;

// add a new category ...
$ini->addItem('categoria', 'item', 'value');

// save changes into a new file
//$ini->save(__DIR__ . '/configuration2.ini');
// save changes into the same file
$ini->save();
