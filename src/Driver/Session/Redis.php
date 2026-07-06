<?php

namespace ColaPHP\Framework\Driver\Session;

use ColaPHP\Framework\Driver\RedisConnect;

class Redis extends RedisConnect implements \SessionHandlerInterface
{
	public function __construct($config = [])
	{
        $this->init($config);
	}

	public function close()
	{
		$this->gc(ini_get('session.gc_maxlifetime'));
		$this->redis_client->close();
		$this->redis_client = null;

		return true;
	}

	public function read($sessID)
	{
		return (string) $this->redis_client->get($this->config['prefix'] . $sessID);
	}

	public function write($sessID, $sessData)
	{
		if ($this->config['expire'] > 0) {
			$result = $this->redis_client->setex($this->config['prefix'] . $sessID, $this->config['expire'], $sessData);
		} else {
			$result = $this->redis_client->set($this->config['prefix'] . $sessID, $sessData);
		}

		return $result ? true : false;
	}

	public function destroy($sessID)
	{
		return $this->redis_client->delete($this->config['prefix'] . $sessID) > 0;
	}

	public function lock($sessID, $timeout = 10)
	{
		if (null == $this->redis_client) {
            $this->open(null, null);
		}

		$lockKey = 'LOCK_PREFIX_' . $sessID;

		$isLock = $this->redis_client->setnx($lockKey, 1);
		if ($isLock) {
			// 设置过期时间，防止死任务的出现
			$this->redis_client->expire($lockKey, $timeout);

			return true;
		}

		return false;
	}

	public function unlock($sessID)
	{
		if (null == $this->redis_client) {
            $this->open(null, null);
		}

		$this->redis_client->del('LOCK_PREFIX_' . $sessID);
	}

    public function gc($max_lifetime)
    {
        return true;
    }
}
