<?php


namespace app\validate;


use think\Validate;

class UserEngineerValidate extends Validate
{
    protected $rule = [
        "user_name|用户名" => "require",
        "password|密码" => "require",
        "old_pwd|原密码" => "require",
        "pwd|新密码" => "require"
    ];

    protected $scene = [
        "login" => ["user_name", "password"],
        "updatePassword" => ["old_pwd", "pwd"]
    ];
}
