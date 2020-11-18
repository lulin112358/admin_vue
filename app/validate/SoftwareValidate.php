<?php


namespace app\validate;


use think\Validate;

class SoftwareValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "software_name|软件名称" => "require|unique:software"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["software_name"],
        "update" => ["id", "software_name"],
        "del" => ["id"]
    ];
}
