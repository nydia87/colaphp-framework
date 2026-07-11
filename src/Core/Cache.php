<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */

namespace ColaPHP\Framework\Core;

class Cache
{
	protected $handler;

	protected static $instance;

	public function __construct()
	{
		$config = config(Config::PREFIX_CACHE);
		$class = '\ColaPHP\Framework\Driver\Cache\\' . ucwords($config['type']);
		if (! class_exists($class)) {
			halt('cache error: ' . $config['type']);
		}

		$this->handler = new $class($config);
	}

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	public function get($name)
	{
		return $this->handler->get($name);
	}

	public function set($name, $value)
	{
		return $this->handler->set($name, $value);
	}

	public function rm($name)
	{
		$this->handler->rm($name);
	}

	public function clear()
	{
		$this->handler->clear();
	}

	public function isConnected()
	{
		return $this->handler->isConnected();
	}
}
