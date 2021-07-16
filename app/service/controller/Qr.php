<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2020
 */

namespace app\service\controller;

use ay\drive\Dir;

class Qr
{

    public function get()
    {
        $text = base64_decode(R('get.text'));
        $path = PUB . '/upload/qr/';
        $file = md5($text . time()) . '.png';
        if (!is_dir($path)) Dir::create($path);
//        if (!file_exists($path . $file)) {
        extend('QRcode.php');
        \QRcode::png($text, $path . $file, QR_ECLEVEL_L, 13, 1, true);
//        }
        header('Content-Type: image/png');
        $img = imagecreatefrompng($path . $file);
        imagepng($img);
        imagedestroy($img);

    }

}