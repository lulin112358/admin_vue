<?php


namespace app\mapper;


use app\model\RefundLog;
use think\facade\Db;

class RefundLogMapper extends BaseMapper
{
    protected $model = RefundLog::class;

    /**
     * 退款记录
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundLogs($where) {
        return Db::table("refund_log")->alias("rl")
            ->join(["refund_view" => "rv"], "rl.refund_id=rv.id")
            ->join(["user" => "u"], "u.id=rl.refund_user")
            ->join(["orders_main" => "om"], "om.id=rv.order_main_id")
            ->join(["user" => "u1"], "u1.id=om.customer_id")
            ->where($where)
            ->field("rl.create_time, rl.refund_account, rl.actual_refund_amount, u.name, rv.*, u1.name as order_cus_name")
            ->select()->toArray();
    }
}
