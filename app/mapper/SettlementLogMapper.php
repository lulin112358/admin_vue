<?php


namespace app\mapper;


use app\model\SettlementLog;
use think\facade\Db;

class SettlementLogMapper extends BaseMapper
{
    protected $model = SettlementLog::class;

    /**
     * 获取全部结算记录
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function settlementLogs($where) {
        return Db::table("settlement_log")->alias("sl")
            ->join(["orders" => "o"], "sl.order_id=o.id")
            ->join(["user" => "u"], "sl.settlement_user=u.id")
            ->where($where)
            ->field("u.name as settlement_user_name, sl.id, sl.settlement_fee, sl.create_time, o.order_sn")
            ->select()->toArray();
    }
}
