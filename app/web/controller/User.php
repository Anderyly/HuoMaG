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

class User extends Common
{

    public function pass()
    {
        return view();
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