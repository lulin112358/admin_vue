<?php


namespace app\validate;


use think\Validate;

class AuthFieldsValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "field|字段名" => "require",
        "field_name|列名" => "require",
        "page|所属页面" => "require"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["field", "field_name", "page"],
        "update" => ["id", "field", "field_name", "page"],
        "del" => ["id"]
    ];
}
