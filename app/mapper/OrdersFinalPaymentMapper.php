<?php


namespace app\mapper;


use app\model\OrdersFinalPayment;

class OrdersFinalPaymentMapper extends BaseMapper
{
    protected $model = OrdersFinalPayment::class;

    /**
     * 尾款列表
     *
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function finalPayment($data) {
        return OrdersFinalPayment::with(["amountAccounts" => function($query) {
            return $query->field("account, id");
        }])->where(["main_order_id" => $data["main_order_id"]])
            ->field("id, amount_account_id, change_amount, final_payment as payment, create_time")->select()->toArray();
    }
}
