<?php


namespace app\admin\service;


use app\admin\workerman\TaskHandle;
use app\mapper\TaskUserMapper;
use app\mapper\UserMapper;
use think\facade\Db;

class TaskUserService extends BaseService
{
    protected $mapper = TaskUserMapper::class;

    private $cycleType = [
        1 => "everyDay",
        2 => "nDay",
        3 => "everyHour",
        4 => "nHour",
        5 => "nMinutes",
        6 => "nWeek",
        7 => "everyMonth"
    ];

    /**
     * 获取任务用户
     * @param $param
     * @return array
     */
    public function taskUsers($param) {
        # 所有用户
        $users = (new UserMapper())->all("name as label, id as `key`");
        # 该任务用户
        $taskUsers = $this->columnBy(["task_id" => $param["task_id"]], "user_id");
        return ["users" => $users, "task_users" => $taskUsers];
    }

    /**
     * 分配任务
     * @param $param
     * @return bool
     */
    public function assignTask($param) {
        Db::startTrans();
        try {
            $res = $this->deleteBy(["task_id" => $param["task_id"]]);
            if ($res === false)
                throw new \Exception("操作失败");
            $addData = [];
            foreach ($param["user_id"] as $k => $v) {
                $item = [
                    "user_id" => $v,
                    "task_id" => $param["task_id"],
                    "create_time" => time(),
                    "update_time" => time(),
                    "last_time" => time()
                ];
                $addData[] = $item;
            }
            $res1 = $this->addAll($addData);
            if (!$res1)
                throw new \Exception("操作失败拉");
            Db::commit();
            return true;
        }catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }


    /**
     * 判断是否需要锁屏
     * @param $where
     * @return array|bool[]|false[]
     */
    public function isNeedLock($where) {
        $data = (new TaskUserMapper())->isNeedLock($where);
        $flag = false;
        foreach ($data as $k => $v) {
            if ($v["type"] == 1) {
                $flag = false;
                if ($v["cycle_count"] > 0) {
                    $flag = date("Y-m-d H:i:00", time()) == date("Y-m-d H:i:00", $v["process_time"]);
                }
            }else {
                if ($v["cycle_count"] > 1) {
                    $flag = true;
                }else {
                    $flag = (new TaskHandle())->{$this->cycleType[$v["cycle_type"]]}($v);
                }
            }
            if ($flag === true)
                return ["lock" => $flag, "content" => $v["task_content"], "title" => $v["task_name"]];
        }
        return ["lock" => $flag];
    }
}
