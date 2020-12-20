<?php
// 应用公共文件
use think\facade\Db;

/**
 * 获取客户端真实IP
 * @return mixed|string
 */
function get_real_ip(){
    $ip=false;
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ips=explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if($ip){ array_unshift($ips, $ip); $ip=FALSE; }
        for ($i=0; $i < count($ips); $i++){
            if(!preg_match ('/^(10│172.16│192.168)./', $ips[$i])){
                $ip=$ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/**
 * 下载文件
 * @param $filePath
 * @param $saveAsFileName
 */
function download_file($filePath,$saveAsFileName){

    // 清空缓冲区并关闭输出缓冲
    ob_end_clean();

    //r: 以只读方式打开，b: 强制使用二进制模式
    $fileHandle=fopen($filePath,"rb");
    if($fileHandle===false){
        echo "Can not find file: $filePath\n";
        exit;
    }

    Header("Content-type: application/octet-stream");
    Header("Content-Transfer-Encoding: binary");
    Header("Accept-Ranges: bytes");
    Header("Content-Length: ".filesize($filePath));
    Header("Content-Disposition: attachment; filename=\"$saveAsFileName\"");

    while(!feof($fileHandle)) {

        //从文件指针 handle 读取最多 length 个字节
        echo fread($fileHandle, 32768);
    }
    fclose($fileHandle);
}

/**
 * 获取用户可见列
 * @return array
 */
function column_auth($page) {
    if (request()->uid == 1) {
        return Db::table("auth_fields")->where(["page" => $page])->column("field");
    }
    $columns_id = (new \app\admin\service\UserAuthFieldsService())->userAuthFields(["uid" => request()->uid]);
    $column = Db::table("auth_fields")->where(["id" => $columns_id, "page" => $page])->column("field");
    # 添加必须字段id
    $column[] = "id";
    return $column;
}

/**
 * 获取用户可见行
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 */
function row_auth() {
    $rows = (new \app\admin\service\UserAuthRowService())->userAuthRow(["uid" => request()->uid]);
    $ret = [];
    foreach ($rows as $k => $v) {
        $ret[explode("/", $v)[0]][] = (int)explode("/", $v)[1];
    }
    return $ret;
}

/**
 * 设置可见列
 *
 * @param $data
 * @param $visible
 * @return mixed
 */
function visible($data, $visible) {
    foreach ($data as $index => $item) {
        $keys = array_keys($item);
        foreach ($keys as $k => $v) {
            if (!in_array($v, $visible)) {
                unset($data[$index][$v]);
            }
        }
    }
    return $data;
}


