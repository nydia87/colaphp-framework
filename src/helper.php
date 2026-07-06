<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */
if (! defined('PROJECT_PATH')) {
	exit('need define `PROJECT_PATH` !!!');
}

$project_path = PROJECT_PATH;
define('ROOT_PATH', rtrim($project_path, '/\\') . DIRECTORY_SEPARATOR);

if (! defined('FRAME_PATH')) {
	exit('need define `FRAME_PATH` !!!');
}

$frame_path = FRAME_PATH;

define('COLAPHP_PATH', rtrim($frame_path, '/\\') . DIRECTORY_SEPARATOR);

if (! is_file(COLAPHP_PATH . 'helper.php')) {
	exit('Invalid path. `FRAME_PATH` : ' . FRAME_PATH . ' !!!');
}

const APP_NAME = 'app';
const IS_CLI = PHP_SAPI == 'cli' ? 1 : 0;
const APP_PATH = ROOT_PATH . APP_NAME . DIRECTORY_SEPARATOR;
const RUNTIME_PATH = ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR;
const LOG_PATH = RUNTIME_PATH . 'logs' . DIRECTORY_SEPARATOR;
const CACHE_PATH = RUNTIME_PATH . 'cache' . DIRECTORY_SEPARATOR;

function bulid_runtime()
{
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
}
