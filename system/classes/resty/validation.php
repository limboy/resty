<?php
class Validation_Exception extends Resty_Exception {}

class Resty_Validation extends ArrayObject {

	public static function factory(array $array)
	{
		return new Validation($array);
	}

	public static function not_empty($value)
	{
		if (is_object($value) && $value instanceof ArrayObject)
		{
			$value = $value->getArrayCopy();
		}

		return ($value === '0' OR ! empty($value));
	}

	public static function regex($value, $expression)
	{
		return (bool) preg_match($expression, (string) $value);
	}

	public static function min_length($value, $length)
	{
		return mb_strlen($value) >= $length;
	}

	public static function max_length($value, $length)
	{
		return mb_strlen($value) <= $length;
	}

	public static function exact_length($value, $length)
	{
		return mb_strlen($value) === $length;
	}

	public static function email($email)
	{
		$expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD';

		return (bool) preg_match($expression, (string) $email);
	}

	public static function url($url)
	{
		return (bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
	}

	public static function ip($ip, $allow_private = TRUE)
	{
		$flags = FILTER_FLAG_NO_RES_RANGE;

		if ($allow_private === FALSE)
		{
			$flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
		}

		return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flags);
	}

	public static function date($str)
	{
		return (strtotime($str) !== FALSE);
	}

	public static function alpha($str)
	{
		$str = (string) $str;
		return ctype_alpha($str);
	}

	public static function alpha_numeric($str)
	{
		return ctype_alnum($str);
	}

	public static function alpha_dash($str)
	{
		$regex = '/^[-a-z0-9_]++$/iD';
		return (bool) preg_match($regex, $str);
	}

	public static function digit($str)
	{
		return is_int($str) || ctype_digit($str);
	}

	public static function numeric($str)
	{
		list($decimal) = array_values(localeconv());
		return (bool) preg_match('/^-?[0-9'.$decimal.']++$/D', (string) $str);
	}

	public static function range($number, $min, $max)
	{
		return ($number >= $min && $number <= $max);
	}

	public static function color($str)
	{
		return (bool) preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
	}

	protected $_filters = array();

	protected $_rules = array();

	protected $_callbacks = array();

	protected $_labels = array();

	protected $_empty_rules = array('not_empty', 'matches');

	protected $_errors = array();

	public function __construct(array $array)
	{
		parent::__construct($array, ArrayObject::STD_PROP_LIST);
	}

	public function as_array()
	{
		return $this->getArrayCopy();
	}

	public function label($field, $label)
	{
		$this->_labels[$field] = $label;
		return $this;
	}

	public function labels(array $labels)
	{
		$this->_labels = $labels + $this->_labels;
		return $this;
	}

	public function filter($field, $filter, array $params = NULL)
	{
		if ($field !== TRUE AND ! isset($this->_labels[$field]))
		{
			$this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
		}

		$this->_filters[$field][$filter] = (array) $params;

		return $this;
	}

	public function filters($field, array $filters)
	{
		foreach ($filters as $filter => $params)
		{
			$this->filter($field, $filter, $params);
		}

		return $this;
	}

	public function rule($field, $rule, array $params = NULL)
	{
		if ($field !== TRUE AND ! isset($this->_labels[$field]))
		{
			$this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
		}

		$this->_rules[$field][$rule] = (array) $params;

		return $this;
	}

	public function rules($field, array $rules)
	{
		foreach ($rules as $rule => $params)
		{
			$this->rule($field, $rule, $params);
		}

		return $this;
	}

	public function callback($field, $callback)
	{
		if ( ! isset($this->_callbacks[$field]))
		{
			$this->_callbacks[$field] = array();
		}

		if ($field !== TRUE AND ! isset($this->_labels[$field]))
		{
			$this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
		}

		if ( ! in_array($callback, $this->_callbacks[$field], TRUE))
		{
			$this->_callbacks[$field][] = $callback;
		}

		return $this;
	}

	public function callbacks($field, array $callbacks)
	{
		foreach ($callbacks as $callback)
		{
			$this->callback($field, $callback);
		}

		return $this;
	}

