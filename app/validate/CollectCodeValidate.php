<?php


namespace app\validate;


use think\Validate;

class CollectCodeValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "collect_code|收款码" => "require",
        "title|收款码名称" => "require",
    ];

    protected $scene = [
        "add" => ["collect_code", "title"],
        "update" => ["id", "collect_code", "title"],
        "del" => ["id"]
    ];
}
