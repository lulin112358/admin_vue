<?php


namespace app\validate;


use think\Validate;

class SchoolValidate extends Validate
{
    protected $rule = [
        "school_name|学校名称" => "require"
    ];

    protected $scene = [
        "search" => ["school_name"]
    ];
}
