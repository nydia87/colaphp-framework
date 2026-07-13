<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */
namespace ColaPHP\Framework\Driver\Cache;

class File
{
	protected $prefix = '~@';

	protected $config = [];

	protected $connected;

	public function __construct($config = [])
	{
		if (!empty($config)) {
			$this->config = $config;
		}
		$this->connected = is_dir(CACHE_PATH) && is_writeable(CACHE_PATH);
	}

	public function get($name)
	{
		$filename = $this->filename($name);
		if (!$this->isConnected() || !is_file($filename)) {
			return false;
		}
		$content = file_get_contents($filename);
		if (false !== $content) {
			$expire = (int) substr($content, 8, 12);
			if (0 != $expire && time() > filemtime($filename) + $expire) {
				// 缓存过期删除缓存文件
				unlink($filename);

				return false;
			}
			$check = substr($content, 20, 32);
			$content = substr($content, 52, -3);
			if ($check != md5($content)) {// 校验错误
				return false;
			}

			return unserialize($content);
		}

		return false;
	}

	public function set($name, $value, $expire = null)
	{
		if (is_null($expire)) {
			$expire = $this->config['expire'];
		}
		$filename = $this->filename($name);
		$data = serialize($value);
		// 开启数据校验
		$check = md5($data);
		$filedata = "<?php\n//" . sprintf('%012d', $expire) . $check . $data . "\n?>";
		if (file_put_contents($filename, $filedata)) {
			clearstatcache();

			return true;
		}

		return false;
	}

	public function rm($name)
	{
		return unlink($this->filename($name));
	}

	public function clear()
	{
		$path = CACHE_PATH;
		if ($dir = opendir($path)) {
			while ($file = readdir($dir)) {
				if (!is_dir($file)) {
					unlink($path . $file);
				}
			}
			closedir($dir);

			return true;
		}
	}

	public function isConnected()
	{
		return $this->connected;
	}

	private function filename($name)
	{
		$name = md5($name);
		$filename = $this->prefix . $name . '.php';

		return CACHE_PATH . $filename;
	}
}
