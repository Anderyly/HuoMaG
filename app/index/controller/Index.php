<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\index\controller;

use ay\lib\Db;
use app\service\controller\FastZhan;


class Index extends Common
{

    public function index()
    {
        go(url('web/index/login'));

    }
}
