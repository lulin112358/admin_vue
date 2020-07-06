<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class UserValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'user_name|用户名' => 'require',
        'password|密码' => 'require'
    ];


	protected $scene = [
	    'login' => ['user_name', 'password']
    ];
}
