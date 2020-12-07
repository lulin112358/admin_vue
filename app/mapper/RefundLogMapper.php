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
            ->where($where)
            ->field("rl.create_time, rl.actual_refund_amount, u.name, rv.*")
            ->select()->toArray();
    }
}
