<?php


namespace app\validate;


use think\Validate;

class CustomerBiValidate extends Validate
{
    protected $rule = [
        "customer_id|客服id" => "require"
    ];

    protected $scene = [
        "cusOrderPerfDetail" => ["customer_id"]
    ];
}
