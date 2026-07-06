<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Core\Cli;

abstract class Controller
{
	public function __construct()
	{
		// 子类初始化
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
	}

	protected function echoLine()
	{
		echo "\033[31m ================【" . GROUP_NAME . '/' . MODULE_NAME . '/' . ACTION_NAME . "】================ \033[0m\n";
	}

	protected function echo($message = '')
	{
		echo "{$message}\n";
	}
}
