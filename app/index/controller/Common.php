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
use ay\lib\View;
use ay\lib\Session;
use app\service\controller\SignatureHelper;

class Common
{
    public $siteConf;

    // 初始化
    public function __construct()
    {
        // 获取网站配置
        $res = Db::table('ay_config')->field('k,v')->select();
        foreach ($res as $v) {
            $arr[$v['k']] = $v['v'];
        }
        $this->siteConf = $arr;
        assign('siteConf', $arr);
    }

}