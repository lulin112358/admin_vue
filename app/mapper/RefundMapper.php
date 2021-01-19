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
            ->join(["orders_main" => "om"], "om.id=rv.order_main_id", "left")
            ->join(["user" => "u"], "u.id=om.customer_id", "left")
            ->where("rv.status", "<>", 1)
            ->where("rv.is_turn_down", "=", 0)
            ->where($where)
            ->field("rv.*, od.amount_account_id as deposit, ofp.amount_account_id as final, u.name")
            ->order("rv.apply_time desc")
            ->select()->toArray();
    }

    /**
     * 退款被驳回列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function turnDownList($where) {
        return Db::table("refund_turn_down_log")->alias("rtdl")
            ->join(["refund_view" => "rv"], "rtdl.refund_id=rv.id")
            ->where($where)->field("rv.*, rtdl.create_time, rtdl.turn_down_reason")->select()->toArray();
    }

    /**
     * 修改退款信息
     * @param $param
     * @return bool
     */
    public function updateRefund($param) {
        Db::startTrans();
        try {
            # 修改退款信息
            $res = $this->updateBy($param);
            if ($res === false)
                throw new \Exception("修改失败");
            # 修改驳回状态
            $res = (new RefundTurnDownLogMapper())->updateWhere(["refund_id" => $param["id"]], ["status" => 0]);
            if ($res === false)
                throw new \Exception("修改失败");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }
}
