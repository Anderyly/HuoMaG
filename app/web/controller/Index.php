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
use ay\lib\Session;
use app\service\controller\Geet;

class Index extends Common
{

    public function index()
    {
//        $y = date('y');
        $m = date('m', time());
        $d = date('d');
        $zm = date("m", strtotime("last month"));
        $zd = date("d", strtotime("-1 day"));

        $data['todayScan'] = Db::name('scan')->field('id')->where('m', $m)->where('d', $d)->count();
        $data['todayPlus'] = Db::name('plus')->field('id')->where('m', $m)->where('d', $d)->count();
        $data['todayZ'] = ($data['todayPlus'] != 0) ? number_format($data['todayPlus'] / $data['todayScan'] * 100, 2) : 0;

        $data['yedayScan'] = Db::name('scan')->field('id')->where('m', $m)->where('d', $zd)->count();
        $data['yedayPlus'] = Db::name('plus')->field('id')->where('m', $m)->where('d', $zd)->count();
        $data['yedayZ'] = ($data['yedayPlus'] != 0) ? number_format($data['yedayPlus'] / $data['yedayScan'] * 100, 2) : 0;

        $data['mScan'] = Db::name('scan')->field('id')->where('m', $m)->count();
        $data['mPlus'] = Db::name('plus')->field('id')->where('m', $m)->count();
        $data['mZ'] = ($data['mPlus'] != 0) ? number_format($data['mPlus'] / $data['mScan'] * 100, 2) : 0;

        $data['zmScan'] = Db::name('scan')->field('id')->where('m', $zm)->count();
        $data['zmPlus'] = Db::name('plus')->field('id')->where('m', $zm)->count();
        $data['zmZ'] = ($data['zmPlus'] != 0) ? number_format($data['zmPlus'] / $data['zmScan'] * 100, 2) : 0;

        assign('data', $data);
        return view();
    }

    public function la()
    {
        return view('', ['url' => URL]);
    }

    public function login()
    {
        return view();
    }

    public function check()
    {
        $data = $this->post;
        $geet = new Geet();
        $geet->checkGeet($data);
        if ($data['username'] and $data['password']) {
            $res = Db::table('ay_admin')->where('account', $data['username'])->find();
            if (!$res) Json::msg(400, '???????????????');
            if ($res['status'] != 1) Json::msg(400, '??????????????????');
            if (user_password_auth($data['password'], $res['password'])) {
                Session::set('admin', $res);
                Json::msg(200, '????????????');
            } else {
                Json::msg(400, '????????????');
            }
        } else {
            Json::msg(400, '?????????????????????');
        }
    }

    public function logout()
    {
        Session::delete('admin');
//        go(url('/user/index/login'));
    }


    public function aqp()
    {
        $id = $this->get['id'];
        if (!empty($id)) {
            $res = Db::name('product')->field('name,title,pid,tips,switch')->where('pid', $id)->find();
            assign('title', '??????');
        } else {
            $res = [
                'name' => '??????' . date('Ymd', $this->time),
                'title' => '????????????',
                'switch' => 1000,
                'tips' => '?????????????????????',
                'pid' => 0

            ];
            assign('title', '??????');
        }
        assign('res', $res);
        return view();
    }

    public function aqpAjax()
    {
        $data = $this->post;
        if ($data['id'] == 0) {
            $num = randKey(20);
            while (true) {
                $res = Db::name('product')->field('pid')->where('code', $num)->find();
                if ($res) {
                    $num = randKey(20);
                } else {
                    break;
                }
            }
            $data['code'] = $num;
            $data['createTime'] = $this->time;
            $data['status'] = 1;
            $data['uid'] = 1;
            $res = Db::name('product')->insert($data);
            if ($res) {
                Json::msg(200, '????????????');
            } else {
                Json::msg(400, '????????????');
            }
        } else {
            $id = $data['id'];
            unset($data['id']);
            $res = Db::name('product')->where('pid', $id)->update($data);
            if ($res) {
                Json::msg(200, '????????????');
            } else {
                Json::msg(400, '????????????');
            }
        }

    }

    public function wxCheck()
    {
        $ld = explode('=', $this->siteConf['ld']);
        $rk = explode('=', $this->siteConf['rk']);
        $arr = [];
        $num = 0;
        foreach ($ld as $v) {
            $res = wxDomain($this->siteConf, $v);
            $num = $res['num'];
            if ($res['code'] == 200) {
                continue;
            } else {
                $arr['ld'] = [$v];
            }
        }
        foreach ($rk as $v) {
            $res = wxDomain($this->siteConf, $v);
            $num = $res['num'];
            if ($res['code'] == 200) {
                continue;
            } else {
                $arr['rk'] = [$v];
            }
        }

        Db::table('ay_config')->where('k', 'yl')->update(['v' => $num]);
        if (count($arr['ld']) == 0 and count($arr['rk']) == 0) {
            Json::msg(200, 'success');
        } else {
//            $str = '???????????????';
            foreach ($arr['ld'] as $v) {
                $str .= $v . '<br>';
            }
//            $str1 = '???????????????';
            foreach ($arr['rk'] as $v) {
                $str .= $v . '<br>';
            }
            Json::msg(201, '???????????????' . $str);
        }

    }


}