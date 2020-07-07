<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class AccountCateValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    "cate_name|类型名称" => "require",
        "id|id" => "require"
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $scene = [
        "add" => ["cate_name"],
        "update" => ["cate_name", "id"],
        "del" => ["id"],
        "one" => ["id"]
    ];
}
