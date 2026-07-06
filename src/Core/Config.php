<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Core;

class Config
{
	public const PREFIX_APP = 'app.';

	public const PREFIX_SESSION = 'session.';

	public const PREFIX_LOG = 'log.';

	public const PREFIX_CACHE = 'cache.';

	public const PREFIX_DB = 'db.';

	protected static $config = [];

	public static function load($file, $name = '')
	{
		if (is_file($file)) {
			$type = pathinfo($file, PATHINFO_EXTENSION);
			if ('php' == $type) {
				return self::set(include $file, $name);
			}
		}

		return self::$config;
	}

	/**
	 * 获取配置参数 为空则获取所有配置.
	 *
	 * @param string $name    配置参数名（支持多级配置 .号分割）
	 * @param mixed  $default 默认值
	 *
	 * @return mixed
	 */
	public static function get($name = null, $default = null)
	{
		// 无参数时获取所有
		if (empty($name)) {
			return self::$config;
		}
		if (false === strpos($name, '.')) {
			$name = self::PREFIX_APP . $name;
		}

		if ('.' == substr($name, -1)) {
			$name = strtolower(substr($name, 0, -1));

			return isset(self::$config[$name]) ? self::$config[$name] : $default;
		}

		$name = explode('.', $name);
		$name[0] = strtolower($name[0]);
		$config = self::$config;
		// 按.拆分成多维数组进行判断
		foreach ($name as $val) {
			if (isset($config[$val])) {
				$config = $config[$val];
			} else {
				return $default;
			}
		}

		return $config;
	}

	/**
	 * 设置配置参数 name为数组则为批量设置.
	 *
	 * @param array|string $name  配置参数名（支持三级配置 .号分割）
	 * @param mixed        $value 配置值
	 */
	public static function set($name, $value = null)
	{
		if (is_string($name)) {
			if (false === strpos($name, '.')) {
				$name = self::PREFIX_APP . $name;
			}

			$name = explode('.', $name, 3);
			if (2 == count($name)) {
				self::$config[strtolower($name[0])][$name[1]] = $value;
			} else {
				self::$config[strtolower($name[0])][$name[1]][$name[2]] = $value;
			}

			return $value;
		}
		if (is_array($name)) {
			// 批量设置
			if (! empty($value)) {
				if (isset(self::$config[$value])) {
					$result = array_merge(self::$config[$value], $name);
				} else {
					$result = $name;
				}
				self::$config[$value] = $result;
			} else {
				foreach ($name as $k => $v) {
					if (isset(self::$config[$k])) {
						self::$config[$k] = array_merge(self::$config[$k], $v);
					} else {
						self::$config[$k] = $v;
					}
				}
				// $result = self::$config = array_merge(self::$config, $name);
				$result = self::$config;
			}
		} else {
			// 为空直接返回 已有配置
			$result = self::$config;
		}

		return $result;
	}

	/**
	 * 移除配置.
	 * 配置参数名（支持三级配置 .号分割）.
	 */
	public static function remove(string $name)
	{
		if (false === strpos($name, '.')) {
			$name = self::PREFIX_APP . $name;
		}

		$name = explode('.', $name, 3);
		if (2 == count($name)) {
			unset(self::$config[strtolower($name[0])][$name[1]]);
		} else {
			unset(self::$config[strtolower($name[0])][$name[1]][$name[2]]);
		}
	}
}
