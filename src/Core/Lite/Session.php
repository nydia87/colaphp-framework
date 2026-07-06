<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Core\Lite;

class Session
{
	protected static $instance;

	protected $config = [];

	protected $prefix = '';

	protected $init;

	public function __construct($config = [])
	{
		$this->config = $config;
	}

	public static function getInstance($config = [])
	{
		if (is_null(self::$instance)) {
			static::$instance = new self();
			static::$instance->init($config);
		}

		return static::$instance;
	}

	public function prefix($prefix = '')
	{
		empty($this->init) && $this->boot();

		if (empty($prefix) && null !== $prefix) {
			return $this->prefix;
		}
		$this->prefix = $prefix;
	}

	public function setConfig(array $config = [])
	{
		$this->config = array_merge($this->config, array_change_key_case($config));

		if (isset($config['prefix'])) {
			$this->prefix = $config['prefix'];
		}
	}

	public function inited()
	{
		$this->init = true;
	}

	/**
	 * session初始化.
	 */
	public function init(array $config = [])
	{
		$config = $config ?: $this->config;

		$isDoStart = false;
		if (isset($config['use_trans_sid'])) {
			ini_set('session.use_trans_sid', $config['use_trans_sid'] ? 1 : 0);
		}

		// 启动session
		if (! empty($config['auto_start']) && PHP_SESSION_ACTIVE != session_status()) {
			ini_set('session.auto_start', 0);
			$isDoStart = true;
		}

		if (isset($config['prefix'])) {
			$this->prefix = $config['prefix'];
		}

		if (isset($config['var_session_id'], $_REQUEST[$config['var_session_id']])) {
			session_id($_REQUEST[$config['var_session_id']]);
		} elseif (isset($config['id']) && ! empty($config['id'])) {
			session_id($config['id']);
		}

		if (isset($config['name'])) {
			session_name($config['name']);
		}

		if (isset($config['path'])) {
			session_save_path($config['path']);
		}

		if (isset($config['domain'])) {
			ini_set('session.cookie_domain', $config['domain']);
		}

		if (isset($config['expire'])) {
			ini_set('session.gc_maxlifetime', $config['expire']);
			ini_set('session.cookie_lifetime', $config['expire']);
		}

		if (isset($config['secure'])) {
			ini_set('session.cookie_secure', $config['secure']);
		}

		if (isset($config['httponly'])) {
			ini_set('session.cookie_httponly', $config['httponly']);
		}

		if (isset($config['use_cookies'])) {
			ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
		}

		if (isset($config['cache_limiter'])) {
			session_cache_limiter($config['cache_limiter']);
		}

		if (isset($config['cache_expire'])) {
			session_cache_expire($config['cache_expire']);
		}

		if (! empty($config['type'])) {
			$class = '\ColaPHP\Framework\Driver\Session\\' . ucwords($config['type']);

			if (! class_exists($class) || ! session_set_save_handler(new $class($config))) {
				halt('session error:' . $config['type']);
			}
		}

		if ($isDoStart) {
			$this->start();
		} else {
			$this->init = false;
		}

		return $this;
	}

	/**
	 * session自动启动或者初始化.
	 */
	public function boot()
	{
		if (is_null($this->init)) {
			$this->init();
		}

		if (false === $this->init) {
			if (PHP_SESSION_ACTIVE != session_status()) {
				$this->start();
			}
			$this->init = true;
		}
	}

	/**
	 * session设置.
	 *
	 * @param string      $name   session名称
	 * @param mixed       $value  session值
	 * @param null|string $prefix 作用域（前缀）
	 */
	public function set($name, $value, $prefix = null)
	{
		empty($this->init) && $this->boot();

		$prefix = ! is_null($prefix) ? $prefix : $this->prefix;

		if (strpos($name, '.')) {
			// 二维数组赋值
			[$name1, $name2] = explode('.', $name);
			if ($prefix) {
				$_SESSION[$prefix][$name1][$name2] = $value;
			} else {
				$_SESSION[$name1][$name2] = $value;
			}
		} elseif ($prefix) {
			$_SESSION[$prefix][$name] = $value;
		} else {
			$_SESSION[$name] = $value;
		}
	}

