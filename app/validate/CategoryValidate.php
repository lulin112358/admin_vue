<?php


namespace app\validate;


use think\Validate;

class CategoryValidate extends Validate
{
    protected $rule = [
        "pid|所属业务" => "require",
        "cate_name|业务名称" => "require",
        "id" => "require"
    ];

    protected $scene = [
        "save" => ["pid", "cate_name"],
        "del" => ["id"]
    ];
}