<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */
namespace ColaPHP\Framework\Core;

class App
{
	/**
	 * 应用初始化.
	 */
	public static function run(): void
	{
		// 路由调度
		Dispatcher::dispatch();
		// 安全过滤
		array_walk_recursive($_GET, 'walk_recursive_filter');
		array_walk_recursive($_POST, 'walk_recursive_filter');
		array_walk_recursive($_REQUEST, 'walk_recursive_filter');
		// session
		session(config(Config::PREFIX_SESSION));
		// 执行
		static::exec();
		logger()->save();
	}

	/**
	 * 执行方法.
	 */
	private static function exec(): void
	{
		if (!preg_match('/^[A-Za-z_0-9]+$/', MODULE_NAME)) {
			$module = false;
		} else {
			$module = import(GROUP_NAME . '/controller/' . MODULE_NAME);
		}

		$module = $module ?: import(GROUP_NAME . '/controller/Error');

		if (!$module) {
			halt('class not exists: ' . GROUP_NAME . '~' . MODULE_NAME);
		}

		$action = ACTION_NAME;
		if (method_exists($module, $action)) {
			$module->{$action}();
		} elseif (method_exists($module, '_empty')) {
			$module->_empty();
		} else {
			halt('action not exists: ' . GROUP_NAME . '~' . MODULE_NAME . '~' . ACTION_NAME);
		}
	}
}
