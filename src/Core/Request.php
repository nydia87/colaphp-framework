<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */

namespace ColaPHP\Framework\Core;

use ColaPHP\Framework\Utils\Upload;

class Request
{
	protected $method;

	protected $input;

	protected $param = [];

	protected $get = [];

	protected $post = [];

	protected $request = [];

	public function __construct()
	{
		// 保存 php://input
		$this->input = file_get_contents('php://input');
		// 请求方法
		$this->method();
	}

	public function __set($name = '', $value = '')
	{
		$this->{$name} = $value;
	}

	public function __get($name = '')
	{
		return $this->{$name} ?: '';
	}

	public function method()
	{
		if (! $this->method) {
			if ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
				$this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
			} else {
				$this->method = $this->methodOrigin();
			}
		}

		return $this->method;
	}

	public function param($name = '')
	{
		$method = $this->methodOrigin();

		// 自动获取请求变量
		switch ($method) {
			case 'POST':
				$vars = $this->post(false);
				break;
			case 'PUT':
			case 'DELETE':
			case 'PATCH':
				$vars = $this->put(false);
				break;
			default:
				$vars = [];
		}

		// 当前请求参数和URL地址中的参数合并
		$this->param = array_merge($this->param, $this->get(false), $vars);

		// 获取包含文件上传信息的数组
		if (true === $name) {
			$file = $this->file();
			$data = is_array($file) ? array_merge($this->param, $file) : $this->param;

			return $this->input($data, '');
		}

		return $this->input($this->param, $name);
	}

	public function request($name = '')
	{
		if (empty($this->request)) {
			$this->request = $_REQUEST;
		}

		return $this->input($this->request, $name);
	}

	public function get($name = '')
	{
		if (empty($this->get)) {
			$this->get = $_GET;
		}

		return $this->input($this->get, $name);
	}

	public function post($name = '')
	{
		if (empty($this->post)) {
			$this->post = ! empty($_POST) ? $_POST : $this->getInputData($this->input);
		}

		return $this->input($this->post, $name);
	}

	public function put($name = '')
	{
		if (is_null($this->put)) {
			$this->put = $this->getInputData($this->input);
		}

		return $this->input($this->put, $name);
	}

	public function file($name = '')
	{
		if (empty($this->file)) {
			$this->file = isset($_FILES) ? $_FILES : [];
		}

		$files = $this->file;
		if (! empty($files)) {
			if (strpos($name, '.')) {
				[$name, $sub] = explode('.', $name);
			}

			// 处理上传文件
			$array = $this->doUploadFile($files, $name);

			if ('' === $name) {
				// 获取全部文件
				return $array;
			}
			if (isset($sub, $array[$name][$sub])) {
				return $array[$name][$sub];
			}
			if (isset($array[$name])) {
				return $array[$name];
			}
		}
	}

	public function input($data = [], $name = '')
	{
		// 获取原始数据
		if (false === $name) {
			return $data;
		}

		$name = (string) $name;
		if ('' != $name) {
			// 解析name
			if (strpos($name, '/')) {
				[$name, $type] = explode('/', $name);
			}

			$data = $this->getData($data, $name);

			if (is_null($data)) {
				return null;
			}

			if (is_object($data)) {
				return $data;
			}
		}

		return $data;
	}

	public function contentType()
	{
		$contentType = $this->server('CONTENT_TYPE');

		if ($contentType) {
			if (strpos($contentType, ';')) {
				[$type] = explode(';', $contentType);
			} else {
				$type = $contentType;
			}

			return trim($type);
		}

		return '';
	}

	protected function getInputData($content)
	{
		if (false !== strpos($this->contentType(), 'application/json') || 0 === strpos($content, '{"')) {
			return (array) json_decode($content, true);
		}
		if (strpos($content, '=')) {
			parse_str($content, $data);

			return $data;
		}

		return [];
	}

	protected function getData(array $data, $name)
	{
		foreach (explode('.', $name) as $val) {
			if (isset($data[$val])) {
				$data = $data[$val];
			} else {
				return;
			}
		}

		return $data;
	}

	protected function doUploadFile($files, $name)
	{
		$array = [];
		foreach ($files as $key => $file) {
			if ($file instanceof Upload) {
				$array[$key] = $file;
			} elseif (is_array($file['name'])) {
				$item = [];
				$keys = array_keys($file);
				$count = count($file['name']);

				for ($i = 0; $i < $count; ++$i) {
					if ($file['error'][$i] > 0) {
						if ($name == $key) {
							$this->throwUploadFileError($file['error'][$i]);
						} else {
							continue;
						}
					}

					$temp['key'] = $key;

					foreach ($keys as $_key) {
						$temp[$_key] = $file[$_key][$i];
					}

					$item[] = (new Upload($temp['tmp_name']))->setUploadInfo($temp);
				}

				$array[$key] = $item;
			} else {
				if ($file['error'] > 0) {
					if ($key == $name) {
						$this->throwUploadFileError($file['error']);
					} else {
						continue;
					}
				}

				$array[$key] = (new Upload($file['tmp_name']))->setUploadInfo($file);
			}
		}

		return $array;
	}

	protected function server($name = '')
	{
		if (empty($name)) {
			return $_SERVER;
		}
		$name = strtoupper($name);

		return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
	}

	protected function throwUploadFileError($error)
	{
		static $fileUploadErrors = [
			1 => 'upload File size exceeds the maximum value',
			2 => 'upload File size exceeds the maximum value',
			3 => 'only the portion of file is uploaded',
			4 => 'no file to uploaded',
			6 => 'upload temp dir not found',
			7 => 'file write error',
		];

		$msg = $fileUploadErrors[$error];

		throw new \Exception($msg);
	}

	private function methodOrigin()
	{
		return $this->server('REQUEST_METHOD') ?: 'GET';
	}
}
