<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
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
		// 安全检测
		if (! preg_match('/^[A-Za-z_0-9]+$/', MODULE_NAME)) {
			$module = false;
		} else {
			// 创建控制器实例
			$module = import(GROUP_NAME . '/controller/' . MODULE_NAME);
		}

		if (! $module) {
			// 空模块
			$module = import(GROUP_NAME . '/controller/Error');
			if (! $module) {
				halt('class not exists: ' . GROUP_NAME . '~' . MODULE_NAME);
			}
		}
		// 获取当前操作名
		$action = ACTION_NAME;
		if (! method_exists($module, $action)) {
			// 空方法
			$action = '_empty';
			if (! method_exists($module, $action)) {
				halt('action not exists: ' . GROUP_NAME . '~' . MODULE_NAME . '~' . ACTION_NAME);
			}
		}
		// 执行当前操作
		$module->{$action}();
	}
}
