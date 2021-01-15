<?php


namespace app\validate;


use think\Validate;

class PartTimeValidate extends Validate
{
    protected $rule = [
        "user_id|用户" => "require",
        "salary|薪水" => "require",
        "id" => "require",
        "field|字段" => "require",
        "value|值" => "require"
    ];

    protected $scene = [
        "detail" => ["user_id"],
        "updateSalary" => ["user_id", "salary"],
        "updatePartTime" => ["id", "field", "value"]
    ];
}
