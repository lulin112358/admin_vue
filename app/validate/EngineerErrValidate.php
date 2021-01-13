<?php


namespace app\validate;


use think\Validate;

class EngineerErrValidate extends Validate
{
    protected $rule = [
        "err|错误原因" => "require",
        "order_id|订单" => "require"
    ];

    protected $scene = [
        "errSubmit" => ["err", "order_id"]
    ];
}
