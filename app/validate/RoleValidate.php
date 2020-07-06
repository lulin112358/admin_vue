<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class RoleValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    "role_name|角色名称" => "require",
        "id|id" => "require",
        "status|状态" => "require"
    ];

    /**
     * 验证场景
     * @var \string[][]
     */
	protected $scene = [
	    "addRole" => ["role_name"],
        "updateRole" => ["role_name", "id"],
        "getOne" => ["id"],
        "delRole" => ["id"],
        "update" => ["id", "status"]
    ];
}
