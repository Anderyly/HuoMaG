<?php
/**
 * 搜狐快站
 * @author anderyly
 * @email admin@aaayun.cc
 * @link https://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\service\controller;

use ay\lib\Curl;

class FastZhan {
    
    public static function init() {
        return new self;
    }
    // 获取域名
    public function getDomain($conf) {
        $url = "https://cloud.kuaizhan.com/api/v1/tbk/getDomain";
        $arr = [
            'appKey' => $conf['ak'],
            'sk' => $conf['sk'],
            'siteId' => $conf['siteId']
        ];
        $arr['sign'] = $this->getSign($arr);
        unset($arr['sk']);
        $res = Curl::url($url)->param($arr)->post();
        // var_dump($res);
        return $res;
    }
    
    
    // 修改js文件
    public function editJs($conf, $js) {
        $url = "https://cloud.kuaizhan.com/api/v1/tbk/modifyPageJs";
        $arr = [
            'appKey' => $conf['ak'],
            'sk' => $conf['sk'],
            'siteId' => $conf['siteId'],
            'content' => $js
        ];
        $arr['sign'] = $this->getSign($arr);
        unset($arr['sk']);
        $res = Curl::url($url)->param($arr)->post();
        return $res;
    }
    
    // 发布站点
    public function push($conf) {
        $url = "https://cloud.kuaizhan.com/api/v1/tbk/publishPage";
        $arr = [
            'appKey' => $conf['ak'],
            'sk' => $conf['sk'],
            'siteId' => $conf['siteId'],
        ];
        $arr['sign'] = $this->getSign($arr);
        unset($arr['sk']);
        $res = Curl::url($url)->param($arr)->post();
        return $res;
    }
    
    // 获取短地址
    public function getDwz($conf, $u) {
        $url = "https://cloud.kuaizhan.com/api/v1/tbk/genKzShortUrl";
        $arr = [
            'appKey' => $conf['ak'],
            'sk' => $conf['sk'],
            // 'siteId' => $conf['siteId'],
            'url' => $u
        ];
        
        $arr['sign'] = $this->getSign($arr);
        unset($arr['sk']);
        $res = Curl::url($url)->param($arr)->post();
        return $res;
    }
    
    // 签名
    private function getSign($data) {
        $sk = $data['sk'];
        unset($data['sk']);
        $str = '';
        ksort($data);
        foreach ($data as $k => $v) {
            // var_dump($data);exit;
            $str .= $k . $v ;
        }
        $str = $sk . $str . $sk;
        // echo $str;
        return md5($str);
    }
    
}