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
    public function manuscripts() {
        return Db::table("orders")->alias("o")
            ->join(["engineer" => "e"], "e.id=o.engineer_id")
            ->where([["o.status", "=", 3], ["o.engineer_id", "<>", 0]])
            ->field("o.id, o.engineer_id, sum(o.manuscript_fee) as manuscript_fee, sum(o.settlemented) as settlemented, sum(o.deduction) as deduction, o.delivery_time, e.qq_nickname")
            ->group("o.engineer_id")->select()->toArray();
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
    public function engineerDetail($engineer_id) {
        return Db::table("orders")->alias("o")
            ->join(["orders_main" => "om"], "om.id=o.main_order_id")
            ->join(["category" => "c"], "c.id=om.category_id")
            ->where([["status", "=", 3], ["engineer_id", "=", $engineer_id]])
            ->field("o.id, o.order_sn, o.status, o.delivery_time, o.manuscript_fee, o.settlemented, o.deduction, c.cate_name")
            ->select()->toArray();
    }
}