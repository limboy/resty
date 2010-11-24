<?php

class Resource_Example extends Resource
{
	public function get()
	{
		$this->_data = array(
			'date' => date('Y/m/d'),
			'name' => 'lzyy',
		);
	}

	public function post()
	{
		$this->_data = array_merge($_POST, array('name' => 'lzyy'));
	}
}
