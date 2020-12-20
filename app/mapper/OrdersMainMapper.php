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
            ->join(["account" => "a"], "a.id=om.order_account_id")
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
        # bi_amount_view视图
        return Db::table("bi_amount_view")
            ->where(function ($query) use ($where) {
                $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                    ->whereOr([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
            })
            ->fieldRaw("(ifnull(sum(deposit), 0) + ifnull(sum(final_payment), 0)) as total_amount, account_id")
            ->group("account_id")
            ->order("total_amount desc")->select()->toArray();
    }

    /**
     * 客服兵力部署数量BI统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["account" => "a"], "a.id=om.order_account_id")
            ->join(["user" => "u"], "u.id=om.customer_id", "right")
            ->where($where)
            ->field("om.customer_id, a.id as account_id, u.name")
            ->select()->toArray();
    }

    /**
     * 客服兵力部署金额BI统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerAmountData($where) {
        return Db::table("bi_amount_view")
            ->where($where)
            ->fieldRaw("(ifnull(deposit, 0) + ifnull(final_payment, 0)) as total_amount, account_id, customer_id, customer_name as name")
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
    public function customerOrderData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["user" => "u"], "u.id=om.customer_id", "right outer")
            ->where($where)
            ->field("om.customer_id, u.name, om.category_id")
            ->select()->toArray();
    }

    /**
     * bi金额统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function amountBiData($where) {
        return Db::table("bi_amount_view")
            ->where($where)
            ->fieldRaw("deposit_time, final_payment_time, refund_time, origin_id, main_order_id, market_user,  deposit, final_payment, refund_amount, account_id, customer_id, customer_name as name")
            ->select()->toArray();
    }

    /**
     * 市场人员Bi统计数据
     * @param $where
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function marketUserBiData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["user" => "u"], "u.id=om.customer_id", "right")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["user" => "u1"], "u1.id=o.market_user")
            ->join(["orders" => "od"], "od.main_order_id=om.id")
            ->where(["od.is_split" => 0])
            ->where($where)
            ->field("om.id, o.market_user as market_user_id, u1.name as market_user, od.check_fee, u.name, od.manuscript_fee, od.check_fee, o.commission_ratio")
            ->select()->toArray();
    }

    /**
     * 市场人员详细信息Bi统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function marketUserOriginBiData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "od"], "od.main_order_id=om.id")
            ->where(["od.is_split" => 0])
            ->where($where)
            ->field("om.id, om.category_id, o.origin_name, om.origin_id, od.check_fee, od.manuscript_fee, od.check_fee, o.commission_ratio")
            ->select()->toArray();
    }

    /**
     * 来源BI统计数据
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function originBiData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "od"], "od.main_order_id=om.id")
            ->where(["od.is_split" => 0])
            ->where($where)
            ->field("om.id, o.origin_name, om.origin_id, od.check_fee, od.manuscript_fee, od.check_fee, o.commission_ratio")
            ->select()->toArray();
    }

    /**
     * 来源详情BI统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function originDetailBiData($where) {
        # orders_view视图
        return Db::table("orders_view")->where($where)
            ->where("is_split", "=", 0)
            ->field("origin_id, origin_name, total_amount, check_fee, manuscript_fee, commission_ratio, refund_amount, deposit, final_payment, create_time")
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
            ->join(["account" => "a"], "a.id=om.order_account_id")
            ->join(["account" => "a1"], "a1.id=om.wechat_id")
            ->join(["user" => "u"], "u.id=om.customer_manager")
            ->join(["category" => "c"], "c.id=om.category_id")
            ->where([["o.status", "<>", 0], ["a.status", "<>", 0], ["od.status", "<>", 0], ["a1.is_wechat", "=", 1], ["u.status", "=", 1]])
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
