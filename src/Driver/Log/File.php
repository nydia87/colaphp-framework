<?php

namespace ColaPHP\Framework\Driver\Log;

class File
{
	// 配置信息
	protected $config = [
		'time_format' => 'c',
		// 单独记录true | string
		'single' => false,
		'file_size' => 2097152,
		// 独立日志的类型 warning|error
		'apart_level' => [],
		'json' => false,
	];

	public function __construct($config = [])
	{
		if (is_array($config)) {
			$this->config = array_merge($this->config, $config);
		}
	}

	public function save(array $log = [])
	{
		$destination = $this->getMasterLogFile();

		$path = dirname($destination);
		! is_dir($path) && mkdir($path, 0755, true);

		$info = [];

		foreach ($log as $type => $val) {
			foreach ($val as $msg) {
				if (! is_string($msg)) {
					$msg = var_export($msg, true);
				}

				$info[$type][] = $this->config['json'] ? $msg : '[ ' . $type . ' ] ' . $msg;
			}

			if (! $this->config['json'] &&  in_array($type, $this->config['apart_level'] )) {
				// 独立记录的日志级别
				$filename = $this->getApartLevelFile($path, $type);

				$this->write($info[$type], $filename);

				unset($info[$type]);
			}
		}

		if ($info) {
			return $this->write($info, $destination);
		}

		return true;
	}

	protected function write($message, $destination)
	{
		// 检测日志文件大小，超过配置大小则备份日志文件重新生成
		$this->checkLogSize($destination);

		// 日志信息封装
		$info['timestamp'] = date($this->config['time_format']);

		foreach ($message as $type => $msg) {
			$msg = is_array($msg) ? implode("\r\n", $msg) : $msg;
			if ( IS_CLI ) {
				$info['msg'] = $msg;
				$info['type'] = $type;
			} else {
				$info[$type] = $msg;
			}
		}

		if ( IS_CLI ) {
			$message = $this->parseCliLog($info);
		} else {
			$message = $this->parseLog($info);
		}

		return error_log($message, 3, $destination);
	}

	protected function getMasterLogFile()
	{
		$cli = IS_CLI ? '_cli' : '';

		if ($this->config['single']) {
			$name = is_string($this->config['single']) ? $this->config['single'] : 'single';
			$destination = LOG_PATH . $name . $cli . '.log';
		} else {
			$filename = date('Ym') . DIRECTORY_SEPARATOR . date('d') . $cli . '.log';
			$destination = LOG_PATH . $filename;
		}

		return $destination;
	}

	protected function getApartLevelFile($path, $type)
	{
		$cli = IS_CLI ? '_cli' : '';

		if ($this->config['single']) {
			$name = is_string($this->config['single']) ? $this->config['single'] : 'single';
		} else {
			$name = date('d');
		}

		return $path . DIRECTORY_SEPARATOR . $name . '_' . $type . $cli . '.log';
	}

	protected function checkLogSize($destination)
	{
		if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
			try {
				rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . time() . '-' . basename($destination));
			} catch (\Exception $e) {
			}
		}
	}

	protected function parseCliLog($info)
	{
		if ($this->config['json']) {
			$message = json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\r\n";
		} else {
			$now = $info['timestamp'];
			unset($info['timestamp']);

			$message = implode("\r\n", $info);

			$message = "[{$now}]" . $message . "\r\n";
		}

		return $message;
	}

	protected function parseLog($info)
	{
		if ($this->config['json']) {
			return json_encode($info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\r\n";
		}

		array_unshift($info, "---------------------------------------------------------------\r\n[{$info['timestamp']}]");
		unset($info['timestamp']);

		return implode("\r\n", $info) . "\r\n";
	}
}
