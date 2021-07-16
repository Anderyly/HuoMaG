<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\index\controller;

use ay\lib\Db;
use ay\lib\Ip;
use ay\lib\Str;

class Code extends Common
{

    public function scan()
    {

        if (!$this->isWechat() and $this->siteConf['wechat_open'] == 1) E('请在微信内打开');

        $ip = Ip::get();
        $ppp = getcwd() . '/cache/' . $ip . '.txt';

        if (is_file($ppp) and $this->siteConf['cache'] == 1) {
        	$dd = file_get_contents($ppp);
        	$dd = json_decode($dd, true);
        	view('', $dd);
        	exit;
        }

        // $ip = '112.23.73.111';
        $area = hcapi20($this->siteConf, $ip);
        $areaP = Str::pinyin($area);
//        echo $areaP;

        $path = PUB . 'me/' . $areaP . '.txt';
//        echo $path;
        if (file_exists($path)) {
            $me = file_get_contents($path);
            if ($me <= 0) {
                file_put_contents($path, $this->siteConf['ip_onenum']);
                $me = $this->siteConf['ip_onenum'];
            }
        } else {
            file_put_contents($path, $this->siteConf['ip_onenum']);
            $me = $this->siteConf['ip_onenum'];
        }

        // 地区屏蔽
        if ($this->siteConf['accessfilter_status'] == 1) {
            $check_city_word = $this->siteConf['check_city_word'];
            if (strstr($check_city_word, $area)) {
                go($this->siteConf['check_city_gotolink']);
            }
        }

        $code = R('get.code');
//        $picId = R('get.id');

        $product = Db::name('product')->where('code', $code)->find();
        if (!$product) E('活码不存在');
        if ($product['status'] != 1) E('活码失效');

        // 查询旗下随机二维码
        $num = Db::name('pic')->field('id')->where('pid', $product['pid'])->count();
        $user = Db::name('user')->alias('a')->field('a.rand,a.uid')->join('hm_pic b', 'a.uid=b.uid')->find();
        
        $userC = Db::name('user_experience')->field('root,endTime1')->where('uid', $user['uid'])->find();
        // var_dump($userC);
        if ($userC['root'] == 1) {
            $time12 = date('YmdH', time());
            $time1 = date('YmdH', $userC['endTime1']);
            if ($time1 < $time12) E('账号体验已到期，请联系客服');
            assign('experience_tips', $this->siteConf['experience_tips']);
        }
            
        if($user['rand'] == 1) {
            $o = "'id asc'";
        } else {
            $o = "'RAND()'";
        }
        if ($num <= 0) E('活码暂无');
        if($user['rand'] == 1) {
        	$sql = "SELECT id,uid,pid,switch,sswitch,url FROM hm_pic WHERE pid = {$product['pid']} AND scan < switch ORDER BY RAND()";
        } else {
        	$sql = "SELECT id,uid,pid,switch,sswitch,url FROM hm_pic WHERE pid = {$product['pid']} AND scan < switch ORDER BY id ASC";
        }
        $pic = Db::name('pic')->doSql($sql);
        $pic = $pic[0];
        // var_dump($pic);exit;
        if (!$pic) E('活码暂无');

        $url = $pic['url'];


        // 增加扫码记录
        $arr = [
            'uid' => $pic['uid'],
            'pid' => $pic['pid'],
            'picid' => $pic['id'],
            'y' => date('Y'),
            'm' => date('m'),
            'd' => date('d'),
            'createTime' => time(),
            'ymd' => date('Ymd')
        ];
        // p($arr);exit;
        Db::name('scan')->insert($arr);
        Db::name('product')->where('pid', $pic['pid'])->setInc('scan');
        Db::name('pic')->where('id', $pic['id'])->setInc('sswitch');
        Db::name('pic')->where('id', $pic['id'])->setInc('scan');
        //
        file_put_contents($path, $me - 1);
        
        if ($this->siteConf['jm'] == 1) {
            $url = "/qr/" . base64_encode($url);
        }
        
        
        assign('area', $area);
        assign('me', $me);
        assign('url', $url);
        assign('qid', $pic['id']);
        assign('p', $product);
        
        if (!is_file($ppp)) {
        	$arrJson = [
	        	'area' => $area,
	        	'me' => $me,
	        	'url' => $url,
	        	'qid' => $pic['id'],
	        	'p' => $product
	        ];
	        $json = json_encode($arrJson);
	        file_put_contents($ppp, $json);
        }
        
        
        return view();
    }

    public function plus()
    {
        
        $id = R('get.id');
        $pic = Db::name('pic')->field('id,pid,uid,url')->where('id', $id)->find();

        // 增加扫码记录
        $arr = [
            'uid' => $pic['uid'],
            'pid' => $pic['pid'],
            'picid' => $pic['id'],
            'y' => date('Y'),
            'm' => date('m'),
            'd' => date('d'),
            'createTime' => time(),
            'ymd' => date('Ymd')
        ];
        Db::name('plus')->insert($arr);
        Db::name('product')->where('pid', $pic['pid'])->setInc('plus');
        Db::name('pic')->where('id', $pic['id'])->setInc('plus');
        //


        // go($pic['url']);
    }

    private function isWechat()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($ua, 'MicroMessenger') == false && strpos($ua, 'Windows Phone') == false) {
            return false;
        } else {
            return true;
        }
    }

}
