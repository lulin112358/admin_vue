<?php
// 这是系统自动生成的公共文件

/**
 * 树形结构
 * @param $data
 * @return array
 */
function generateTree($data, $children_name = 'children'){
    $items = array();
    foreach($data as $v){
        $items[$v['id']] = $v;
    }
    $tree = array();
    foreach($items as $k => $item){
        if(isset($items[$item['pid']])){
            $items[$item['pid']][$children_name][] = &$items[$k];
        }else{
            $tree[] = &$items[$k];
        }
    }
    return $tree;
}

# 生成展示菜单
function generateMenu($data) {
    $items = array();
    foreach($data as $v){
        $v["index"] = trim($v["path"], "/");
        $v["title"] = $v["name"];
        $items[$v['id']] = $v;
    }
    $tree = array();
    foreach($items as $k => $item){
        if(isset($items[$item['pid']])){
            $items[$item['pid']]["subs"][] = &$items[$k];
        }else{
            $tree[] = &$items[$k];
        }
    }
    return $tree;
}

function generateTreeText($cate , $lefthtml = '— — ' , $pid=0 , $lvl=0, $leftpin=0) {
    $arr=array();
    foreach ($cate as $v){
        if($v['pid']==$pid){
            $v['name']=str_repeat($lefthtml,$lvl).$v['name'];
            $arr[]=$v;
            $arr= array_merge($arr,generateTreeText($cate,$lefthtml,$v['id'],$lvl+1 , $leftpin+20));
        }
    }
    return $arr;
}

/**
 * 处理入账数据
 * @param $data
 * @param $type
 * @return array
 */
function processAmount($data, $type, $field = 'name') {
    $retData = [];
    foreach ($data as $k => $v) {
        $retData[$v[$field]] = $retData[$v[$field]]??0;
        $retData[$v[$field]] += $v[$type];
    }
    return $retData;
}
