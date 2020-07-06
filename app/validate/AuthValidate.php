<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class AuthValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    "role_id|角色" => "require",
    ];

    /**
     * 验证场景
     * @var \string[][]
     */
    protected $scene = [
        "save" => ["role_id"],
        "getRule" => ["role_id"]
    ];
}
