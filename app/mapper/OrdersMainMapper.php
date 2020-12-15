<?php


namespace app\mapper;


use app\model\OrdersMain;
use think\facade\Db;

class OrdersMainMapper extends BaseMapper
{
    protected $model = OrdersMain::class;

    /**
     * 来源数量排序
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountSortData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->where($where)
            ->field("count(a.id) as account_id_count, a.id as account_id")
            ->group("a.id")
            ->order("account_id_count desc")
            ->select()->toArray();
    }

    /**
     * 来源金额排序
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountAmountSortData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->where($where)
            ->field("sum(om.total_amount) as total_amount, a.id as account_id")
            ->group("a.id")
            ->order("total_amount desc")
            ->select()->toArray();
    }

    /**
     * 客服接单BI统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->join(["user" => "u"], "u.id=om.customer_id", "right")
            ->where($where)
            ->field("om.customer_id, a.id as account_id, om.total_amount, u.name")
            ->select()->toArray();
    }

    /**
     * 填写主订单的主要信息
     *
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function mainOrders($limit) {
        return OrdersMain::with(["deposits" => function($query) {
            return $query->order("create_time asc")->limit(1);
        }])->where(["customer_id" => request()->uid])
            ->limit($limit)
            ->select()->toArray();
    }

    /**
     * 自动填充数据
     *
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function autoFillData($where) {
        $map = [];
        if (request()->uid != 1) {
            $rowAuth = row_auth();
            $map = ["om.order_account_id" => ($rowAuth["account_id"]??[]),
                "om.origin_id" => ($rowAuth["origin_id"]??[]),
                "od.amount_account_id" => ($rowAuth["amount_account_id"]??[])];
        }

        return Db::table("orders_main")->alias("om")
            ->join(["orders_deposit" => "od"], "om.id=od.main_order_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["orders_account" => "oa1"], "oa1.id=om.wechat_id")
            ->join(["user" => "u"], "u.id=om.customer_manager")
            ->join(["category" => "c"], "c.id=om.category_id")
            ->where([["o.status", "<>", 0], ["oa.status", "<>", 0], ["od.status", "<>", 0], ["oa1.is_wechat", "=", 1], ["u.status", "=", 1]])
            ->where(["om.customer_id" => request()->uid])
            ->where($map)
            ->where($where)
            ->field("od.main_order_id, od.create_time, om.origin_id, om.order_account_id, om.customer_manager, om.id, 
            concat(om.origin_id, '-', om.order_account_id, '-', od.amount_account_id) as auto,
             om.category_id, om.wechat_id, od.amount_account_id")
            ->order("od.create_time asc")
            ->group("om.id")
            ->limit(50)
            ->select()->toArray();
    }
}
