<?php


namespace app\validate;


use think\Validate;

class AccountValidate extends Validate
{
    protected $rule = [
        "account|接单账号" => "require",
        "nickname|接单昵称" => "require",
        "account_cate|接单账号类型" => "require",
        "account_id|接单账号id" => "require"
    ];

    protected $scene = [
        "add" => ["account", "nickname", "account_cate"],
        "update" => ["account", "nickname", "account_cate", "account_id"],
        "del" => ["account_id"]
    ];
}
