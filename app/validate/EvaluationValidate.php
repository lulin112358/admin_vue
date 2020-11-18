<?php


namespace app\validate;


use think\Validate;

class EvaluationValidate extends Validate
{
    protected $rule = [
        "id" => "require",
        "evaluation" => "require|unique:evaluation"
    ];

    protected $scene = [
        "info" => ["id"],
        "add" => ["evaluation"],
        "update" => ["id", "evaluation"],
        "del" => ["id"]
    ];
}
