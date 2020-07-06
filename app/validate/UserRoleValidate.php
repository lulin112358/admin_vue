<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class UserRoleValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    "user_name|账户信息" => "require",
        "password|密码" => "require",
        "role_id|角色" => "require",
        "user_id|用户id" => "require",
        "status|状态" => "require"
    ];

    /**
     * 验证场景
     * @var \string[][]
     */
	protected $scene = [
	    "add" => ["user_name", "password", "role_id"],
        "update" => ["user_name", "role_id", "user_id"],
        "del" => ["user_id"],
        "upStatus" => ["id", "status"],
        "getOne" => ["user_id"]
    ];
}
