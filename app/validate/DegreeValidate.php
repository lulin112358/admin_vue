<?php


namespace app\validate;


use think\Validate;

class DegreeValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "degree_name|学位名称" => "require|unique:degree"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["degree_name"],
        "update" => ["id", "degree_name"],
        "del" => ["id"]
    ];
}
