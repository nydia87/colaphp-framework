<?php
/**
 * @author: nydia87 <349196713@qq.com>
 */
use ColaPHP\Framework\Core\Cache;
use ColaPHP\Framework\Core\Config;
use ColaPHP\Framework\Core\Env;
use ColaPHP\Framework\Core\Log;
use ColaPHP\Framework\Core\Session;
use ColaPHP\Framework\Utils\Verify;

if (!defined('COLAPHP_PATH')) {
	exit;
}

/**
 * 错误输出.
 *
 * @param mixed $error
 */
function halt($error)
{
	$e = [];
	$config = config(Config::PREFIX_APP);
	if (IS_CLI) {
		var_dump($error);
	} else {
		if ($config['app_debug']) {
			// 调试模式下输出错误信息
			if (!is_array($error)) {
				$trace = debug_backtrace();
				$e['message'] = $error;
				$e['file'] = $trace[0]['file'];
				$e['class'] = $trace[0]['class'];
				$e['function'] = $trace[0]['function'];
				$e['line'] = $trace[0]['line'];
				$traceInfo = '';
				$time = date('y-m-d H:i:m');
				foreach ($trace as $t) {
					$traceInfo .= '[' . $time . '] ' . $t['file'] . ' (' . $t['line'] . ') ';
					$traceInfo .= $t['class'] . $t['type'] . $t['function'] . '(';
					$traceInfo .= implode(', ', $t['args']);
					$traceInfo .= ')<br/>';
				}
				$e['trace'] = $traceInfo;
			} else {
				$e = $error;
			}
			// 包含异常页面模板
			include $config['tmpl_exception_file'];
		} else {
			// 否则定向到错误页面
			$error_page = $config['error_page'];
			if (!empty($error_page)) {
				redirect($error_page);
			} else {
				if ($config['show_error_msg']) {
					$e['message'] = is_array($error) ? $error['message'] : $error;
				} else {
					$e['message'] = $config['error_message'];
				}
				// 包含异常页面模板
				include $config['tmpl_exception_file'];
			}
		}
	}
	exit;
}

/**
 * 获取全局 Log.
 */
function logger()
{
	static $log;
	if (!isset($log)) {
		$log = new Log(config(Config::PREFIX_LOG));
	}

	return $log;
}

/**
 * 调用项目类.
 *
 * @param mixed $name
 */
function import($name = '')
{
	static $_class = [];
	if (isset($_class[$name])) {
		return $_class[$name];
	}
	if (empty($name)) {
		return false;
	}
	$names = explode('/', $name);
	if (3 == count($names)) {
		$group = strtolower($names[0]);
		$model = strtolower($names[1]);
		$action = ucfirst($names[2]);
	} elseif (2 == count($names)) {
		$group = strtolower(config(Config::PREFIX_APP . 'default_group'));
		$model = strtolower($names[0]);
		$action = ucfirst($names[1]);
	} else {
		return false;
	}
	$class = sprintf('\%s\%s\%s\%s', ucfirst(APP_NAME), $group, $model, $action);

	if (!class_exists($class)) {
		return false;
	}
	$obj = new $class();
	$_class[$name] = $obj;

	return $obj;
}

/**
 * 过滤表单中的表达式.
 *
 * @param mixed $value
 */
function walk_recursive_filter(&$value)
{
	// 过滤查询特殊字符
	if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|LIKE|NOTLIKE|BETWEEN|NOTBETWEEN|NOT BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
		$value .= ' ';
	}
}

/**
 * 获取和设置配置参数.
 *
 * @param array|string $name  参数名
 * @param mixed        $value 参数值
 */
function config($name = '', $value = null)
{
	if (is_null($value) && is_string($name)) {
		return Config::get($name);
	}

	return Config::set($name, $value);
}

/**
 * 获取环境变量值
 *
 * @param string $name    环境变量名（支持二级 .号分割）
 * @param string $default 默认值
 */
function env($name = null, $default = null)
{
	return Env::get($name, $default);
}

/**
 * Session.
 *
 * @param mixed      $name
 * @param mixed      $value
 * @param null|mixed $prefix
 */
function session($name, $value = '', $prefix = null)
{
	if (is_array($name)) { // 初始化
		Session::getInstance($name);
	} elseif (is_null($name)) { // 清除
		Session::getInstance()->clear($prefix);
	} elseif ('' === $value) {// 判断或获取
		return 0 === strpos($name, '?') ? Session::getInstance()->has(substr($name, 1), $prefix) : Session::getInstance()->get($name, $prefix);
	} elseif (is_null($value)) {// 删除
		return Session::getInstance()->delete($name, $prefix);
	} else {// 设置
		return Session::getInstance()->set($name, $value, $prefix);
	}
}

/**
 * 缓存.
 *
 * @param mixed      $name
 * @param mixed      $value
 * @param null|mixed $expire
 */
function cache($name, $value = '', $expire = null)
{
	if (is_null($name)) {
		return Cache::getInstance()->clear();
	}

	if ('' !== $value) {
		if (is_null($value)) {
			// 删除缓存
			return Cache::getInstance()->rm($name);
		}

		// 缓存数据
		return Cache::getInstance()->set($name, $value, $expire);
	}

	// 获取缓存数据
	return Cache::getInstance()->get($name);
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 *
 * @param string $name    字符串
 * @param int    $type    转换类型
 * @param bool   $ucfirst 首字母是否大写（驼峰规则）
 */
function parse_name($name, $type = 0, $ucfirst = true)
{
	if ($type) {
		$name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
			return strtoupper($match[1]);
		}, $name);

		return $ucfirst ? ucfirst($name) : lcfirst($name);
	}

	return strtolower(trim(preg_replace('/[A-Z]/', '_\0', $name), '_'));
}

