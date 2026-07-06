<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Driver\Cache;

use ColaPHP\Framework\Driver\RedisConnect;

class Redis extends RedisConnect
{
	public function __construct($config = [])
	{
		$this->init($config);
		$this->open(null, null);
	}

	public function get($name)
	{
		$value = $this->redis_client->get($this->config['prefix'] . $name);

		return unserialize($value);
	}

	public function set($name, $value, $expire = null)
	{
		if (is_null($expire)) {
			$expire = $this->config['expire'];
		}
		if (is_int($expire)) {
			$result = $this->redis_client->setex($this->config['prefix'] . $name, $expire, serialize($value));
		} else {
			$result = $this->redis_client->set($this->config['prefix'] . $name, serialize($value));
		}

		return $result;
	}

	public function rm($name)
	{
		return $this->redis_client->delete($this->config['prefix'] . $name);
	}

	public function clear()
	{
		return $this->redis_client->flushDB();
	}
}
