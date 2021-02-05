<?php


namespace app\mapper;


use app\model\TaskUser;
use think\facade\Db;

class TaskUserMapper extends BaseMapper
{
    protected $model = TaskUser::class;

    /**
     * 获取所有需要监控的任务
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function listenTasks() {
        return Db::table("task_user")->alias("tu")
            ->join(["task" => "t"], "t.id=tu.task_id")
            ->where(["t.status" => 1])
            ->field("tu.cycle_count, tu.id, tu.last_time, tu.task_id, tu.user_id, t.task_name, t.task_content, t.type, t.cycle_type, t.cycle_config, t.process_time")
            ->select()->toArray();
    }

    public function isNeedLock($where) {
        return Db::table("task_user")->alias("tu")
            ->join(["task" => "t"], "t.id=tu.task_id")
            ->where(["t.status" => 1])
            ->where($where)
            ->field("tu.cycle_count, tu.id, tu.last_time, tu.task_id, tu.user_id, t.task_name, t.task_content, t.type, t.cycle_type, t.cycle_config, t.process_time")
            ->select()->toArray();
    }
}
