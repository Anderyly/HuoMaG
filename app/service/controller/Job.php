<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */
 
namespace app\service\controller;

use ay\lib\Db;

class Job {
	
	
	public function index() {
		$res = Db::name('product')->field('scan,plus,pid')->where([['scan', '!=', 0], ['plus', '!=', 0]])->select();
		foreach ($res as $k => $v) {
			if ($v['scan'] != 0 or $v['plus'] != 0) {
				Db::name('product')->where('pid', $v['pid'])->update(['scan' => 0, 'plus' => 0]);
			}
		}
		echo 'ok';
	}
	
	// 快站监控
	public function vk() {
        
        $res = Db::table('ay_config')->field('k,v')->select();
        foreach ($res as $v) {
            $arr[$v['k']] = $v['v'];
        }
        $siteConf = $arr;
        
        // exit;
        $ld = explode('=', $siteConf['ld']);
        $str = "'";
        foreach ($ld as $v) {
            $vk = wxDomain($siteConf, $v);
            Db::table('ay_config')->where('k', 'yl')->update(['v' => $vk['num']]);
            echo $vk['code'];
            if ($vk['code'] != 200) continue;
            $str .= $v . "','";
        }
        $str = rtrim($str, "'");
        $str = rtrim($str, ",");
        $ss = <<<eof
<script>\n
    var code = getQueryVariable('code');
    console.log(code);
    if (code != null && code != "") {
        var domain = [{$str}];
    
        var rand = Math.floor(Math.random() * domain.length + 1)-1; 
        window.location.href = "http://" + domain[rand] + "/code/" + code;
    }
    

    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable) {
                return pair[1];
                       
            }
        }
        return(false);
    }
</script>
eof;
        $conf = Db::name('kuaizhan')->field('ak,sk,siteId')->find();
        $res = FastZhan::init()->editJs($conf, $ss);
        $res = json_decode($res, true);
        if ($res['code'] == 200) {
            $re = FastZhan::init()->push($conf);
            $re = json_decode($re, true);
            if ($re['code'] == 200) {
                echo "ok";
            } else {
                echo $re['msg'];
            }
        } else {
            echo $res['msg'];
        }

    }
    
    // 普通监控
	public function pt() {
        
        $res = Db::table('ay_config')->field('k,v')->select();
        foreach ($res as $v) {
            $arr[$v['k']] = $v['v'];
        }
        $siteConf = $arr;
        
        // exit;
        $ld = explode('=', $siteConf['ld']);
        $str = "";
        foreach ($ld as $v) {
            $vk = wxDomain($siteConf, $v);
            Db::table('ay_config')->where('k', 'yl')->update(['v' => $vk['num']]);
            // echo $vk['code'];
            if ($vk['code'] != 200) continue;
            $str .= $v . "=";
        }
        $str = rtrim($str, '=');
        $res = Db::table('ay_config')->where('k', 'ld')->update(['v' => $str]);
        if ($res) {
            echo 'ok';
        } else {
            echo 'fail';   
        }

    }
	
}