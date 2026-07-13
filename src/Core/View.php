<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */
namespace ColaPHP\Framework\Core;

class View
{
	protected $tVar = [];

	public function assign($name = '', $value = null)
	{
		if (is_array($name)) {
			$this->tVar = array_merge($this->tVar, $name);
		} elseif (is_object($name)) {
			foreach ($name as $key => $val) {
				$this->tVar[$key] = $val;
			}
		} else {
			$this->tVar[$name] = $value;
		}
	}

	public function get($name = '')
	{
		if (isset($this->tVar[$name])) {
			return $this->tVar[$name];
		}

		return false;
	}

	public function getAllVar()
	{
		return $this->tVar;
	}

	public function display($templateFile = '')
	{
		// 解析并获取模板内容
		$content = $this->fetch($templateFile);
		// 输出模板内容
		$this->show($content);
	}

	public function show($content)
	{
		// 网页字符编码
		header('Content-Type:text/html; charset=utf-8');
		header('Cache-control: private');  // 支持页面回跳
		// 输出模板文件
		echo $content;
	}

	public function fetch($templateFile = '')
	{
		$module = MODULE_NAME;
		$action = ACTION_NAME;
		if (!empty($templateFile)) {
			$names = explode(':', $templateFile);
			if (2 == count($names)) {
				$module = ucfirst($names[0]);
				$action = $names[1];
			} elseif (1 == count($names)) {
				$action = $names[0];
			}
		}
		$file = sprintf('/%s/view/%s/%s.php', GROUP_NAME, $module, $action);
		$filepath = APP_PATH . $file;
		// 模板文件不存在直接返回
		if (!is_file($filepath)) {
			halt('tpl not exists: ' . $file);
		}
		// 页面缓存
		ob_start();
		ob_implicit_flush(0);
		// 模板阵列变量分解成为独立变量
		extract($this->tVar, EXTR_OVERWRITE);
		// 直接载入PHP模板
		include $filepath;
		// 获取并清空缓存
		$content = ob_get_clean();

		// 输出模板文件
		return $content;
	}
}
