<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\service\controller;

use ay\lib\Db;
use ay\lib\Json;
use ay\lib\Session;

class Geet
{

    const GT_SDK_VERSION = 'php_3.0.0';

    public static $connectTimeout = 1;
    public static $socketTimeout = 1;

    private $response;

    public function __construct()
    {
        $res = Db::table('ay_config')->field('k,v')->select();
        foreach ($res as $v) {
            $arr[$v['k']] = $v['v'];
        }
        $this->captcha_id = $arr['geet_id'];
        $this->private_key = $arr['geet_key'];
    }

    public function index()
    {
        $data = [
            "user_id" => 'public',
            "client_type" => "web",
            "ip_address" => $_SERVER['REMOTE_ADDR']
        ];
        $status = $this->pre_process($data, 1);
        Session::set('gtserver', $status);
        Session::set('user_id', 'public');
        echo $this->get_response_str();
    }

    public function checkGeet($data)
    {
        $gdata = [
            "user_id" => 'public',
            "client_type" => "web",
            "ip_address" => $_SERVER['REMOTE_ADDR']
        ];
        if (Session::get('gtserver') == 1) {
            $result = $this->success_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'], $gdata);
            if ($result) {
                $geetest = true;
            } else {
                $geetest = false;
            }
        } else {
            if ($this->fail_validate($data['geetest_challenge'], $data['geetest_validate'], $data['geetest_seccode'])) {
                $geetest = true;
            } else {
                $geetest = false;
            }
        }
        if (!$geetest) Json::msg(400, '请先完成滑动验证！');
    }

    /**
     * 判断极验服务器是否down机
     *
     * @param array $data
     * @return int
     */
    public function pre_process($param, $new_captcha = 1)
    {
        $data = [
            'gt' => $this->captcha_id,
            'new_captcha' => $new_captcha
        ];
        $data = array_merge($data, $param);
        $query = http_build_query($data);
        $url = "http://api.geetest.com/register.php?" . $query;
        $challenge = $this->send_request($url);
        if (strlen($challenge) != 32) {
            $this->failback_process();
            return 0;
        }
        $this->success_process($challenge);
        return 1;
    }

    /**
     * @param $challenge
     */
    private function success_process($challenge)
    {
        $challenge = md5($challenge . $this->private_key);
        $result = [
            'success' => 1,
            'gt' => $this->captcha_id,
            'challenge' => $challenge,
            'new_captcha' => 1
        ];
        $this->response = $result;
    }

    /**
     *
     */
    private function failback_process()
    {
        $rnd1 = md5(rand(0, 100));
        $rnd2 = md5(rand(0, 100));
        $challenge = $rnd1 . substr($rnd2, 0, 2);
        $result = [
            'success' => 0,
            'gt' => $this->captcha_id,
            'challenge' => $challenge,
            'new_captcha' => 1
        ];
        $this->response = $result;
    }

    /**
     * @return mixed
     */
    public function get_response_str()
    {
        return json_encode($this->response);
    }

    /**
     * 返回数组方便扩展
     *
     * @return mixed
     */
    public function get_response()
    {
        return $this->response;
    }

    /**
     * 正常模式获取验证结果
     *
     * @param string $challenge
     * @param string $validate
     * @param string $seccode
     * @param array $param
     * @return int
     */
    public function success_validate($challenge, $validate, $seccode, $param, $json_format = 1)
    {
        if (!$this->check_validate($challenge, $validate)) {
            return 0;
        }
        $query = [
            "seccode" => $seccode,
            "timestamp" => time(),
            "challenge" => $challenge,
            "captchaid" => $this->captcha_id,
            "json_format" => $json_format,
            "sdk" => self::GT_SDK_VERSION
        ];
        $query = array_merge($query, $param);
        $url = "http://api.geetest.com/validate.php";
        $codevalidate = $this->post_request($url, $query);
        $obj = json_decode($codevalidate, true);
        if ($obj === false) {
            return 0;
        }
        if ($obj['seccode'] == md5($seccode)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 宕机模式获取验证结果
     *
     * @param $challenge
     * @param $validate
     * @param $seccode
     * @return int
     */
    public function fail_validate($challenge, $validate, $seccode)
    {
        if (md5($challenge) == $validate) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param $challenge
     * @param $validate
     * @return bool
     */
    private function check_validate($challenge, $validate)
    {
        if (strlen($validate) != 32) {
            return false;
        }
        if (md5($this->private_key . 'geetest' . $challenge) != $validate) {
            return false;
        }

        return true;
    }

    /**
     * GET 请求
     *
     * @param $url
     * @return mixed|string
     */
    private function send_request($url)
    {

        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            curl_close($ch);
            if ($curl_errno > 0) {
                return 0;
            } else {
                return $data;
            }
        } else {
            $opts = [
                'http' => [
                    'method' => "GET",
                    'timeout' => self::$connectTimeout + self::$socketTimeout,
                ]
            ];
            $context = stream_context_create($opts);
            $data = @file_get_contents($url, false, $context);
            if ($data) {
                return $data;
            } else {
                return 0;
            }
        }
    }

    /**
     *
     * @param       $url
     * @param array $postdata
     * @return mixed|string
     */
    private function post_request($url, $postdata = '')
    {
        if (!$postdata) {
            return false;
        }

        $data = http_build_query($postdata);
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);

            //不可能执行到的代码
            if (!$postdata) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            } else {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            $data = curl_exec($ch);

            if (curl_errno($ch)) {
                $err = sprintf("curl[%s] error[%s]", $url, curl_errno($ch) . ':' . curl_error($ch));
                $this->triggerError($err);
            }

            curl_close($ch);
        } else {
            if ($postdata) {
                $opts = [
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                        'content' => $data,
                        'timeout' => self::$connectTimeout + self::$socketTimeout
                    ]
                ];
                $context = stream_context_create($opts);
                $data = file_get_contents($url, false, $context);
            }
        }

        return $data;
    }


    /**
     * @param $err
     */
    private function triggerError($err)
    {
        trigger_error($err);
    }
}
