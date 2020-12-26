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
        # bi_amount_view视图
        return Db::table("bi_amount_view")
            ->where(function ($query) use ($where) {
                $query->where([["deposit_time", ">=", $where[0][2]], ["deposit_time", "<=", $where[1][2]]])
                    ->whereOr([["final_payment_time", ">=", $where[0][2]], ["final_payment_time", "<=", $where[1][2]]]);
            })
            ->fieldRaw("deposit, final_payment, account_id, deposit_time, final_payment_time")
            ->select()->toArray();
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
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
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
            ->fieldRaw("deposit, final_payment, deposit_time, final_payment_time, account_id, customer_id, customer_name as name")
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
            ->join(["user" => "u"], "u.id=om.customer_id", "right")
            ->where($where)
            ->field("om.customer_id, u.name, om.category_id, om.create_time")
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
            ->field("order_sn, origin_name, deposit_time, final_payment_time, refund_time, origin_id, main_order_id, market_user,  deposit, final_payment, refund_amount, account_id, customer_id, customer_name as name")
            ->select()->toArray();
    }

    /**
     * bi金额统计数据带有订单编号
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function amountBiDataWithOrderSn($where) {
        return Db::table("bi_amount_view")->alias("bav")
            ->join(["orders" => "o"], "o.main_order_id=bav.main_order_id")
            ->where($where)
            ->field("bav.origin_name, o.order_sn, bav.deposit_time, bav.final_payment_time, bav.refund_time, bav.origin_id, bav.main_order_id, bav.market_user,  bav.deposit, bav.final_payment, bav.refund_amount, bav.account_id, bav.customer_id, bav.customer_name as name")
            ->group("o.main_order_id")
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
            ->join(["user" => "u"], "u.id=om.customer_id", 'left')
            ->join(["origin" => "o"], "o.id=om.origin_id", 'left')
            ->join(["user" => "u1"], "u1.id=o.market_user", 'left')
            ->join(["orders" => "od"], "od.main_order_id=om.id", 'left')
            ->join(["orders_deposit" => "ode"], "ode.main_order_id=om.id", "left")
            ->join(["orders_final_payment" => "ofp"], "ofp.main_order_id=om.id", "left")
            ->join(["refund" => "r"], "r.order_main_id=om.id", "left")
            ->where(["od.is_split" => 0])
            ->where($where)
            ->where(function ($query) {
                $query->where("ode.status", "=", 1)->whereOr("ode.status", "=", null);
            })
            ->where(function ($query) {
                $query->where("ofp.status", "=", 1)->whereOr("ofp.status", "=", null);
            })
            ->field("ofp.final_payment, ode.deposit, r.refund_amount, om.id, o.market_user as market_user_id, u1.name as market_user, od.check_fee, u.name, od.manuscript_fee, od.check_fee, o.commission_ratio")
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
            ->join(["orders_deposit" => "ode"], "ode.main_order_id=om.id", "left")
            ->join(["orders_final_payment" => "ofp"], "ofp.main_order_id=om.id", "left")
            ->join(["refund" => "r"], "r.order_main_id=om.id", "left")
            ->where(["od.is_split" => 0])
            ->where($where)
            ->where(function ($query) {
                $query->where("ode.status", "=", 1)->whereOr("ode.status", "=", null);
            })
            ->where(function ($query) {
                $query->where("ofp.status", "=", 1)->whereOr("ofp.status", "=", null);
            })
            ->field("ofp.final_payment, ode.deposit, r.refund_amount, om.id, om.category_id, o.origin_name, om.origin_id, od.check_fee, od.manuscript_fee, od.check_fee, o.commission_ratio")
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
            ->join(["orders_deposit" => "ode"], "ode.main_order_id=om.id", "left")
            ->join(["orders_final_payment" => "ofp"], "ofp.main_order_id=om.id", "left")
            ->where(["od.is_split" => 0])
            ->where($where)
            ->where(function ($query) {
                $query->where("ode.status", "=", 1)->whereOr("ode.status", "=", null);
            })
            ->where(function ($query) {
                $query->where("ofp.status", "=", 1)->whereOr("ofp.status", "=", null);
            })
            ->field("ofp.final_payment, ode.deposit, om.id, o.origin_name, om.origin_id, od.check_fee, od.manuscript_fee, od.check_fee, o.commission_ratio")
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
            $map = ["a.id" => ($rowAuth["account_id"]??[]),
                "om.origin_id" => ($rowAuth["origin_id"]??[]),
                "od.amount_account_id" => ($rowAuth["amount_account_id"]??[])];
        }

        return Db::table("orders_main")->alias("om")
            ->join(["orders_deposit" => "od"], "om.id=od.main_order_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->join(["orders_account" => "oa1"], "oa1.id=om.wechat_id")
            ->join(["account" => "a1"], "a1.id=oa1.account_id")
            ->join(["user" => "u"], "u.id=om.customer_manager")
            ->join(["category" => "c"], "c.id=om.category_id")
            ->where([["o.status", "<>", 0], ["oa.status", "<>", 0], ["od.status", "<>", 0], ["a1.is_wechat", "=", 1], ["u.status", "=", 1]])
            ->where(["om.customer_id" => request()->uid])
            ->where($map)
            ->where($where)
            ->field("od.main_order_id, od.create_time, om.origin_id, om.customer_manager, om.id, 
            oa.id as order_account_id,
            concat(om.origin_id, '-', oa.id, '-', od.amount_account_id) as auto,
             om.category_id, oa1.id as wechat_id, od.amount_account_id")
            ->order("od.create_time asc")
            ->group("om.id")
            ->limit(50)
            ->select()->toArray();
    }
}
