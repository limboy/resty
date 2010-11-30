<?php

class Resty_Core
{
	protected static $_instance;

	public static function instance()
	{
		if (empty(self::$_instance))
		{
			self::$_instance = new Resty();
		}
		return self::$_instance;
	}

	public function init()
	{
		spl_autoload_register(array($this, 'autoload'));
	}

	public function findfile($filename)
	{
		$filename = str_replace('_', DS, $filename);
		$app_file = APP_PATH.'classes'.DS.$filename.'.php';
		$sys_file = SYS_PATH.'classes'.DS.$filename.'.php';
		return file_exists($app_file) ?
			$app_file :
			(file_exists($sys_file) ? $sys_file : false)
			;
	}

	public function autoload($name)
	{
		if ($file = $this->findfile(strtolower($name)))
		{
			require $file;
			return true;
		}
		return false;
	}
}
