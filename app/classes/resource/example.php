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
		$this->_data = array(
			'date' => date('Y/m/d H:i:s'),
			'name' => 'lzyy',
		);
	}

	public function post()
	{
		$this->_data = array_merge($_POST, array('name' => 'lzyy'));
	}
}
