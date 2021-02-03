<?php


namespace app\validate;


use think\Validate;

class TaskValidate extends Validate
{
    protected $rule = [
        "type|任务类型" => "require",
        "task_name|任务名称" => "require",
        "cycle_type|执行周期" => "requireIf:type,2",
        "cycle_config|周期配置" => "requireIf:type,2",
        "task_content|任务内容" => "require",
        "id" => "require",
        "status|状态" => "require"
    ];

    protected $scene = [
        "add" => ["type", "task_name", "cycle_type", "cycle_config", "task_content"],
        "update" => ["type", "task_name", "cycle_type", "cycle_config", "task_content", "id"],
        "delete" => ["id"],
        "info" => ["id"],
        "status" => ["id", "status"]
    ];
}
