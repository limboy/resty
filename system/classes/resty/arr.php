<?php

class Resty_Arr
{
	public static function path($array, $path, $default = NULL)
	{
		$path = trim($path, '.* ');
		$keys = explode('.', $path);

		do
		{
			$key = array_shift($keys);

			if (ctype_digit($key))
			{
				$key = (int) $key;
			}

			if (isset($array[$key]))
			{
				if ($keys)
				{
					if (is_array($array[$key]))
					{
						$array = $array[$key];
					}
					else
					{
						break;
					}
				}
				else
				{
					return $array[$key];
				}
			}
			else
			{
				break;
			}
		}
		while ($keys);

		return $default;
	}

	public static function get($array, $key, $default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}

	public static function extract($array, array $keys, $default = NULL)
	{
		$found = array();
		foreach ($keys as $key)
		{
			$found[$key] = isset($array[$key]) ? $array[$key] : $default;
		}

		return $found;
	}

	public static function overwrite($array1, $array2)
	{
		foreach (array_intersect_key($array2, $array1) as $key => $value)
		{
			$array1[$key] = $value;
		}

		if (func_num_args() > 2)
		{
			foreach (array_slice(func_get_args(), 2) as $array2)
			{
				foreach (array_intersect_key($array2, $array1) as $key => $value)
				{
					$array1[$key] = $value;
				}
			}
		}

		return $array1;
	}

	public static function flatten($array)
	{
		$flat = array();
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$flat += Arr::flatten($value);
			}
			else
			{
				$flat[$key] = $value;
			}
		}
		return $flat;
	}

}
