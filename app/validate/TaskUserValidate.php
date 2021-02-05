<?php


namespace app\validate;


use think\Validate;

class TaskUserValidate extends Validate
{
    protected $rule = [
        "task_id|任务" => "require",
        "user_id|用户" => "require",
        "id" => "require"
    ];

    protected $scene = [
        "taskUser" => ["task_id"],
        "assignTask" => ["task_id", "user_id"],
        "auditTask" => ["id"]
    ];
}
