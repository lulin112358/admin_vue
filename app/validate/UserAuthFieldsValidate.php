<?php


namespace app\validate;


use think\Validate;

class UserAuthFieldsValidate extends Validate
{
    protected $rule = [
        "uid|用户" => "require",
        "field_id|列" => "require"
    ];

    protected $scene = [
        "list" => ["uid"],
        "assign" => ["field_id", "uid"]
    ];
}
