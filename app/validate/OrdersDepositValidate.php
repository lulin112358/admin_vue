<?php


namespace app\validate;


use think\Validate;

class OrdersDepositValidate extends Validate
{
    protected $rule = [
        "main_order_id|订单id" => "require"
    ];

    protected $scene = [
        "list" => ["main_order_id"]
    ];
}
