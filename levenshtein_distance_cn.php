<?php
/*
 * 字符串相似度算法 levenshtein distance 编辑距离，支持中文UTF-8
 *（字符串1 编辑到 字符串2 所需要修改、移动、删除 字符步数）
 * @author: liukelin 314566990@qq.com
 */

/**
 * 以特殊方式strip字符串，去除or替换特殊字符
 * @param string $str
 * @return string
 */
function special_strip($str) {
    $str = trim($str);
    $old = $str;
    $str = strtolower($str);
    $str = str_replace(' ', '', $str);
    $str = str_replace(' ', '', $str);
    $str = str_replace("'\t'", '', $str);
    $symbolList = array('`', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '=', '_', '+', '[', ']', '{',
        '}', '\\', '|', ';', ':', '\'', '"', ',', '<', '.', '>', '/', '?', '·', '~', '！', '@', '#', '￥', '……',
        '（', '）', '—', '+', '【', '】', '、', '；', '‘', '：', '“', '，', '《', '。', '》', '、', '？', '·', '「',
        '」', "”", "’", '～', '•', '▪', '＃', '…');
    foreach ($symbolList as $symbol) {
        $str = str_replace($symbol, '', $str);
    }
    if (empty($str)) {
        $str = punctuation_zh2en($str);
    }
    $replaceList = array('Ⅰ' => 'I', 'Ⅱ' => 'II', 'Ⅲ' => 'III', 'Ⅳ' => 'IV', 'Ⅴ' => 'V', 'Ⅵ' => 'VI', 'Ⅶ' => 'VII',
        'Ⅷ' => 'VIII', 'Ⅸ' => 'IX', 'Ⅹ' => 'X', 'Ⅺ' => 'XI', 'Ⅻ' => 'XII');
    foreach ($replaceList as $old => $new) {
        $str = str_replace($old, $new, $str);
    }
    return $str;
}

/**
 * 中文标点符号转换为英文标点符号
 * @param $str
 * @return string
 */
function punctuation_zh2en($str) {
    $map = array('·' => '`', '~' => '~', '！' => '!', '@' => '@', '#' => '#', '￥' => '$', '%' => '%',
        '*' => '*', '（' => '(', '）' => ')', '-' => '-', '=' => '=', '+'=> '+', '【' => '[', '｛' => '{',
        '】'=> ']', '｝' => '}', '；'=> ';', '：' => ':', '‘' => '\'', '’' => '\'', '“' => '"', '”' => '"',
        '，' => ',', '《' => '<', '。' => '.', '》' => '>', '？' => '?', '|' => '|');
    $str = trim($str);
    if (empty($str)) {
        return $str;
    }
    foreach ($map as $old => $new) {
        $str = str_replace($old, $new, $str);
    }
    return $str;
}

/**
 * 检查两个字符串是否相等
 * @param $nameA
 * @param $nameB
 * @return bool
 */
function is_str_equal($nameA, $nameB) {
    return special_strip($nameA) == special_strip($nameB);
}

/**
 * 支持中文的最短编辑距离
 * @param $str1
 * @param $str2
 * @param int $costReplace 设定修改一个字符所占步数
 * @param string $encoding
 * @return mixed
 */
function levenshtein_cn($str1, $str2, $costReplace = 1, $encoding= 'UTF-8') {
    $count_same_letter = 0;
    $d = array();
    $mb_len1 = mb_strlen($str1, $encoding);
    $mb_len2 = mb_strlen($str2, $encoding);
    $mb_str1 = mb_string_to_array($str1, $encoding);
    $mb_str2 = mb_string_to_array($str2, $encoding);
    for ($i1 = 0; $i1 < $mb_len1 + 1; $i1++) {
        $d[$i1] = array();
        $d[$i1][0] = $i1;
    }
    for ($i2 = 0; $i2 < $mb_len2 + 1; $i2++) {
        $d[0][$i2] = $i2;
    }
    for ($i1 = 1; $i1 <= $mb_len1; $i1++) {
        for ($i2 = 1; $i2 <= $mb_len2; $i2++) {
            if ($mb_str1[$i1 - 1] === $mb_str2[$i2 - 1]){
                $cost = 0;
                $count_same_letter++;
            }else{
                $cost = $costReplace; //替换
            }
            $d[$i1][$i2] = min($d[$i1 - 1][$i2] + 1, //插入
                $d[$i1][$i2 - 1] + 1, //删除
                $d[$i1 - 1][$i2 - 1] + $cost);
        }
    }
    return $d[$mb_len1][$mb_len2];
}

/**
 * 字符串转数组
 * @param $string
 * @param string $encoding
 * @return array
 */
function mb_string_to_array($string, $encoding = 'UTF-8') {
    $arrayResult = array();
    while ($iLen = mb_strlen($string, $encoding)) {
        array_push($arrayResult, mb_substr($string, 0, 1, $encoding));
        $string = mb_substr($string, 1, $iLen, $encoding);
    }
    return $arrayResult;
}

/**
 * 切割字符串(包含中文UTF8情况)
 * @param string
 * @return array 
 */
function tempaddtext($tempaddtext){
    $cind = 0;
    $arr_cont = array();
    for ($i = 0; $i < strlen($tempaddtext); $i++) {
        if (strlen(substr($tempaddtext, $cind, 1)) > 0) {
            if (ord(substr($tempaddtext, $cind, 1)) < 192) {
                if (substr($tempaddtext, $cind, 1) != " ") {
                    array_push($arr_cont, substr($tempaddtext, $cind, 1));
                }
                $cind++;
            } elseif(ord(substr($tempaddtext, $cind, 1)) < 224) {
                array_push($arr_cont, substr($tempaddtext, $cind, 2));
                $cind+=2;
            } else {
                array_push($arr_cont, substr($tempaddtext, $cind, 3));
                $cind+=3;
            }
        }
    }
    return $arr_cont;
}

/**
 * 计算编辑距离占比 
 * 编辑距离/最大字符串长度
 * @param string 对比字符串1
 * @param string 对比字符串2
 * @return float 
 */
function compare_string($strA, $strB) {
    $lenA = mb_strlen($strA, 'UTF-8');
    $lenB = mb_strlen($strB, 'UTF-8');
    $len = max($lenA, $lenB);
    $similar = levenshtein_cn($strA, $strB, 2);
    return ($similar / $len);
}
/*=========================结束最小编辑距离============================*/



// is_str_equal($strA, $FileName);//字符串是否相等
$strA = 'php是最好的编程语言';
$strB = 'PHP不是最好的编程语言';
$source = compare_string($strA, $strB);
print_r($source); //一般数值 < 0.3 则判定为字符串一致，可规定对比双方字符串长度再使用





