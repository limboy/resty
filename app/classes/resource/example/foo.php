<?php

class Resource_Example_Foo extends Resource
{
	public function get()
	{
		$this->_data = array_merge($this->get_data(), array('type' => 'post'));
	}
}
