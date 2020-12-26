<?php


namespace app\validate;


use think\Validate;

class SecretsValidate extends Validate
{
    protected $rule = [
        "secret" => "require",
        "id" => "require"
    ];

    protected $scene = [
        "add" => ["secret"],
        "update" => ["id", "secret"],
        "del" => ["id"],
        "info" => ["id"]
    ];
}
