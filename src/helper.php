<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */
use ColaPHP\Framework\Core\Config;
use ColaPHP\Framework\Core\Env;

const DS = DIRECTORY_SEPARATOR;

define('ROOT_PATH', dirname(__FILE__, 5) . DS);
define('COLAPHP_PATH', dirname(__FILE__) . DS);

if (! is_file(COLAPHP_PATH . 'helper.php')) {
	exit('Error: invalid path : `' . COLAPHP_PATH . '`');
}

const APP_NAME = 'app';
const IS_CLI = PHP_SAPI == 'cli' ? 1 : 0;
const APP_PATH = ROOT_PATH . APP_NAME . DS;
const RUNTIME_PATH = ROOT_PATH . 'runtime' . DS;
const LOG_PATH = RUNTIME_PATH . 'logs' . DS;
const CACHE_PATH = RUNTIME_PATH . 'cache' . DS;

function app_init()
{
	// Runtime
	if (! is_dir(RUNTIME_PATH)) {
		@mkdir(RUNTIME_PATH, 0755);
	} elseif (! is_writeable(RUNTIME_PATH)) {
		header('Content-Type:text/html; charset=utf-8');
		exit('目录 [ ' . RUNTIME_PATH . ' ] 不可写！');
	}
	if (! is_dir(LOG_PATH)) {
		@mkdir(LOG_PATH, 0755);
	}
	if (! is_dir(CACHE_PATH)) {
		@mkdir(CACHE_PATH, 0755);
	}

	// 框架配置
	Config::set(include COLAPHP_PATH . 'convention.php');
	if (is_file(ROOT_PATH . '.env')) {
		Env::load(ROOT_PATH . '.env');
	}
	include COLAPHP_PATH . 'common.php';

	// 项目配置
	if (is_file(APP_PATH . 'config.php')) {
		Config::load(APP_PATH . 'config.php');
	}
	if (is_file(APP_PATH . 'common.php')) {
		include APP_PATH . 'common.php';
	}

	// 分组配置
	if (defined('GROUP_NAME')) {
		if (is_file(APP_PATH . GROUP_NAME . DS . 'config.php')) {
			Config::load(APP_PATH . GROUP_NAME . DS . 'config.php');
		}
		if (is_file(APP_PATH . GROUP_NAME . DS . 'function.php')) {
			include APP_PATH . GROUP_NAME . DS . 'function.php';
		}
	}

	// 设置系统时区
	date_default_timezone_set(config(Config::PREFIX_APP . 'default_timezone'));

	// 错误、异常
	set_error_handler(['ColaPHP\Framework\ColaPHP', 'appError']);
	set_exception_handler(['ColaPHP\Framework\ColaPHP', 'appException']);
}
app_init();
