<?php


namespace app\mapper;


use app\model\OrdersDeposit;
use think\facade\Db;

class OrdersDepositMapper extends BaseMapper
{
    protected $model = OrdersDeposit::class;


    /**
     * 定金列表
     *
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deposit($data) {
        return OrdersDeposit::with(["amountAccounts" => function($query) {
            return $query->field("account, id");
        }])->where(["main_order_id" => $data["main_order_id"]])
            ->field("id, amount_account_id, change_deposit as change_amount, deposit as payment, create_time")->select()->toArray();
    }

    /**
     * 来源对账定金
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function depositWithOrigin($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["amount_account" => "aa"], "aa.id=od.amount_account_id")
            ->where($where)
            ->field("aa.account, od.amount_account_id, od.change_deposit as change_amount")
            ->select()->toArray();
    }

    /**
     * 对账定金详情
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function depositRecWithOrderSn($where) {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->join(["origin" => "o"], "o.id=om.origin_id")
            ->join(["amount_account" => "aa"], "aa.id=od.amount_account_id")
            ->join(["orders" => "or"], "or.main_order_id=om.id")
            ->join(["user" => "u"], "u.id=od.payee_id", "left")
            ->join(["user" => "u1"], "u1.id=om.customer_id", "left")
            ->where($where)
            ->where(["or.is_split" => 0])
            ->field("aa.account, od.change_deposit as change_amount, or.order_sn, od.create_time, u.name, u1.name as customer_name")
            ->select()->toArray();
    }
}
