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
use ay\lib\Validator;

class Set extends Common
{

    public function domain()
    {
        $ip = gethostbyname($_SERVER["SERVER_NAME"]);
        assign('ip', $ip);
        return view();
    }

    public function op()
    {
        return view();
    }

    public function access()
    {
        return view();
    }

    public function saveAjax()
    {
        $data = R('post.');

        foreach ($data as $k => $v) {
            if (isset($this->siteConf[$k]) and $data[$k] != $this->siteConf[$k]) {
                Db::table('ay_config')->where('k', $k)->update(['v' => $v]);
            }
        }
        Json::msg(200, '修改成功');

    }

    public function opAjax()
    {
        $data = R('post.');
        $arr = ['wechat_open', 'cache', 'security', 'me_domain', 'jm'];
        foreach ($arr as $v) {
            if (!isset($data[$v])) {
                Db::table('ay_config')->where('k', $v)->update(['v' => 0]);
            } else {
                Db::table('ay_config')->where('k', $v)->update(['v' => 1]);
            }
        }
        Json::msg(200, '操作成功');
        /**
         * wechat_open: 1
         * cache: 1
         * security: 1
         * me_domain: 1
         */

    }

    public function accessAjax()
    {
        /**
        diqu_shibie: 1
        ip_onenum:
        area_type: 1
        accessfilter_status: 2
        check_city_word:
        check_city_gotolink:
         */

    }

    public function editPassAjax()
    {
        $data = $this->post;

        $checkArr = [
            'oldpassword' => 'require',
            'password' => 'require',
            'repassword' => 'require',
        ];

        Validator::check($data, $checkArr);

        if ($data['password'] != $data['repassword']) Json::msg(400, '密码不一致');

        $res = user_password_auth($data['oldpassword'], $this->admin['password']);
        if ($res) {
            $pass = user_password($data['password']);
            $rs = Db::table('ay_admin')->where('aid', $this->aid)->update(['password' => $pass]);
            Json::msg(200, '修改成功');
        } else {
            Json::msg(400, '旧密码错误');
        }
    }

    public function set()
    {
        $show = $this->post['show'];
        $res = Db::name('user')->where('uid', $this->uid)->update(['rand' => $show]);
        if ($res) {
            Json::msg(200, '操作成功');
        } else {
            Json::msg(400, '操作失败');
        }
    }

}