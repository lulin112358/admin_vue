<?php


namespace app\validate;


use think\Validate;

class RoleAuthFieldsValidate extends Validate
{
    protected $rule = [
        "role_id|角色" => "require",
        "field_id|列" => "require"
    ];

    protected $scene = [
        "info" => ["role_id"],
        "assign" => ["field_id", "role_id"]
    ];
}
