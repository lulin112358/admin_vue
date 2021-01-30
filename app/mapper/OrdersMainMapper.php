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
     * 来源金额排序定金数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountSortDepositData($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->where($where)
            ->field("od.change_deposit as deposit, a.id as account_id")
            ->select()->toArray();
    }

    /**
     * 来源金额排序尾款数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function accountSortFinalData($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->where($where)
            ->field("od.change_amount as final_payment, a.id as account_id")
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
     * 客服兵力部署定金金额BI统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerDepositData($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where($where)
            ->field("od.change_deposit as deposit, a.id as account_id, u.name, om.customer_id")
            ->select()->toArray();
    }

    /**
     * 客服兵力部署尾款金额BI统计数据
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function customerFinalData($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where($where)
            ->field("od.change_amount as final_payment, a.id as account_id, u.name, om.customer_id")
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
            ->field("om.customer_id, u.name, om.category_id, om.create_time, u.department")
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
    public function customerOrderDeposit($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where($where)
            ->field("od.change_deposit as deposit, u.name, om. customer_id, u.department")
            ->select()->toArray();
    }
    public function customerOrderFinal($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where($where)
            ->field("od.change_amount as final_payment, u.name, om. customer_id, u.department")
            ->select()->toArray();
    }
    public function customerRefund($where) {
        return Db::table("refund")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.order_main_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where($where)
            ->field("od.refund_amount, u.name, om. customer_id, u.department")
            ->select()->toArray();
    }

//    public function amountBiData($where) {
//        return Db::table("bi_amount_view")
//            ->where($where)
//            ->field("order_sn, origin_name, deposit_time, final_payment_time, refund_time, origin_id, main_order_id, market_user,  deposit, final_payment, refund_amount, account_id, customer_id, customer_name as name")
//            ->select()->toArray();
//    }
    public function originDetailDeposit($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "ods"], "ods.main_order_id=od.main_order_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("u.name, od.change_deposit as deposit, od.create_time as amount_time, ods.order_sn")
            ->select()->toArray();
    }
    public function originDetailFinal($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "ods"], "ods.main_order_id=od.main_order_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("u.name, od.change_amount as final_payment, od.create_time as amount_time, ods.order_sn")
            ->select()->toArray();
    }
    public function originDetailRefund($where) {
        return Db::table("refund")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.order_main_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "ods"], "ods.main_order_id=od.order_main_id")
            ->join(["user" => "u"], "u.id=om.customer_id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("u.name, od.refund_amount, od.refund_time as amount_time, ods.order_sn")
            ->select()->toArray();
    }


    public function marketDetailDeposit($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["orders" => "ods"], "ods.main_order_id=od.main_order_id", "left")
            ->where($where)
            ->where(["ods.is_split" => 0])
            ->field("om.id as main_order_id, ods.is_split, od.change_deposit as deposit, o.origin_name, om.origin_id")
            ->select()->toArray();
    }
    public function marketDetailFinal($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["orders" => "ods"], "ods.main_order_id=od.main_order_id", "left")
            ->where($where)
            ->where(["ods.is_split" => 0])
            ->field("om.id as main_order_id, ods.is_split, od.change_amount as final_payment, o.origin_name, om.origin_id")
            ->select()->toArray();
    }
    public function marketDetailRefund($where) {
        return Db::table("refund")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.order_main_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["orders" => "ods"], "ods.main_order_id=od.order_main_id", "left")
            ->where($where)
            ->where(["ods.is_split" => 0])
            ->field("om.id as main_order_id, ods.is_split, od.refund_amount, o.origin_name, om.origin_id")
            ->select()->toArray();
    }

    public function marketDeposit($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["user" => "u"], "u.id=o.market_user", "left")
            ->where($where)
            ->field("om.id as main_order_id, om.create_time, u.name as market_user_name, o.market_user, od.change_deposit as deposit")
            ->select()->toArray();
    }
    public function marketFinal($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["user" => "u"], "u.id=o.market_user", "left")
            ->where($where)
            ->field("om.id as main_order_id, om.create_time, u.name as market_user_name, o.market_user, od.change_amount as final_payment")
            ->select()->toArray();
    }
    public function marketRefund($where) {
        return Db::table("refund")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.order_main_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["user" => "u"], "u.id=o.market_user", "left")
            ->where($where)
            ->field("om.id as main_order_id, om.create_time, u.name as market_user_name, o.market_user, od.refund_amount")
            ->select()->toArray();
    }
    public function marketGrossProfit($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders_deposit" => "od"], "od.main_order_id=om.id", "left")
            ->join(["orders_final_payment" => "ofp"], "ofp.main_order_id=om.id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["user" => "u"], "u.id=o.market_user", "left")
            ->where($where)
            ->where(function ($query) {
                $query->where(["od.status" => 1])->whereOr(["ofp.status" => 1]);
            })
            ->field("om.id as main_order_id, od.deposit, ofp.final_payment, u.name as market_user_name, o.commission_ratio")
            ->select()->toArray();
    }
    public function marketFee($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders" => "o"], "o.main_order_id=om.id")
            ->join(["origin" => "og"], "og.id=om.origin_id")
            ->join(["user" => "u"], "u.id=og.market_user")
            ->where($where)
            ->field("o.check_fee, o.manuscript_fee, om.id as main_order_id, u.name as market_user_name, og.market_user")
            ->select()->toArray();
    }
    public function marketDetailGrossProfit($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders_deposit" => "od"], "od.main_order_id=om.id", "left")
            ->join(["orders_final_payment" => "ofp"], "ofp.main_order_id=om.id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->where($where)
            ->where(function ($query) {
                $query->where(["od.status" => 1])->whereOr(["ofp.status" => 1]);
            })
            ->field("om.id as main_order_id, od.deposit, ofp.final_payment, o.commission_ratio, o.origin_name")
            ->select()->toArray();
    }
    public function marketDetailFee($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders" => "o"], "o.main_order_id=om.id")
            ->join(["origin" => "og"], "og.id=om.origin_id")
            ->where($where)
            ->field("o.check_fee, o.manuscript_fee, om.id as main_order_id, og.origin_name")
            ->select()->toArray();
    }


    public function customerDetailDeposit($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["orders" => "ods"], "ods.main_order_id=om.id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("od.change_deposit as deposit, o.origin_name, ods.order_sn, od.create_time, om.category_id")
            ->select()->toArray();
    }
    public function customerDetailFinal($where) {
        return Db::table("orders_final_payment")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["orders" => "ods"], "ods.main_order_id=om.id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("od.change_amount as final_payment, o.origin_name, ods.order_sn, od.create_time, om.category_id")
            ->select()->toArray();
    }
    public function customerDetailRefund($where) {
        return Db::table("refund")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.order_main_id")
            ->join(["orders" => "ods"], "ods.main_order_id=om.id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("od.refund_amount, od.refund_time as create_time, o.origin_name, ods.order_sn, om.category_id")
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
            ->join(["origin" => "o"], "om.origin_id=o.id")
            ->join(["user" => "u"], "u.id=o.market_user")
            ->join(["orders" => "ods"], "ods.main_order_id=om.id")
            ->where($where)
            ->where(["ods.is_split" => 0])
            ->field("om.id as main_order_id, om.origin_id, o.origin_name, ods.is_split, o.market_user as market_user_id, u.name as market_user_name, ods.check_fee, ods.manuscript_fee, o.commission_ratio")
            ->select()->toArray();
    }


    public function originData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "ods"], "ods.main_order_id=om.id")
            ->where($where)
            ->where(["ods.is_split" => 0])
            ->field("ods.is_split, o.origin_name, om.origin_id")
            ->select()->toArray();
    }
    public function marketDetailData($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["orders" => "ods"], "ods.main_order_id=om.id")
            ->where(["ods.is_split" => 0])
            ->where($where)
            ->field("om.id as main_order_id, o.origin_name")->select()->toArray();
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
            ->join(["orders_deposit" => "od"], "om.id=od.main_order_id", "left")
            ->join(["origin" => "o"], "o.id=om.origin_id", "left")
            ->join(["orders_account" => "oa"], "oa.id=om.order_account_id", "left")
            ->join(["account" => "a"], "a.id=oa.account_id", "left")
            ->join(["orders_account" => "oa1"], "oa1.id=om.wechat_id", "left")
            ->join(["account" => "a1"], "a1.id=oa1.account_id", "left")
            ->join(["user" => "u"], "u.id=om.customer_manager", "left")
            ->join(["category" => "c"], "c.id=om.category_id", "left")
            ->where([["o.status", "<>", 0], ["oa.status", "<>", 0], ["od.status", "<>", 0], ["a1.is_wechat", "=", 1], ["u.status", "=", 1]])
            ->where(["om.customer_id" => request()->uid])
            ->where($map)
            ->where($where)
            ->field("od.main_order_id, od.create_time, om.origin_id, om.customer_manager, om.id, 
            oa.id as order_account_id,
            concat(om.origin_id, '-', oa.id, '-', od.amount_account_id) as auto,
             om.category_id, oa1.id as wechat_id, od.amount_account_id, om.school_id, om.degree_id")
            ->order("od.create_time asc")
            ->group("om.id")
            ->limit(50)
            ->select()->toArray();
    }

    /**
     * 文档下载
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function downDoc($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["orders" => "o"], "om.id=o.main_order_id")
            ->where($where)
            ->where(["is_split" => 0])
            ->field("om.file, o.order_sn, o.require")
            ->select()->toArray();
    }

    /**
     * 获取订单来源是否是中介
     * @param $where
     * @return mixed
     */
    public function isIntermediary($where) {
        return Db::table("orders_main")->alias("om")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->where($where)
            ->value("o.is_intermediary");
    }
}
