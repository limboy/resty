<?php
class Request_Exception extends Resty_Exception {}

class Resty_Request 
{
	public $resources;
	public $request_method;

	protected $_data;

	private static $_instance;

	public static function instance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new Request();
		}
		return self::$_instance;
	}

	protected function _initData()
	{
		switch($this->request_method)
		{
			case 'GET':
				$this->_data = $_GET;
				break;
			case 'POST':
				$this->_data = $_POST;
				break;
			default:
				$fp = fopen('php://input', 'r');
				$tmp_data = '';
				while(!feof($fp))
				{
					$tmp_data .= fread($fp, 1024);
				}
				parse_str($tmp_data, $this->_data);
		}
	}

	public function getData()
	{
		return $this->_data;
	}

	public function setData($key, $val = null)
	{
		if (is_array($key))
		{
			$this->_data = array_merge($this->_data, $key);
		}
		else
		{
			$this->_data[$key] = $val;
		}
	}

	public function __construct()
	{
		$this->resources = Config::get('resource');
		$request_method = strtoupper($_SERVER['REQUEST_METHOD']);
		if (!in_array($request_method, array('POST', 'GET', 'PUT', 'DELETE'))) 
		{
			throw new Request_Exception('request method not support: '.$request_method);
		}
		$this->request_method = $request_method;
		$this->_initData();
	}

	public function getResource()
	{
		static $resource;
		if (empty($resource))
			$resource = Route::parse();
		return $resource;
	}

	public function exec()
	{
		$class_name = 'Resource_'.str_replace('/', '_', $this->getResource());
		$class = new ReflectionClass($class_name);
		$resource = $class->newInstance($this);
		$class->getMethod('before')->invoke($resource);
		$class->getMethod($this->request_method)->invoke($resource);
		$class->getMethod('after')->invoke($resource);
		
		$response = Response::instance();
		$response->set_body($resource->get_data());
		return $response;
	}
}
