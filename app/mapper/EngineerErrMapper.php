<?php


namespace app\mapper;


use app\model\EngineerErr;
use think\facade\Db;

class EngineerErrMapper extends BaseMapper
{
    protected $model = EngineerErr::class;

    /**
     * 报错订单
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function errOrders($status) {
        return Db::table("engineer_err")->alias("ee")
            ->join(["orders" => "o"], "o.id=ee.order_id")
            ->join(["orders_main" => "om"], "om.id=o.main_order_id")
            ->join(["user" => "u"], "u.id=ee.deal_user", "left")
            ->join(["user" => "u1"], "u1.id=o.biller", "left")
            ->where(["ee.status" => $status])
            ->field("ee.id, ee.create_time, ee.update_time, ee.err, o.order_sn, om.customer_manager, o.biller, u.name, u1.name as biller_name")
            ->order("ee.update_time desc")
            ->paginate(50)->items();
    }

    /**
     * 获取错误订单发单人
     * @param $param
     * @return mixed
     */
    public function orderBiller($param) {
        return Db::table("engineer_err")->alias("ee")
            ->join(["orders" => "o"], "o.id=ee.order_id")
            ->where(["ee.id" => $param["id"]])
            ->value("o.biller");
    }
}
