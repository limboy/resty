<?php

class Resource_Example extends Resource
{
	public function get()
	{
		/* set etag
		Response::instance()
			->if_none_match(md5('hello'))
			->add_etag(md5('hello'))
			;
		//*/
		if ($this->validate())
		{
			$this->_data = $this->get_data();
		}
		else 
		{
			$this->_data = array('error' => implode(',', $this->getErrors()), 'request' => $_SERVER['REQUEST_URI']);
		}
	}

	public function post()
	{
		$this->_data = array_merge($this->get_data(), array('type' => 'post'));
	}
}