	public function check()
	{
		$data = $this->_errors = array();

		$expected = array_keys($this->_labels);

		$filters   = $this->_filters;
		$rules     = $this->_rules;
		$callbacks = $this->_callbacks;

		foreach ($expected as $field)
		{
			if (isset($this[$field]))
			{
				$data[$field] = $this[$field];
			}
			else
			{
				$data[$field] = NULL;
			}

			if (isset($filters[TRUE]))
			{
				if ( ! isset($filters[$field]))
				{
					$filters[$field] = array();
				}

				$filters[$field] += $filters[TRUE];
			}

			if (isset($rules[TRUE]))
			{
				if ( ! isset($rules[$field]))
				{
					$rules[$field] = array();
				}

				$rules[$field] += $rules[TRUE];
			}

			if (isset($callbacks[TRUE]))
			{
				if ( ! isset($callbacks[$field]))
				{
					$callbacks[$field] = array();
				}

				$callbacks[$field] += $callbacks[TRUE];
			}
		}

		$this->exchangeArray($data);

		unset($filters[TRUE], $rules[TRUE], $callbacks[TRUE]);

		foreach ($filters as $field => $set)
		{
			$value = $this[$field];

			foreach ($set as $filter => $params)
			{
				array_unshift($params, $value);

				if (strpos($filter, '::') === FALSE)
				{
					$function = new ReflectionFunction($filter);

					$value = $function->invokeArgs($params);
				}
				else
				{
					list($class, $method) = explode('::', $filter, 2);

					$method = new ReflectionMethod($class, $method);

					$value = $method->invokeArgs(NULL, $params);
				}
			}

			$this[$field] = $value;
		}

		foreach ($rules as $field => $set)
		{
			$value = $this[$field];

			foreach ($set as $rule => $params)
			{
				if ( ! in_array($rule, $this->_empty_rules) AND ! Validation::not_empty($value))
				{
					continue;
				}

				array_unshift($params, $value);

				if (method_exists($this, $rule))
				{
					$method = new ReflectionMethod($this, $rule);

					if ($method->isStatic())
					{
						$passed = $method->invokeArgs(NULL, $params);
					}
					else
					{
						$passed = call_user_func_array(array($this, $rule), $params);
					}
				}
				elseif (strpos($rule, '::') === FALSE)
				{
					$function = new ReflectionFunction($rule);

					$passed = $function->invokeArgs($params);
				}
				else
				{
					list($class, $method) = explode('::', $rule, 2);

					$method = new ReflectionMethod($class, $method);

					$passed = $method->invokeArgs(NULL, $params);
				}

				if ($passed === FALSE)
				{
					array_shift($params);

					$this->error($field, $rule, $params);

					break;
				}
			}
		}

		foreach ($callbacks as $field => $set)
		{
			if (isset($this->_errors[$field]))
			{
				continue;
			}

			foreach ($set as $callback)
			{
				if (is_string($callback) AND strpos($callback, '::') !== FALSE)
				{
					$callback = explode('::', $callback, 2);
				}

				if (is_array($callback))
				{
					list ($object, $method) = $callback;

					$method = new ReflectionMethod($object, $method);

					if ( ! is_object($object))
					{
						$object = NULL;
					}

					$method->invoke($object, $this, $field);
				}
				else
				{
					$function = new ReflectionFunction($callback);

					$function->invoke($this, $field);
				}

				if (isset($this->_errors[$field]))
				{
					break;
				}
			}
		}

		Request::instance()->setData($this->as_array());

		return empty($this->_errors);
	}

	public function error($field, $error, array $params = NULL)
	{
		$this->_errors[$field] = array($error, $params);
		return $this;
	}

	public function getErrors()
	{
		$messages = array();

		foreach ($this->_errors as $field => $set)
		{
			list($error, $params) = $set;

			$label = $this->_labels[$field];

			$values = array(':field' => $label);

			if ($params)
			{
				$values[':value'] = array_shift($params);

				if (is_array($values[':value']))
				{
					$values[':value'] = implode(', ', Arr::flatten($values[':value']));
				}

				foreach ($params as $key => $value)
				{
					if (is_array($value))
					{
						$value = implode(', ', Arr::flatten($value));
					}

					if (isset($this->_labels[$value]))
					{
						$value = $this->_labels[$value];

					}

					$values[':param'.($key + 1)] = $value;
				}
			}
			else
			{
				$values[':value'] = NULL;
			}

			$resource = Request::instance()->getResource();

			if ($message = Config::get("message.{$resource}.{$field}.{$error}"))
			{
			}
			elseif ($message = Config::get("message.{$field}.{$error}"))
			{
			}
			elseif ($message = Config::get("message.{$field}.default"))
			{
			}
			elseif ($message = Config::get("message.{$error}"))
			{
			}
			else
			{
				$message = "{$field}.{$error}";
			}

			$message = strtr($message, $values);
			$messages[$field] = $message;
		}

		return $messages;
	}

	protected function matches($value, $match)
	{
		return ($value === $this[$match]);
	}

}