/**
 * 重定向地址
 *
 * @param mixed $url
 * @param mixed $time
 * @param mixed $msg
 */
function redirect($url, $time = 0, $msg = '')
{
	// 多行URL地址支持
	$url = str_replace(["\n", "\r"], '', $url);
	if (empty($msg)) {
		$msg = "系统将在{$time}秒之后自动跳转到{$url}！";
	}
	if (!headers_sent()) {
		// redirect
		if (0 === $time) {
			header('Location: ' . $url);
		} else {
			header("refresh:{$time};url={$url}");
			echo $msg;
		}

		exit;
	}
	$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
	if (0 != $time) {
		$str .= $msg;
	}

	exit($str);
}

/**
 * XML 编码
 *
 * @param mixed $data
 * @param mixed $encoding
 * @param mixed $root
 */
function xml_encode($data = [], $encoding = 'utf-8', $root = 'colaphp')
{
	$xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
	$xml .= '<' . $root . '>';
	$xml .= data_to_xml($data);
	$xml .= '</' . $root . '>';

	return $xml;
}

/**
 * XML编码 data.
 *
 * @param mixed $data
 */
function data_to_xml($data = [])
{
	$xml = '';
	foreach ($data as $key => $val) {
		is_numeric($key) && $key = "item id=\"{$key}\"";
		$xml .= "<{$key}>";
		$xml .= (is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
		$key = current(explode(' ', $key));
		$xml .= "</{$key}>";
	}

	return $xml;
}

/**
 * 循环创建目录.
 *
 * @param mixed $dir
 * @param mixed $mode
 */
function mk_dir($dir = '', $mode = 0777)
{
	if (is_dir($dir) || @mkdir($dir, $mode)) {
		return true;
	}
	if (!mk_dir(dirname($dir), $mode)) {
		return false;
	}

	return @mkdir($dir, $mode);
}

/**
 * 使用正则验证数据.
 *
 * @param mixed $value
 * @param mixed $rule
 */
function regex($value, $rule)
{
	$regexs = [
		'alphaDash' => '/^[A-Za-z0-9\-\_]+$/', // 字母和数字，下划线_及破折号-
		'chs' => '/^[\x{4e00}-\x{9fa5}]+$/u', // 汉字
		'chsAlpha' => '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', // 汉字、字母
		'chsAlphaNum' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', // 汉字、字母和数字
		'chsDash' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u', // 汉字、字母、数字和下划线_及破折号-
		'mobile' => '/^1[3-9][0-9]\d{8}$/',
		'idCard' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$)/',
		'zip' => '/\d{6}/',
	];

	if (isset($regexs[$rule])) {
		$rule = $regexs[$rule];
	}

	if (0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
		// 不是正则表达式则两端补上/
		$rule = '/^' . $rule . '$/';
	}

	return is_scalar($value) && 1 === preg_match($rule, (string) $value);
}

/**
 * 验证邮箱.
 *
 * @param mixed $email
 */
function validate_email($email)
{
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * 判断是否为 ajax 请求
 */
function is_ajax()
{
	return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
		&& 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
}

/**
 * 返回 Array 结构.
 *
 * @param mixed $message
 * @param mixed $status
 * @param mixed $data
 */
function cola_data($message = '', $status = 0, $data = [])
{
	return ['status' => $status, 'message' => $message, 'data' => $data];
}

/**
 * 输出数据到客户端.
 *
 * @param mixed      $message
 * @param mixed      $status
 * @param null|mixed $data
 * @param mixed      $type
 */
function cola_return($message = '', $status = 0, $data = null, $type = 'JSON')
{
	switch (strtoupper($type)) {
		case 'XML':
			header('Content-Type:text/xml; charset=utf-8');
			exit(xml_encode(cola_data($message, $status, $data)));
			break;
		case 'EVAL':
			header('Content-Type:text/html; charset=utf-8');
			exit($data);
			break;
		case 'JSON':
		default:
			header('Content-Type:text/html; charset=utf-8');
			exit(json_encode(cola_data($message, $status, $data)));
			break;
	}
}

/**
 * 获取客户端IP地址
 *
 * @return string
 */
function get_client_ip()
{
	$ip = '127.0.0.1';

	// 检查代理IP
	if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// 处理多个代理的情况，取第一个有效IP
		$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		foreach ($ips as $proxyIp) {
			$proxyIp = trim($proxyIp);
			if (filter_var($proxyIp, FILTER_VALIDATE_IP)) {
				$ip = $proxyIp;
				break;
			}
		}
	} elseif (isset($_SERVER['HTTP_X_FORWARDED']) && filter_var($_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP)) {
		$ip = $_SERVER['HTTP_X_FORWARDED'];
	} elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
		$ip = $_SERVER['HTTP_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_FORWARDED']) && filter_var($_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
		$ip = $_SERVER['HTTP_FORWARDED'];
	} elseif (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return $ip;
}

/**
 * 生成图片验证码
 *
 * @param string $id
 * @param array  $config
 */
function cola_verify($id = '', $config = [])
{
	$verfiy = new Verify($config);
	$verfiy->entry($id);
}

/**
 * 核对验证码
 *
 * @param string $code
 * @param string $id
 */
function cola_verify_check($code = '', $id = '')
{
	$verfiy = new Verify();

	return $verfiy->check($code, $id);
}
