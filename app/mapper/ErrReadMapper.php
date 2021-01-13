<?php


namespace app\mapper;


use app\model\ErrRead;
use think\facade\Db;

class ErrReadMapper extends BaseMapper
{
    protected $model = ErrRead::class;

    /**
     * 获取已读信息
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function alreadyRead() {
        return Db::table("err_read")->alias("er")
            ->join(["engineer_err" => "ee"], "ee.id=er.err_id")
            ->join(["orders" => "o"], "o.id=ee.order_id")
            ->join(["user" => "u"], "u.id=er.user_id", "left")
            ->where(["er.user_id" => request()->uid])
            ->field("ee.id, ee.create_time, ee.update_time, ee.err, o.order_sn, o.biller, u.name")
            ->order("er.create_time desc")
            ->paginate(50)->items();
    }
}
