<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Core;

use ColaPHP\Framework\Driver\Log\File;

class Log
{
	protected $log = [];

	protected $config = [];

	protected $driver;

	protected $key;

	public function __construct($config = [])
	{
		if (is_array($config)) {
			$this->config = $config;
		}
		$this->driver = new File($config);
	}

	public function init($config = [])
	{
		$this->config = $config;
		$this->driver = new File($config);

		return $this;
	}

	public function getLogs($type = 'debug')
	{
		return $type ? $this->log[$type] : $this->log;
	}

	public function clear()
	{
		$this->log = [];

		return $this;
	}

	public function save()
	{
		if (empty($this->log)) {
			return true;
		}

		$log = [];

		foreach ($this->log as $level => $info) {
			if ('debug' == $level) {
				continue;
			}

			if (in_array($level, $this->config['level'])) {
				$log[$level] = $info;
			}
		}

		$result = $this->driver->save($log);

		if ($result) {
			$this->log = [];
		}

		return $result;
	}

	public function error($message, array $context = [])
	{
		$this->log(__FUNCTION__, $message, $context);
	}

	public function warning($message, array $context = [])
	{
		$this->log(__FUNCTION__, $message, $context);
	}

	public function notice($message, array $context = [])
	{
		$this->log(__FUNCTION__, $message, $context);
	}

	public function info($message, array $context = [])
	{
		$this->log(__FUNCTION__, $message, $context);
	}

	public function debug($message, array $context = [])
	{
		$this->log(__FUNCTION__, $message, $context);
	}

	public function sql($message, array $context = [])
	{
		$this->log(__FUNCTION__, $message, $context);
	}

	protected function log($level, $message, array $context = [])
	{
		if (is_string($message) && ! empty($context)) {
			$replace = [];
			foreach ($context as $key => $val) {
				$replace['{' . $key . '}'] = $val;
			}
			$message = strtr($message, $replace);
		}

		if (IS_CLI && in_array($level, $this->config['level'])) {
			$this->driver->save([$level => [$message]]);
		} else {
			$this->log[$level][] = $message;
		}
	}
}
