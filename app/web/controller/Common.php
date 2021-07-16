<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\web\controller;

use ay\lib\Db;
use ay\lib\Session;

class Common
{
    public $aid;
    public $get;
    public $post;
    public $data;
    public $time;
    public $admin;
    public $siteConf;

    // 初始化
    public function __construct()
    {

        $this->auth();
        $this->get = R('get.');
        $this->post = R('post.');
        $this->data = R('param');

        $this->time = time();

        $res = Db::table('ay_config')->field('k,v')->select();
        foreach ($res as $v) {
            $arr[$v['k']] = $v['v'];
        }
        $this->siteConf = $arr;
        assign('siteConf', $arr);

        if (Session::has('admin')) {
            // assign('menu', $this->getMenu());
            assign('admin', $this->admin);
        }



    }

    public function getMenu()
    {
        $resAuth = Db::table('ay_auth_role')->field('root,name')->where('rid', $this->admin['rid'])->find();
        if (!$resAuth) E('用户未分配权限');
        $this->admin['superName'] = $resAuth['name'];
        if ($resAuth['root'] == '*') {
            $where[] = ['mid', '!=', 0];
        } else {
            $root = explode(',', $resAuth['root']);
            foreach ($root as $v) {
                $where[] = ['mid', '=', $v, 'OR'];
            }
        }


        $res = Db::table('ay_auth_menu')->where($where)->where('status', 1)->where($where)->select();
        return tree($res, 'mid', 'pmid');
    }

    private function auth()
    {
        $url = strtolower('/' . MODE . '/' . CONTROLLER . '/' . ACTION);
        if (!Session::has('admin') and !strstr($url, 'index/login') and !strstr($url, 'index/check')) {
            go(url('/web/index/login'));
            exit;
        }
        if (Session::has('admin') and $url == '/web/index/login') {
            go(url('/web/index/index'));
            exit;
        }
        $this->admin = Session::get('admin');
        $this->aid = Session::get('admin')['aid'];
    }


}