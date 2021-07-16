<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\web\controller;

use ay\lib\Db;
use ay\lib\Json;

class Chart extends Common
{

    public function index()
    {
        $sql = "select distinct ymd from hm_scan order by ymd desc limit 30";
        $res = Db::name('scan')->dosql($sql);
//        echo date('Y-m-d', strtotime(20201017));
        $arr = [];
        foreach ($res as $k => $v) {
            $ymd = date('Y-m-d', strtotime($v['ymd']));
            $arr[$ymd]['scan'] = Db::name('scan')->field('id')->where('ymd', $v['ymd'])->count();
            $arr[$ymd]['plus'] = Db::name('plus')->field('id')->where('ymd', $v['ymd'])->count();
            $arr[$ymd]['zh'] = ($arr[$ymd]['plus'] != 0) ? number_format($arr[$ymd]['plus'] / $arr[$ymd]['scan'] * 100, 2) : 0 . '%';
        }

        assign('res', $arr);
        return view();
    }
}