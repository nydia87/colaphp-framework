<?php

namespace ColaPHP\Framework\Core\Lite;

use ColaPHP\Framework\Core\Config;

class Dispatcher {

    /**
     * 路由
     */
    public static function dispatch(): void
    {
        $config = config(Config::PREFIX_APP );
        // 提取PATH_INFO
        static::extractPathInfo($config);
        // 解析路由参数
        $routeParams = static::parseRouteParams($config);
        $_GET = array_merge($routeParams, $_GET);
        // 定义路由常量
        define('GROUP_NAME', static::getGroup($config['var_group'], $config));
        define('MODULE_NAME', static::getModule($config['var_module'], $config));
        define('ACTION_NAME', static::getAction($config['var_action'], $config));
        $_REQUEST = array_merge($_POST, $_GET);
    }

    /**
     * 获得实际的模块名称
     */
    private static function getModule(string $var, array $config): string
    {
        $module = (! empty($_GET[$var]) ? $_GET[$var] : $config['default_module']);
        unset($_GET[$var]);
        // 智能识别方式 /user_type/index/
        $module = ucfirst(parse_name(strtolower($module), 1));

        return strip_tags($module);
    }

    /**
     * 获得实际的操作名称
     */
    private static function getAction(string $var, array $config): string
    {
        $action = ! empty($_POST[$var])
            ? $_POST[$var]
            : (! empty($_GET[$var]) ? $_GET[$var] : $config['default_action']);
        unset($_POST[$var],$_GET[$var]);

        return strip_tags($action);
    }

    /**
     * 获得实际的分组名称
     */
    private static function getGroup(string $var, array $config): string
    {
        $group = (! empty($_GET[$var]) ? $_GET[$var] : $config['default_group']);
        unset($_GET[$var]);

        return strip_tags(strtolower($group));
    }

    /**
     * 移除脚本名称
     */
    private static function stripScriptName(string $pathInfo): string
    {
        if (0 === strpos($pathInfo, $_SERVER['SCRIPT_NAME'])) {
            return substr($pathInfo, strlen($_SERVER['SCRIPT_NAME']));
        }
        return $pathInfo;
    }

    /**
     * 提取PATH_INFO
     */
    private static function extractPathInfo(array $config): void
    {
        if (!empty($_GET[$config['var_pathinfo']])) {
            $_SERVER['PATH_INFO'] = $_GET[$config['var_pathinfo']];
            unset($_GET[$config['var_pathinfo']]);
            return;
        }

        if (empty($_SERVER['PATH_INFO'])) {
            $types = explode(',', $config['url_pathinfo_fetch']);
            foreach ($types as $type) {
                if (0 === strpos($type, ':')) {
                    $funcName = substr($type, 1);
                    $allowedFuncs = ['getPathInfo', 'customPathResolver'];
                    if (in_array($funcName, $allowedFuncs, true) && function_exists($funcName)) {
                        $_SERVER['PATH_INFO'] = call_user_func($funcName);
                        break;
                    }
                }
                if (!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = static::stripScriptName($_SERVER[$type]);
                    break;
                }
            }
        }
    }

    /**
     * 解析路由参数
     */
    private static function parseRouteParams(array $config): array
    {
        if (empty($_SERVER['PATH_INFO'])) {
            // 支持自定义 GROUP
            if( defined('DEFAULT_GROUP_NAME') ){
                return array($config['var_group'] => DEFAULT_GROUP_NAME);
            }
            return [];
        }

        $depr = $config['url_pathinfo_depr'];
        if ($config['url_html_suffix']) {
            $_SERVER['PATH_INFO'] = preg_replace('/\.' . trim($config['url_html_suffix'], '.') . '$/i', '', $_SERVER['PATH_INFO']);
        }

        $paths = explode($depr, trim($_SERVER['PATH_INFO'], '/'));
        if ($config['var_url_params']) {
            $_GET[$config['var_url_params']] = $paths;
        }

        $var = [];
        // 支持自定义 GROUP
        if( defined('DEFAULT_GROUP_NAME') ){
            $var[$config['var_group']] = DEFAULT_GROUP_NAME;
        } else {
            if (!isset($_GET[$config['var_group']])) {
                $var[$config['var_group']] = !empty($paths) ? array_shift($paths) : $config['var_group'];
            }
        }

        if (!isset($_GET[$config['var_module']])) {
            $var[$config['var_module']] = !empty($paths) ? array_shift($paths) : $config['default_module'];
        }

        $var[$config['var_action']] = !empty($paths) ? array_shift($paths) : $config['default_action'];

        if (!empty($paths)) {
            $deprEscaped = preg_quote($depr, '@');
            preg_replace_callback('@(\w+)' . $deprEscaped . '([^' . $deprEscaped . '\/]+)@', function ($res) use (&$var) { $var[$res[1]] = $res[2]; }, implode($depr, $paths));
        }

        return $var;
    }

}