<?php

define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(__FILE__).DS);
define('RESOURCE_PATH', dirname(__FILE__).DS.'resources'.DS);

require APP_PATH.'core'.DS.'request.php';
require APP_PATH.'core'.DS.'response.php';
require APP_PATH.'core'.DS.'resource.php';

$resources = array(
	'/example' => 'example',
);

$request = new Request($resources);
$response = $request->exec();
$response->output();
