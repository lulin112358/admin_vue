<?php


namespace app\validate;


use think\Validate;

class UserExtendValidate extends Validate
{
    protected $rule = [
        "user_id|用户" => "require",
        "age|年龄" => "require",
        "sex|性别" => "require",
        "id_card|身份证号" => "require",
        "entry_time|入职时间" => "require",
        "degree_id|学历" => "require",
        "hometown|籍贯" => "require",
        "current_residence|现住地" => "require"
    ];
    protected $scene = [
        "user_extend" => ["user_id"],
        "update" => ["user_id", "age", "sex", "id_card", "entry_time", "degree_id", "hometown", "current_residence"]
    ];
}
