<?php


namespace app\validate;


use think\Validate;

class RoleAuthRowValidate extends Validate
{
    protected $rule = [
        "role_id|角色" => "require",
        "row_info|行" => "require"
    ];

    protected $scene = [
        "info" => ["role_id"],
        "assign" => ["row_info", "role_id"]
    ];
}
