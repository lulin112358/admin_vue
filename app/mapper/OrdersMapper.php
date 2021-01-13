<?php


namespace app\mapper;


use app\model\Orders;
use think\facade\Db;

class OrdersMapper extends BaseMapper
{
    protected $model = Orders::class;

    /**
     * 获取工程师稿费结算情况
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function manuscripts($where) {
        return Db::table("orders")->alias("o")
            ->join(["engineer" => "e"], "e.id=o.engineer_id")
            ->where([["o.status", "=", 3], ["o.engineer_id", "<>", 0]])
            ->where($where)
            ->field("o.id, o.engineer_id, o.manuscript_fee, o.settlemented, o.deduction, o.actual_delivery_time, o.delivery_time, e.qq_nickname, e.contact_qq, e.collection_code")
            ->select()->toArray();
    }

    /**
     * 获取工程师已核对可结算稿费
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function canSettlement($where) {
        return Db::table("orders")->alias("o")
            ->join(["engineer" => "e"], "e.id=o.engineer_id")
            ->where([["o.status", "=", 3], ["o.engineer_id", "<>", 0], ["o.is_check", "=", 1], ["settlemented", "<", Db::raw("manuscript_fee")]])
            ->where($where)
            ->field("o.id, o.engineer_id, o.manuscript_fee, o.settlemented, o.deduction, o.actual_delivery_time, o.delivery_time, e.qq_nickname, e.contact_qq, e.collection_code")
            ->select()->toArray();
    }

    /**
     * 获取工程师稿费详情
     *
     * @param $engineer_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function engineerDetail($engineer_id, $where) {
        return Db::table("orders")->alias("o")
            ->join(["orders_main" => "om"], "om.id=o.main_order_id")
            ->join(["category" => "c"], "c.id=om.category_id")
            ->where([["o.status", "=", 3], ["o.engineer_id", "=", $engineer_id]])
            ->where($where)
            ->where(function ($query) {
                $query->where("o.manuscript_fee", "<>", Db::raw("o.settlemented"))
                    ->whereOr("o.manuscript_fee", "=", 0);
            })
//            ->where([["o.manuscript_fee", "<>", Db::raw("o.settlemented")]])
            ->field("o.require, o.id, o.order_sn, o.status, o.delivery_time, o.actual_delivery_time, o.manuscript_fee, o.settlemented, o.deduction, c.cate_name, o.is_check")
            ->select()->toArray();
    }

    /**
     * 获取工程师以核定可结算稿费详情
     * @param $engineer_id
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function canSettlementDetail($engineer_id, $where) {
        return Db::table("orders")->alias("o")
            ->join(["orders_main" => "om"], "om.id=o.main_order_id")
            ->join(["category" => "c"], "c.id=om.category_id")
            ->where([["o.status", "=", 3], ["o.engineer_id", "=", $engineer_id], ["o.is_check", "=", 1]])
            ->where($where)
            ->where([["o.manuscript_fee", "<>", Db::raw("o.settlemented")]])
            ->field("o.require, o.id, o.order_sn, o.status, o.delivery_time, o.actual_delivery_time, o.manuscript_fee, o.settlemented, o.deduction, c.cate_name, o.is_check")
            ->select()->toArray();
    }

    /**
     * 工程师稿费
     * @param $engineer_id
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function engineerManuscript($engineer_id, $where) {
        return Db::table("orders")->alias("o")
            ->join(["orders_main" => "om"], "om.id=o.main_order_id", "left")
            ->join(["category" => "c"], "c.id=om.category_id", "left")
            ->join(["engineer_err" => "ee"], "ee.order_id=o.id", "left")
            ->where([["o.engineer_id", "=", $engineer_id]])
            ->where($where)
            ->where(function ($query) {
                $query->where("o.manuscript_fee", "<>", Db::raw("o.settlemented"))
                    ->whereOr("o.manuscript_fee", "=", 0);
            })
//            ->where([["o.manuscript_fee", "<>", Db::raw("o.settlemented")]])
            ->fieldRaw("if(actual_delivery_time=0, delivery_time, if(delivery_time>actual_delivery_time,actual_delivery_time,delivery_time)) as time, ee.err, ee.status as err_status, o.require, o.id, o.order_sn, o.status, o.delivery_time, o.actual_delivery_time, o.manuscript_fee, o.settlemented, o.deduction, c.cate_name, o.is_check")
            ->orderRaw("if(o.is_check=1, 1, 0)")
            ->order("time")
            ->select()->toArray();
    }


    /**
     * 指定工程师稿费信息
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function engineerManuscripts($where) {
        return Db::table("orders")->alias("o")
            ->join(["engineer" => "e"], "e.id=o.engineer_id")
            ->where([["o.status", "=", 3]])
            ->where($where)
            ->field("sum(o.manuscript_fee) as manuscript_fee, sum(o.settlemented) as settlemented")
            ->select()->toArray();
    }

    /**
     * 工程师稿费详情
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function engineerManuscriptsDetail($where) {
        return Db::table("orders")->alias("o")
            ->join(["engineer" => "e"], "e.id=o.engineer_id", "left")
            ->join([Db::table("settlement_log")->order("id desc")->buildSql() => "sl"], "sl.order_id=o.id", "left")
            ->where([["o.status", "=", 3]])
            ->where($where)
            ->field("sl.settlement_fee, o.order_sn, o.manuscript_fee, o.require, o.actual_delivery_time, o.status, max(sl.create_time) as settlement_time")
            ->group("o.id")
            ->select()->toArray();
    }

    /**
     * 订单详情
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderInfo($where) {
        return Db::table("orders")->alias("o")
            ->join([Db::table("settlement_log")->order("id desc")->buildSql() => "sl"], "sl.order_id=o.id", "left")
            ->where(["o.status" => 3])
            ->where($where)
            ->field("o.require, o.delivery_time, o.manuscript_fee, o.settlemented, sl.create_time as settlement_time, o.order_sn")
            ->group("o.id")
            ->select()->toArray();
    }
}
