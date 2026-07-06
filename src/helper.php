<?php
// 框架目录
define("COLAPHP_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR);
// 根目录
define("ROOT_PATH", dirname(COLAPHP_PATH, 4) . DIRECTORY_SEPARATOR);
const APP_NAME = 'app';
const IS_CLI = PHP_SAPI == 'cli' ? 1 : 0;
const APP_PATH = ROOT_PATH . APP_NAME .DIRECTORY_SEPARATOR;
const RUNTIME_PATH = ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR;
const LOG_PATH = RUNTIME_PATH . 'logs' . DIRECTORY_SEPARATOR;
const CACHE_PATH = RUNTIME_PATH . 'cache' . DIRECTORY_SEPARATOR;

function bulid_runtime()
{
    if (! is_dir(RUNTIME_PATH)) {
        @mkdir(RUNTIME_PATH, 0755);
    }elseif(!is_writeable(RUNTIME_PATH)) {
        header('Content-Type:text/html; charset=utf-8');
        exit('目录 [ '.RUNTIME_PATH.' ] 不可写！');
    }
    if (! is_dir(LOG_PATH)) {
        @mkdir(LOG_PATH, 0755);
    }
    if (! is_dir(CACHE_PATH)) {
        @mkdir(CACHE_PATH, 0755);
    }
}