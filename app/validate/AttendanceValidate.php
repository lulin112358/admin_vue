<?php


namespace app\validate;


use think\Validate;

class AttendanceValidate extends Validate
{
    protected $rule = [
        "user_id|用户" => "require",
    ];

    protected $scene = [
        "user_attendance" => ["user_id"]
    ];
}
