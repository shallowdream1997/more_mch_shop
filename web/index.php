<?php
error_reporting(E_ERROR | E_PARSE);
//define('YII_DEBUG', true);
//defined('YII_ENV') or define('YII_ENV', 'dev');
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require __DIR__ . '/../vendor/autoload.php';

$app = new app\hejiang\Application();
$app->run();
