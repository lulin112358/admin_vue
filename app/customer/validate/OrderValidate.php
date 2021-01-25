<?php


namespace app\customer\validate;


use think\Validate;

class OrderValidate extends Validate
{
    protected $rule = [
        "main_order_id|订单号" => "require",
        "total_amount|总价" => "require",
        "deposit|定金" => "require",
        "require|要求" => "require",
        "customer_contact|联系电话" => "require"
    ];

    protected $scene = [
        "update" => ["order_id", "require", "customer_contact"]
    ];
}
