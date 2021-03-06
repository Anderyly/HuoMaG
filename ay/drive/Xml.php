<?php
/**
 * @author anderyly
 * @email admin@aaayun.cc
 * @link http://vclove.cn/
 * @copyright Copyright (c) 2018
 */

namespace ay\drive;

class Xml
{

    /**
     * 解析XML文件
     * @param  string $xml
     * @return array
     */

    private static function compile($xml)
    {
        $xmlRes = xml_parser_create('utf-8');
        xml_parser_set_option($xmlRes, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($xmlRes, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($xmlRes, $xml, $arr, $index);
        xml_parser_free($xmlRes);
        return $arr;
    }

    /**
     * 创建xml文件
     * @param array  $data     数据
     * @param string $root     根据节点
     * @param string $encoding 编码
     * @return string          XML字符串
     */
    public static function create($data, $root = null, $encoding = "UTF-8")
    {
        $xml = '';
        $root = is_null($root) ? "root" : $root;
        $xml .= "<?xml version=\"1.0\" encoding=\"$encoding\"?>";
        $xml .= "<$root>";
        $xml .= self::formatXml($data);
        $xml .= "</$root>";
        return $xml;
    }

    /**
     * 格式化XML
     * @param $data
     * @return string
     */
    private static function formatXml($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        $xml = '';
        foreach ($data as $k => $v) {
            if (is_numeric($k)) {
                $k = "item id=\"$k\"";
            }
            $xml .= "<$k>";
            if (is_object($v) || is_array($v)) {
                $xml .= self::formatXml($v);
            } else {
                $xml .= str_replace(array("&", "<", ">", "\"", "'", "-"), array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;", "&#45;"), $v);
            }
            list($k, ) = explode(" ", $k);
            $xml .= "</$k>";
        }
        return $xml;
    }

    /**
     * 将XML字符串或文件转为数组
     * @param string $xml XML字符串或XML文件
     * @return array|bool        解析后的数组
     */
    public static function toArray($xml)
    {
        $arrData = self::compile($xml);
        $k = 1;
        return $arrData ? self::getData($arrData, $k) : false;
    }

    /**
     * 解析编译后的内容为数组
     * @param array $arrData 数组数据
     * @param int   $i       层级
     * @return array    数组
     */
    private static function getData($arrData, &$i)
    {
        $data = array();
        for ($i = $i; $i < count($arrData); $i++) {
            $name = $arrData[$i]['tag'];
            $type = $arrData[$i]['type'];
            switch ($type) {
                case "attributes":
                    $data[$name]['att'][] = $arrData[$i]['attributes'];
                    break;
                case "complete": //内容标签
                    $data[$name][] = isset($arrData[$i]['value']) ? $arrData[$i]['value'] : '';
                    break;
                case "open": //块标签
                    $k = isset($data[$name]) ? count($data[$name]) : 0;
                    $data[$name][$k] = self::getData($arrData, ++$i);
                    break;
                case "close":
                    return $data;
            }
        }
        return $data;
    }
}
