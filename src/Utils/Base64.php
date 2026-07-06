<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Utils;

class Base64
{
	/**
	 * 加密字符串.
	 *
	 * @param string $key    加密key
	 * @param int    $expire 有效期（秒）
	 * @param mixed  $data
	 *
	 * @return string
	 */
	public static function encrypt($data, $key = '', $expire = 0)
	{
		$expire = sprintf('%010d', $expire ? $expire + time() : 0);
		$key = md5($key);
		$data = base64_encode($expire . $data);
		$char = '';
		$str = '';
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		for ($i = 0; $i < $len; ++$i) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= substr($key, $x, 1);
			++$x;
		}

		for ($i = 0; $i < $len; ++$i) {
			$str .= chr(ord(substr($data, $i, 1)) + ord(substr($char, $i, 1)) % 256);
		}

		return $str;
	}

	/**
	 * 解密字符串.
	 *
	 * @param string $key  加密key
	 * @param mixed  $data
	 *
	 * @return string
	 */
	public static function decrypt($data, $key = '')
	{
		$key = md5($key);
		$x = 0;
		$len = strlen($data);
		$l = strlen($key);
		$char = '';
		$str = '';
		for ($i = 0; $i < $len; ++$i) {
			if ($x == $l) {
				$x = 0;
			}
			$char .= substr($key, $x, 1);
			++$x;
		}
		for ($i = 0; $i < $len; ++$i) {
			if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
				$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
			} else {
				$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
			}
		}
		$data = base64_decode($str);
		$expire = substr($data, 0, 10);
		if ($expire > 0 && $expire < time()) {
			return '';
		}

		return substr($data, 10);
	}
}
