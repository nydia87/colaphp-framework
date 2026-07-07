<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Core;

abstract class Controller
{
	/**
	 * 请求对象
	 *
	 * @var object
	 */
	protected $requestObj;

	/**
	 * 视图实例对象
	 *
	 * @var object
	 */
	protected $view;

	/**
	 * 架构函数.
	 */
	public function __construct()
	{
		// 请求对象
		$this->requestObj = new Request();
		// 视图
		$this->view = new View();
		// 子类初始化
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
	}

	/**
	 * 设置模板显示变量的值
	 *
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function __set($name = null, $value = null)
	{
		$this->view->assign($name, $value);
	}

	/**
	 * 取得模板显示变量的值
	 *
	 * @param string $name
	 */
	public function __get($name = '')
	{
		return $this->view->get($name);
	}

	/**
	 * 模板显示.
	 *
	 * @param mixed $templateFile
	 */
	protected function display($templateFile = '')
	{
		$this->view->display($templateFile);
	}

	/**
	 * 获取输出页面内容.
	 *
	 * @param mixed $templateFile
	 */
	protected function fetch($templateFile = '')
	{
		return $this->view->fetch($templateFile);
	}

	/**
	 * 模板变量赋值
	 *
	 * @param mixed $name
	 * @param mixed $value
	 */
	protected function assign($name = null, $value = null)
	{
		$this->view->assign($name, $value);
	}
}
