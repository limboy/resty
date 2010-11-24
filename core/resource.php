<?php

class Resource 
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

	public function output()
	{
		echo json_encode($this->_data);
	}

	public function after()
	{
	}
}
