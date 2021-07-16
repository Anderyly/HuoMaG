<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

use ay\lib\Db;

function user_password_auth($tj, $sql)
{
    $pass = user_password($tj);
    if ($pass == $sql) {
        return true;
    } else {
        return false;
    }
}

function user_password($password)
{
    return md5(sha1($password . 'AYPHP'));
}

function getSign($data, $key)
{
    ksort($data);
    $str = '';
    foreach ($data as $k => $v) {
        $str .= $k . '=' . $v . '&';
    }
    return md5($str . 'key=' . $key);
}

function smsbao($conf, $tel, $eid, $content = '')
{
    //----------------短信宝---------------------
    $statusStr = array(
        "0" => "短信发送成功",
        "-1" => "参数不全",
        "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
        "30" => "密码错误",
        "40" => "账号不存在",
        "41" => "余额不足",
        "42" => "帐户已过期",
        "43" => "IP地址限制",
        "50" => "内容含有敏感词"
    );
    if (empty($content)) $content = "【" . $conf['sms_sign'] . "】您好！快递订单编号为{$eid}提交订单2小时后可查询单号！";
    $sendurl = "http://api.smsbao.com/sms?u=" . $conf['sms_ak'] . "&p=" . md5($conf['sms_sk']) . "&m=" . $tel . "&c=" . urlencode("【轻风云】" . $content);
    $result = file_get_contents($sendurl);

    if ($result == '0') {
        return ['status' => 1, 'msg' => "发送成功"];
    } else {
        return ['status' => 0, 'msg' => $statusStr[$result]];
    }

}

function dwz($siteConf, $url)
{
    $row = Db::name('dwz')->field('dwz')->where('url', $url)->find();
    if (isset($row['dwz'])) {
        return $row['dwz'];
    } else {
        $sUrl = 'http://api.suowo.cn/api.htm?key=' . $siteConf['dwz1_token'] . '&url=' . urlencode($url) . '&expireDate=2030-03-31';
        $res = file_get_contents($sUrl);
        $insert = [
            'type' => 1,
            'url' => $url,
            'dwz' => $res
        ];
        Db::name('dwz')->insert($insert);
        return $res['ae_url'];
    }

}

function dwz1($siteConf, $url)
{

    $row = Db::name('dwz')->field('dwz')->where('url', $url)->find();
    if (isset($row['dwz'])) {
        return $row['dwz'];
    } else {
        $sUrl = 'http://check.uomg.com/api/dwz/sogou?token=' . $siteConf['dwz_token'] . '&format=json&longurl=' . ($url);
        $res = file_get_contents($sUrl);
        $res = json_decode($res, true);
        if ($res['code'] != 200) return '短网址余额不足';
        $insert = [
            'type' => 1,
            'url' => $url,
            'dwz' => $res['ae_url']
        ];
        Db::name('dwz')->insert($insert);
        Db::table('ay_config')->where('k', 'dwz_num')->update(['v' => $res['num']]);
        return $res['ae_url'];
    }

}

function UNHTML($content)
{
    $content = htmlspecialchars($content);
    $content = str_replace(chr(13), "<br>", $content);
    $content = str_replace(chr(32), " ", $content);
    $content = str_replace("[_[", "<", $content);
    $content = str_replace("]_]", ">", $content);
    $content = str_replace("|_|", " ", $content);
    return trim($content);
}

function wxDomain($siteConf, $url)
{
    $url = "http://check.uomg.com/api/urlsec/vx?token={$siteConf['dwz_token']}&domain=" . $url;
    $res = file_get_contents($url);
    return json_decode($res, true);
}

function hcapi20($siteConf, $ip)
{
    $url = "https://hcapi20.market.alicloudapi.com/ip?ip=" . $ip;
    $appcode = $siteConf['appcode'];
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $res = \ay\lib\Curl::url($url)->header($headers)->get();
    $res = json_decode($res, true);
//    p($res);
    return $res['data']['region'];
}

function randKey($length)
{
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{mt_rand(0, 35)};
    }
    return $key;
}

function kdwz($domain, $code) {
    $res = Db::name('kuaizhan')->field('ak,sk')->where('domain', $domain)->find();
    if (!$res) return ;
    $r = \app\service\controller\FastZhan::init()->getDwz($res, "https://" . $domain . "?code=" . $code);
    $r = json_decode($r, true);
    return $r['data']['shortUrl'];
    
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;

    // 密匙
    $key = md5($key ? $key : 'anderyly');

    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
        substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
//解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
        sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
            substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}