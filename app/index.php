<?php

define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(__FILE__).DS);
define('SYS_PATH', realpath(dirname(__FILE__).DS.'..'.DS.'system').DS);
define('RESOURCE_PATH', dirname(__FILE__).DS.'classes'.DS.'resource'.DS);

require SYS_PATH.'classes'.DS.'resty'.DS.'core.php';
require SYS_PATH.'classes'.DS.'resty.php';

Resty::instance()->init();

$resources = array(
	'/example' => 'example',
);

$request = new Request($resources);
$response = $request->exec();
$response->output();
