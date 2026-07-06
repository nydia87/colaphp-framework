<?php

namespace ColaPHP\Framework\Core\Cli;

use ColaPHP\Framework\Core\Config;

class App
{

    static public function run(): void
    {
        static::init();
        // 设置系统时区
        date_default_timezone_set(config(Config::PREFIX_APP . 'default_timezone'));
        // 执行
        static::exec();
        logger()->save();
    }

    private static function init(): void
    {
        // 解析路由
        static::dispatch();
        // APP 配置
        if(is_file(APP_PATH . '/config.php'))
            Config::load( APP_PATH . '/config.php');
        if(is_file(APP_PATH . '/common.php'))
            include APP_PATH . '/common.php';
        // 分组配置
        if(is_file(APP_PATH . GROUP_NAME . '/config.php'))
            Config::load( APP_PATH . GROUP_NAME . '/config.php');
        if(is_file(APP_PATH.GROUP_NAME.'/function.php'))
            include APP_PATH.GROUP_NAME.'/function.php';
    }

    /**
     * 执行方法.
     */
    private static function exec(): void
    {
        // 安全检测
        if (! preg_match('/^[A-Za-z_0-9]+$/', MODULE_NAME)) {
            $module = false;
        } else {
            // 创建控制器实例
            $module = import(GROUP_NAME . '/controller/' . MODULE_NAME);
        }

        if (! $module) {
            halt('class not exists: ' . GROUP_NAME . '~' . MODULE_NAME);
        }
        // 获取当前操作名
        $action = ACTION_NAME;
        if (! method_exists($module, $action)) {
            halt('action not exists: ' . GROUP_NAME . '~' . MODULE_NAME . '~' . ACTION_NAME);
        }
        // 执行当前操作
        $module->$action();
    }

    /**
     * 解析 URL
     * 示例： php index.php group/module/action id/5/name/wang
     */
    private static function dispatch()
    {
        // 取得模块和操作名称
        $path   = $_SERVER['argv'][1] ?? '';
        if(!empty($path)) {
            $pathParams = explode('/',trim($path,'/'));
        }

        $config = config(Config::PREFIX_APP);

        // 支持自定义 GROUP
        if( defined('DEFAULT_GROUP_NAME') ){
            define('GROUP_NAME',DEFAULT_GROUP_NAME);
        } else {
            define('GROUP_NAME', !empty($pathParams)?array_shift($pathParams):$config['default_group']);
        }

        define('MODULE_NAME', !empty($pathParams)?array_shift($pathParams):$config['default_module']);
        define('ACTION_NAME', !empty($pathParams)?array_shift($pathParams):$config['default_action']);

        // 解析参数
        $params = $_SERVER['argv'][2] ?? '';

        if(!empty($params)) {
            preg_replace_callback(
                '@(\w+)/([^/]+)@',
                function($matches) {
                    $_GET[$matches[1]] = $matches[2];
                    return '';
                },
                $params
            );
        }
    }
}