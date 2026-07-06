<?php

namespace ColaPHP\Framework\Core;

class Env
{

	protected static $data = [];

	public static function load($file)
	{
		$env = parse_ini_file($file, true);
		self::set($env);
	}

	public static function set($env, $value = null)
	{
		if (is_array($env)) {
			$env = array_change_key_case($env, CASE_UPPER);

			foreach ($env as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $k => $v) {
						self::$data[$key . '_' . strtoupper($k)] = $v;
					}
				} else {
					self::$data[$key] = $val;
				}
			}
		} else {
			$name = strtoupper(str_replace('.', '_', $env));

			self::$data[$name] = $value;
		}
	}

	public static function get($name = null, $default = null, $php_prefix = true)
	{
		if (is_null($name)) {
			return self::$data;
		}

		$name = strtoupper(str_replace('.', '_', $name));

		if (isset(self::$data[$name])) {
			return self::$data[$name];
		}

		return self::getEnv($name, $default, $php_prefix);
	}

	protected static function getEnv($name, $default = null, $php_prefix = true)
	{
		if ($php_prefix) {
			$name = 'PHP_' . $name;
		}

		$result = getenv($name);

		if (false === $result) {
			return $default;
		}

		if ('false' === $result) {
			$result = false;
		} elseif ('true' === $result) {
			$result = true;
		}

		if (! isset(self::$data[$name])) {
			self::$data[$name] = $result;
		}

		return $result;
	}
}
