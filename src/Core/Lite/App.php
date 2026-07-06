<?php

namespace ColaPHP\Framework\Core\Lite;

use ColaPHP\Framework\Core\Config;

class App
{
	/**
	 * 应用初始化.
	 */
	public static function run(): void
	{
        // 初始化
        static::init();
        // 设置系统时区
        date_default_timezone_set(config(Config::PREFIX_APP . 'default_timezone'));
        // session
        session(config(Config::PREFIX_SESSION ));
        // 执行
        static::exec();
        logger()->save();
	}


	/**
	 * 启动项
	 */
	private static function init(): void
	{
        // 路由调度
        Dispatcher::dispatch();
        // APP 配置
        if(is_file(APP_PATH . '/config.php'))
            Config::load( APP_PATH . '/config.php');
        if(is_file(APP_PATH . '/common.php'))
            include APP_PATH . '/common.php';
        // 分组配置
        if(is_file(APP_PATH . GROUP_NAME . '/config.php'))
            Config::load( APP_PATH . GROUP_NAME . '/config.php');
        if(is_file(APP_PATH.GROUP_NAME.'/function.php'))
            include APP_PATH.GROUP_NAME.'/function.php';
        // 安全过滤
        array_walk_recursive($_GET, 'walk_recursive_filter');
        array_walk_recursive($_POST, 'walk_recursive_filter');
        array_walk_recursive($_REQUEST, 'walk_recursive_filter');
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
			halt('class not exists: ' . GROUP_NAME . '~' . MODULE_NAME);
		}
		// 获取当前操作名
		$action = ACTION_NAME;
		if (! method_exists($module, $action)) {
			halt('action not exists: ' . GROUP_NAME . '~' . MODULE_NAME . '~' . ACTION_NAME);
		}
		// 执行当前操作
		$module->$action();
	}

}
