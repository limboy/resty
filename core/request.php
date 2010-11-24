<?php

class Request_Exception extends Exception {}

class Request 
{
	public $resources;
	public $request_method;

	public function __construct($resources)
	{
		$this->resources = $resources;
		$request_method = strtoupper($_SERVER['REQUEST_METHOD']);
		if (!in_array($request_method, array('POST', 'GET', 'PUT', 'DELETE'))) 
		{
			throw new Request_Exception('request method not support: '.$request_method);
		}
		$this->request_method = $request_method;
	}

	public function exec()
	{
		$resource_uri = parse_url($_SERVER['REQUEST_URI']);
		$resource_uri = str_replace('/index.php', '', $resource_uri['path']);
		if(array_key_exists($resource_uri, $this->resources))
		{
			require RESOURCE_PATH.$this->resources[$resource_uri].'.php';
		}
		else 
		{
			throw new Request_Exception('resource not found: '.$resource_uri);
		}

		$class_name = 'Resource_'.str_replace('/', '_', $this->resources[$resource_uri]);
		$class = new ReflectionClass($class_name);
		$resource = $class->newInstance($this);
		$class->getMethod('before')->invoke($resource);
		$class->getMethod($this->request_method)->invoke($resource);
		$class->getMethod('after')->invoke($resource);
		
		return $resource;
	}
}
