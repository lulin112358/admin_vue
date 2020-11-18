<?php


namespace app\validate;


use think\Validate;

class TendencyValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "tendency_name|倾向类型" => "require|unique:tendency"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["tendency_name"],
        "update" => ["id", "tendency_name"],
        "del" => ["id"]
    ];
}
