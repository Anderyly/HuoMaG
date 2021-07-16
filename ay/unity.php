<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2018
 */

use ay\Drive\Log;
use ay\lib\Request;

/**
 * 跳转函数
 * @param string $str 地址
 * @return string $s 字符
 */
function url($str, $s = '')
{


    $arr = explode('/', trim($str, '/'));
    $len = count($arr);

    if ($len == 3) {
        $mode = $arr[0];
    } else {
        $mode = MODE;
    }
    if (defined('BIND')) {
        switch (true) {
            case (BIND == $arr[0] and $len == 3) :
                $mode = BIND;
                break;
            case ($len == 3) :
                $mode = $arr[0];
                break;
            case (BIND == MODE) :
                $mode = CIND . '.php';
                break;
            default :
                $mode = MODE;
        }
    }

    switch ($len) {
        case 3 :
            $path = URL . '/' . $mode . '/' . $arr[1] . '/' . $arr[2];
            break;
        case 2 :
            $path = URL . '/' . $mode . '/' . $arr[0] . '/' . $arr[1];
            break;
        default :
            $path = URL . '/' . $mode . '/' . CONTROLLER . '/' . $arr[0];
    }

    if (!empty($s)) {
        $path .= $s;
    } else {
        $path .= '.' . C('REWRITE');
    }

    return $path;
}

/**
 * 打印函数
 * @param array|string $arr
 */
function p($arr)
{
    if (is_bool($arr)) {
        var_dump($arr);
    } elseif (is_null($arr)) {
        var_dump(null);
    } else {
        echo "<pre style='padding:10px;border_radius:5px;background:#f5f5f5;border:1px solid #ccc;'>";
        print_r($arr);
        echo "</pre>";
    }
}

/**
 * 打印函数
 * @param string $arr 输出内容
 * @param string $lx 类型
 * @param string $alink 跳转链接
 */
function E($msg = '页面错误！请稍后再试～', $alink = null)
{
    assign('msg', $msg);
    if (!is_null($alink)) assign('link', $alink);
    view(TEMPLATE . '/fail.html');
    exit;
}


/**
 * @param null $str
 * @param null $type
 * @return array|mixed|string|string[]|null
 */
function R($str = NULL, $type = null)
{
    if (!strpos($str, '.')) {
        $qm = $str;
        $hm = '';
    } else {
        $hm = substr($str, strripos($str, '.') + 1);
        $qm = substr($str, 0, strrpos($str, '.'));
    }

    switch ($qm) {
        case 'get':
            $data = Request::get($hm, $type);
            break;
        case 'post':
            $data = Request::post($hm, $type);
            break;
        case 'url':
            $data = Request::url();
            break;
        case 'file':
            $data = Request::file($hm);
            break;
        case 'param':
            $data = Request::param();
            break;
        case '?get':
            $data = Request::has($hm, 'get');
            break;
        case '?post':
            $data = Request::has($hm, 'post');
            break;
        default:
            $data = false;
    }
    return $data;
}

function controller($name, $vae = '')
{
    $suffix = strchr($name, '/');
    if (empty($suffix)) {
        $filePath = APP_PATH . MODE . '/controller/' . $name . '.php';
        $space = '\\app\\' . MODE . '\\controller\\' . $name;
    } else {
        $arr = explode('/', $name);
        $filePath = APP_PATH . $arr[0] . '/controller/' . $arr[1] . '.php';
        $space = '\\app\\' . $arr[0] . '\\controller\\' . $arr[1];
    }
    if (is_file($filePath)) {
        require_once $filePath;
        if (empty($vae)) :
            $object = new $space();
            return $object;
        endif;
    } else {
        halt('找不到:' . $filePath . ' 控制器');
    }
}

/**
 * @param string $filename
 * @param null $data
 * @throws Exception
 */
function view($filename = '', $data = null)
{
    \ay\lib\View::view($filename, $data);
}

function assign($name, $value)
{
    \ay\lib\View::assign($name, $value);
}

/**
 * 导入extend下文件
 * @param string $filepath
 * @throws Exception
 */
function extend($filePath)
{
    $filePath = EXTEND . $filePath;
    if (!is_file($filePath)) halt($filePath . ' 不存在');
    include_once $filePath;
}

/**
 * 导入vendor目录下文件
 * @param string $filepath 路径
 * @throws Exception
 */
function vendor($filePath)
{
    $filePath = VENDOR . $filePath;
    if (!is_file($filePath)) halt($filePath . ' 不存在');
    include_once $filePath;
}

/**
 * 全局导入
 * @param string $file 文件名
 * @param array $path 路径
 * @throws Exception
 */
