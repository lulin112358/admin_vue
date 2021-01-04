<?php


namespace app\mapper;


use app\model\AmountAccount;
use think\facade\Db;

class AmountAccountMapper extends BaseMapper
{
    protected $model = AmountAccount::class;

    /**
     * 收款账号排序列表
     * @return array
     */
    public function accountSort() {
        return Db::table("orders_deposit")->alias("od")
            ->join(["orders_main" => "om"], "om.id=od.main_order_id")
            ->where(["om.customer_id" => request()->uid])
            ->column("od.amount_account_id");
    }
}
