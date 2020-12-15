<?php


namespace app\validate;


use think\Validate;

class AttendanceValidate extends Validate
{
    protected $rule = [
        "user_id|用户" => "require",
        "id" => "require",
        "field|修改字段" => "require",
        "value|修改值" => "require"
    ];

    protected $scene = [
        "user_attendance" => ["user_id"],
        "update" => ["id", "field", "value"],
        "info" => ["id"]
    ];
}
