<?php


namespace app\validate;


use think\Validate;

class AuthFieldsValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "field|字段名" => "require",
        "field_name|列名" => "require"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["field", "field_name"],
        "update" => ["id", "field", "field_name"],
        "del" => ["id"]
    ];
}
