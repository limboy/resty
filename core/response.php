<?php
class Response_Exception extends Exception{}

class Response {
	
	protected $_header = array();
	protected $_body = '';
	protected $_status = 200;

	protected $_messages = array(
		100 => 'Continue',
		101 => 'Switching Protocols',

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',

		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	);

	private static $_instance;

	public static function instance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new Response();
		}
		return self::$_instance;
	}

	public function add_vary($vary)
	{
		if (isset($this->_header['Vary'])) 
			$this->_header['Vary'] .= ' '.$vary;
		else 
			$this->_header['Vary'] = $vary;
		return $this;
	}

	public function set_header($key, $val = null)
	{
		if (is_array($key))
		{
			$this->_header = array_merge($this->_header + $key);
		}
		else
		{
			$this->_header[$key] = $val;
		}
		return $this;
	}

	public function get_header($key)
	{
		return isset($this->_header[$key]) ? $key : null;
	}

	public function set_status($status)
	{
		if(!array_key_exists($status, $this->_messages))
		{
			throw new Response_Exception('invalid status code:'. $status);
		}
		$this->_status = $status;
		return $this;
	}

	public function add_etag($etag)
	{
		$this->_header['etag'] = $etag;
		return $this;
	}

	public function add_cache($time = 86400) 
	{
		if ($time) 
			$this->_header['Cache-Control'] = 'max-age='.$time.', must-revalidate';
		else 
			$this->_header['Cache-Control'] = 'no-cache';
		return $this;
	}
	
	public function if_match($etag) {

		if (!empty($_SERVER['HTTP_IF_MATCH']))
		{
			$if_match = trim($_SERVER['HTTP_IF_MATCH']);
			if ($if_match == '*' || $if_match == $etag)
			{
				header('Status: 304 '.$this->_messages[304]);
				exit;
			}
		}
		return $this;
	}

	public function if_none_match($etag) {

		if (!empty($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			$if_none_match = trim($_SERVER['HTTP_IF_NONE_MATCH']);
			if ($if_none_match == '*' || $if_none_match == $etag)
			{
				header('Status: 304 '.$this->_messages[304]);
				exit;
			}
		}
		return $this;
	}

	private function _content_encoding()
	{
		if (!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && ini_get('zlib.output_compression') == 0)
		{
			$accept_encoding = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
			foreach ($accept_encoding as $encoding) 
			{
				switch($encoding) 
				{
					case 'gzip':
						$this->_header['Content-Encoding'] = 'gzip';
						$this->add_vary('Accept-Encoding');
						$this->_body = gzencode($this->_body);
						return;
					case 'deflate':
						$this->_header['Content-Encoding'] = 'deflate';
						$this->add_vary('Accept-Encoding');
						$this->_body = gzdeflate($this->_body);
						return;
					case 'compress':
						$this->_header['Content-Encoding'] = 'compress';
						$this->add_vary('Accept-Encoding');
						$this->_body = gzcompress($this->_body);
						return;
					case 'identity':
						return;
				}
			}
		}
	}
	
	public function set_body($data)
	{
		$this->_body = json_encode($data);
		return $this;
	}

	public function output() 
	{
		$this->_content_encoding();
		header('Content-type:application/json;charset=utf-8');
		header('Status:'.$this->_status.' '.$this->_messages[$this->_status]);
		header('Content-Length: '.strlen($this->_body));
		foreach($this->_header as $key => $val)
		{
			header($key.':'.$val);
		}
		echo $this->_body;
	}
}
