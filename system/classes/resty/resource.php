<?php
class Resource_Exception extends Resty_Exception {}

class Resty_Resource 
{
	protected $_data;
	protected $_request;
	protected $_validation;

	public function __construct($request)
	{
		$this->_request = $request;
	}

	public function validate()
	{
		$this->_validation = Validation::factory($this->_request->getData());
		$resource = $this->_request->getResource();
		$config = Config::get('validation.'.$resource.'.'.strtolower($this->_request->request_method));
		foreach ($config as $key => $val)
		{
			foreach ($val as $field => $func)
			{
				$this->_validation->$key($field, $func);
			}
		}
		return $this->_validation->check();
	}

	public function getErrors()
	{
		return $this->_validation->getErrors();
	}

	public function before() 
	{
	}

	public function get_data() 
	{
		return $this->_data;
	}

	public function after()
	{
	}
}
