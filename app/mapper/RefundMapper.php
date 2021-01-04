<?php


namespace app\mapper;


use app\model\Refund;
use think\facade\Db;

class RefundMapper extends BaseMapper
{
    protected $model = Refund::class;

    /**
     * 退款列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function refundList($where) {
        # refund_view视图
        return Db::table("refund_view")->alias("rv")
            ->join(["orders_deposit" => "od"], "rv.order_main_id=od.main_order_id", "left")
            ->join(["orders_final_payment" => "ofp"], "ofp.main_order_id=rv.order_main_id", "left")
            ->where("rv.status", "<>", 1)
            ->where($where)
            ->field("rv.*, od.amount_account_id as deposit, ofp.amount_account_id as final")
            ->order("rv.apply_time desc")
            ->select()->toArray();
    }
}
