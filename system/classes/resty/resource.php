<?php
class Resource_Exception extends Resty_Exception {}

class Resty_Resource 
{
	protected $_data;
	protected $_request;

	public function __construct($request)
	{
		$this->_request = $request;
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