function import($file, $path)
{
    $filePath = $path . $file;
    if (!is_file($filePath)) halt($filePath . ' 不存在');
    include_once $filePath;
}

/**
 * 获得浏览器版本
 */
function browserInfo()
{
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    $browser = null;
    if (strstr($agent, 'msie 9.0')) {
        $browser = 'msie9';
    } elseif (strstr($agent, 'msie 8.0')) {
        $browser = 'msie8';
    } elseif (strstr($agent, 'msie 7.0')) {
        $browser = 'msie7';
    } elseif (strstr($agent, 'msie 6.0')) {
        $browser = 'msie6';
    } elseif (strstr($agent, 'firefox')) {
        $browser = 'firefox';
    } elseif (strstr($agent, 'chrome')) {
        $browser = 'chrome';
    } elseif (strstr($agent, 'safari')) {
        $browser = 'safari';
    } elseif (strstr($agent, 'opera')) {
        $browser = 'opera';
    }
    return $browser;
}

/**
 * 载入或设置配置顶
 * @param string $name 配置名
 * @param string $value 配置值
 * @return array|string
 */
function C($name = null, $value = null)
{
    static $config = [];
    if (is_null($name)) {
        return $config;
    } elseif (is_string($name)) {
        $name = strtoupper($name);
        $data = array_change_key_case($config, CASE_UPPER);
        if (!strstr($name, '.')) {
            //获得配置
            if (is_null($value)) {
                return isset($data[$name]) ? $data[$name] : null;
            } else {
                return $config[$name] = isset($data[$name]) && is_array($data[$name]) && is_array($value) ? array_merge($config[$name], (array)($value)) : $value;
            }
        } else {
            //二维数组
            $name = array_change_key_case(explode(".", $name));
            if (is_null($value)) {
                return isset($data[$name[0]][$name[1]]) ? $data[$name[0]][$name[1]] : null;
            } else {
                return $config[$name[0]][$name[1]] = $value;
            }
        }
    } elseif (is_array($name)) {
        return $config = array_merge($config, array_change_key_case($name, CASE_UPPER));
    }
}

/**
 * 快速缓存 以文件形式缓存
 * @param array $name 缓存KEY
 * @param array|string $value 删除缓存
 * @param int $time 缓存时间
 * @return bool
 */
function F($name, $value = '', $time = 3600)
{

    static $_cache = [];
    $path = TEMP . 'cache.txt';

    if (!isset($name[1])) $name[1] = 0;

    if (is_file($path)) {
        $_cache = json_decode(file_get_contents($path), true);
    } else {
        file_put_contents($path, '');
    }

    if ($name == 'delAll') {
        file_put_contents($path, '');
        return true;
    } else if ($value == 'del') {
        if (isset($name[1]) and !empty($name[1])) {
            unset($_cache[$name[0]][$name[1]]);
        } else {
            unset($_cache[$name[0]]);
        }
        file_put_contents($path, json_encode($_cache));
        return true;
    } else if (is_int($value)) {
        if (isset($_cache[$name[0]][$name[1]]) and ($_cache[$name[0]][$name[1]]['time'] + $value) > time()) {
            return $_cache[$name[0]][$name[1]]['value'];
        } else {
            unset($_cache[$name[0]][$name[1]]);
            file_put_contents($path, json_encode($_cache));
            return false;
        }
    } else {

        $_cache[$name[0]][$name[1]] = ['value' => $value, 'time' => time()];
        file_put_contents($path, json_encode($_cache));
        return true;
    }

}

/**
 * 跳转网址
 * @param string $url 跳转
 * @param int $time 跳转时间
 * @param string $msg
 */
function go($url, $time = 0, $msg = '')
{
    if (!headers_sent()) {
        $time == 0 ? header("Location:" . $url) : header("refresh:{$time};url={$url}");
        exit($msg);
    } else {
        echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time) {
            exit($msg);
        }
    }
}

/**
 * 计算脚本运行时间
 * 传递$end参数时为得到执行时间
 * @param string $start 开始标识
 * @param string $end 结束标识
 * @param int $decimals 小数位
 * @return string
 */
function runtime($start, $end = '', $decimals = 3)
{
    static $runtime = [];
    if ($end != '') {
        $runtime [$end] = microtime();
        return number_format($runtime [$end] - $runtime [$start], $decimals);
    }
    $runtime[$start] = microtime();
}

/**
 * HTTP状态信息设置
 * @param Number $code 状态码
 */
