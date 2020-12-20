<?php


namespace app\validate;


use think\Validate;

class IpWhiteValidate extends Validate
{
    protected $rule = [
        "ip" => "require",
        "id" => "require"
    ];

    protected $scene = [
        "add" => ["ip"],
        "update" => ["id", "ip"],
        "del" => ["id"],
        "info" => ["id"]
    ];
}
