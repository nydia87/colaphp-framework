<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework;

use ColaPHP\Framework\Core\Config;
use ColaPHP\Framework\Core\Env;

class ColaPHP
{
	public static function start(): void
	{
		set_error_handler(['ColaPHP\Framework\ColaPHP', 'appError']);
		set_exception_handler(['ColaPHP\Framework\ColaPHP', 'appException']);
		spl_autoload_register(['ColaPHP\Framework\ColaPHP', 'autoload']);
		ColaPHP::buildApp();
		if (IS_CLI) {
			Core\Cli\App::run();
		} else {
			Core\Lite\App::run();
		}
	}

	/**
	 * 自定义错误处理.
	 */
	public static function appError(int $errno, string $errstr, string $errfile, int $errline): void
	{
		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	/**
	 * 自定义异常处理.
	 */
	public static function appException(\Throwable $e): void
	{
		halt($e->__toString());
	}

	/**
	 * 自动加载路径.
	 *
	 * @param mixed $class
	 */
	public static function autoload($class): void {}

	private static function buildApp()
	{
		Config::set(include COLAPHP_PATH . 'convention.php');

		if (is_file(ROOT_PATH . '.env')) {
			Env::load(ROOT_PATH . '.env');
		}

		include COLAPHP_PATH . 'common.php';

		bulid_runtime();
	}
}
