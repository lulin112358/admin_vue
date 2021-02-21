<?php


namespace app\mapper;


use app\model\OrdersAccount;
use think\facade\Db;

class OrdersAccountMapper extends BaseMapper
{
    protected $model = OrdersAccount::class;

    /**
     * 获取接单账号信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function ordersAccount() {
        return Db::table("orders_account")->alias("oa")
            ->join(["account" => "a"], "a.id=oa.account_id")
            ->field("oa.nickname, a.account, oa.id")
            ->select()->toArray();
    }
}
