<?php

class Resource_Example extends Resource
{
	public function get()
	{
		/*
		Response::instance()
			->if_none_match(md5('hello'))
			->add_etag(md5('hello'))
			;
		//*/
		if ($this->validate())
		{
			$this->_data = array(
				'date' => date('Y/m/d H:i:s'),
				'name' => 'lzyy',
			);
		}
		else 
		{
			$this->_data = array('error' => implode(',', $this->getErrors()), 'request' => $_SERVER['REQUEST_URI']);
		}
	}

	public function post()
	{
		$this->_data = array_merge($_POST, array('name' => 'lzyy'));
	}
}
