<?php
/*
 * @Author: xhboke
 * @Date: 2021-05-22 19:50:22
 * @LastEditTime: 2021-05-28 13:52:11
 * @Description: 分析舆情关键词
 * @FilePath: index.php
 */

header("Content-type: text/html; charset=utf-8");
ini_set('memory_limit', '1024M');
require_once "class.data.php";
require_once "vendor/autoload.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;

Jieba::init();
Finalseg::init();
Posseg::init();


// 实词标签
$shici = array(
    'n', 'nr', 'nr1', 'nr2', 'nrj', 'nrf', 'ns', 'nsf', 'nt', 'nz', 'nl', 'ng', 't', 'tg', 's', 'f', 'v', 'vd', 'vn', 'vshi', 'vyou', 'vf', 'vx', 'vi', 'vl', 'vg', 'a', 'ad', 'an', 'ag', 'al', 'b', 'bl', 'z', 'q', 'qv', 'qt'
);
// 数据池
$all_data = array(
    '微博热搜榜' => Data::getWeibo(),
    '百度风云榜' => Data::getBaidu(),
    '澎湃新闻' => Data::getPengpai(),
    '贴吧热议榜' => Data::getTieba(),
    '知乎热搜榜' => Data::getZhihu(),
);
$all_count = array_sum(array_map(function ($val) {
    return $val['count'];
}, $all_data));

// 遍历
$return = array();
$return['data'] = array();
$return['count'] = $all_count;
foreach ($all_data as $from => $data) {
    // 分词统计关键词
    for ($i = 0; $i < $data['count']; $i++) {
        $seg_list = Posseg::cut($data['data'][$i]['str']);
        $seg_weight = $data['data'][$i]['weight'];
        $seg_count = count($seg_list);
        for ($j = 0; $j < $seg_count; $j++) {
            // 只统计实词
            if (in_array($seg_list[$j]['tag'], $shici)) {
                // 已存在则加权重，不存在则赋予权重
                if (array_key_exists($seg_list[$j]['word'], $return['data'])) {
                    $return['data'][$seg_list[$j]['word']] = $return['data'][$seg_list[$j]['word']] + $seg_weight;
                } else {
                    $return['data'][$seg_list[$j]['word']] = $seg_weight;
                }
            }
        }
    }
}

// 逆序输出
arsort($return['data']);
echo json_encode($return, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
