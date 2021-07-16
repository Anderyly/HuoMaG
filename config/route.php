<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2018
 */

return [
    '^qr\/(.*?)$' => 'service/Qr/get/text/$1',
    '^code\/(.*?)$' => 'index/code/scan/code/$1',
    '^plus\/(.*?)$' => 'index/code/plus/id/$1',
    '^geet.*?$' => 'index/index/geet',
    '^shop.*?$' => 'index/index/shop',
//    '^user\/(\d+)$' => 'User/User/getUserById/id/$1',
//    '^user\/(\d+)\/article$' => 'User/User/getUserArticle/uid/$1',
];