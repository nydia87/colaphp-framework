<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */
if (! defined('COLAPHP_PATH')) {
	exit;
}

return [
	'app' => [
		// 调试：app_debug ? '输出错误信息': ( error_page ? '定向到错误页面' : ( show_error_msg ? '显示错误信息' : error_message ) )
		'app_debug' => false,
		'error_page' => '',
		'show_error_msg' => true,
		'error_message' => '页面错误！请稍后再试～',

		// 默认设定
		'default_timezone' => 'PRC',
		'default_group_list' => ['admin', 'home', 'api'],
		'default_group' => 'home',
		'default_module' => 'Index',
		'default_action' => 'index',

		// 模板引擎设置
		'tmpl_exception_file' => COLAPHP_PATH . 'Tpl/exception.tpl', // 异常页面的模板文件
		'tmpl_template_suffix' => '.html',     // 默认模板文件后缀
		'tmpl_file_depr' => '/', // 模板文件MODULE_NAME与ACTION_NAME之间的分割符，只对项目分组部署有效

		// URL设置
		'url_pathinfo_depr' => '/',	// PATHINFO模式下，各参数之间的分割符号
		'url_pathinfo_fetch' => 'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
		'url_html_suffix' => '.html',  // URL伪静态后缀设置

		// 系统变量名称设置
		'var_group' => 'group',     // 默认分组获取变量
		'var_module' => 'controller',	// 默认模块获取变量
		'var_action' => 'action',		// 默认操作获取变量
		'var_pathinfo' => 's',	// PATHINFO 兼容模式获取变量例如 ?s=/module/action/id/1 后面的参数取决于URL_PATHINFO_DEPR
		'var_url_params' => '_URL_', // PATHINFO URL参数变量
	],
	'session' => [
		'id' => '',
		'var_session_id' => '',
		'prefix' => 'cola::session::',
		'auto_start' => true,
		'httponly' => true,
		'secure' => false,
		'expire' => 300,
		'type' => '', // 留空(原生) | redis
		'redis_config' => [],
	],
	'cache' => [
		'prefix' => 'cola::cache::',
		'expire' => 300,
		'type' => 'file', // file | redis
		'redis_config' => [],
	],
	'log' => [
		'level' => ['debug', 'info', 'notice', 'warning', 'error', 'sql'],
		'time_format' => 'c',
		'single' => false,
		'file_size' => 2097152,
		'apart_level' => ['warning', 'error', 'sql'],
		'json' => false,
	],
];
