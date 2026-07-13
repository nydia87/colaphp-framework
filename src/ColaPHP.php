<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */
namespace ColaPHP\Framework;

class ColaPHP
{
	/**
	 * 运行.
	 */
	public static function start(): void
	{
		Core\App::run();
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
}
