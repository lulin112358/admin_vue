<?php


namespace app\mapper;


use app\model\RefundTurnDownLog;
use think\facade\Db;

class RefundTurnDownLogMapper extends BaseMapper
{
    protected $model = RefundTurnDownLog::class;

    /**
     * 驳回
     * @param $param
     * @return bool
     */
    public function turnDown($param) {
        Db::startTrans();
        try {
            $data = [
                "refund_id" => $param["id"],
                "turn_down_reason" => $param["turndown_reason"],
                "user_id" => request()->uid,
                "create_time" => time()
            ];
            $res = $this->add($data);
            if (!$res)
                throw new \Exception("操作失败");
            $res = (new RefundMapper())->updateWhere(["id" => $param["id"]], ["is_turn_down" => 1]);
            if ($res === false)
                throw new \Exception("操作失败啦");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 驳回记录
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function turnDownLog($where) {
        return Db::table("refund_turn_down_log")->alias("rtdl")
            ->join(["refund_view" => "rv"], "rtdl.refund_id=rv.id")
            ->join(["user" => "u"], "u.id=rtdl.user_id")
            ->join(["orders_main" => "om"], "om.id=rv.order_main_id")
            ->join(["user" => "u1"], "u1.id=om.customer_id")
            ->where($where)
            ->field("rtdl.*, u.name, rv.*, u1.name as order_cus_name")
            ->select()->toArray();
    }
}
