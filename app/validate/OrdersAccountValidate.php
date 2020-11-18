<?php


namespace app\validate;


use think\Validate;

class OrdersAccountValidate extends Validate
{
    protected $rule = [
        "account_id|接单账号" => "require"
    ];

    protected $scene = [
        "info" => ["account_id"]
    ];
}
