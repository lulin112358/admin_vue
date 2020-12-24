<?php


namespace app\validate;


use think\Validate;

class OrdersDepositValidate extends Validate
{
    protected $rule = [
        "main_order_id|订单id" => "require",
        "id" => "require",
        "type_id|类型" => "require",
        "amount_account_id|收款账号" => "require"
    ];

    protected $scene = [
        "list" => ["main_order_id"],
        "update_account" => ["id", "type_id", "amount_account_id"]
    ];
}
