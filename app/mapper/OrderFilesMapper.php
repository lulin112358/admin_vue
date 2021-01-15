<?php


namespace app\mapper;


use app\model\OrderFiles;
use think\facade\Db;

class OrderFilesMapper extends BaseMapper
{
    protected $model = OrderFiles::class;

    /**
     * 下载列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function docList($where) {
        return Db::table("order_files")->alias("of")
            ->join(["orders" => "o"], "o.id=of.order_id")
            ->join(["user" => "u"], "u.id=of.user_id")
            ->where($where)
            ->field("of.*, o.order_sn, u.name")->select()->toArray();
    }

    public function downDoc($where) {
        return Db::table("order_files")->alias("of")
            ->join(["orders" => "o"], "o.id=of.order_id")
            ->where($where)
            ->field("o.order_sn, o.require, of.file, of.filename")
            ->select()->toArray();
    }
}
