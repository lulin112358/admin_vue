<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class MenuValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'name|标题' => 'require',
        'path|路径' => 'require',
        'id|id' => 'require'
    ];


    /**
     * 定义验证场景
     *
     * @var array
     */
	protected $scene = [
        'addMenu' => ['name', 'path'],
        'saveMenu' => ['name', 'path', 'id'],
        'delMenu' => ['id'],
        'getOne' => ['id'],
        'update' => ['id']
    ];
}
