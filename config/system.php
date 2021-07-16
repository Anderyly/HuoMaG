<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2018
 */

return [
    /******************************** 基本参赛 ********************************/
    'DEBUG' => true,
    'DEFAULT_TIME_ZONE' => 'PRC',
    /******************************** 基本参赛 ********************************/
    'REWRITE' => 'action',                                                        //伪静态
    /******************************** 日志 ********************************/
    'SAVE_ERROR_LOG' => true,
    'SAVE_VISIT_LOG' => true,
    /******************************** 缓存 ********************************/
    'CACHE' => true,
    'CACHE_TIME' => 1,
    'TPL_TAG_LEFT' => '{{',
    'TPL_TAG_RIGHT' => '}}',
    /******************************** 储存 ********************************/
    'STORAGE_DRIVER' => 'File',                                                       //储存驱动 支持File与Memcache储存
    /******************************** 文件上传 ********************************/
    'UPLOAD_THUMB_ON' => FALSE,                                                       //上传图片缩略图处理
    'UPLOAD_ALLOW_TYPE' => [                                                            //允许上传类型
        'jpg', 'jpeg', 'gif', 'png'
    ],
    'UPLOAD_ALLOW_SIZE' => 2097152,                                                     //允许上传文件大小 单位B
    'UPLOAD_PATH' => PUB . 'Upload/',                                             //上传路径
    /******************************** 图像水印处理 ********************************/
    'WATER_ON' => true,                                                        //开关
    'WATER_FONT' => AY . 'data/font/font.ttf',                                   //水印字体
    'WATER_IMG' => AY . 'data/Image/water.png',                                 //水印图像
    'WATER_POS' => 9,                                                           //位置  1~9九个位置  0为随机
    'WATER_PCT' => 60,                                                          //透明度
    'WATER_QUALITY' => 80,                                                          //压缩比
    'WATER_TEXT' => 'blog.aaayun.cc',                                            //水印文字
    'WATER_TEXT_COLOR' => '#f00f00',                                                   //文字颜色
    'WATER_TEXT_SIZE' => 12,                                                         //文字大小
    /******************************** 图片缩略图 ********************************/
    'THUMB_PREFIX' => '',                                                          //缩略图前缀
    'THUMB_ENDFIX' => '_thumb',                                                    //缩略图后缀
    'THUMB_TYPE' => 6,                                                           //生成方式,
    //1:固定宽度,高度自增 2:固定高度,宽度自增 3:固定宽度,高度裁切
    //4:固定高度,宽度裁切 5:缩放最大边       6:自动裁切图片
    'THUMB_WIDTH' => 300,                                                         //缩略图宽度
    'THUMB_HEIGHT' => 300,                                                         //缩略图高度
    /******************************** 验证码 ********************************/
    'CODE_FONT' => AY . 'data/font/font.ttf',                                   //字体
    'CODE_STR' => '0123456789abcdefghjkmnopqrstuvwsyz',                           //验证码种子
    'CODE_WIDTH' => 120,                                                         //宽度
    'CODE_HEIGHT' => 35,                                                          //高度
    'CODE_BG_COLOR' => '#ffffff',                                                   //背景颜色
    'CODE_LEN' => 4,                                                           //文字数量
    'CODE_FONT_SIZE' => 20,                                                          //字体大小
    'CODE_FONT_COLOR' => '',                                                          //字体颜色
    /******************************** 钓子 ********************************/
    'HOOK' => array(),
    /******************************** 别名导入 ********************************/
    'ALIAS' => array(),
    'home' => 'index.php',
    'mode' => 'index',
    'controller' => 'index',
    'action' => 'index'
];