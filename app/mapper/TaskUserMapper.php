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

    /**
     * needAudit
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function needAudit($where) {
        return Db::table("task_user")->alias("tu")
            ->join(["task" => "t"], "t.id=tu.task_id")
            ->join(["user" => "u"], "u.id=tu.user_id")
            ->where($where)
            ->field("tu.id, tu.user_id, tu.task_id, t.task_name, u.name, tu.cycle_count")
            ->select()->toArray();
    }

    /**
     * 获取任务类型
     * @param $where
     * @return mixed
     */
    public function getType($where) {
        return Db::table("task_user")->alias("tu")
            ->join(["task" => "t"], "t.id=tu.task_id")
            ->where($where)
            ->value("t.type");
    }
}
