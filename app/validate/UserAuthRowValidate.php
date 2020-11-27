<?php


namespace app\validate;


use think\Validate;

class UserAuthRowValidate extends Validate
{
    protected $rule = [
        "uid|用户" => "require",
        "row_info|行权限" => "require"
    ];

    protected $scene = [
        "info" => ["uid"],
        "assign" => ["row_info", "uid"]
    ];
}