function setHttpCode($code)
{
    $state = [
        200 => 'OK', // Success 2xx
        // Redirection 3xx
        301 => 'Moved Permanently', 302 => 'Moved Temporarily ',
        // Client Error 4xx
        400 => 'Bad Request', 403 => 'Forbidden', 404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error', 503 => 'Service Unavailable',
    ];
    if (isset($state[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $state[$code]);
        header('Status:' . $code . ' ' . $state[$code]);
    }
}

/**
 * 是否为SSL协议
 * @return boolean
 */
function is_ssl()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

/**
 * 打印常量
 * @return array
 */
function print_const()
{
    $define = get_defined_constants(true);
    foreach ($define['user'] as $k => $d) {
        $const[$k] = $d;
    }
    p($const);
}

function D()
{

}

/**
 * 抛出异常
 * @throws Exception
 */
//function halt($msg, $file = '', $line = '')
//{
//    throw new \Exception($msg);
//}

function halt($msg, $file = '', $line = '')
{
    Log::error($msg . ' [' . $file . '(' . $line . ')]');
    if ($_SERVER['REQUEST_METHOD'] == 'cli') {
        exit($msg);
    } else if (C('DEBUG')) {
        $e['message'] = $msg;
        $e['file'] = $file;
        $e['line'] = $line;
        include_once TEMPLATE . '/error.html';
        exit;
    } else {
        setHttpCode(500);
        include_once TEMPLATE . '/close.html';
        exit;
    }
}

function download($con, $name, $type = 'file')
{
    $length = ($type == 'file') ? filesize($con) : strlen($con);
    header("Content-type: application/octet-stream");
    header("Accept-Ranges: bytes");
    header("Content-Length: " . $length);
    header('Pragma: cache');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Content-Disposition: attachment; filename="' . urlencode($name) . '"; charset=utf-8'); //下载显示的名字,注意格式
    header("Content-Transfer-Encoding: binary ");
    if ($type == 'file') {
        readfile($con);
    } else {
        echo $con;
    }
}

/**
 * 无限级分类树
 */
function tree($arr, $id = 'id', $pid = 'pid')
{
    $refer = [];
    $tree = [];
    foreach ($arr as $k => $v) {
        $refer[$v[$id]] = &$arr[$k];
    }
    foreach ($arr as $k => $v) {
        $sid = $v[$pid];
        if ($sid == 0) {
            $tree[] = &$arr[$k];
        } else {
            if (isset($refer[$sid])) {
                $refer[$sid]['children'][] = &$arr[$k];
            }
        }
    }
    return $tree;
}

// 转义危险字符 并 判断违禁词
function clean($data, $filter)
{
    if (!get_magic_quotes_gpc()) {
        $data = addslashes($data);
    }
    $data = strtolower($data);
    $data = str_replace("_", "\_", $data);
    $data = str_replace("%", "\%", $data);
    $data = str_replace("*", "\*", $data);
    $data = str_replace("select", "\select", $data);
    $data = str_replace("insert", "\insert", $data);
    $data = str_replace("delete", "\delete", $data);
    $data = str_replace("update", "\update", $data);
    $data = nl2br($data);
    $data = htmlspecialchars($data);

    $arr = explode('|', $filter);
    foreach ($arr as $item) {
        if (strstr($data, $item)) \ay\lib\Json::msg(400, '含有违禁词');
    }
    return $data;

}

function summary($content, $count)
{
    $content = preg_replace("@<script(.*?)</script>@is", "", $content);
    $content = preg_replace("@<iframe(.*?)</iframe>@is", "", $content);
    $content = preg_replace("@<style(.*?)</style>@is", "", $content);
    $content = preg_replace("@<(.*?)>@is", "", $content);
    $content = str_replace(PHP_EOL, '', $content);
    $space = array(" ", "　", "  ", " ", " ");
    $go_away = array("", "", "", "", "");
    $content = str_replace($space, $go_away, $content);
    $res = mb_substr($content, 0, $count, 'UTF-8');
    if (mb_strlen($content, 'UTF-8') > $count) {
        $res = $res . "...";
    }
    return $res;
}

function lastTime($date, $template)
{
    $s = (time() - $date) / 60;
    //echo $s;exit;
    switch ($s) {
        case ($s < 60) :
            $msg = intval($s) . '分钟前';
            break;
        case ($s >= 60 && $s < (60 * 24)):
            $msg = intval($s / 60) . '小时前';
            break;
        case ($s >= (60 * 24) and $s < (60 * 24 * 3)) :
            $msg = intval($s / 60 / 24) . '天前';
            break;
        default :
            $msg = date($template, $date);
            break;
    }
    return $msg;
}
