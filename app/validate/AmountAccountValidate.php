<?php


namespace app\validate;


use think\Validate;

class AmountAccountValidate extends Validate
{
    protected $rule = [
        "account|收款账户" => "require|unique:amount_account",
        "id" => "require"
    ];

    protected $scene = [
        "add" => ["account"],
        "update" => ["account", "id"],
        "info" => ["id"],
        "del" => ["id"]
    ];
}
