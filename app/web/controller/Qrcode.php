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

class Qrcode extends Common
{

    public function index()
    {
        $key = R('get.key');
        if (!empty($key)) {
            $where = [['status', '=', 1]];
            $whereOr = [['name', ' like ', '%' . $key . '%'], ['code', ' like ', '%' . $key . '%', 'or']];
        } else {
            $where = ['status', '=', 1];
            $whereOr = ['status', '=', 1];
        }
//        p($where);
        $res = Db::name('product')
            ->field('uid,pid,code,name,title,scan,switch,plus,pic')
//            ->where('status', 1)
            ->where($where)
            ->where($whereOr)
            ->select();
        $num = Db::name('product')
            ->field('pid')
//            ->where('status', 1)
            ->where($where)
            ->where($whereOr)
            ->count();

        foreach ($res as $k => $v) {
            $user = Db::name('user')->field('account')->where('uid', $v['uid'])->find();
            $res[$k]['account'] = $user['account'];
        }
        assign('res', $res);
        assign('num', $num);
        assign('key', $key);
        return view();
    }

    public function hsz()
    {
        $key = R('get.key');
        if (!empty($key)) {
            $where = [['status', '=', 0]];
            $whereOr = [['name', ' like ', '%' . $key . '%'], ['code', ' like ', '%' . $key . '%', 'or']];
        } else {
            $where = ['status', '=', 0];
            $whereOr = ['status', '=', 0];
        }
        $res = Db::name('product')
            ->field('uid,pid,code,name,title,scan,switch,plus,pic')
            
            ->where($where)
            ->where($whereOr)
            ->select();
        $num = Db::name('product')
            ->field('pid')
            
            ->where($where)
            ->where($whereOr)
            ->count();

        foreach ($res as $k => $v) {
            $user = Db::name('user')->field('account')->where('uid', $v['uid'])->find();
            $res[$k]['account'] = $user['account'];
        }

        assign('res', $res);
        assign('num', $num);
        assign('key', $key);
        return view();
    }

    public function qrdel()
    {
        $data = $this->get;
        $row = Db::name('product')->field('pid')->where('pid', $data['id'])->find();
        if (!$row) Json::msg(400, '数据错误');
        $res = Db::name('product')->where('pid', $data['id'])->update(['status' => $data['status']]);
        if ($res) {
            Json::msg(200, '操作成功');
        } else {
            Json::msg(400, '操作失败');
        }

    }

    public function qrdelall()
    {
        $res = Db::name('product')->where('status', 0)->update(['status' => 2]);
        if ($res) {
            Json::msg(200, '删除成功');
        } else {
            Json::msg(400, '删除失败');
        }
    }

    public function link()
    {
        
        // var_dump($_GET);
        $id = $this->get['id'];
        $type = $this->get['type'];
        $res = Db::name('product')->field('pid,name,code')->where('pid', $id)->find();
        //
        // $ld = explode('=', $this->siteConf['ld']);
        if ($type == 3) {
            $url = Db::name('kuaizhan')->field('domain')->select();
        	foreach ($url as $v) {
        	    $ld[] = $v['domain'];
        	}
        // 	var_dump($ld);
        } else if ($type == 2) {
        	$ld = explode('=', $this->siteConf['dsf_url']);
        } else {
        	$ld = explode('=', $this->siteConf['ld']);
        }

        assign('ld', $ld);
        //
        assign('res', $res);
        assign('type', $type);
        return view();
        
        return view();
    }

    public function dwz()
    {
        $url = $this->get['url'];
        $res = dwz1($this->siteConf, $url);
        Json::msg(200, 'success', ['dwz' => $res]);

    }

    public function set()
    {
        return view();
    }

    public function pic()
    {
        // echo authcode('http://vclove.cn', 'ENCODE');
        $id = $this->get['id'];
        $res = Db::name('product')->field('name,code,pid,title')->where('pid', $id)->find();
        $num = Db::name('pic')
            ->field('id')
            ->where('pid', $res['pid'])
            ->count();
        $pic = Db::name('pic')
            ->where('pid', $res['pid'])
            ->order('id desc')
            ->select();
        assign('res', $res);
        assign('num', $num);
        assign('pic', $pic);
        return view();
    }

    public function picDel()
    {
        $id = $this->get['id'];
        $row = Db::name('pic')->field('id,pid')->where('id', $id)->find();
        if (!$row) Json::msg(400, '二维码不存在');
        $res = Db::name('pic')->where('id', $id)->delete();
        if ($res) {
            Db::name('product')->where('pid', $row['pid'])->setDec('pic');
            Json::msg(200, '删除成功');
        } else {
            Json::msg(400, '删除失败');
        }
    }

    public function qrcodeadd()
    {
        $data = $this->post;
        $product = Db::name('product')->where('pid', $data['id'])->find();
        if (!$product) Json::msg(400, '数据错误');
        if($this->siteConf['jm'] == 1) {
            extend('qrread/QrReader.php');
            $qrcode = new \Zxing\QrReader($data['ossurl']);  //图片路径
            $text = $qrcode->text(); //返回识别后的文本
            if (!strstr(strtolower($text), 'http')) Json::msg(0, '请上传二维码');
        } else {
            $text = $data['ossurl'];
        }

        $insertArr = [
            'uid' => $product['uid'],
            'pid' => $product['pid'],
            'meta' => $data['qrname'],
            'url' => $text,
            'createTime' => $this->time,
            'switch' => $product['switch']
        ];
        Db::name('pic')->insert($insertArr);
        Db::name('product')->where('pid', $data['id'])->setInc('pic');
        $arr = [
            'dz' => $data['ossurl'],
            'url' => $text
        ];
        Json::msg(200, '上传成功', $arr);
    }


    public function changeNum()
    {
        $data = $this->post;
        if ($data['num'] > 100000) Json::msg(400, '切换次数超限');
//        echo $this->uid;
        $row = Db::name('pic')->field('id')->where('id', $data['id'])->find();
        if (!$row) Json::msg(400, '二维码不存在');
        $res = Db::name('pic')->where('id', $data['id'])->update(['switch' => $data['num']]);
        if ($res) {
            Json::msg(200, '修改成功');
        } else {
            Json::msg(400, '修改失败');
        }

    }

    public function aqp()
    {
        return view();
    }

    public function aqpAjax()
    {
        $data = $this->post;
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
        $data['uid'] = $this->user['uid'];
        $res = Db::name('product')->insert($data);
        if ($res) {
            Json::msg(200, '创建成功');
        } else {
            Json::msg(400, '创建失败');
        }
    }


}