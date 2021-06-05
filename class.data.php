<?php
/*
 * @Author: xhboke
 * @Date: 2021-05-28 12:01:16
 * @LastEditTime: 2021-05-28 13:47:44
 * @Description: 获取分词数据
 * @FilePath: class.data.php
 */

class Data
{
    public static function getWeibo()
    {
        // 数据地址
        $url = 'https://m.weibo.cn/api/container/getIndex?containerid=106003type%3D25%26t%3D3%26disable_hot%3D1%26filter_type%3Drealtimehot&title=%E5%BE%AE%E5%8D%9A%E7%83%AD%E6%90%9C&extparam=seat%3D1%26pos%3D0_0%26dgr%3D0%26mi_cid%3D100103%26cate%3D10103%26filter_type%3Drealtimehot%26c_type%3D30%26display_time%3D1618060631&luicode=10000011&lfid=231583';
        // 获取数据
        $data = file_get_contents($url);
        // 转换为Json对象
        $json = json_decode($data)->data->cards[0]->card_group;
        // 获取条数
        $len = count($json);
        // 循环遍历返回
        $return = array();
        $return['count'] = $len;
        $return['data'] = array();
        for ($i = 0; $i < $len; $i++) {
            // 赋予权重
            $return['data'][$i]['weight'] = ($len - $i + 1) / $len;
            // 目标
            $return['data'][$i]['str'] = $json[$i]->desc;
        }
        return $return;
    }

    public static function getBaidu()
    {
        // 数据地址
        $url = 'http://top.baidu.com/buzz?b=1';
        // 获取数据
        $data = file_get_contents($url);
        // 正则匹配
        $reg = '/<a class="list-title" target="_blank" href="http:\/\/www.baidu.com\/baidu\?cl=3&tn=SE_baiduhomet8_jmjb7mjw&rsv_dl=fyb_top&fr=top1000&wd=(.*)<\/a>/';
        preg_match_all($reg, $data, $matches);
        // 获取条数
        $len = count($matches['1']);
        // 输出结果
        for ($x = 0; $x < $len; $x++) {
            $str = substr($matches['1'][$x], strrpos($matches['1'][$x], '>') + 1); //去除多余
            // 赋予权重
            $arr[$x]['weight'] = ($len - $x + 1) / $len;
            // 目标
            $arr[$x]['str'] = $str;
        }
        // 循环遍历返回
        $return = array();
        $return['count'] = $len;
        $return['data'] = self::array_transcoding($arr);
        return $return;
    }

    public static function getPengpai()
    {
        // 数据地址
        $url = 'https://www.thepaper.cn/';
        // 正则匹配
        $reg = '#<a target="_blank" href="(.*?)">(.*?)<\/a>#';
        return self::model($url, $reg, 2, '<ul class="list_hot" id="listhot0">', '</ul>');
    }

    public static function getTieba()
    {
        // 数据地址
        $url = 'http://tieba.baidu.com/hottopic/browse/topicList?res_type=1&red_tag=n0379530944';
        // 正则规则
        $reg = '#class="topic-text">(.*?)<\/a>#';
        return self::model($url, $reg, 1);
    }


    public static function getZhihu()
    {
        // 数据地址
        $url = 'https://www.zhihu.com/billboard';
        // 正则规则
        $reg = '#<div class="HotList-itemTitle">(.*?)<\/div>#';
        return self::model($url, $reg, 1);
    }


    static function model($url, $reg, $index, $leftStr = null, $rightStr = null)
    {
        // 获取数据
        if ($leftStr && $rightStr) $data = self::getSubstr(file_get_contents($url), $leftStr, $rightStr);
        else $data = file_get_contents($url);
        preg_match_all($reg, $data, $matches);
        // 获取条数
        $len = count($matches[$index]);
        // 循环遍历返回
        $return = array();
        $return['count'] = $len;
        $return['data'] = array();
        for ($i = 0; $i < $len; $i++) {
            // 赋予权重
            $return['data'][$i]['weight'] = ($len - $i + 1) / $len;
            // 目标
            $return['data'][$i]['str'] = $matches[1][$i];
        }
        return $return;
    }

    // 取文本中间
    static function getSubstr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        //echo '左边:'.$left;
        $right = strpos($str, $rightStr, $left);
        //echo '<br>右边:'.$right;
        if ($left < 0 or $right < $left) return '';
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }

    // 转换到Utf-8
    static function array_transcoding($array, $out_charset = "utf-8", $in_charset = "gbk")
    {
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $array[$k] = self::array_transcoding($v);
            }
            return $array;
        } else {
            if (is_string($array)) {
                return mb_convert_encoding($array, $out_charset, $in_charset);
            } else {
                return $array;
            }
        }
    }
}
