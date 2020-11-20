<?php


namespace app\validate;


use think\Validate;

class EngineerValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "status|状态" => "require"
    ];

    protected $scene = [
        "updateStatus" => ["id", "status"]
    ];
}
