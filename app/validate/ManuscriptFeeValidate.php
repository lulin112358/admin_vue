<?php


namespace app\validate;


use think\Validate;

class ManuscriptFeeValidate extends Validate
{
    protected $rule = [
        "engineer_id|编辑id" => "require"
    ];

    protected $scene = [
        "detail" => ["engineer_id"]
    ];
}