	/**
	 * session获取.
	 *
	 * @param string      $name   session名称
	 * @param null|string $prefix 作用域（前缀）
	 *
	 * @return mixed
	 */
	public function get($name = '', $prefix = null)
	{
		empty($this->init) && $this->boot();

		$prefix = ! is_null($prefix) ? $prefix : $this->prefix;

		$value = $prefix ? (! empty($_SESSION[$prefix]) ? $_SESSION[$prefix] : []) : $_SESSION;

		if ('' != $name) {
			$name = explode('.', $name);

			foreach ($name as $val) {
				if (isset($value[$val])) {
					$value = $value[$val];
				} else {
					$value = null;
					break;
				}
			}
		}

		return $value;
	}

	/**
	 * session获取并删除.
	 *
	 * @param string      $name   session名称
	 * @param null|string $prefix 作用域（前缀）
	 *
	 * @return mixed
	 */
	public function pull($name, $prefix = null)
	{
		$result = $this->get($name, $prefix);

		if ($result) {
			$this->delete($name, $prefix);

			return $result;
		}
	}

	/**
	 * session设置 下一次请求有效.
	 *
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function flash($name, $value)
	{
		$this->set($name, $value);

		if (! $this->has('__flash__.__time__')) {
			$this->set('__flash__.__time__', $_SERVER['REQUEST_TIME_FLOAT']);
		}

		$this->push('__flash__', $name);
	}

	/**
	 * 清空当前请求的session数据.
	 */
	public function flush()
	{
		if (! $this->init) {
			return;
		}

		$item = $this->get('__flash__');

		if (! empty($item)) {
			$time = $item['__time__'];

			if ($_SERVER['REQUEST_TIME_FLOAT'] > $time) {
				unset($item['__time__']);
				$this->delete($item);
				$this->set('__flash__', []);
			}
		}
	}

	/**
	 * 删除session数据.
	 *
	 * @param mixed      $name
	 * @param null|mixed $prefix
	 */
	public function delete($name, $prefix = null)
	{
		empty($this->init) && $this->boot();

		$prefix = ! is_null($prefix) ? $prefix : $this->prefix;

		if (is_array($name)) {
			foreach ($name as $key) {
				$this->delete($key, $prefix);
			}
		} elseif (strpos($name, '.')) {
			[$name1, $name2] = explode('.', $name);
			if ($prefix) {
				unset($_SESSION[$prefix][$name1][$name2]);
			} else {
				unset($_SESSION[$name1][$name2]);
			}
		} else {
			if ($prefix) {
				unset($_SESSION[$prefix][$name]);
			} else {
				unset($_SESSION[$name]);
			}
		}
	}

	/**
	 * 清空session数据.
	 *
	 * @param null|mixed $prefix
	 */
	public function clear($prefix = null)
	{
		empty($this->init) && $this->boot();
		$prefix = ! is_null($prefix) ? $prefix : $this->prefix;

		if ($prefix) {
			unset($_SESSION[$prefix]);
		} else {
			$_SESSION = [];
		}
	}

	/**
	 * 判断session数据.
	 *
	 * @param mixed      $name
	 * @param null|mixed $prefix
	 */
	public function has($name, $prefix = null)
	{
		empty($this->init) && $this->boot();

		$prefix = ! is_null($prefix) ? $prefix : $this->prefix;
		$value = $prefix ? (! empty($_SESSION[$prefix]) ? $_SESSION[$prefix] : []) : $_SESSION;

		$name = explode('.', $name);

		foreach ($name as $val) {
			if (! isset($value[$val])) {
				return false;
			}
			$value = $value[$val];
		}

		return true;
	}

	/**
	 * 添加数据到一个session数组.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function push($key, $value)
	{
		$array = $this->get($key);

		if (is_null($array)) {
			$array = [];
		}

		$array[] = $value;

		$this->set($key, $array);
	}

	/**
	 * 启动session.
	 */
	public function start()
	{
		session_start();

		$this->init = true;
	}

	/**
	 * 销毁session.
	 */
	public function destroy()
	{
		if (! empty($_SESSION)) {
			$_SESSION = [];
		}

		session_unset();
		session_destroy();

		$this->init = null;
	}

	/**
	 * 重新生成session_id.
	 *
	 * @param bool $delete 是否删除关联会话文件
	 */
	public function regenerate($delete = false)
	{
		session_regenerate_id($delete);
	}

	/**
	 * 暂停session.
	 */
	public function pause()
	{
		// 暂停session
		session_write_close();
		$this->init = false;
	}
}
