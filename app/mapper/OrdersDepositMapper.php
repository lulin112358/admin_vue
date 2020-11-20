<?php


namespace app\mapper;


use app\model\OrdersDeposit;

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
            ->field("id, amount_account_id, change_deposit, deposit, create_time")->select()->toArray();
    }
}
